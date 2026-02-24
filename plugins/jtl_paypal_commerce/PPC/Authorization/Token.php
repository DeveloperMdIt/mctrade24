<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use DateInterval;
use DateTime;
use DateTimeZone;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Environment\EnvironmentInterface;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;

/**
 * Class Token
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
class Token
{
    /** @var static[] */
    protected static array $instance = [];

    /** @var EnvironmentInterface */
    protected EnvironmentInterface $environment;

    /** @var string|null */
    protected ?string $token = null;

    /** @var string|null */
    protected ?string $tokenType = null;

    /** @var DateTime|null */
    protected ?DateTime $expires = null;

    /**
     * PPCToken constructor.
     * @param EnvironmentInterface $environment
     */
    protected function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        self::$instance[$environment->getAuthorizationString()] = $this;
    }

    /**
     * @return static
     * @throws AuthorizationException
     */
    public static function getInstance(): static
    {
        $environment = PPCHelper::getEnvironment();
        $instance    = self::$instance[$environment->getAuthorizationString()] ?? null;
        if (isset($instance) && !$instance->willExpire()) {
            return $instance;
        }

        $config   = PPCHelper::getConfiguration();
        $instance = $instance ?? new static($environment);
        $instance->setSerializedToken($config->getConfigValues()->getAuthToken());
        if ($instance->willExpire()) {
            try {
                $instance->refresh();
                $config->getConfigValues()->setAuthToken($instance->getSerializedToken());
            } catch (JsonException $e) {
                $config->getConfigValues()->clearAuthToken();
                throw new AuthorizationException('Can not refresh auth token.', $e->getCode(), $e);
            }
        }

        return $instance;
    }

    /**
     * @return void
     */
    public static function inValidate(): void
    {
        $environment = PPCHelper::getEnvironment();
        $instance    = self::$instance[$environment->getAuthorizationString()] ?? null;
        if (isset($instance)) {
            $instance->token     = null;
            $instance->tokenType = null;
            $instance->expires   = new DateTime();
        }
    }

    /**
     * @param string $serialized
     */
    protected function setSerializedToken(string $serialized): void
    {
        try {
            $tokenData       = \json_decode($serialized, true, 128, \JSON_THROW_ON_ERROR);
            $this->token     = $tokenData['token'] ?? '';
            $this->tokenType = $tokenData['tokenType'] ?? '';
            $this->expires   = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $tokenData['expires'] ?? (new DateTime())->format('Y-m-d H:i:s'),
                new DateTimeZone(\SHOP_TIMEZONE)
            );
        } catch (JsonException) {
            $this->token     = null;
            $this->tokenType = null;
            $this->expires   = new DateTime();

            return;
        }
    }

    /**
     * @return string
     * @throws JsonException
     */
    protected function getSerializedToken(): string
    {
        $tokenData = [
            'token'     => $this->token ?? '',
            'tokenType' => $this->tokenType ?? '',
            'expires'   => $this->expires === null ? '' : $this->expires->format('Y-m-d H:i:s'),
        ];

        return \json_encode($tokenData, \JSON_THROW_ON_ERROR);
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return ($this->expires === null) || ($this->expires < (new DateTime()));
    }


    /**
     * @param int $seconds
     * @return bool
     */
    public function willExpire(int $seconds = 120): bool
    {
        try {
            $expireTime = (new DateTime())->add(new DateInterval('PT' . $seconds . 'S'));
        } catch (Exception) {
            $expireTime = (new DateTime())->setTimestamp(\time() + $seconds);
        }

        return ($this->expires === null) || ($this->expires <= $expireTime);
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return empty($this->token) ? null : $this->token;
    }

    /**
     * @return string|null
     */
    public function getTokenType(): ?string
    {
        return empty($this->tokenType) ? null : $this->tokenType;
    }

    /**
     * @return void
     * @throws AuthorizationException
     */
    public function refresh(): void
    {
        try {
            $client   = new PPCClient($this->environment);
            $response = new TokenResponse($client->send(new TokenRequest($this->environment)));
        } catch (GuzzleException | PPCRequestException $e) {
            throw new AuthorizationException($e->getMessage(), $e->getCode(), $e);
        }

        $this->token     = $response->getToken();
        $this->tokenType = $response->getTokenType();
        $expiresIn       = $response->getExpires();
        try {
            $this->expires = (new DateTime())->add(new DateInterval('PT' . $expiresIn . 'S'));
        } catch (Exception) {
            $this->expires = (new DateTime())->setTimestamp(\time() + $expiresIn);
        }
    }
}
