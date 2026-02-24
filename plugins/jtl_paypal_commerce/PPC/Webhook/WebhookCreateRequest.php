<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class WebhookCreateRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookCreateRequest extends AuthorizedRequest
{
    /** @var string */
    private string $url;

    /** @var JSON */
    private JSON $types;

    /**
     * WebhookCreateRequest constructor.
     * @param string      $token
     * @param string      $url
     * @param EventType[] $types
     */
    public function __construct(string $token, string $url, array $types)
    {
        $this->url   = $url;
        $this->types = new JSON($types);

        parent::__construct($token);
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return new JSON((object)[
            'url'         => $this->url,
            'event_types' => $this->types,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v1/notifications/webhooks';
    }
}
