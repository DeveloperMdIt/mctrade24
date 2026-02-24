<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class ShippingOption
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class ShippingOption extends JSON
{
    public const OPTION_SHIPPING = 'SHIPPING';
    public const OPTION_PICKUP   = 'PICKUP';

    /**
     * ShippingOption constructor
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'type' => self::OPTION_SHIPPING,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $amountData = $this->getData()->amount ?? null;
        if ($amountData !== null && !($amountData instanceof Amount)) {
            $this->setAmount(new Amount($amountData));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->data->id ?? '';
    }

    /**
     * @param string $id
     * @return static
     */
    public function setId(string $id): static
    {
        $this->data->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->data->label ?? '';
    }

    /**
     * @param string $label
     * @return static
     */
    public function setLabel(string $label): static
    {
        $this->data->label = PPCHelper::shortenStr($label, 127);

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->data->type ?? self::OPTION_SHIPPING;
    }

    /**
     * @param string $type
     * @return static
     */
    public function setType(string $type): static
    {
        if (!\in_array($type, [self::OPTION_SHIPPING, self::OPTION_PICKUP])) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid shipping type.', $type));
        }

        $this->data->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSelected(): bool
    {
        return (bool)($this->data->selected ?? false);
    }

    /**
     * @param bool $selected
     * @return static
     */
    public function setSelected(bool $selected): static
    {
        $this->data->selected = $selected;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->data->amount ?? new Amount();
    }

    /**
     * @param Amount $amount
     * @return static
     */
    public function setAmount(Amount $amount): static
    {
        $this->data->amount = $amount;

        return $this;
    }
}
