<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use JTL\Smarty\JTLSmarty;

/**
 * Class AccountPage
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class AccountPage extends AbstractPayPalPage
{
    public const STEP_OVERVIEW = 1;

    public function render(JTLSmarty $smarty): void
    {
        // todo: placeholder for SHOP-8157
    }
}
