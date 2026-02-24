<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\Order\Phone;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Trait PropertyPhoneNumberTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyPhoneNumberTrait
{
    public function setPhoneNumber(?Phone $phoneNumber = null): static
    {
        $this->setMappedValue('phone_number', $phoneNumber);

        return $this;
    }

    public function getPhoneNumber(): ?Phone
    {
        /** @var Phone $phone */
        $phone = $this->getMappedValue('phone_number');
        if ($phone === null || $phone->isEmpty()) {
            return null;
        }

        return $phone;
    }

    protected function initdataPhoneNumber(string|object|null $data): void
    {
        if ($data === null) {
            $this->setPhoneNumber();
        } elseif (!($data instanceof Phone)) {
            $this->setPhoneNumber(
                (new Phone())->setNumber(\is_string($data) ? $data : $data->national_number ?? '')
            );
        }
    }

    protected function serializePhoneNumber(object $data): void
    {
        $mappedName = $this->mapEntitie('phone_number');
        $phone      = $this->getPhoneNumber();
        if ($phone === null || ($phone instanceof SerializerInterface && $phone->isEmpty())) {
            unset($data->$mappedName);
        } else {
            $data->$mappedName = (object)[
                'national_number' => $phone->getNumber('00', ''),
            ];
        }
    }
}
