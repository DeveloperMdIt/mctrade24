<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Trait PropertyVaultIdTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyVaultIdTrait
{
    public function setVaultid(string $name): static
    {
        $this->setMappedValue('vault_id', PPCHelper::validateStr($name, 1, 255, '^[0-9a-zA-Z_-]+$'));

        return $this;
    }

    public function getVaultId(): string
    {
        return $this->getMappedValue('vault_id') ?? '';
    }
}
