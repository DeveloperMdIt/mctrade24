<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyAttributesTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyBillingAdressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyBillingAgreementIdTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyBirthDateTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyEmailAddressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyExperienceContextTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyNameTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyPhoneNumberTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyVaultIdTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Phone;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class PayPalPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class PayPalPaymentSource extends AbstractPaymentSource
{
    use PropertyNameTrait;
    use PropertyEmailAddressTrait;
    use PropertyPhoneNumberTrait;
    use PropertyBirthDateTrait;
    use PropertyBillingAdressTrait;
    use PropertyExperienceContextTrait;
    use PropertyBillingAgreementIdTrait;
    use PropertyVaultIdTrait;
    use PropertyAttributesTrait;

    /**
     * PayPalPaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'experience_context',
            'billing_agreement_id',
            'vault_id',
            'email_address',
            'name',
            'phone',
            'birth_date',
            'address',
            'attributes',
            'attribute_vault',
        ]);
    }

    protected function mapEntitie(string $name): string
    {
        return match ($name) {
            'billing_address' => 'address',
            'phone_number' => 'phone',
            default => parent::mapEntitie($name),
        };
    }

    protected function createVaultRequest(): void
    {
        $this->addAttribute('vault', new JSON((object)[
            'permit_multiple_payment_tokens' => "false",
            'store_in_vault'                 => 'ON_SUCCESS',
            'usage_type'                     => 'MERCHANT',
            'customer_type'                  => 'CONSUMER',
        ]));
    }

    protected function removeVaultRequest(): void
    {
        $this->addAttribute('vault');
    }

    public function buildExperienceContext(?object $data = null): ExperienceContext
    {
        return new ExperienceContext($data, [
            'brand_name',
            'shipping_preference',
            'landing_page',
            'user_action',
            'payment_method_preference',
            'locale',
            'return_url',
            'cancel_url',
        ]);
    }

    public function setName(string $name): static
    {
        if ($name === '') {
            $this->setMappedValue('name', null);

            return $this;
        }

        $nameParts = explode(' ', $name, 2);
        $data      = (object)[
            'given_name' => $nameParts[0] ?? '',
            'surname'    => $nameParts[1] ?? '',
        ];
        $this->setMappedValue('name', $data);

        return $this;
    }

    public function getName(): string
    {
        $name = $this->getMappedValue('name');

        return \trim(($name->given_name ?? '') . ' ' . ($name->surname ?? ''));
    }

    protected function initdataPhone(object|null $data): void
    {
        if ($data === null) {
            $this->setPhoneNumber();
        } elseif (!($data instanceof Phone)) {
            $this->setPhoneNumber(
                (new Phone())->setNumber(\is_string($data->phone_number)
                    ? $data->phone_number
                    : $data->phone_number->national_number ?? '')
            );
        }
    }

    protected function serializePhone(object $data): void
    {
        $phone = $this->getPhoneNumber();
        if ($phone === null || ($phone->isEmpty())) {
            unset($data->phone);
        } else {
            $data->phone = (object)[
                'phone_number' => (object)[
                    'national_number' => $phone->getNumber('00', ''),
                ],
            ];
        }
    }
}
