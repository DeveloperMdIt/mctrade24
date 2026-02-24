<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use JTL\Cart\Cart;
use JTL\Catalog\Currency;

/**
 * Class AmountWithBreakdown
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class AmountWithBreakdown extends Amount
{
    public const WK_ALL      = [
        \C_WARENKORBPOS_TYP_VERSANDPOS,
        \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
        \C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR,
        \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
        \C_WARENKORBPOS_TYP_VERPACKUNG,
        \C_WARENKORBPOS_TYP_ZAHLUNGSART,
        \C_WARENKORBPOS_TYP_GUTSCHEIN,
        \C_WARENKORBPOS_TYP_KUPON,
        \C_WARENKORBPOS_TYP_NEUKUNDENKUPON,
        \C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR,
        \C_WARENKORBPOS_TYP_ZINSAUFSCHLAG,
    ];
    public const WK_HANDLING = [
        \C_WARENKORBPOS_TYP_ZAHLUNGSART,
        \C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR,
        \C_WARENKORBPOS_TYP_ZINSAUFSCHLAG,
    ];
    public const WK_SHIPPING = [
        \C_WARENKORBPOS_TYP_VERSANDPOS,
        \C_WARENKORBPOS_TYP_VERSANDZUSCHLAG,
        \C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR,
        \C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG,
        \C_WARENKORBPOS_TYP_VERPACKUNG,
    ];
    public const WK_DISCOUNT = [
        \C_WARENKORBPOS_TYP_GUTSCHEIN,
        \C_WARENKORBPOS_TYP_KUPON,
        \C_WARENKORBPOS_TYP_NEUKUNDENKUPON,
    ];

    /**
     * AmountWithBreakdown constructor.
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'value'         => 0.0,
            'currency_code' => 'EUR',
            'breakdown'     => []
        ]);
    }

    /**
     * @param Cart   $cart
     * @param string $currencyCode
     * @param bool   $merchant
     * @return AmountWithBreakdown
     */
    public static function createFromCart(Cart $cart, string $currencyCode, bool $merchant): self
    {
        $gross     = !$merchant;
        $total     = $cart->gibGesamtsummeWaren($gross);
        $itemTotal = $cart->gibGesamtsummeWarenOhne(self::WK_ALL, $gross);
        $handling  = $cart->gibGesamtsummeWarenExt(self::WK_HANDLING, $gross);
        $shipping  = $cart->gibGesamtsummeWarenExt(self::WK_SHIPPING, $gross);
        $discount  = $cart->gibGesamtsummeWarenExt(self::WK_DISCOUNT, $gross);

        if ($handling < 0) {
            $discount += $handling;
            $handling = 0.0;
        }

        $amount = ((new AmountWithBreakdown())
                ->setValue(Currency::convertCurrency($total, $currencyCode))
                ->setCurrencyCode($currencyCode)
            )
            ->setItemTotal((new Amount())
                ->setValue(Currency::convertCurrency($itemTotal, $currencyCode))
                ->setCurrencyCode($currencyCode))
            ->setHandling($handling > 0.0
                ? (new Amount())
                    ->setValue(Currency::convertCurrency($handling, $currencyCode))
                    ->setCurrencyCode($currencyCode)
                : null)
            ->setDiscount($discount !== 0.0
                ? (new Amount())
                    ->setValue(Currency::convertCurrency(-$discount, $currencyCode))
                    ->setCurrencyCode($currencyCode)
                : null)
            ->setShipping($shipping > 0.0
                ? (new Amount())
                    ->setValue(Currency::convertCurrency($shipping, $currencyCode))
                    ->setCurrencyCode($currencyCode)
                : null)
            ->setShippingDiscount($shipping < 0.0
                ? (new Amount())
                    ->setValue(Currency::convertCurrency(-$shipping, $currencyCode))
                    ->setCurrencyCode($currencyCode)
                : null);

        if ($merchant) {
            $totalWithTax = Currency::convertCurrency($cart->gibGesamtsummeWaren(true), $currencyCode);
            $tax          = $totalWithTax - Currency::convertCurrency($total, $currencyCode);
            $amount->setValue($totalWithTax);
            $amount->setTaxTotal(
                (new Amount())
                    ->setValue(\round($tax, 2))
                    ->setCurrencyCode($currencyCode)
            );
        }

        return $amount->adjustTotal();
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $breakDown             = $this->getData()->breakdown ?? [];
        $this->data->breakdown = [];
        foreach ($breakDown as $key => $value) {
            $this->addBreakdownItem($key, ($value instanceof Amount) ? $value : new Amount($value));
        }

        return $this;
    }

    /**
     * @return self
     */
    public function adjustTotal(): self
    {
        if (!$this->validateBreakdown()) {
            $compensation = $this->getValue() - $this->getTotalBreakdown();
            if ($compensation < 0.0) {
                $discount = $this->getDiscount();
                $discount->addValue(-$compensation);
                $this->setDiscount($discount);
            } else {
                $handling = $this->getHandling();
                $handling->addValue($compensation);
                $this->setHandling($handling);
            }
        }

        return $this;
    }

    /**
     * @return self
     */
    public function calculateTotal(): self
    {
        if (!$this->validateBreakdown()) {
            $this->setValue($this->getTotalBreakdown());
        }

        return $this;
    }

    /**
     * @return Amount
     */
    public function getHandling(): Amount
    {
        return $this->data->breakdown['handling'] ?? (new Amount())
                ->setValue(0.0)
                ->setCurrencyCode($this->getCurrencyCode());
    }

    /**
     * @param Amount|null $handling
     * @return AmountWithBreakdown
     */
    public function setHandling(?Amount $handling): self
    {
        if ($handling === null) {
            unset($this->data->breakdown['handling']);
        } else {
            $this->data->breakdown['handling'] = $handling;
        }

        return $this;
    }

    /**
     * @return Amount
     */
    public function getInsurance(): Amount
    {
        return $this->data->breakdown['insurance'] ?? (new Amount())
                ->setValue(0.0)
                ->setCurrencyCode($this->getCurrencyCode());
    }

    /**
     * @param Amount|null $insurance
     * @return AmountWithBreakdown
     */
    public function setInsurance(?Amount $insurance): self
    {
        if ($insurance === null) {
            unset($this->data->breakdown['insurance']);
        } else {
            $this->data->breakdown['insurance'] = $insurance;
        }

        return $this;
    }

    /**
     * @return Amount
     */
    public function getItemTotal(): Amount
    {
        return $this->data->breakdown['item_total'] ?? (new Amount())
                ->setValue(0.0)
                ->setCurrencyCode($this->getCurrencyCode());
    }

    /**
     * @param Amount|null $itemTotal
     * @return AmountWithBreakdown
     */
    public function setItemTotal(?Amount $itemTotal): self
    {
        if ($itemTotal === null) {
            unset($this->data->breakdown['item_total']);
        } else {
            $this->data->breakdown['item_total'] = $itemTotal;
        }

        return $this;
    }

    /**
     * @return Amount
     */
    public function getShipping(): Amount
    {
        return $this->data->breakdown['shipping'] ?? (new Amount())
                ->setValue(0.0)
                ->setCurrencyCode($this->getCurrencyCode());
    }

    /**
     * @param Amount|null $shipping
     * @return AmountWithBreakdown
     */
    public function setShipping(?Amount $shipping): self
    {
        if ($shipping === null) {
            unset($this->data->breakdown['shipping']);
        } else {
            $this->data->breakdown['shipping'] = $shipping;
        }

        return $this;
    }

    /**
     * @return Amount
     */
    public function getShippingDiscount(): Amount
    {
        return $this->data->breakdown['shipping_discount'] ?? (new Amount())
                ->setValue(0.0)
                ->setCurrencyCode($this->getCurrencyCode());
    }

    /**
     * @param Amount|null $shippingDiscount
     * @return AmountWithBreakdown
     */
    public function setShippingDiscount(?Amount $shippingDiscount): self
    {
        if ($shippingDiscount === null) {
            unset($this->data->breakdown['shipping_discount']);
        } else {
            $this->data->breakdown['shipping_discount'] = $shippingDiscount;
        }

        return $this;
    }

    /**
     * @return Amount
     */
    public function getDiscount(): Amount
    {
        return $this->data->breakdown['discount'] ?? (new Amount())
                ->setValue(0.0)
                ->setCurrencyCode($this->getCurrencyCode());
    }

    /**
     * @param Amount|null $discount
     * @return AmountWithBreakdown
     */
    public function setDiscount(?Amount $discount): self
    {
        if ($discount === null) {
            unset($this->data->breakdown['discount']);
        } else {
            $this->data->breakdown['discount'] = $discount;
        }

        return $this;
    }

    /**
     * @return Amount
     */
    public function getTaxTotal(): Amount
    {
        return $this->data->breakdown['tax_total'] ?? (new Amount())
                ->setValue(0.0)
                ->setCurrencyCode($this->getCurrencyCode());
    }

    /**
     * @param Amount|null $taxTotal
     * @return AmountWithBreakdown
     */
    public function setTaxTotal(?Amount $taxTotal): self
    {
        if ($taxTotal === null) {
            unset($this->data->breakdown['tax_total']);
        } else {
            $this->data->breakdown['tax_total'] = $taxTotal;
        }

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalBreakdown(): float
    {
        $total = 0.0;
        foreach (['item_total', 'handling', 'insurance', 'shipping', 'tax_total'] as $item) {
            if (isset($this->data->breakdown[$item])) {
                $total += $this->data->breakdown[$item]->getValue();
            }
        }
        foreach (['discount', 'shipping_discount'] as $item) {
            if (isset($this->data->breakdown[$item])) {
                $total -= $this->data->breakdown[$item]->getValue();
            }
        }

        return $total;
    }

    /**
     * @param string $item
     * @param Amount $amount
     * @return AmountWithBreakdown
     */
    public function addBreakdownItem(string $item, Amount $amount): self
    {
        $this->data->breakdown[$item] = $amount;

        return $this;
    }

    /**
     * @param string $item
     * @return Amount|null
     */
    public function getBreakdownItem(string $item): ?Amount
    {
        return $this->data->breakdown[$item] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function stringify(): string
    {
        if (
            $this->getItemTotal()->getValue() === $this->getValue()
            || (
                $this->getHandling()->getValue() === 0.0
                && $this->getInsurance()->getValue() === 0.0
                && $this->getItemTotal()->getValue() === 0.0
                && $this->getShipping()->getValue() === 0.0
                && $this->getShippingDiscount()->getValue() === 0.0
                && $this->getTaxTotal()->getValue() === 0.0
                && $this->getDiscount()->getValue() === 0.0
            )
        ) {
            unset($this->data->breakdown);
        } elseif (!$this->validateBreakdown()) {
            throw new InvalidAmountException(
                \sprintf('Amount of %f differs from %f.', $this->getValue(), $this->getTotalBreakdown())
            );
        } elseif (!$this->validateCurrencyCode()) {
            throw new InvalidAmountException(
                'Currency code of total amount differs from currency code in breakdown amounts.'
            );
        }

        return parent::stringify();
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): object
    {
        $data            = parent::jsonSerialize();
        $data->breakdown = \array_filter($this->data->breakdown);

        if (\count($data->breakdown) === 0) {
            unset($data->breakdown);
        } elseif (!$this->validateBreakdown()) {
            throw new InvalidAmountException(
                \sprintf('Amount of %f differs from %f.', $this->getValue(), $this->getTotalBreakdown())
            );
        } elseif (!$this->validateCurrencyCode()) {
            throw new InvalidAmountException(
                'Currency code of total amount differs from currency code in breakdown amounts.'
            );
        }

        return $data;
    }

    /**
     * @return bool
     */
    public function validateBreakdown(): bool
    {
        return \round($this->getTotalBreakdown(), 2) === \round($this->getValue(), 2);
    }

    /**
     * @return bool
     */
    public function validateCurrencyCode(): bool
    {
        $cCode = $this->getCurrencyCode();

        foreach (
            ['item_total', 'handling', 'insurance',
             'shipping', 'tax_total', 'discount', 'shipping_discount'
            ] as $item
        ) {
            if (isset($this->data->breakdown[$item]) && $this->data->breakdown[$item]->getCurrencyCode() !== $cCode) {
                return false;
            }
        }

        return true;
    }
}
