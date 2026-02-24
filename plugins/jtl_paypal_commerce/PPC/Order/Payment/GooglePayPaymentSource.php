<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyAttributesTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyCardDetailsTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyEmailAddressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyNameTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyPhoneNumberTrait;

/**
 * Class GooglePayPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class GooglePayPaymentSource extends AbstractPaymentSource
{
    use PropertyNameTrait;
    use PropertyEmailAddressTrait;
    use PropertyPhoneNumberTrait;
    use PropertyAttributesTrait;
    use PropertyCardDetailsTrait;

    /**
     * GooglePayPaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'name',
            'email_address',
            'phone_number',
            'card',
            'attributes',
        ]);
    }
}
