<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class PatchShippingName
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PatchShippingName extends Patch
{
    /**
     * PatchShippingName constructor.
     * @param string $name
     * @param string $op
     */
    public function __construct(string $name, string $op = self::OP_REPLACE)
    {
        parent::__construct(
            "/purchase_units/@reference_id=='" . PurchaseUnit::REFERENCE_DEFAULT . "'/shipping/name",
            new JSON((object)['full_name' => $name]),
            $op
        );
    }
}
