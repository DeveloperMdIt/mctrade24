<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class PatchInvoiceId
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PatchInvoiceId extends Patch
{
    /**
     * PatchInvoiceId constructor.
     * @param string $invoiceId
     * @param string $op
     */
    public function __construct(string $invoiceId, string $op = self::OP_REPLACE)
    {
        parent::__construct(
            "/purchase_units/@reference_id=='" . PurchaseUnit::REFERENCE_DEFAULT . "'/invoice_id",
            new JSON($invoiceId),
            $op
        );
    }
}
