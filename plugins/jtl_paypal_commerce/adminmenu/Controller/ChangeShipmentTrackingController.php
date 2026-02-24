<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\CronJob\CronHelper;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class ChangeShipmentTrackingController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class ChangeShipmentTrackingController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $settingName = Settings::BACKEND_SETTINGS_SECTION_GENERAL . '_shipmenttracking';
        $config      = $this->getConfig();

        if ($config->getPrefixedConfigItem($settingName, 'N') === 'N') {
            $config->saveConfigItems([$settingName => 'Y']);
            CronHelper::createCron(Request::postInt('frequency', 6), Request::postVar('time', '2:00'));
        } else {
            $config->saveConfigItems([$settingName => 'N']);
            CronHelper::dropCron();
        }
    }
}
