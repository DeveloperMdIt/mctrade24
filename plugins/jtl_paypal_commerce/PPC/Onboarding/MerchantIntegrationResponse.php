<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Onboarding;

use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

/**
 * Class MerchantIntegrationResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Onboarding
 */
class MerchantIntegrationResponse extends JSONResponse
{
    /** @var Product[]|null */
    private ?array $products = null;

    /** @var Capability[]|null */
    private ?array $capabilities = null;

    /**
     * @return string|null
     * @throws UnexpectedResponseException
     */
    public function getMerchantId(): ?string
    {
        try {
            return $this->getData()->merchant_id ?? null;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return Product[]
     * @throws UnexpectedResponseException
     */
    public function getProducts(): array
    {
        if ($this->products !== null) {
            return $this->products;
        }

        $this->products = [];
        try {
            foreach ($this->getData()->products ?? [] as $product) {
                $this->products[] = new Product($product);
            }
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }

        return $this->products;
    }

    /**
     * @param string $name
     * @return Product|null
     */
    public function getProductByName(string $name): ?Product
    {
        try {
            $products = $this->getProducts();
        } catch (UnexpectedResponseException) {
            return null;
        }

        foreach ($products as $product) {
            if ($product->getName() === $name) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return bool
     * @throws UnexpectedResponseException
     */
    public function getPaymentsReceivable(): bool
    {
        try {
            return $this->getData()->payments_receivable ?? false;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return Capability[]
     * @throws UnexpectedResponseException
     */
    public function getCapabilities(): array
    {
        if ($this->capabilities !== null) {
            return $this->capabilities;
        }

        $this->capabilities = [];
        try {
            foreach ($this->getData()->capabilities ?? [] as $capability) {
                $this->capabilities[] = new Capability($capability);
            }
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }

        return $this->capabilities;
    }

    /**
     * @param string $name
     * @return Capability|null
     */
    public function getCapabilityByName(string $name): ?Capability
    {
        try {
            $capabilities = $this->getCapabilities();
        } catch (UnexpectedResponseException) {
            return null;
        }

        foreach ($capabilities as $capability) {
            if ($capability->getName() === $name) {
                return $capability;
            }
        }

        return null;
    }

    /**
     * @throws UnexpectedResponseException
     */
    public function getLegalName(): string
    {
        try {
            return $this->getData()->legal_name ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return bool
     * @throws UnexpectedResponseException
     */
    public function isPrimaryEmailConfirmed(): bool
    {
        try {
            return $this->getData()->primary_email_confirmed ?? false;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string|null
     * @throws UnexpectedResponseException
     */
    public function getPrimaryEmail(): ?string
    {
        try {
            return $this->getData()->primary_email ?? null;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }
}
