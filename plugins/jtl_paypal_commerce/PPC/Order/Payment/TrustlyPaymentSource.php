<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyCountryCodeTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyExperienceContextTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyNameTrait;

/**
 * Class TrustlyPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class TrustlyPaymentSource extends AbstractPaymentSource
{
    use PropertyNameTrait;
    use PropertyCountryCodeTrait;
    use PropertyExperienceContextTrait;

    /**
     * TrustlyPaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'name',
            'country_code',
            'experience_context'
        ]);
    }

    public function buildExperienceContext(?object $data = null): ExperienceContext
    {
        return new ExperienceContext($data, [
            'brand_name',
            'shipping_preference',
            'locale',
            'return_url',
            'cancel_url',
        ]);
    }
}
