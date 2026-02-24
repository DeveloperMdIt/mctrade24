<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use Plugin\jtl_paypal_commerce\PPC\BackendUIsettings;

/**
 * Class ResetSettingsController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class ResetSettingsController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $settings        = [];
        $defaultSettings = BackendUIsettings::getDefaultSettings();
        foreach ($defaultSettings as $index => $value) {
            if ($index === 'clientID' || $index === 'clientSecret') {
                continue;
            }
            $settings[$index] = $value['value'];
        }
        $this->getConfig()->saveConfigItems($settings);
        $this->getLogger()->write(\LOGLEVEL_DEBUG, 'Reset UI configuration.');
    }
}
