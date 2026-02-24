<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

use function Functional\first;

/**
 * Class WebhookListResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookListResponse extends JSONResponse
{
    /**
     * @param string $url
     * @return object|null
     * @throws UnexpectedResponseException
     */
    public function getWebhook(string $url): ?object
    {
        try {
            $webhooks = $this->getData()->webhooks ?? [];
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }

        if (count($webhooks) === 0) {
            return null;
        }

        return first($webhooks, static function (object $item) use ($url) {
            return $item->url === $url;
        });
    }

    /**
     * @param string $url
     * @return string|null
     * @throws UnexpectedResponseException
     */
    public function getId(string $url): ?string
    {
        $webhook = $this->getWebhook($url);

        return $webhook !== null ? ($webhook->id ?? null) : null;
    }

    /**
     * @param string $url
     * @return array
     * @throws UnexpectedResponseException
     */
    public function getEventTypes(string $url): array
    {
        $webhook = $this->getWebhook($url);

        return $webhook !== null ? ($webhook->event_types ?? []) : [];
    }
}
