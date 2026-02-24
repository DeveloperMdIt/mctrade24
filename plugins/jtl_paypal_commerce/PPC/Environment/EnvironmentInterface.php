<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Environment;

/**
 * Interface Environment
 * @package Plugin\jtl_paypal_commerce\PPC\HTTPClient
 */
interface EnvironmentInterface
{
    /**
     * @return string
     */
    public function baseUrl(): string;

    /**
     * @return bool
     */
    public function isSandbox(): bool;

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @return void
     */
    public function reInit(string $clientId, string $clientSecret): void;

    /**
     * @return string
     */
    public function getClientId(): string;

    /**
     * @return string
     */
    public function getClientSecret(): string;

    /**
     * @return string
     */
    public function getAuthorizationString(): string;

    /**
     * @return string|null
     */
    public function getMetaDataId(): ?string;
}
