<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Onboarding;

use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

/**
 * Class OAuthResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Onboarding
 */
class OAuthResponse extends JSONResponse
{
    /**
     * @return string
     * @throws AuthorizationException
     */
    public function getToken(): string
    {
        try {
            return $this->getData()->access_token ?? '';
        } catch (JsonException | UnexpectedResponseException $e) {
            throw new AuthorizationException('Unexpected token response', $e->getCode(), $e);
        }
    }
}
