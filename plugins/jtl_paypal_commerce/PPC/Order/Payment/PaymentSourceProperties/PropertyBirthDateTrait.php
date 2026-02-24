<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use DateTime;
use Exception;

/**
 * Trait PropertyBirthDateTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyBirthDateTrait
{
    public function setBirthDate(?DateTime $birthDate = null): static
    {
        $this->setMappedValue('birth_date', $birthDate);

        return $this;
    }

    public function getBirthDate(): ?DateTime
    {
        return $this->getMappedValue('birth_date');
    }

    protected function initdataBirthDate(string|object|null $data): void
    {
        if (\is_string($data)) {
            try {
                $this->setBirthDate(new DateTime($data));
            } catch (Exception) {
                $this->setBirthDate();
            }
        }
    }

    protected function serializeBirthDate(object $data): void
    {
        $mappedName = $this->mapEntitie('birth_date');
        $birthDate  = $this->getBirthDate();
        if ($birthDate === null) {
            unset($data->$mappedName);
        } else {
            $data->$mappedName = $birthDate->format('Y-m-d');
        }
    }
}
