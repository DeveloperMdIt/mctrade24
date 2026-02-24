<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyAttributesTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyEmailAddressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyExperienceContextTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyVaultIdTrait;

/**
 * Class VenmoPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class VenmoPaymentSource extends AbstractPaymentSource
{
    use PropertyExperienceContextTrait;
    use PropertyEmailAddressTrait;
    use PropertyVaultIdTrait;
    use PropertyAttributesTrait;

    /**
     * VenmoPaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'experience_context',
            'vault_id',
            'email_address',
            'attributes',
        ]);
    }

    public function buildExperienceContext(?object $data = null): ExperienceContext
    {
        return new ExperienceContext($data, [
            'brand_name',
            'shipping_preference',
        ]);
    }
}
