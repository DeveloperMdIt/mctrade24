<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyBankReferenceTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyBillingAdressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyBirthDateTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyEmailAddressTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyExperienceContextTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyNameTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties\PropertyPhoneNumberTrait;
use Plugin\jtl_paypal_commerce\PPC\Order\Phone;

/**
 * Class PUIPaymentSource
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class PUIPaymentSource extends AbstractPaymentSource
{
    use PropertyNameTrait;
    use PropertyBirthDateTrait;
    use PropertyPhoneNumberTrait;
    use PropertyEmailAddressTrait;
    use PropertyBillingAdressTrait;
    use PropertyBankReferenceTrait;
    use PropertyExperienceContextTrait;

    /**
     * PUIPaymentSource constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data, [
            'name',
            'email',
            'birth_date',
            'phone',
            'billing_address',
            'experience_context',
            'payment_reference',
            'deposit_bank_details',
        ]);
    }

    protected function mapEntitie(string $name): string
    {
        return match ($name) {
            'email_address' => 'email',
            'phone_number'  => 'phone',
            default => parent::mapEntitie($name),
        };
    }

    public function buildExperienceContext(?object $data = null): ExperienceContext
    {
        return new ExperienceContext($data, [
            'brand_name',
            'locale',
            'logo_url',
            'customer_service_instructions',
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
                (new Phone())->setNationalNumber($data->national_number ?? '')
                             ->setCountryCode($data->country_code ?? '')
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
                'national_number' => $phone->getNationalNumber(''),
                'country_code'    => $phone->getCountryCode(''),
            ];
        }
    }

    protected function serializeName(object $data): void
    {
        $mappedName = $this->mapEntitie('name');
        if (empty($data->$mappedName)) {
            unset($data->$mappedName);
        } else {
            $empty = true;
            foreach ($data->$mappedName as $value) {
                if (!empty($value)) {
                    $empty = false;
                    break;
                }
            }
            if ($empty) {
                unset($data->$mappedName);
            }
        }
    }
}
