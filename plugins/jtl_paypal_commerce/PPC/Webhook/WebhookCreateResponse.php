<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class WebhookCreateResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookCreateResponse extends JSONResponse
{
    /**
     * WebhookCreateResponse constructor
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([201]);
    }

    /**
     * @return object|null
     * @throws UnexpectedResponseException
     */
    public function getWebhook(): ?object
    {
        try {
            return $this->getData();
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getId(): string
    {
        try {
            return $this->getData()->id ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getURL(): string
    {
        try {
            return $this->getData()->url ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return EventType[]
     * @throws UnexpectedResponseException
     */
    public function getEventTypes(): array
    {
        $result = [];
        try {
            $types = $this->getData()->event_types;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
        if ($types === null) {
            return $result;
        }

        foreach ($types as $type) {
            $result[] = new EventType($type);
        }

        return $result;
    }
}
