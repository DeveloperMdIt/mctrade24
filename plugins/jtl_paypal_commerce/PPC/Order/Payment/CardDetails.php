<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class CardDetails
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class CardDetails extends JSON
{
    /**
     * CardPaymentSource constructor
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $authResult = $this->getData()->authentication_result ?? null;
        if ($authResult !== null && !($authResult instanceof AuthResult)) {
            $this->setAuthResult(new AuthResult($authResult));
        }
        $address = $this->getData()->billing_address ?? null;
        if ($address !== null && !($address instanceof Address)) {
            $this->setBillingAddress(new Address($address));
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->getData()->name ?? '';
    }

    public function setName(?string $name = null): self
    {
        if ($name === null) {
            unset($this->data->name);
        } else {
            $this->data->name = PPCHelper::validateStr($name, 1, 300);
        }

        return $this;
    }

    public function getType(): string
    {
        return $this->getData()->type ?? '';
    }

    public function setType(?string $type = null): self
    {
        if ($type === null) {
            unset($this->data->type);
        } else {
            $this->data->type = PPCHelper::validateStr($type, 1, 255, '^[A-Z_]+$');
        }

        return $this;
    }

    public function getBrand(): string
    {
        return $this->getData()->brand ?? '';
    }

    public function setBrand(?string $brand = null): self
    {
        if ($brand === null) {
            unset($this->data->brand);
        } else {
            $this->data->brand = PPCHelper::validateStr($brand, 1, 255, '^[A-Z_]+$');
        }

        return $this;
    }

    public function getLastDigits(): string
    {
        return $this->getData()->last_digits ?? '';
    }

    public function setLastDigits(?string $lastDigits = null): self
    {
        if ($lastDigits === null) {
            unset($this->data->last_digits);
        } else {
            $this->data->last_digits = $lastDigits;
        }

        return $this;
    }

    public function getAuthResult(): ?AuthResult
    {
        return $this->getData()->authentication_result ?? null;
    }

    public function setAuthResult(?AuthResult $authResult = null): self
    {
        if ($authResult === null) {
            unset($this->data->authentication_result);
        } else {
            $this->data->authentication_result = $authResult;
        }

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->getData()->billing_address ?? null;
    }

    public function setBillingAddress(?Address $billingAddress = null): self
    {
        if ($billingAddress === null) {
            unset($this->data->billing_address);
        } else {
            $this->data->billing_address = $billingAddress;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = parent::jsonSerialize();

        foreach (['name', 'type', 'brand', 'last_digits', 'authentication_result', 'billing_address'] as $property) {
            if (
                empty($data->$property)
                || ($data->$property instanceof SerializerInterface && $data->$property->isEmpty())
            ) {
                unset($data->$property);
            }
        }

        return $data;
    }
}
