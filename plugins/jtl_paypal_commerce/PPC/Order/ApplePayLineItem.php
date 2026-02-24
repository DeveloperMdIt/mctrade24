<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class ApplePayLineItem
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class ApplePayLineItem extends JSON
{
    /**
     * ApplePayLineItem constructor
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        if ($data === null) {
            $data = (object)[
                'type' => 'final'
            ];
        } else {
            $data->type = 'final';
        }

        parent::__construct($data);
    }

    /**
     * @param Amount $amount
     * @return self
     */
    public static function fromAmount(Amount $amount): self
    {
        $instance = new self();
        $instance->setAmount(\number_format($amount->getValue(), 2));

        return $instance;
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
     * @return self
     */
    public function setLabel(string $label): self
    {
        $this->data->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->data->amount ?? '0.00';
    }

    /**
     * @param string $amount
     * @return self
     */
    public function setAmount(string $amount): self
    {
        if (!\is_numeric($amount)) {
            throw new InvalidArgumentException('Amount must be a numeric value');
        }

        $this->data->amount = $amount;

        return $this;
    }
}
