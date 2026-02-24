<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Trait PropertyEmailAddressTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyEmailAddressTrait
{
    public function getEmail(): string
    {
        return $this->getMappedValue('email_address') ?? '';
    }

    public function setEmail(string $email): static
    {
        if ($email === '') {
            $this->setMappedValue('email_address', null);
        } else {
            $this->setMappedValue('email_address', PPCHelper::validateStr($email, 3, 254));
        }

        return $this;
    }
}
