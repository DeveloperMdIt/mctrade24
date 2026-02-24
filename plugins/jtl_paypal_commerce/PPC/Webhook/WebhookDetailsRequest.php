<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;

/**
 * Class WebhookDetailsRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookDetailsRequest extends WebhookBaseRequest
{
    /**
     * WebhookDetailsRequest constructor.
     * @param string $token
     * @param string $webhookId
     */
    public function __construct(string $token, string $webhookId)
    {
        parent::__construct($token, $webhookId, MethodType::GET);
    }
}
