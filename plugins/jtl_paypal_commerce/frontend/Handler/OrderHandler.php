<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend\Handler;

use DateTime;
use Exception;
use JTL\Checkout\Bestellung;
use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Settings;
use Plugin\jtl_paypal_commerce\Repositories\TrackingRepository;

/**
 * Class OrderHandler
 * @package Plugin\jtl_paypal_commerce\frontend\Handler
 */
class OrderHandler
{
    private readonly PluginInterface $plugin;
    private readonly TrackingRepository $repository;

    private bool $trackingActive;

    /**
     * OrderHandler constructor
     */
    public function __construct(
        PluginInterface $plugin,
        ?DbInterface $db = null,
        ?TrackingRepository $repository = null
    ) {
        $this->plugin     = $plugin;
        $this->repository = $repository ?? new TrackingRepository($db ?? Shop::Container()->getDB());
        $config           = PPCHelper::getConfiguration($this->plugin);

        $settingName          = Settings::BACKEND_SETTINGS_SECTION_GENERAL . '_shipmenttracking';
        $this->trackingActive = $config->getPrefixedConfigItem($settingName, 'N') === 'Y';
    }

    public function saveOrder(array $args): void
    {
        /** @var Bestellung $order */
        $order  = $args['oBestellung'];
        $helper = Helper::getInstance($this->plugin);

        $payMethod = $helper->getPaymentFromID((int)$order->kZahlungsart);
        if ($payMethod !== null) {
            $payMethod->finalizeOrderInDB($order);
        }
    }

    public function updateOrder(array $args): void
    {
        if (!$this->trackingActive) {
            return;
        }

        $wawi = $args['oBestellungWawi'];
        try {
            $sent = empty($wawi->dVersandt) ? null : new DateTime($wawi->dVersandt);
        } catch (Exception) {
            $sent = new DateTime();
        }
        $now = new DateTime();

        if ($sent === null || $now->diff($sent)->format('%a') > \BESTELLUNG_VERSANDBESTAETIGUNG_MAX_TAGE) {
            $this->repository->deleteByTrackingId((int)$wawi->kBestellung, $wawi->cIdentCode ?? '');

            return;
        }

        $this->repository->updateShipmentDate((int)$wawi->kBestellung, $wawi->cIdentCode, $sent->format('Y-m-d'));
    }

    public function addTracking(array $args): void
    {
        if (!$this->trackingActive) {
            return;
        }

        $shipping = $args['shipping'];

        if (empty($shipping->cIdentCode) || empty($shipping->cLogistik)) {
            return;
        }

        $trackingData = $this->repository->getTrackingByDeliveryNote(
            (int)$shipping->kLieferschein,
            \array_map(static function ($method) {
                return $method->getMethodID();
            }, $this->plugin->getPaymentMethods()->getMethods()),
            $shipping
        );
        if ($trackingData !== null) {
            $this->repository->updateOrInsert($trackingData);
        }
    }
}
