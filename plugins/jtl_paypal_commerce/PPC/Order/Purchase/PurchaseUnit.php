<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Purchase;

use JTL\Cart\Cart;
use JTL\Cart\CartItem;
use JTL\Catalog\Currency;
use JTL\Helpers\Tax;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\PPC\Order\Amount;
use Plugin\jtl_paypal_commerce\PPC\Order\AmountWithBreakdown;
use Plugin\jtl_paypal_commerce\PPC\Order\Capture;
use Plugin\jtl_paypal_commerce\PPC\Order\InvalidAmountException;
use Plugin\jtl_paypal_commerce\PPC\Order\Shipping;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

use function Functional\first;

/**
 * Class PurchaseUnit
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PurchaseUnit extends JSON
{
    public const REFERENCE_DEFAULT = 'default';

    private bool $withTaxRate;

    /**
     * PurchaseUnit constructor.
     * @param object|null $data
     * @param bool        $withTaxRate
     */
    public function __construct(?object $data = null, bool $withTaxRate = false)
    {
        parent::__construct($data ?? (object)[]);

        $this->withTaxRate = $withTaxRate;
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $shippingData = $this->getData()->shipping ?? null;
        if ($shippingData !== null && !($shippingData instanceof Shipping)) {
            $this->setShipping(new Shipping($shippingData));
        }
        $amountData = $this->getData()->amount ?? null;
        if ($amountData !== null && !($amountData instanceof Amount)) {
            $this->setAmount((isset($amountData->breakdown)
                ? new AmountWithBreakdown($amountData)
                : new Amount())->setData($amountData));
        }
        $items = $this->getData()->items ?? [];
        foreach (\array_keys($items) as $key) {
            if ($items[$key] !== null && !($items[$key] instanceof PurchaseItem)) {
                $items[$key] = new PurchaseItem($items[$key]);
            }
        }
        $this->setItems($items);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReferenceId(): ?string
    {
        return $this->data->reference_id ?? null;
    }

    /**
     * @param string|null $referenceId
     * @return PurchaseUnit
     */
    public function setReferenceId(?string $referenceId): self
    {
        if ($referenceId === null) {
            unset($this->data->reference_id);
        } else {
            $this->data->reference_id = \mb_substr($referenceId, 0, 256);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasAmount(): bool
    {
        return isset($this->data->amount);
    }

    /**
     * @return Amount|AmountWithBreakdown
     */
    public function getAmount(): Amount|AmountWithBreakdown
    {
        return $this->data->amount;
    }

    /**
     * @param Amount|AmountWithBreakdown $amount
     * @return PurchaseUnit
     */
    public function setAmount(Amount|AmountWithBreakdown $amount): self
    {
        $this->data->amount = $amount;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomId(): ?string
    {
        return $this->data->custom_id ?? null;
    }

    /**
     * @param string|null $customId
     * @return PurchaseUnit
     */
    public function setCustomId(?string $customId): self
    {
        if ($customId === null) {
            unset($this->data->custom_id);
        } else {
            $this->data->custom_id = \mb_substr($customId, 0, 127);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->data->description ?? '';
    }

    /**
     * @param string|null $description
     * @return PurchaseUnit
     */
    public function setDescription(?string $description): self
    {
        if ($description === null) {
            unset($this->data->description);
        } else {
            $this->data->description = PPCHelper::shortenStr($description, 127);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInvoiceId(): ?string
    {
        return $this->data->invoice_id ?? null;
    }

    /**
     * @param string|null $invoiceId
     * @return PurchaseUnit
     */
    public function setInvoiceId(?string $invoiceId): self
    {
        if ($invoiceId === null) {
            unset($this->data->invoice_id);
        } else {
            $this->data->invoice_id = \mb_substr($invoiceId, 0, 127);
        }

        return $this;
    }

    /**
     * @return Shipping|null
     */
    public function getShipping(): ?Shipping
    {
        return $this->data->shipping ?? null;
    }

    /**
     * @param Shipping|null $shipping
     * @return PurchaseUnit
     */
    public function setShipping(?Shipping $shipping): self
    {
        if ($shipping === null || $shipping->isEmpty()) {
            unset($this->data->shipping);
        } else {
            $this->data->shipping = $shipping;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSoftDescriptor(): ?string
    {
        return $this->data->soft_descriptor ?? null;
    }

    /**
     * @param string|null $softDescriptor
     * @return PurchaseUnit
     */
    public function setSoftDescriptor(?string $softDescriptor): self
    {
        if ($softDescriptor === null) {
            unset($this->data->soft_descriptor);
        } else {
            $this->data->soft_descriptor = PPCHelper::shortenStr($softDescriptor, 22);
        }

        return $this;
    }

    /**
     * @return Capture[]|null
     */
    public function getCaptures(): ?array
    {
        $captures = $this->getData()->payments->captures ?? null;

        if ($captures === null) {
            return null;
        }

        foreach (\array_keys($captures) as $key) {
            if (!($captures[$key] instanceof Capture)) {
                $captures[$key] = new Capture($captures[$key]);
            }
        }

        return $captures;
    }

    /**
     * @param string|null $captureId
     * @return Capture|null
     */
    public function getCapture(?string $captureId = null): ?Capture
    {
        $captures = $this->getCaptures();

        if ($captures === null || count($captures) === 0) {
            return null;
        }

        return first($captures, static function (Capture $item) use ($captureId) {
            return $captureId === null || $item->getId() === $captureId;
        });
    }

    /**
     * @return PurchaseItem[]
     */
    public function getItems(): array
    {
        return $this->data->items;
    }

    /**
     * @param PurchaseItem[] $items
     * @return self
     */
    public function setItems(array $items): self
    {
        $this->data->items = $items;

        return $this;
    }

    /**
     * @param PurchaseItem $item
     * @return self
     */
    public function addItem(PurchaseItem $item): self
    {
        $this->data->items[] = $item;

        return $this;
    }

    public static function getNameWithQuantity(int|float $qty, string $decSep, ?string $unit, string $name): string
    {
        if ((float)$qty === 1.0) {
            return $name;
        }

        $qtyStr = \number_format($qty, \is_float($qty) ? 2 : 0, $decSep, '');

        return $name . ' (' . $qtyStr . ' ' . ($unit ?? 'x') . ')';
    }

    /**
     * @param Cart     $cart
     * @param Currency $currency
     * @param bool     $merchant
     * @param bool     $withTaxRate
     * @return PurchaseItem[]
     */
    public static function createItemsFromCart(Cart $cart, Currency $currency, bool $merchant, bool $withTaxRate): array
    {
        $items        = [];
        $currencyCode = $currency->getCode();
        foreach ($cart->PositionenArr as $cartPos) {
            if (
                !\in_array($cartPos->nPosTyp, [
                    \C_WARENKORBPOS_TYP_ARTIKEL,
                    \C_WARENKORBPOS_TYP_GRATISGESCHENK
                ], true)
            ) {
                continue;
            }

            $taxRate = CartItem::getTaxRate($cartPos);
            $name    = \is_array($cartPos->cName) ? $cartPos->cName[Shop::getLanguageCode()] : $cartPos->cName;
            $netto   = $cartPos->fPreis * $cartPos->nAnzahl;
            $gross   = Tax::getGross($netto, $taxRate);
            // because PayPal does not support fractional quantities
            // and to avoid rounding differences: quantity will always set to 1
            $quantity = 1;
            $name     = self::getNameWithQuantity(
                $cartPos->nAnzahl,
                $currency->getDecimalSeparator(),
                $cartPos->cEinheit,
                $name
            );

            $item = (new PurchaseItem())
                ->setName($name)
                ->setSKU($cartPos->cArtNr ?? null)
                ->setURLFailSave($cartPos->Artikel->cURLFull ?? null)
                ->setImageURLFailSave($cartPos->Artikel->cVorschaubildURL ?? null)
                ->setCategoryByProduct($cartPos->Artikel)
                ->setQuantity($quantity)
                ->setAmount((new Amount())
                    ->setValue(Currency::convertCurrency($merchant ? $netto : $gross, $currencyCode))
                    ->setCurrencyCode($currencyCode));
            if ($withTaxRate) {
                $item->setAmount((new Amount())
                        ->setValue(Currency::convertCurrency($netto, $currencyCode))
                        ->setCurrencyCode($currencyCode))
                     ->setTax((new Amount())
                         ->setValue(Currency::convertCurrency($gross - \round($netto, 2), $currencyCode))
                         ->setCurrencyCode($currencyCode))
                     ->setTaxRate((float)($cartPos->fMwSt ?? (float)Tax::getSalesTax($cartPos->kSteuerklasse)));
            }

            $items[] = $item;
        }

        return $items;
    }

    public function addItemsFromCart(Cart $cart, Currency $currency, bool $merchant): self
    {
        $this->setItems(self::createItemsFromCart($cart, $currency, $merchant, $this->withTaxRate));
        $amount = AmountWithBreakdown::createFromCart($cart, $currency->getCode(), $merchant)
                                     ->setItemTotal((new Amount())
                                         ->setValue($this->getTotalItemValue())
                                         ->setCurrencyCode($currency->getCode()));
        if ($this->withTaxRate) {
            $amount->setTaxTotal((new Amount())
                        ->setValue($this->getTotalItemTax())
                        ->setCurrencyCode($currency->getCode()));
        }
        $this->setAmount($amount->adjustTotal());

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalItemValue(): float
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += (\round($item->getAmount()->getValue(), 2) * $item->getQuantity());
        }

        return \round($total, 2);
    }

    /**
     * @return float
     */
    public function getTotalItemTax(): float
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += (\round($item->getTax()->getValue(), 2) * $item->getQuantity());
        }

        return \round($total, 2);
    }

    /**
     * @return bool
     */
    public function validateItems(): bool
    {
        $amount = $this->getAmount();
        if ($amount instanceof AmountWithBreakdown) {
            if (!$amount->validateBreakdown()) {
                return false;
            }

            $itemTotal = \round($this->getTotalItemValue(), 2);
            $taxTotal  = \round($this->getTotalItemTax(), 2);
            return $itemTotal === \round($amount->getItemTotal()->getValue(), 2)
                && ($taxTotal === 0.0 || $taxTotal === \round($amount->getTaxTotal()->getValue(), 2));
        }

        return \count($this->getItems()) === 0;
    }

    /**
     * @return bool
     */
    public function validateCurrencyCode(): bool
    {
        $amount = $this->getAmount();
        if ($amount instanceof AmountWithBreakdown && !$amount->validateCurrencyCode()) {
            return false;
        }

        $cCode = $amount->getCurrencyCode();
        foreach ($this->getItems() as $item) {
            if ($item->getAmount()->getCurrencyCode() !== $cCode) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (empty($data->shipping) || ($data->shipping instanceof SerializerInterface && $data->shipping->isEmpty())) {
            unset($data->shipping);
        }
        if (!\is_array($data->items) || \count($data->items) === 0) {
            unset($data->items);
        } elseif (!$this->validateItems()) {
            $amount = $this->getAmount();
            throw new InvalidAmountException(
                \sprintf(
                    'Total amount of %.2f differs from %.2f.',
                    \round(($amount instanceof AmountWithBreakdown)
                        ? $amount->getItemTotal()->getValue()
                        : $this->getAmount()->getValue(), 2),
                    \round($this->getTotalItemValue(), 2)
                )
            );
        } elseif (!$this->validateCurrencyCode()) {
            throw new InvalidAmountException(
                'Currency code of total amount differs from currency code in items.'
            );
        }

        return $data;
    }
}
