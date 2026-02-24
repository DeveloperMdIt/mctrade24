<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;

/**
 * Class PatchAmount
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PatchAmount extends Patch
{
    /**
     * PatchAmount constructor.
     * @param Amount $amount
     * @param string $op
     */
    public function __construct(Amount $amount, string $op = self::OP_REPLACE)
    {
        parent::__construct(
            "/purchase_units/@reference_id=='" . PurchaseUnit::REFERENCE_DEFAULT . "'/amount",
            $amount,
            $op
        );
    }
}
