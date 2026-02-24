<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use GuzzleHttp\Psr7\Response;
use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;

/**
 * Class WebhookCallResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookCallResponse extends JSONResponse
{
    /**
     * WebhookCallResponse constructor.
     * @param string $content
     */
    public function __construct(string $content)
    {
        parent::__construct(new Response(200, [], $content));
    }

    /**
     * @inheritDoc
     */
    public function getData(): string|array|object|null
    {
        return parent::getData()->resource ?? null;
    }

    /**
     * @return string|array|object|null
     * @throws JsonException | UnexpectedResponseException
     */
    public function getOriginalData(): string|array|object|null
    {
        return parent::getData();
    }

    /**
     * @return string|null
     */
    public function getEventType(): ?string
    {
        try {
            return $this->getOriginalData()->event_type ?? null;
        } catch (JsonException | UnexpectedResponseException) {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getResourceType(): ?string
    {
        try {
            return $this->getOriginalData()->resource_type ?? null;
        } catch (JsonException | UnexpectedResponseException) {
            return null;
        }
    }

    /**
     * @return string|null
     */
    public function getSummary(): ?string
    {
        try {
            return $this->getOriginalData()->summary ?? null;
        } catch (JsonException | UnexpectedResponseException) {
            return null;
        }
    }
}
