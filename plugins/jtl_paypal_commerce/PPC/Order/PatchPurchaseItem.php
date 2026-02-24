<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class PatchPurchaseItem
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PatchPurchaseItem extends Patch
{
    /**
     * PatchPurchaseItem constructor.
     * @param string $item
     * @param mixed  $value
     * @param string $op
     */
    public function __construct(string $item, $value, string $op = self::OP_REPLACE)
    {
        parent::__construct(
            "/purchase_units/@reference_id=='" . PurchaseUnit::REFERENCE_DEFAULT . "'/" . $item,
            new JSON($value),
            $op
        );
    }
}
