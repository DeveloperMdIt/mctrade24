<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use InvalidArgumentException;

/**
 * Trait PropertyTypeTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyTypeTrait
{
    public function setType(string $type): static
    {
        if ($type !== PropertyConstants::TYPE_BILLING_AGREEMENT) {
            throw new InvalidArgumentException(sprintf('%s is not a vaild type', $type));
        }

        $this->setMappedValue('type', $type);

        return $this;
    }

    public function getType(): string
    {
        return $this->getMappedValue('type') ?? '';
    }
}
