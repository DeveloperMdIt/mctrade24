<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use JTL\Shop;
use Plugin\jtl_paypal_commerce\PPC\Order\Capture;

/**
 * Class AssignedPayment
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class AssignedPayment
{
    /** @var Capture */
    private Capture $capture;

    /** @var int|null */
    private ?int $incommingId;

    /**
     * AssignedPayment constructor
     * @param Capture  $capture
     * @param int|null $incommingPayment
     */
    private function __construct(Capture $capture, ?int $incommingPayment)
    {
        $this->capture     = $capture;
        $this->incommingId = $incommingPayment;
    }

    /**
     * @param Capture $capture
     * @return static
     */
    public static function load(Capture $capture): self
    {
        $db = Shop::Container()->getDB();
        $iP = $db->getSingleInt(
            'SELECT kZahlungseingang
                FROM tzahlungseingang
                WHERE cHinweis = :captureId',
            'kZahlungseingang',
            ['captureId' => $capture->getId()]
        );

        return new self($capture, $iP);
    }

    /**
     * @return Capture
     */
    public function getCapture(): Capture
    {
        return $this->capture;
    }

    /**
     * @return int
     */
    public function getIncommingId(): int
    {
        return $this->incommingId ?? 0;
    }

    /**
     * @return bool
     */
    public function hasIncommingPayment(): bool
    {
        return $this->getIncommingId() > 0;
    }
}
