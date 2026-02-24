<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\Nullable;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class WebhookListRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookListRequest extends AuthorizedRequest
{
    /**
     * WebhookListRequest constructor.
     * @param string $token
     */
    public function __construct(string $token)
    {
        parent::__construct($token, MethodType::GET);
    }

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
        return '/v1/notifications/webhooks';
    }
}
