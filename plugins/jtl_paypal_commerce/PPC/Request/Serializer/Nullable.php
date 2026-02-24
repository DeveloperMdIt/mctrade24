<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request\Serializer;

/**
 * Class Nullable
 * @package Plugin\jtl_paypal_commerce\PPC\Request\Serializer
 */
class Nullable implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'application/json';
    }

    /**
     * @inheritDoc
     */
    public function stringify(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return true;
    }
}
