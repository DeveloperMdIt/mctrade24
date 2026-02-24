<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class AppleTransactionInfo
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class AppleTransactionInfo extends JSON
{
    /**
     * AppleTransactionInfo constructor
     */
    public function __construct()
    {
        parent::__construct((object)[
            'countryCode'                => 'DE',
            'currencyCode'               => 'EUR',
        ]);
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->data->countryCode ?? 'DE';
    }

    /**
     * @param string $countryCode
     * @return self
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->data->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->data->currencyCode ?? 'EUR';
    }

    /**
     * @param string $currencyCode
     * @return self
     */
    public function setCurrencyCode(string $currencyCode): self
    {
        $this->data->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * @return ApplePayLineItem
     */
    public function getTotal(): ApplePayLineItem
    {
        return ($this->data->total ?? null) instanceof ApplePayLineItem
            ? $this->data->total
            : new ApplePayLineItem($this->data->total ?? null);
    }

    /**
     * @param ApplePayLineItem $total
     * @return self
     */
    public function setTotal(ApplePayLineItem $total): self
    {
        $this->data->total = $total;

        return $this;
    }

    /**
     * @return ApplePayPaymentContact
     */
    public function getShippingContact(): ApplePayPaymentContact
    {
        return ($this->data->shippingContact ?? null) instanceof ApplePayPaymentContact
            ? $this->data->shippingContact
            : new ApplePayPaymentContact($this->data->shippincContact ?? null);
    }

    /**
     * @param ApplePayPaymentContact|null $shippingContact
     * @return self
     */
    public function setShippingContact(?ApplePayPaymentContact $shippingContact = null): self
    {
        if ($shippingContact === null) {
            unset($this->data->shippingContact);
        } else {
            $this->data->shippingContact = $shippingContact;
        }

        return $this;
    }

    /**
     * @return ApplePayPaymentContact
     */
    public function getBillingContact(): ApplePayPaymentContact
    {
        return ($this->data->billingContact ?? null) instanceof ApplePayPaymentContact
            ? $this->data->billingContact
            : new ApplePayPaymentContact($this->data->billingContact ?? null);
    }

    /**
     * @param ApplePayPaymentContact|null $billingContact
     * @return self
     */
    public function setBillingContact(?ApplePayPaymentContact $billingContact = null): self
    {
        if ($billingContact === null) {
            unset($this->data->billingContact);
        } else {
            $this->data->billingContact = $billingContact;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (($data->shippingContact ?? null) !== null) {
            $data->shippingContactEditingMode = 'storePickup';
        } else {
            unset($data->shippingContactEditingMode);
        }

        return $data;
    }
}
