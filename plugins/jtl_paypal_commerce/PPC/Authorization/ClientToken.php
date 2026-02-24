<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use JTL\Session\Frontend;
use Plugin\jtl_paypal_commerce\PPC\Environment\EnvironmentInterface;
use DateInterval;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;

/**
 * Class ClientToken
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
class ClientToken
{
    /** @var static[] */
    protected static $instance = [];

    /** @var EnvironmentInterface */
    protected $environment;

    /** @var object */
    protected $tokenVar;

    /**
     * ClientToken constructor
     * @param EnvironmentInterface $environment
     */
    protected function __construct(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
        $this->tokenVar    = $this->createToken('', 0);

        self::$instance[$environment->getAuthorizationString()] = $this;
    }

    /**
     * @return static
     * @noinspection PhpMissingReturnTypeInspection
     * @throws AuthorizationException
     */
    public static function getInstance()
    {
        $environment = PPCHelper::getEnvironment();
        $instance    = self::$instance[$environment->getAuthorizationString()] ?? null;
        if (isset($instance) && !$instance->willExpire()) {
            return $instance;
        }

        $instance           = $instance ?? new static($environment);
        $instance->tokenVar = Frontend::get('ClientToken_tokenVar', $instance->createToken('', 0));
        if ($instance->willExpire()) {
            try {
                $instance->refresh();
            } catch (JsonException $e) {
                throw new AuthorizationException('Can not refresh client token.', $e->getCode(), $e);
            }
        }

        return $instance;
    }

    /**
     * @param string $token
     * @param int    $expiresIn
     * @return object
     */
    private function createToken(string $token, int $expiresIn): object
    {
        try {
            $expires = (new DateTime())->add(new DateInterval('PT' . $expiresIn . 'S'));
        } catch (Exception $e) {
            $expires = (new DateTime())->setTimestamp(\time() + $expiresIn);
        }

        return (object)[
            'token'   => $token,
            'expires' => $expires,
        ];
    }

    /**
     * @param int $seconds
     * @return bool
     */
    public function willExpire(int $seconds = 30): bool
    {
        try {
            $expireTime = (new DateTime())->add(new DateInterval('PT' . $seconds . 'S'));
        } catch (Exception $e) {
            $expireTime = (new DateTime())->setTimestamp(\time() + $seconds);
        }

        return (!is_a($this->tokenVar->expires, DateTime::class)) || ($this->tokenVar->expires <= $expireTime);
    }

    /**
     * @return void
     * @throws AuthorizationException
     */
    public function refresh(): void
    {
        try {
            $client   = new PPCClient($this->environment);
            $response = new ClientTokenResponse($client->send(
                new ClientTokenRequest(Token::getInstance()->getToken())
            ));
        } catch (GuzzleException | PPCRequestException $e) {
            throw new AuthorizationException($e->getMessage(), $e->getCode(), $e);
        }

        $this->tokenVar = $this->createToken($response->getToken(), $response->getExpires());
        Frontend::set('ClientToken_tokenVar', $this->tokenVar);
    }

    /**
     * @return string|null
     */
    public function getToken(): ?string
    {
        return empty($this->tokenVar->token) ? null : $this->tokenVar->token;
    }
}
