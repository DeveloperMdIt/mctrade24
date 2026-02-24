<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class WebhookBaseRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookBaseRequest extends AuthorizedRequest
{
    /** @var string */
    protected string $webhookId;

    /**
     * WebhookBaseRequest constructor
     * @param string $token
     * @param string $webhookId
     * @param string $method
     */
    public function __construct(string $token, string $webhookId, string $method)
    {
        $this->webhookId = $webhookId;
        parent::__construct($token, $method);
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return new JSON();
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v1/notifications/webhooks/' . $this->webhookId;
    }
}
