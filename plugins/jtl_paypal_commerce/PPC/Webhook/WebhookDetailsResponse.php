<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use JsonException;

class WebhookDetailsResponse extends JSONResponse
{
    /**
     * @return object|null
     * @throws UnexpectedResponseException
     */
    public function getWebhook(): ?object
    {
        try {
            $webhook = $this->getData();
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }

        return $webhook;
    }

    /**
     * @return string|null
     * @throws UnexpectedResponseException
     */
    public function getId(): ?string
    {
        $webhook = $this->getWebhook();

        return $webhook !== null && !empty($webhook->id) ? $webhook->id : null;
    }

    /**
     * @return string|null
     * @throws UnexpectedResponseException
     */
    public function getUrl(): ?string
    {
        $webhook = $this->getWebhook();

        return $webhook !== null && !empty($webhook->url) ? $webhook->url : null;
    }

    /**
     * @return array|null
     * @throws UnexpectedResponseException
     */
    public function getEventTypes(): ?array
    {
        $webhook = $this->getWebhook();

        return $webhook !== null && !empty($webhook->event_types) ? $webhook->event_types : null;
    }
}
