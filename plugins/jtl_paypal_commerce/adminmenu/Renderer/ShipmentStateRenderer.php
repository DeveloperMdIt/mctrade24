<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Renderer;

use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\adminmenu\CarrierMapping;
use Plugin\jtl_paypal_commerce\CronJob\CronHelper;
use Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking\Carrier;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class ShipmentStateRenderer
 * @package Plugin\jtl_paypal_commerce\adminmenu\Renderer
 */
class ShipmentStateRenderer extends AbstractRenderer
{
    /**
     * @inheritDoc
     */
    public function render(JTLSmarty $smarty): void
    {
        $this->checkRendering();

        $config      = $this->getConfig();
        $settingName = Settings::BACKEND_SETTINGS_SECTION_GENERAL . '_shipmenttracking';
        $cron        = CronHelper::getCron();
        $frequency   = 6;
        $cronLink    = '';
        Shop::Container()->getGetText()->loadAdminLocale('pages/cron');

        if ($cron !== null) {
            $frequency = $cron->getFrequency();
            $cronLink  = ' (<a class="small" href="' . Shop::getAdminURL() . '/cron' . '">'
                . \__($cron->getType()) . '</a>)';
        }
        $smarty
            ->assign('settingDescription', \__(
                'Die Versandinformationen umfassen Tracking-ID, Versandart und Versanddatum',
                $frequency,
                $cronLink
            ))
            ->assign('settingName', $settingName)
            ->assign('trackingEnabled', $config->getPrefixedConfigItem($settingName, 'N'))
            ->assign('mappingItems', (new CarrierMapping(Shop::Container()->getDB()))->getMappings())
            ->assign('paypalCarriers', Carrier::CARRIERS);
    }
}
