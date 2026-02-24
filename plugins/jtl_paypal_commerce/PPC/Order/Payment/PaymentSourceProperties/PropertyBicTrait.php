<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Trait PropertyBicTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyBicTrait
{
    public function setBIC(string $bic): static
    {
        try {
            $this->setMappedValue(
                'bic',
                PPCHelper::validateStr($bic, 8, 11, '^[A-Z-a-z0-9]{4}[A-Z-a-z]{2}[A-Z-a-z0-9]{2}([A-Z-a-z0-9]{3})?$')
            );
        } catch (InvalidArgumentException) {
            throw new InvalidArgumentException('BIC must be a valid bank identification code');
        }

        return $this;
    }

    public function getBIC(): string
    {
        return $this->getMappedValue('bic') ?? '';
    }
}
