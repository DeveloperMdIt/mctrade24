<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;

/**
 * Class PatchShippingAddress
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PatchShippingAddress extends Patch
{
    /**
     * PatchShippingAddress constructor.
     * @param Address $address
     * @param string  $op
     */
    public function __construct(Address $address, string $op = self::OP_REPLACE)
    {
        parent::__construct(
            "/purchase_units/@reference_id=='" . PurchaseUnit::REFERENCE_DEFAULT . "'/shipping/address",
            $address,
            $op
        );
    }
}
