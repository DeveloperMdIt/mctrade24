<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

/**
 * Class PatchPayer
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PatchPayer extends Patch
{
    /**
     * PatchPayer constructor.
     * @param Payer  $payer
     * @param string $op
     */
    public function __construct(Payer $payer, string $op = self::OP_REPLACE)
    {
        parent::__construct('/payer', $payer, $op);
    }
}
