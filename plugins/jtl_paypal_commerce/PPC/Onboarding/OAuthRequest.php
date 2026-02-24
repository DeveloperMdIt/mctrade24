<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Onboarding;

use GuzzleHttp\Psr7\Uri;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequest;

/**
 * Class OAuthRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Onboarding
 */
class OAuthRequest extends PPCRequest
{
    /**
     * OAuthRequest constructor.
     * @param string $sharedId
     * @param string $nonce
     * @param string $authCode
     */
    public function __construct(string $sharedId, string $nonce, string $authCode)
    {
        parent::__construct(
            new Uri('/v1/oauth2/token'),
            MethodType::POST,
            [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . \base64_encode($sharedId . ':')
            ],
            \http_build_query([
                'grant_type'    => 'authorization_code',
                'code_verifier' => $nonce,
                'code'          => $authCode,
            ])
        );
    }
}
