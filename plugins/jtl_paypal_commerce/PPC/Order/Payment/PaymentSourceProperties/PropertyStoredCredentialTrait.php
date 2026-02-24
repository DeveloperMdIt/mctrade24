<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\Order\Payment\StoredCredential;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Trait PropertyStoredCredentialTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyStoredCredentialTrait
{
    public function setStoredCredential(?StoredCredential $storedCredential = null): static
    {
        $this->setMappedValue('stored_credential', $storedCredential);

        return $this;
    }

    public function getStoredCredential(): ?StoredCredential
    {
        $storedCredential = $this->getMappedValue('stored_credential');
        if (
            empty($storedCredential)
            || ($storedCredential instanceof SerializerInterface && $storedCredential->isEmpty())
        ) {
            return null;
        }

        return $storedCredential instanceof StoredCredential
            ? $storedCredential
            : new StoredCredential($storedCredential);
    }

    protected function initdataStoredCredential(object $data): void
    {
        if (!($data instanceof StoredCredential)) {
            $this->data->stored_credential = new StoredCredential($data);
        }
    }
}
