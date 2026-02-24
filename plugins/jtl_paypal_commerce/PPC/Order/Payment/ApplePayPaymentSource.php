<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyAttributesTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyEmailAddressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyIdTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyNameTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyPhoneNumberTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyStoredCredentialTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyVaultIdTrait;

/**
 * Class ApplePayPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class ApplePayPaymentSource extends AbstractPaymentSource
{
    use PropertyNameTrait;
    use PropertyEmailAddressTrait;
    use PropertyPhoneNumberTrait;
    use PropertyAttributesTrait;
    use PropertyIdTrait;
    use PropertyStoredCredentialTrait;
    use PropertyVaultIdTrait;

    /**
     * ApplePayPaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'id',
            'stored_credential',
            'attributes',
            'name',
            'email_address',
            'phone_number',
            'vault_id',
        ]);
    }
}
