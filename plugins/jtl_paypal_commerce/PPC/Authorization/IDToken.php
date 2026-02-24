<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use GuzzleHttp\Exception\GuzzleException;
use Plugin\jtl_paypal_commerce\PPC\Environment\EnvironmentInterface;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class IdToken
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
class IDToken extends JSON
{
    /** @var self[] */
    private static array $instance = [];

    /**
     * @inheritDoc
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @throws AuthorizationException
     */
    public static function getInstance(
        EnvironmentInterface $environment,
        string $vaultCustomer,
        ?Logger $logger = null
    ): self {
        $instKey  = $environment::class . '.' . $vaultCustomer;
        $instance = self::$instance[$instKey] ?? null;
        if ($instance === null || $instance->getIDToken() === '') {
            try {
                $client   = new PPCClient($environment, $logger);
                $response = new IDTokenResponse($client->send(new IDTokenRequest($environment, $vaultCustomer)));
                if ($logger !== null) {
                    $logger->write(LOGLEVEL_DEBUG, 'IDTokenResponse:', $response);
                }

                self::$instance[$instKey] = $response->getToken();
            } catch (GuzzleException | PPCRequestException $e) {
                throw new AuthorizationException($e->getMessage(), $e->getCode(), $e);
            }
        }

        return self::$instance[$instKey];
    }

    public function getAccessToken(): string
    {
        return $this->getData()->access_token ?? '';
    }

    public function getIDToken(): string
    {
        return $this->getData()->id_token ?? '';
    }

    public function getNonce(): string
    {
        return $this->getData()->nonce ?? '';
    }

    public function getAppId(): string
    {
        return $this->getData()->app_id ?? '';
    }
}
