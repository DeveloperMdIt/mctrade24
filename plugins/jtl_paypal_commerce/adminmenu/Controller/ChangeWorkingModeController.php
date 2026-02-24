<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

/**
 * Class ChangeWorkingModeController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class ChangeWorkingModeController extends AbstractController
{
    public function run(): void
    {
        $configValues = $this->getConfig()->getConfigValues();
        $configValues->setWorkingMode($configValues->getWorkingMode() === 'sandbox'
            ? 'production' : 'sandbox');

        $this->redirectSelf();
    }
}
