<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyIdTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyTypeTrait;

/**
 * Class TokenPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class TokenPaymentSource extends AbstractPaymentSource
{
    use PropertyIdTrait;
    use PropertyTypeTrait;

    /**
     * TokenPaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'id',
            'type'
        ]);
    }
}
