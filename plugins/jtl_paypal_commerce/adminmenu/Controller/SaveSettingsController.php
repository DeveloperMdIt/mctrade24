<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;

/**
 * Class SaveSettingsController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class SaveSettingsController extends AbstractController
{
    public function run(): void
    {
        $settings = Request::postVar('settings', []);
        $plugin   = $this->getPlugin();
        $config   = $this->getConfig();

        if (!isset($settings['paymentMethods_enabled'])) {
            $settings['paymentMethods_enabled'] = 'false';
        }

        if (isset($settings)) {
            $helper = Helper::getInstance($plugin);
            foreach ($plugin->getPaymentMethods()->getMethods() as $paymentMethod) {
                $ppcPayment = $helper->getPaymentFromID($paymentMethod->getMethodID());
                if ($ppcPayment !== null) {
                    $ppcPayment->validatePaymentConfiguration($paymentMethod, $settings);
                }
            }
            $config->saveConfigItems($settings);
        }

        $this->redirectSelf();
    }
}
