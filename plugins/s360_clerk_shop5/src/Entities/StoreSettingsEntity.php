<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Entities;

class StoreSettingsEntity extends Entity
{
    public function __construct(
        private ?int $currency,
        private bool $enableProducts,
        private bool $enableCharacteristics,
        private bool $enableAttributes,
        private bool $enableFuncAttributes,
        private bool $enableCategories,
        private bool $enableCustomers,
        private bool $enableLastOrders,
        private bool $enableBlog,
        private bool $enableCms,
        private bool $productsWithoutPrice,
        private bool $minBulkPriceAsPrice,
        private ?string $categorySeparator,
        private ?string $blogIdPrefix,
        private ?string $cmsIdPrefix,
        private ?string $mappingColors,
        private ?string $mappingColorNames,
        private ?string $mappingGender,
        private ?string $facetsDesign,
        private bool $disableAuthCheck,
        private string $feedProductMode
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            !empty($data['currency']) ? (int) $data['currency'] : null,
            !empty($data['enable_products']) ? $data['enable_products'] == 1 : false,
            !empty($data['enable_characteristics']) ? $data['enable_characteristics'] == 1 : false,
            !empty($data['enable_attributes']) ? $data['enable_attributes'] == 1 : false,
            !empty($data['enable_func_attributes']) ? $data['enable_func_attributes'] == 1 : false,
            !empty($data['enable_categories']) ? $data['enable_categories'] == 1 : false,
            !empty($data['enable_customers']) ? $data['enable_customers'] == 1 : false,
            !empty($data['enable_last_orders']) ? $data['enable_last_orders'] == 1 : false,
            !empty($data['enable_blog']) ? $data['enable_blog'] == 1 : false,
            !empty($data['enable_cms']) ? $data['enable_cms'] == 1 : false,
            !empty($data['products_without_price']) ? $data['products_without_price'] == 1 : false,
            !empty($data['min_bulk_price_as_price']) ? $data['min_bulk_price_as_price'] == 1 : false,
            $data['category_separator'] ?? null,
            $data['blog_id_prefix'] ?? null,
            $data['cms_id_prefix'] ?? null,
            $data['mapping_colors'] ?? null,
            $data['mapping_color_names'] ?? null,
            $data['mapping_gender'] ?? null,
            $data['facets_design'] ?? null,
            !empty($data['disable_auth_check']) ? $data['disable_auth_check'] == 1 : false,
            $data['feed_product_mode'] ?? 'all',
        );
    }

    public function toArray(): array
    {
        return [
            'currency' => $this->currency,
            'enable_products' => $this->enableProducts,
            'enable_characteristics' => $this->enableCharacteristics,
            'enable_attributes' => $this->enableAttributes,
            'enable_func_attributes' => $this->enableFuncAttributes,
            'enable_categories' => $this->enableCategories,
            'enable_customers' => $this->enableCustomers,
            'enable_last_orders' => $this->enableLastOrders,
            'enable_blog' => $this->enableBlog,
            'enable_cms' => $this->enableCms,
            'products_without_price' => $this->productsWithoutPrice,
            'min_bulk_price_as_price' => $this->minBulkPriceAsPrice,
            'category_separator' => $this->categorySeparator,
            'blog_id_prefix' => $this->blogIdPrefix,
            'cms_id_prefix' => $this->cmsIdPrefix,
            'mapping_colors' => $this->mappingColors,
            'mapping_color_names' => $this->mappingColorNames,
            'mapping_gender' => $this->mappingGender,
            'facets_design' => $this->facetsDesign,
            'disable_auth_check' => $this->disableAuthCheck,
            'feed_product_mode' => $this->feedProductMode,
        ];
    }
    
    public function getFeedProductMode(): string
    {
        return $this->feedProductMode;
    }

    public function getCurrency(): ?int
    {
        return $this->currency;
    }

    public function getEnableProducts(): bool
    {
        return $this->enableProducts;
    }

    public function getEnableCategories(): bool
    {
        return $this->enableCategories;
    }

    public function getEnableCustomers(): bool
    {
        return $this->enableCustomers;
    }

    public function getEnableLastOrders(): bool
    {
        return $this->enableLastOrders;
    }

    public function getEnableBlog(): bool
    {
        return $this->enableBlog;
    }

    public function getEnableCms(): bool
    {
        return $this->enableCms;
    }

    public function getProductsWithoutPrice(): bool
    {
        return $this->productsWithoutPrice;
    }

    public function getCategorySeparator(): ?string
    {
        return $this->categorySeparator;
    }

    public function getBlogIdPrefix(): ?string
    {
        return $this->blogIdPrefix;
    }

    public function getCmsIdPrefix(): ?string
    {
        return $this->cmsIdPrefix;
    }

    public function getMappingColors(): ?string
    {
        return $this->mappingColors;
    }

    public function getMappingColorNames(): ?string
    {
        return $this->mappingColorNames;
    }

    public function getMappingGender(): ?string
    {
        return $this->mappingGender;
    }

    public function getDisableAuthCheck(): bool
    {
        return $this->disableAuthCheck;
    }

    public function getEnableCharacteristics(): bool
    {
        return $this->enableCharacteristics;
    }

    public function getEnableAttributes(): bool
    {
        return $this->enableAttributes;
    }

    public function getEnableFuncAttributes(): bool
    {
        return $this->enableFuncAttributes;
    }

    public function getMinBulkPriceAsPrice(): bool
    {
        return $this->minBulkPriceAsPrice;
    }

    public function getFacetsDesign(): ?string
    {
        return $this->facetsDesign;
    }
}
