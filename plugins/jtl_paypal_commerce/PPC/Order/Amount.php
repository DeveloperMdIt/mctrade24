<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class Amount
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class Amount extends JSON
{
    /**
     * Amount constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'value'         => 0.0,
            'currency_code' => 'EUR',
        ]);
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string
    {
        return $this->data->currency_code ?? 'EUR';
    }

    /**
     * @param string $currencyCode
     * @return Amount
     */
    public function setCurrencyCode(string $currencyCode): self
    {
        if (\mb_strlen($currencyCode) !== 3) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid currency code.', $currencyCode));
        }
        $this->data->currency_code = $currencyCode;

        return $this;
    }

    /**
     * @return float
     */
    public function getValue(): float
    {
        return (float)($this->data->value ?? 0.0);
    }

    /**
     * @param float $value
     * @return Amount
     */
    public function setValue(float $value): self
    {
        $this->data->value = $value;

        return $this;
    }

    /**
     * @param float $value
     * @return float
     */
    public function addValue(float $value): float
    {
        $this->data->value = $this->getValue() + $value;

        return $this->data->value;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        return (object)[
            'value'         => \number_format($this->getValue(), 2, '.', ''),
            'currency_code' => $this->getCurrencyCode(),
        ];
    }
}
