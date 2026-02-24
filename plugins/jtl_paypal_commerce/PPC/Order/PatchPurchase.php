<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class PatchPurchase
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PatchPurchase extends Patch
{
    /**
     * PatchPurchase constructor
     * @param mixed  $value
     * @param string $op
     */
    public function __construct($value, string $op = self::OP_REPLACE)
    {
        parent::__construct(
            "/purchase_units/@reference_id=='" . PurchaseUnit::REFERENCE_DEFAULT . "'",
            new JSON($value),
            $op
        );
    }
}
