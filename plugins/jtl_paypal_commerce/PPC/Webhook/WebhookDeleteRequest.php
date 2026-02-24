<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;

/**
 * Class WebhookDeleteRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookDeleteRequest extends WebhookBaseRequest
{
    /**
     * WebhookDeleteRequest constructor.
     * @param string $token
     * @param string $webhookId
     */
    public function __construct(string $token, string $webhookId)
    {
        parent::__construct($token, $webhookId, MethodType::DELETE);
    }
}
