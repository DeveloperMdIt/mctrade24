<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\CronJob;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JTL\Cache\JTLCacheInterface;
use JTL\Cron\Job;
use JTL\Cron\JobHydrator;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\DB\DbInterface;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking\Carrier;
use Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking\Tracking;
use Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking\TrackingRequest;
use Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking\TrackingResponse;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseItem;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\Repositories\TrackingRepository;
use Psr\Log\LoggerInterface;

/**
 * Class CronJob
 * @package Plugin\jtl_paypal_commerce\CronJob
 */
class CronJob extends Job
{
    private const MAX_SENT_COUNT     = 5;
    private const SUCCESS_SENT_COUNT = 99;
    private const LIMIT_TO_SEND      = 10;

    private readonly TrackingRepository $repository;

    private readonly PPCClient $ppcClient;

    /**
     * @inheritDoc
     */
    public function __construct(
        DbInterface $db,
        LoggerInterface $logger,
        JobHydrator $hydrator,
        JTLCacheInterface $cache,
        ?TrackingRepository $repository = null,
        ?PPCClient $ppcClient = null,
    ) {
        parent::__construct($db, $logger, $hydrator, $cache);

        $this->repository = $repository ?? new TrackingRepository($db);
        $this->ppcClient  = $ppcClient ?? new PPCClient(PPCHelper::getEnvironment());
    }

    /**
     * @inheritDoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);
        $this->garbageCollect();
        $this->setFinished($this->sendTrackingInfo());

        return $this;
    }

    /**
     * @return void
     */
    private function garbageCollect(): void
    {
        $this->repository->deleteByDate(\BESTELLUNG_VERSANDBESTAETIGUNG_MAX_TAGE, self::MAX_SENT_COUNT);
    }

    /**
     * @throws GuzzleException
     * @throws AuthorizationException
     * @throws PPCRequestException
     */
    private function sendTrackingRequest(object $trackingInfo): ?TrackingResponse
    {
        if (($trackingInfo->transaction_id ?? '') === '' || ($trackingInfo->capture_id ?? '') === '') {
            return null;
        }

        $tracking = (new Tracking())
            ->setOrderId($trackingInfo->transaction_id)
            ->setCaptureId($trackingInfo->capture_id)
            ->setTrackingNumber($trackingInfo->tracking_id)
            ->setCarrier(
                $trackingInfo->carrier === Carrier::CARRIER_OTHER
                    ? $trackingInfo->carrier_name
                    : $trackingInfo->carrier
            );
        if ((int)$trackingInfo->delivery_note_id > 0) {
            $items = $this->repository->getItemsToTrack((int)$trackingInfo->delivery_note_id);
            foreach ($items as $item) {
                $tracking->addItem((new PurchaseItem())
                    ->setName(PurchaseUnit::getNameWithQuantity(
                        (float)$item->fAnzahl,
                        ',',
                        $item->cEinheit,
                        $item->cName
                    ))
                    ->setSKU($item->cArtNr)
                    ->setQuantity(1));
            }
        }

        return new TrackingResponse(
            $this->ppcClient->send(new TrackingRequest(Token::getInstance()->getToken(), $tracking))
        );
    }

    public function markItemAsSent(int $stateId): void
    {
        $this->repository->updateSentState($stateId, self::SUCCESS_SENT_COUNT);
    }

    public function markItemAsFailed(int $stateId, string $stateInfo = ''): void
    {
        $this->repository->updateFailedState($stateId, $stateInfo);
    }

    private function removeItem(int $stateId): void
    {
        $this->repository->delete($stateId);
    }

    private function sendTrackingInfo(): bool
    {
        $rowCounter    = 0;
        $trackingInfos = $this->repository->getItemsToSend(self::MAX_SENT_COUNT, self::LIMIT_TO_SEND, $rowCounter);

        if ($rowCounter === 0 || $trackingInfos->count() === 0) {
            return true;
        }

        foreach ($trackingInfos as $trackingInfo) {
            try {
                $trackingResponse = $this->sendTrackingRequest($trackingInfo);
            } catch (Exception | GuzzleException $e) {
                $msg = $e instanceof PPCRequestException ? $e->getResponse()->getMessage() : $e->getMessage();
                if ((int)$trackingInfo->status_sent >= self::MAX_SENT_COUNT) {
                    $this->removeItem((int)$trackingInfo->id);
                } else {
                    $this->markItemAsFailed((int)$trackingInfo->id, PPCHelper::shortenStr($msg, 512));
                }
                $this->logger->error('TrackersRequest failed (' . $msg . ')');

                continue;
            }

            if ($trackingResponse === null) {
                $this->removeItem((int)$trackingInfo->id);
            } elseif ($trackingResponse->getId() === $trackingInfo->transaction_id) {
                $this->markItemAsSent((int)$trackingInfo->id);
            }
        }

        return $rowCounter <= self::LIMIT_TO_SEND;
    }
}
