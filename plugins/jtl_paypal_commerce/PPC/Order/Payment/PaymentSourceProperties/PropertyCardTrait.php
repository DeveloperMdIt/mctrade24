<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Trait PropertyCardTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyCardTrait
{
    public function setNumber(string $number): static
    {
        $this->setMappedValue('number', PPCHelper::validateStr($number, 13, 19));

        return $this;
    }

    public function getNumber(): string
    {
        return $this->getMappedValue('number') ?? '';
    }

    public function setSecurityCode(string $securityCode): static
    {
        $this->setMappedValue('security_code', PPCHelper::validateStr($securityCode, 3, 4));

        return $this;
    }

    public function getSecurityCode(): string
    {
        return $this->getMappedValue('security_code') ?? '';
    }

    public function setExpiry(string $expiry): static
    {
        try {
            $this->setMappedValue('expiry', PPCHelper::validateStr($expiry, 7, 7, '^\d{4}-(0[1-9]|1[0-2])$'));
        } catch (InvalidArgumentException) {
            throw new InvalidArgumentException('Expiry date must be in YYYY-MM format');
        }

        return $this;
    }

    public function getExpiry(): string
    {
        return $this->getMappedValue('expiry') ?? '';
    }
}
