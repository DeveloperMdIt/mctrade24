<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Authorization;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\Nullable;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class ClientTokenRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Authorization
 */
class ClientTokenRequest extends AuthorizedRequest
{
    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return new Nullable();
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v1/identity/generate-token';
    }
}
