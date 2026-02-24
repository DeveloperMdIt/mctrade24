<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

/**
 * Class GoogleTransactionInfo
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class GoogleTransactionInfo extends AmountWithBreakdown
{
    public const PRICESTATUS_FINAL = 'FINAL';
    public const ITEMTYPE_SUBTOTAL = 'SUBTOTAL';
    public const ITEMTYPE_TAX      = 'TAX';
    public const ITEMTYPE_TOTAL    = 'TOTAL';

    /** @var string */
    private string $totalPriceStatus = self::PRICESTATUS_FINAL;

    /** @var string */
    private string $countryCode = 'DE';

    private array $labels = [
        self::ITEMTYPE_SUBTOTAL => 'Subtotal',
        self::ITEMTYPE_TAX      => 'Tax',
        self::ITEMTYPE_TOTAL    => 'Total',
    ];

    /**
     * @return string
     */
    public function getTotalPriceStatus(): string
    {
        return $this->totalPriceStatus;
    }

    /**
     * @param string $totalPriceStatus
     * @return self
     */
    public function setTotalPriceStatus(string $totalPriceStatus): self
    {
        $this->totalPriceStatus = $totalPriceStatus;

        return $this;
    }

    /**
     * @param string $type
     * @param string $label
     * @return self
     */
    public function setLabel(string $type, string $label): self
    {
        if (\in_array($type, [self::ITEMTYPE_TOTAL, self::ITEMTYPE_TAX, self::ITEMTYPE_SUBTOTAL])) {
            $this->labels[$type] = $label;
        }

        return $this;
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function getLabel(string $type): ?string
    {
        return match ($type) {
            self::ITEMTYPE_TOTAL    => $this->labels[$type] ?? 'Total',
            self::ITEMTYPE_TAX      => $this->labels[$type] ?? 'Tax',
            self::ITEMTYPE_SUBTOTAL => $this->labels[$type] ?? 'Subtotal',
            default => null,
        };
    }

    /**
     * @return string
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     * @return self
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        return (object)[
            'countryCode'      => $this->getCountryCode(),
            'currencyCode'     => $this->getCurrencyCode(),
            'totalPriceStatus' => $this->getTotalPriceStatus(),
            'totalPrice'       => (string)$this->getValue(),
            'totalPriceLabel'  => $this->getLabel(self::ITEMTYPE_TOTAL),
        ];
    }
}
