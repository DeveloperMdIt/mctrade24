<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Trait PropertyCountryCodeTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyCountryCodeTrait
{
    public function setCountryCode(string $cc): static
    {
        try {
            $this->setMappedValue('country_code', PPCHelper::validateStr($cc, 2, 2, '^([A-Z]{2}|C2)$'));
        } catch (InvalidArgumentException) {
            throw new InvalidArgumentException('Countrycode must be a valid two-character ISO 3166-1 country code');
        }

        return $this;
    }

    public function getCountryCode(): string
    {
        return $this->getMappedValue('country_code') ?? '';
    }
}
