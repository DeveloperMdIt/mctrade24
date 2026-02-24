<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Purchase;

use InvalidArgumentException;
use JTL\Catalog\Product\Artikel;
use JTL\Helpers\Text;
use Plugin\jtl_paypal_commerce\PPC\Order\Amount;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class PurchaseItem
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class PurchaseItem extends JSON
{
    /** @var string    Goods that are stored, delivered, and used in their electronic format. */
    public const CATEGORY_DIGITAL = 'DIGITAL_GOODS';

    /** @var string    A tangible item that can be shipped with proof of delivery */
    public const CATEGORY_PHYSICAL = 'PHYSICAL_GOODS';

    /** @var string    A contribution or gift for which no good or service is exchanged. */
    public const CATEGORY_DONATION = 'DONATION';

    public const ATTRIBUTE_PURCHASE_CATEGORY = 'paypal_purchase_category';

    protected const CATGERY_ENUM = [
        self::CATEGORY_DIGITAL, self::CATEGORY_PHYSICAL, self::CATEGORY_DONATION,
    ];

    /**
     * PurchaseItem constructor
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[
            'name'        => '',
            'unit_amount' => new Amount(),
            'quantity'    => 0.0,
        ]);

        if (isset($data, $data->url)) {
            $url = $data->url;
            unset($this->data->url);
            $this->setURL($url);
        }
        if (isset($data, $data->image_url)) {
            $url = $data->image_url;
            unset($this->data->image_url);
            $this->setImageURL($url);
        }
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $amountData = $this->getData()->unit_amount ?? null;
        if ($amountData !== null && !($amountData instanceof Amount)) {
            $this->setAmount(new Amount($amountData));
        }
        $taxData = $this->getData()->tax ?? null;
        if ($taxData !== null && !($taxData instanceof Amount)) {
            $this->setTax(new Amount($taxData));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->data->name ?? '';
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->data->name = PPCHelper::shortenStr(Text::unhtmlentities($name), 127);

        return $this;
    }

    public function getSKU(): ?string
    {
        return $this->data->sku;
    }

    public function setSKU(?string $sku = null): self
    {
        if ($sku === null) {
            unset($this->data->sku);
        } else {
            $this->data->sku = PPCHelper::shortenStr($sku, 127);
        }

        return $this;
    }

    /**
     * @return Amount
     */
    public function getAmount(): Amount
    {
        return $this->data->unit_amount ?? new Amount();
    }

    /**
     * @param Amount $amount
     * @return self
     */
    public function setAmount(Amount $amount): self
    {
        $this->data->unit_amount = $amount;

        return $this;
    }

    /**
     * @return Amount
     */
    public function getTax(): Amount
    {
        return $this->data->tax ?? new Amount();
    }

    /**
     * @param Amount $tax
     * @return self
     */
    public function setTax(Amount $tax): self
    {
        $this->data->tax = $tax;

        return $this;
    }

    public function getTaxRate(): float
    {
        return (float)($this->data->tax_rate ?? 0.0);
    }

    public function setTaxRate(float $taxRate): self
    {
        $this->data->tax_rate = $taxRate;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return (int)$this->data->quantity;
    }

    /**
     * @param int $quantity
     * @return self
     */
    public function setQuantity(int $quantity): self
    {
        $this->data->quantity = $quantity;

        return $this;
    }

    public function getURL(): ?string
    {
        return $this->data->url ?? null;
    }

    public function setURL(string $url): self
    {
        if ($url === '') {
            unset($this->data->url);

            return $this;
        }

        if (\mb_strlen($url) < 2048) {
            $filteredVar = \filter_var($url, \FILTER_VALIDATE_URL);
            if ($filteredVar === false) {
                throw new InvalidArgumentException('Invalid url');
            }
            $this->data->url = $filteredVar;
        } else {
            throw new InvalidArgumentException('url too long');
        }

        return $this;
    }

    public function setURLFailSave(?string $url): self
    {
        try {
            $this->setURL($url ?? '');
        } catch (InvalidArgumentException) {
            $this->setURL('');
        }

        return $this;
    }

    public function getImageURL(): ?string
    {
        return $this->data->image_url ?? null;
    }

    public function setImageURL(string $url): self
    {
        if ($url === '') {
            unset($this->data->image_url);

            return $this;
        }

        if (\mb_strlen($url) < 2048) {
            $filteredVar = \filter_var($url, \FILTER_VALIDATE_URL, \FILTER_FLAG_PATH_REQUIRED);
            if ($filteredVar === false) {
                throw new InvalidArgumentException('Invalid image url');
            }
            if (!\preg_match('/^(https:)([\/|.\w\s-])*\.(?:jpg|gif|png|jpeg|JPG|GIF|PNG|JPEG)/', $url)) {
                throw new InvalidArgumentException('Image url does not match regular pattern');
            }
            $this->data->image_url = $filteredVar;
        } else {
            throw new InvalidArgumentException('Image url too long');
        }

        return $this;
    }

    public function setImageURLFailSave(?string $url): self
    {
        try {
            $this->setImageURL($url ?? '');
        } catch (InvalidArgumentException) {
            $this->setImageURL('');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->data->category ?? self::CATEGORY_PHYSICAL;
    }

    /**
     * @param string $category
     * @return self
     */
    public function setCategory(string $category): self
    {
        if (!\in_array($category, self::CATGERY_ENUM)) {
            throw new InvalidArgumentException($category . ' is not a valid purchase category');
        }

        $this->data->category = PPCHelper::shortenStr($category, 20);

        return $this;
    }

    public function setCategoryByProduct(?Artikel $product): self
    {
        if ($product === null) {
            $this->setCategory(self::CATEGORY_PHYSICAL);

            return $this;
        }

        try {
            $this->setCategory(
                $product->getFunctionalAttributevalue(self::ATTRIBUTE_PURCHASE_CATEGORY) ?? self::CATEGORY_PHYSICAL
            );
        } catch (InvalidArgumentException) {
            $this->setCategory(self::CATEGORY_PHYSICAL);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        $data = clone $this->getData();

        if (empty($data->tax) || ($data->tax instanceof SerializerInterface && $data->tax->isEmpty())) {
            unset($data->tax);
        }
        if (empty($data->url)) {
            unset($data->url);
        }
        if (empty($data->image_url)) {
            unset($data->image_url);
        }
        if (empty($data->sku)) {
            unset($data->sku);
        }

        $data->quantity = \number_format($this->getQuantity(), 0, '.', '');
        if (($data->tax_rate ?? null) !== null) {
            $data->tax_rate = \number_format($this->getTaxrate(), 2, '.', '');
        } else {
            unset($data->tax_rate);
        }

        return $data;
    }
}
