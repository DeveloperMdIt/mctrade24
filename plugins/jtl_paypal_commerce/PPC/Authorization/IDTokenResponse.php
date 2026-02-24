<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use Exception;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;

/**
 * Class IDTokenResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
class IDTokenResponse extends JSONResponse
{
    /**
     * @throws AuthorizationException
     */
    public function getToken(): IDToken
    {
        try {
            return new IDToken($this->getData());
        } catch (Exception $e) {
            throw new AuthorizationException('Unexpected token response', $e->getCode(), $e);
        }
    }
}
