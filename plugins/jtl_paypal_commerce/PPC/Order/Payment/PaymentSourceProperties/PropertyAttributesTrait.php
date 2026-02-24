<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceProperties;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSONData;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Trait PropertyExperienceContextTrait
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
trait PropertyAttributesTrait
{
    public function setAttributes(?JSON $attributes = null): static
    {
        if ($attributes === null || $attributes->getData() === null) {
            $this->setMappedValue('attributes', null);

            return $this;
        }

        $data = $attributes->getData();
        foreach ($data as $name => $attribute) {
            $this->addAttribute($name, $attribute instanceof JSON ? $attribute : new JSON($attribute));
        }

        return $this;
    }

    public function addAttribute(string $name, ?JSON $attribute = null): static
    {
        $attributes        = $this->getMappedValue('attributes') ?? (object)[];
        $attributes->$name = $attribute === null ? null : $attribute->getData();
        $this->setMappedValue('attributes', $attributes);

        return $this;
    }

    public function getAttributes(): ?JSON
    {
        $attributes = $this->getMappedValue('attributes');
        if (empty($attributes) || ($attributes instanceof SerializerInterface && $attributes->isEmpty())) {
            return null;
        }

        return $attributes instanceof JSON ? $attributes : new JSON($attributes);
    }

    public function getAttribute(string $name): ?JSONData
    {
        $data = $this->getAttributes();
        if ($data === null) {
            return null;
        }

        $attribute = $data->getData()->$name;
        if (empty($attribute) || ($attribute instanceof SerializerInterface && $attribute->isEmpty())) {
            return null;
        }

        return $attribute instanceof JSON ? new JSONData($attribute->getData()) : new JSONData($attribute);
    }

    public function serializeAttributes(object $data): void
    {
        $mappedName = $this->mapEntitie('attributes');
        if (empty($data->$mappedName)) {
            unset($data->$mappedName);

            return;
        }

        foreach ($data->$mappedName as $attribute) {
            if (!empty($attribute)) {
                return;
            }
        }

        unset($data->$mappedName);
    }
}
