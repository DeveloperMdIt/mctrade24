<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use InvalidArgumentException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class PayMethod
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PayMethod extends JSON
{
    public const PAYEE_UNRESTRICTED = 'UNRESTRICTED';
    public const PAYEE_IMMEDIATE    = 'IMMEDIATE_PAYMENT_REQUIRED';

    /**
     * PayMethod constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'payee_preferred' => self::PAYEE_UNRESTRICTED,
        ]);
    }

    /**
     * @return string
     */
    public function getPreferred(): string
    {
        return $this->data->payee_preferred ?? self::PAYEE_UNRESTRICTED;
    }

    /**
     * @param string $preferred
     * @return PayMethod
     */
    public function setPreferred(string $preferred): self
    {
        if (!\in_array($preferred, [self::PAYEE_UNRESTRICTED, self::PAYEE_IMMEDIATE])) {
            throw new InvalidArgumentException(\sprintf('%s is not a valid payee preferred.', $preferred));
        }

        $this->data->payee_preferred = $preferred;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSelected(): ?string
    {
        return $this->data->payer_selected ?? null;
    }

    /**
     * @param string|null $selected
     * @return PayMethod
     */
    public function setSelected(?string $selected): self
    {
        static $pattern = '/^[0-9A-Z_]+$./';

        if ($selected === null) {
            unset($this->data->payer_selected);
        } else {
            if (!\preg_match($pattern, $selected)) {
                throw new InvalidArgumentException(\sprintf('%s is not a valid payment method.', $selected));
            }

            $this->data->payer_selected = $selected;
        }

        return $this;
    }
}
