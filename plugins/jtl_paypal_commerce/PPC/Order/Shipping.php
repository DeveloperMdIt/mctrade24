<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

use function Functional\first;

/**
 * Class Shipping
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Shipping extends JSON
{
    public const TYPE_SHIPPING = 'SHIPPING';
    public const TYPE_PICKUP   = 'PICKUP_IN_PERSON';

    /**
     * Shipping constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'type' => self::TYPE_SHIPPING,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $adressData = $this->getData()->address ?? null;
        if ($adressData !== null && !($adressData instanceof Address)) {
            $this->setAddress(new Address($adressData));
        }

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->data->address ?? new Address();
    }

    /**
     * @param Address $address
     * @return static
     */
    public function setAddress(Address $address): static
    {
        $this->data->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return ($this->data->name ?? null) === null ? '' : $this->data->name->full_name ?? '';
    }

    /**
     * @param string $name
     * @return static
     */
    public function setName(string $name): static
    {
        $this->data->name = (object)[
            'full_name' => PPCHelper::shortenStr($name, 300),
        ];

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->data->type ?? self::TYPE_SHIPPING;
    }

    /**
     * @param string $type
     * @return static
     */
    public function setType(string $type): static
    {
        if (!\in_array($type, [self::TYPE_SHIPPING, self::TYPE_PICKUP])) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid shipping type.', $type));
        }

        $this->data->type = $type;

        return $this;
    }

    /**
     * @return static
     */
    public function clearType(): static
    {
        unset($this->data->type);

        return $this;
    }

    /**
     * @return ShippingOption[]
     */
    public function getOptions(): array
    {
        $options = $this->data->options ?? [];
        foreach ($options as $key => $option) {
            if (!($option instanceof ShippingOption)) {
                $options[$key] = new ShippingOption($option);
            }
        }

        return $options;
    }

    /**
     * @param bool $selected
     * @return ShippingOption|null
     */
    public function getOption(bool $selected = false): ?ShippingOption
    {
        $options = $this->getOptions();

        return \count($options) === 0
            ? null
            : first($options, static function (ShippingOption $option) use ($selected) {
                return !$selected || $option->isSelected();
            });
    }

    /**
     * @param string $id
     * @return ShippingOption|null
     */
    public function getOptionById(string $id): ?ShippingOption
    {
        $options = $this->getOptions();

        return \count($options) === 0
            ? null
            : first($options, static function (ShippingOption $option) use ($id) {
                return $option->getId() === $id;
            });
    }

    /**
     * @param ShippingOption $option
     * @return static
     */
    public function addOption(ShippingOption $option): static
    {
        if (\is_array($this->data->options) && count($this->data->options) > 9) {
            $this->data->options = \array_slice($this->data->options, -9);
        }
        $this->data->options[] = $option;

        return $this;
    }

    /**
     * @param string $id
     * @return ShippingOption|null
     */
    public function selectOption(string $id): ?ShippingOption
    {
        $prevOption = $this->getOption(true);
        if ($prevOption !== null) {
            $prevOption->setSelected(false);
        }
        $nextOption = $this->getOptionById($id);
        if ($nextOption !== null) {
            $nextOption->setSelected(true);
        } elseif ($prevOption !== null) {
            $prevOption->setSelected(true);
        }

        return $nextOption ?? $prevOption;
    }

    /**
     * @param array  $shippingMethods
     * @param string $langCode
     * @param string $currencyCode
     * @param float  $taxRate
     * @return static
     */
    public function setOptions(array $shippingMethods, string $langCode, string $currencyCode, float $taxRate): static
    {
        $this->data->options = [];
        $count               = 0;
        foreach ($shippingMethods as $shippingMethod) {
            $gross = $shippingMethod->eSteuer !== 'netto';
            if (isset($shippingMethod->finalGrossCost)) {
                $shippingCosts = \round((float)$shippingMethod->finalGrossCost, 2);
            } elseif ($taxRate === 0.0) {
                $shippingCosts = $gross
                    ? \round((float)$shippingMethod->fEndpreis / (100 + $taxRate) * 100.0, 2)
                    : \round((float)$shippingMethod->fEndpreis, 2);
            } elseif ($gross) {
                $shippingCosts = \round((float)$shippingMethod->fEndpreis, 2);
            } else {
                $shippingCosts = \round((float)$shippingMethod->fEndpreis * (100 + $taxRate) / 100, 2);
            }
            $option = (new ShippingOption())
                ->setId((string)$shippingMethod->kVersandart)
                ->setLabel(empty($shippingMethod->angezeigterName[$langCode])
                    ? $shippingMethod->cName
                    : $shippingMethod->angezeigterName[$langCode])
                ->setType(ShippingOption::OPTION_SHIPPING)
                ->setSelected($count === 0)
                ->setAmount((new Amount())
                    ->setValue($shippingCosts)
                    ->setCurrencyCode($currencyCode));

            $this->addOption($option);
            $count++;

            if ($count === 10) {
                break;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return empty($this->getName()) && $this->getAddress()->isEmpty() && count($this->getOptions()) === 0;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (empty($data->address) || ($data->address instanceof SerializerInterface && $data->address->isEmpty())) {
            unset($data->address);
        }
        if (empty($data->options) || count($data->options) === 0) {
            unset($data->options);
        } elseif (\is_array($data->options) && count($data->options) > 10) {
            $data->options = \array_slice($data->options, 0, 10);
        }

        return $data;
    }
}
