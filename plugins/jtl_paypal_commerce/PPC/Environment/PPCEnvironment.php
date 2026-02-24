<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Environment;

/**
 * Class BaseEnvironment
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
abstract class PPCEnvironment implements EnvironmentInterface
{
    /** @var string */
    protected string $clientID;

    /** @var string */
    protected string $clientSecret;

    /** @var string|null */
    protected ?string $metaDataId;

    /**
     * BaseEnvironment constructor.
     * @param string      $clientId
     * @param string      $clientSecret
     * @param string|null $metaDataId
     */
    public function __construct(string $clientId, string $clientSecret, ?string $metaDataId = null)
    {
        $this->clientID     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->metaDataId   = $metaDataId;
    }

    /**
     * @inheritDoc
     */
    public function reInit(string $clientId, string $clientSecret): void
    {
        $this->clientID     = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @inheritDoc
     */
    public function getClientId(): string
    {
        return $this->clientID;
    }

    /**
     * @inheritDoc
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizationString(): string
    {
        return \base64_encode($this->getClientId() . ':' . $this->getClientSecret());
    }

    /**
     * @inheritDoc
     */
    public function getMetaDataId(): ?string
    {
        return $this->metaDataId;
    }
}
