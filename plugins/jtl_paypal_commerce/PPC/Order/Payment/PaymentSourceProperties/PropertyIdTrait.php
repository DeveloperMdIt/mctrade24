<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Trait PropertyIdTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyIdTrait
{
    public function setId(string $id): static
    {
        $this->setMappedValue('id', PPCHelper::validateStr($id, 1, 255));

        return $this;
    }

    public function getId(): string
    {
        return $this->getMappedValue('id') ?? '';
    }
}
