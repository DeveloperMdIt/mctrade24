<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Trait PropertyBillingAdressTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyBillingAdressTrait
{
    public function setBillingAddress(?Address $address = null): static
    {
        $this->setMappedValue('billing_address', $address);

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        $address = $this->getMappedValue('billing_address');
        if (empty($address) || ($address instanceof SerializerInterface && $address->isEmpty())) {
            return null;
        }

        return $address instanceof Address ? $address : new Address($address);
    }

    protected function initdataBillingAddress(object $data): void
    {
        if (!($data instanceof Address)) {
            $this->data->billing_address = new Address($data);
        }
    }
}
