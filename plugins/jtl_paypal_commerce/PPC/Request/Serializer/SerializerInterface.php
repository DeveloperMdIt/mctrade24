<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request\Serializer;

/**
 * Interface SerializerInterface
 * @package Plugin\jtl_paypal_commerce\PPC\Request\Serializer
 */
interface SerializerInterface
{
    /**
     * @return string
     */
    public function contentType(): string;

    /**
     * @return string|null
     */
    public function stringify(): ?string;

    /**
     * @return bool
     */
    public function isEmpty(): bool;
}
