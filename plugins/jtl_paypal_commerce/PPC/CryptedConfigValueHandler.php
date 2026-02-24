<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use JTL\Services\JTL\CryptoServiceInterface;
use JTL\Shop;

/**
 * Class CryptedConfigValueHandler
 * @package Plugin\jtl_paypal_commerce\PPC
 */
class CryptedConfigValueHandler implements ConfigValueHandlerInterface
{
    private CryptoServiceInterface $cryptoService;

    /**
     * CryptedConfigValueHandler constructor
     */
    public function __construct(?CryptoServiceInterface $cryptoService = null)
    {
        $this->cryptoService = $cryptoService ?? Shop::Container()->getCryptoService();
    }

    public function prepare(string $value): string
    {
        return $value;
    }

    public function followUp(string $value): string
    {
        return $value;
    }

    public function getValue(string $value): string
    {
        return \trim($this->cryptoService->decryptXTEA($this->prepare($value)));
    }

    public function setValue(string $value): string
    {
        return $this->followUp($this->cryptoService->encryptXTEA($value));
    }
}
