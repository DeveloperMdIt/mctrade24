<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyCountryCodeTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyEmailAddressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyExperienceContextTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyNameTrait;

/**
 * Class P24PaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class P24PaymentSource extends AbstractPaymentSource
{
    use PropertyNameTrait;
    use PropertyEmailAddressTrait;
    use PropertyCountryCodeTrait;
    use PropertyExperienceContextTrait;

    /**
     * P24PaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'name',
            'email',
            'country_code',
            'experience_context'
        ]);
    }

    protected function mapEntitie(string $name): string
    {
        return match ($name) {
            'email_address' => 'email',
            default => parent::mapEntitie($name),
        };
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
