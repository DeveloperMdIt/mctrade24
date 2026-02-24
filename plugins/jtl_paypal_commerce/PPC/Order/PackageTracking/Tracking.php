<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseItem;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Tracker
 * @package Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking
 */
class Tracking extends JSON
{
    private ?string $orderId = null;

    /**
     * @inheritDoc
     */
    public function __construct(?object $data = null)
    {
        $data = $data ?? (object)[];

        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function setData(object | array | string $data): static
    {
        parent::setData($data);

        if (isset($data->order_id)) {
            $this->setOrderId($data->order_id);
            unset($this->data->order_id);
        }
        if (isset($data->items)) {
            $this->setItems($data->items);
        }

        return $this;
    }

    public function setOrderId(string $orderId): self
    {
        $this->orderId = PPCHelper::shortenStr($orderId, 64, '');

        return $this;
    }

    public function getOrderId(): string
    {
        return $this->orderId ?? '';
    }

    public function setCaptureId(string $captureId): self
    {
        $this->data->capture_id = PPCHelper::shortenStr($captureId, 50, '');

        return $this;
    }

    public function getCaptureId(): string
    {
        return $this->data->capture_id ?? '';
    }

    public function setTrackingNumber(string $trackingNumber): self
    {
        $this->data->tracking_number = PPCHelper::shortenStr($trackingNumber, 64, '');

        return $this;
    }

    public function getTrackingNumber(): string
    {
        return $this->data->tracking_number ?? '';
    }

    public function setCarrier(string $carrier, ?string $carrierName = null): self
    {
        if (!\in_array($carrier, Carrier::CARRIERS)) {
            $carrierName = $carrierName ?? $carrier;
            $carrier     = Carrier::CARRIER_OTHER;
        }
        $this->data->carrier = $carrier;
        if ($carrier === Carrier::CARRIER_OTHER) {
            if (empty($carrierName)) {
                throw new InvalidArgumentException('carrier_name must be set if carrier is "OTHER"');
            }

            $this->data->carrier_name_other = $carrierName;
        } else {
            unset($this->data->carrier_name_other);
        }

        return $this;
    }

    public function getCarrier(): string
    {
        return ($this->data->carrier ?? Carrier::CARRIER_OTHER) === Carrier::CARRIER_OTHER
            ? $this->data->carrier_name_other ?? ''
            : $this->data->carrier;
    }

    /**
     * @param PurchaseItem[]|object[]|null $items
     * @return self
     */
    public function setItems(?array $items): self
    {
        unset($this->data->items);
        if ($items === null) {
            return $this;
        }

        foreach ($items as $item) {
            $this->addItem($item instanceof PurchaseItem ? $item : new PurchaseItem($item));
        }

        return $this;
    }

    public function addItem(PurchaseItem $item): self
    {
        $this->data->items   = $this->data->items ?? [];
        $this->data->items[] = $item;

        return $this;
    }

    /**
     * @return PurchaseItem[]|null
     */
    public function getItems(): ?array
    {
        return $this->data->items ?? null;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (empty($data->items)) {
            unset($data->items);
        }
        if ($data->carrier !== Carrier::CARRIER_OTHER) {
            unset($data->carrier_name_other);
        }

        return $data;
    }
}
