<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Environment;

/**
 * Class SandboxEnvironment
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class SandboxEnvironment extends PPCEnvironment
{
    /**
     * @inheritDoc
     */
    public function baseUrl(): string
    {
        return 'https://api-m.sandbox.paypal.com';
    }

    /**
     * @inheritDoc
     */
    public function isSandbox(): bool
    {
        return true;
    }
}
