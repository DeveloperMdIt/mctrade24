<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\PPCP;

use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderCreateResponse;

/**
 * Class PPCPPUIOrder
 * @package Plugin\jtl_paypal_commerce\paymentmethod\PPCP
 */
class PPCPPUIOrder extends PPCPOrder
{
    /**
     * @inheritDoc
     */
    protected function apiCreateOrder(
        Order $ppOrder,
        string $bnCode = MerchantCredentials::BNCODE_CHECKOUT
    ): OrderCreateResponse {
        return parent::apiCreateOrder($ppOrder, $bnCode)->setExpectedResponseCode([200, 201]);
    }
}
