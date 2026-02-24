<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Trait PropertyNameTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyNameTrait
{
    public function setName(string $name): static
    {
        $this->setMappedValue('name', PPCHelper::shortenStr($name, 300));

        return $this;
    }

    public function getName(): string
    {
        return $this->getMappedValue('name') ?? '';
    }
}
