<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class WebhookVerifySignatureRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookVerifySignatureRequest extends AuthorizedRequest
{
    /** @var string */
    private string $authAlgo;

    /** @var string */
    private string $certUrl;

    /** @var string */
    private string $transId;

    /** @var string */
    private string $transSig;

    /** @var string */
    private string $transTime;

    /** @var string */
    private string $webhookId;

    /** @var object */
    private object $resource;

    /**
     * WebhookVerifySignatureRequest constructor.
     * @param string $token
     * @param string $authAlgo
     * @param string $certUrl
     * @param string $transId
     * @param string $transSig
     * @param string $transTime
     * @param string $webhookId
     * @param object $resource
     */
    public function __construct(
        string $token,
        string $authAlgo,
        string $certUrl,
        string $transId,
        string $transSig,
        string $transTime,
        string $webhookId,
        object $resource
    ) {
        $this->authAlgo  = $authAlgo;
        $this->certUrl   = $certUrl;
        $this->transId   = $transId;
        $this->transSig  = $transSig;
        $this->transTime = $transTime;
        $this->webhookId = $webhookId;
        $this->resource  = $resource;

        parent::__construct($token);
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return new JSON((object)[
            'auth_algo'         => $this->authAlgo,
            'cert_url'          => $this->certUrl,
            'transmission_id'   => $this->transId,
            'transmission_sig'  => $this->transSig,
            'transmission_time' => $this->transTime,
            'webhook_id'        => $this->webhookId,
            'webhook_event'     => $this->resource,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v1/notifications/verify-webhook-signature';
    }
}
