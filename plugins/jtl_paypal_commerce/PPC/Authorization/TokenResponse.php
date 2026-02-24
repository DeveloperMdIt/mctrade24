<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

/**
 * Class TokenResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
final class TokenResponse extends JSONResponse
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

    /**
     * @return string
     * @throws AuthorizationException
     */
    public function getTokenType(): string
    {
        try {
            return $this->getData()->token_type ?? '';
        } catch (JsonException | UnexpectedResponseException $e) {
            throw new AuthorizationException('Unexpected token response', $e->getCode(), $e);
        }
    }

    /**
     * @return int
     * @throws AuthorizationException
     */
    public function getExpires(): int
    {
        try {
            return (int)$this->getData()->expires_in;
        } catch (JsonException | UnexpectedResponseException $e) {
            throw new AuthorizationException('Unexpected token response', $e->getCode(), $e);
        }
    }
}
