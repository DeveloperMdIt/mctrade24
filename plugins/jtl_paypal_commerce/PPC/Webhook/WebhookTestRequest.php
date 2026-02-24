<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class WebhookTestRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookTestRequest extends AuthorizedRequest
{
    /** @var string */
    private string $webhookId;

    /** @var string */
    private string $type;

    /**
     * WebhookTestRequest constructor.
     * @param string $token
     * @param string $webhookId
     * @param string $type
     */
    public function __construct(string $token, string $webhookId, string $type)
    {
        $this->webhookId = $webhookId;
        $this->type      = $type;

        parent::__construct($token);
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return new JSON((object)[
            'webhook_id'       => $this->webhookId,
            'event_type'       => $this->type,
            'resource_version' => '2.0'
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v1/notifications/simulate-event';
    }
}
