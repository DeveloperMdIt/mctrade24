<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use JTL\Services\JTL\CryptoServiceInterface;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;

/**
 * Class ConfigValues
 * @package Plugin\jtl_paypal_commerce\PPC
 */
class ConfigValues
{
    public const WORKING_MODE_SANDBOX    = 'sandbox';
    public const WORKING_MODE_PRODUCTION = 'production';

    private Configuration $config;

    private ConfigValueHandlerInterface $cryptedValueHandler;

    private ConfigValueHandlerInterface $cryptedTagValueHandler;

    private ?string $authToken = null;

    private ?string $workingMode = null;

    /** @var string[] */
    private array $nonce = [];

    /** @var string[] */
    private array $clientId = [];

    /** @var string[] */
    private array $clientSecret = [];

    /** @var string[] */
    private array $merchantId = [];

    /**
     * ConfigValues constructor
     * @param Configuration          $config
     * @param CryptoServiceInterface $cryptoService
     */
    public function __construct(Configuration $config, CryptoServiceInterface $cryptoService)
    {
        $this->config                 = $config;
        $this->cryptedValueHandler    = new CryptedConfigValueHandler($cryptoService);
        $this->cryptedTagValueHandler = new CryptedTagConfigValueHandler($cryptoService);
    }

    public function reset(): void
    {
        $this->authToken    = null;
        $this->workingMode  = null;
        $this->nonce        = [];
        $this->clientId     = [];
        $this->clientSecret = [];
    }

    public function isAuthConfigured(): bool
    {
        return $this->getClientID() !== ''
            && $this->getClientSecret() !== '';
    }

    public function getAuthToken(): string
    {
        if ($this->authToken === null) {
            $repository      = $this->config->getRepository();
            $valueName       = $this->config->getPrefix() . 'authToken';
            $cryptedToken    = $repository->getValue($valueName);
            $this->authToken = $cryptedToken === null ? null : $this->cryptedValueHandler->getValue($cryptedToken);
        }

        return $this->authToken ?? '';
    }

    public function setAuthToken(string $authToken): void
    {
        if ($this->authToken === $authToken) {
            return;
        }

        $repository = $this->config->getRepository();
        $valueName  = $this->config->getPrefix() . 'authToken';
        $repository->delete($valueName);
        $repository->insert($valueName, $this->cryptedValueHandler->setValue($authToken));
        $this->authToken = $authToken;
    }

    public function clearAuthToken(): void
    {
        $this->config->saveConfigItems(['authToken' => '']);
        $this->authToken = null;
        Token::inValidate();
    }

    public function getNonce(?string $workingMode = null): string
    {
        $workingMode = $workingMode ?? $this->getWorkingMode();
        if (($this->nonce[$workingMode] ?? null) === null) {
            $repository   = $this->config->getRepository();
            $valueName    = $this->config->getPrefix() . 'nonce_' . $workingMode;
            $cryptedNonce = $repository->getValue($valueName);

            $this->nonce[$workingMode] = $cryptedNonce === null
                ? null
                : $this->cryptedValueHandler->getValue($cryptedNonce);
        }

        return $this->nonce[$workingMode] ?? '';
    }

    public function setNonce(string $nonce, ?string $workingMode = null): void
    {
        $workingMode = $workingMode ?? $this->getWorkingMode();
        if (($this->nonce[$workingMode] ?? null) === $nonce) {
            return;
        }

        $repository = $this->config->getRepository();
        $valueName  = $this->config->getPrefix() . 'nonce_' . $workingMode;
        $repository->delete($valueName);
        $repository->insert($valueName, $this->cryptedValueHandler->setValue($nonce));
        $this->nonce[$workingMode] = $nonce;
        $this->clearAuthToken();
    }

    public function getWorkingMode(): string
    {
        if ($this->workingMode === null) {
            $this->workingMode = $this->config->getPrefixedConfigItem('clientType', self::WORKING_MODE_SANDBOX);
        }

        return $this->workingMode;
    }

    public function setWorkingMode(string $workingMode): void
    {
        if ($this->workingMode === $workingMode) {
            return;
        }

        $this->config->saveConfigItems(['clientType' => $workingMode]);
        $this->workingMode = $this->config->getPrefixedConfigItem('clientType');
        $this->clearAuthToken();
    }

    public function getClientID(?string $workingMode = null): string
    {
        $workingMode = $workingMode ?? $this->getWorkingMode();
        if (($this->clientId[$workingMode] ?? null) === null) {
            $cryptedId = $this->config->getPrefixedConfigItem('clientID_' . $workingMode);

            $this->clientId[$workingMode] = $cryptedId === null
                ? null
                : $this->cryptedTagValueHandler->getValue($cryptedId);
        }

        return $this->clientId[$workingMode] ?? '';
    }

    public function setClientID(string $clientID, ?string $workingMode = null): void
    {
        $workingMode = $workingMode ?? $this->getWorkingMode();
        if (($this->clientId[$workingMode] ?? null) === $clientID) {
            return;
        }

        if ($clientID === '') {
            $this->config->deleteConfigItems(['clientID_' . $workingMode]);
        } else {
            $this->config->saveConfigItems(
                ['clientID_' . $workingMode => $this->cryptedTagValueHandler->setValue($clientID)]
            );
        }
        $this->clientId[$workingMode] = $clientID;
        $this->clearAuthToken();
    }

    public function migrateClientId(string $workingMode): void
    {
        $clientId = $this->config->getPrefixedConfigItem('clientID_' . $workingMode, '');
        if (\strcmp($clientId, $this->cryptedTagValueHandler->prepare($clientId)) === 0) {
            $this->setClientID($clientId, $workingMode);
        }
    }

    public function getClientSecret(?string $workingMode = null): string
    {
        $workingMode = $workingMode ?? $this->getWorkingMode();
        if (($this->clientSecret[$workingMode] ?? null) === null) {
            $cryptedSecret = $this->config->getPrefixedConfigItem('clientSecret_' . $workingMode);

            $this->clientSecret[$workingMode] = $cryptedSecret === null
                ? null
                : $this->cryptedTagValueHandler->getValue($cryptedSecret);
        }

        return $this->clientSecret[$workingMode] ?? '';
    }

    public function setClientSecret(string $clientSecret, ?string $workingMode = null): void
    {
        $workingMode = $workingMode ?? $this->getWorkingMode();
        if (($this->clientSecret[$workingMode] ?? null) === $clientSecret) {
            return;
        }

        if ($clientSecret === '') {
            $this->config->deleteConfigItems(['clientSecret_' . $workingMode]);
        } else {
            $this->config->saveConfigItems(
                ['clientSecret_' . $workingMode => $this->cryptedTagValueHandler->setValue($clientSecret)]
            );
        }
        $this->clientSecret[$workingMode] = $clientSecret;
        $this->clearAuthToken();
    }

    public function migrateClientSecret(string $workingMode): void
    {
        $clientSecret = $this->config->getPrefixedConfigItem('clientSecret_' . $workingMode, '');
        if (\strcmp($clientSecret, $this->cryptedTagValueHandler->prepare($clientSecret)) === 0) {
            $this->setClientSecret($clientSecret, $workingMode);
        }
    }

    public function getMerchantID(?string $workingMode = null): string
    {
        $workingMode = $workingMode ?? $this->getWorkingMode();
        if (($this->merchantId[$workingMode] ?? null) === null) {
            $this->merchantId[$workingMode] = $this->config->getPrefixedConfigItem('merchantID_' . $workingMode, '');
        }

        return $this->merchantId[$workingMode];
    }

    public function setMerchantID(string $merchantId, ?string $workingMode = null): void
    {
        $workingMode = $workingMode ?? $this->getWorkingMode();
        if (($this->merchantId[$workingMode] ?? null) === $merchantId) {
            return;
        }

        if ($merchantId === '') {
            $this->config->deleteConfigItems(['merchantID_' . $workingMode]);
        } else {
            $this->config->saveConfigItems(
                ['merchantID_' . $workingMode => $merchantId]
            );
        }

        $this->merchantId[$workingMode] = $merchantId;
    }
}
