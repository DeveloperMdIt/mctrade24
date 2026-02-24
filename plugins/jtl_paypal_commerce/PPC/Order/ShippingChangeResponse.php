<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class ShippingChangeResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class ShippingChangeResponse extends JSON
{
    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $adressData = $this->getData()->shipping_address ?? null;
        if ($adressData !== null && !($adressData instanceof Address)) {
            $this->setShippingAddress(new Address($adressData));
        }
        $amount = $this->getData()->amount ?? null;
        if ($amount !== null && !($amount instanceof AmountWithBreakdown)) {
            $this->setAmount(new AmountWithBreakdown($amount));
        }
        $shippingOption = $this->getData()->selected_shipping_option ?? null;
        if ($shippingOption !== null && !($shippingOption instanceof ShippingOption)) {
            $this->setSelectedShippingOption(new ShippingOption($shippingOption));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderID(): string
    {
        return $this->data->orderID ?? '';
    }

    /**
     * @return AmountWithBreakdown|null
     */
    public function getAmount(): ?AmountWithBreakdown
    {
        return $this->data->amount;
    }

    /**
     * @param AmountWithBreakdown $amount
     * @return void
     */
    private function setAmount(AmountWithBreakdown $amount): void
    {
        $this->data->amount = $amount;
    }

    /**
     * @return Address|null
     */
    public function getShippingAddress(): ?Address
    {
        return $this->data->shipping_address;
    }

    /**
     * @param Address $address
     * @return void
     */
    private function setShippingAddress(Address $address): void
    {
        $this->data->shipping_address = $address;
    }

    /**
     * @return ShippingOption|null
     */
    public function getShippingOption(): ?ShippingOption
    {
        return $this->data->selected_shipping_option;
    }

    /**
     * @param ShippingOption $shippingOption
     * @return void
     */
    private function setSelectedShippingOption(ShippingOption $shippingOption): void
    {
        $this->data->selected_shipping_option = $shippingOption;
    }
}
