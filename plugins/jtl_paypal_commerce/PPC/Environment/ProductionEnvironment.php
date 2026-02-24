<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Environment;

/**
 * Class LiveEnvironment
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class ProductionEnvironment extends PPCEnvironment
{
    /**
     * @inheritDoc
     */
    public function baseUrl(): string
    {
        return 'https://api-m.paypal.com';
    }

    /**
     * @inheritDoc
     */
    public function isSandbox(): bool
    {
        return false;
    }
}
