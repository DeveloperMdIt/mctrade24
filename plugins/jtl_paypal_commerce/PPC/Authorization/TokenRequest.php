<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use GuzzleHttp\Psr7\Uri;
use Plugin\jtl_paypal_commerce\PPC\Environment\EnvironmentInterface;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequest;

/**
 * Class TokenRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
class TokenRequest extends PPCRequest
{
    private ?string $refreshToken;

    protected EnvironmentInterface $environment;

    /**
     * TokenRequest constructor.
     * @param EnvironmentInterface $environment
     * @param string|null          $refreshToken
     */
    public function __construct(EnvironmentInterface $environment, ?string $refreshToken = null)
    {
        $this->refreshToken = $refreshToken;
        $this->environment  = $environment;
        $body               = $this->initBody();
        $headers            = $this->initHeaders([
            'Authorization' => 'Basic ' . $environment->getAuthorizationString(),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ]);
        $uriString          = new Uri($this->getPath());

        parent::__construct($uriString, MethodType::POST, $headers, $body);
    }

    protected function initHeaders(array $headers): array
    {
        return $headers;
    }

    protected function initBody(): string
    {
        return \http_build_query(
            $this->refreshToken === null
                ? [
                'grant_type' => 'client_credentials',
            ]
                : [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $this->refreshToken,
            ]
        );
    }

    protected function getPath(): string
    {
        return '/v1/oauth2/token';
    }
}
