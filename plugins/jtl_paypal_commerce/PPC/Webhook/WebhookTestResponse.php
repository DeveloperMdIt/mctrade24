<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use DateTime;
use DateTimeInterface;
use DateTimeZone;
use JsonException;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class WebhookTestResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookTestResponse extends JSONResponse
{
    /**
     * WebhookTestResponse constructor
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([202]);
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
     * @return DateTime
     * @throws UnexpectedResponseException
     */
    public function getCreateTime(): DateTime
    {
        try {
            return  DateTime::createFromFormat(
                DateTimeInterface::RFC3339,
                $this->getData()->create_time
            )->setTimezone(new DateTimeZone(\SHOP_TIMEZONE));
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getResourceType(): string
    {
        try {
            return $this->getData()->resource_type ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getEventVersion(): string
    {
        try {
            return $this->getData()->event_version ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getEventType(): string
    {
        try {
            return $this->getData()->event_type ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getSummary(): string
    {
        try {
            return $this->getData()->summary ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return string
     * @throws UnexpectedResponseException
     */
    public function getResourceVersion(): string
    {
        try {
            return $this->getData()->resource_version ?? '';
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }

    /**
     * @return object|null
     * @throws UnexpectedResponseException
     */
    public function getResource(): ?object
    {
        try {
            return $this->getData()->resource ?? null;
        } catch (JsonException $e) {
            throw new UnexpectedResponseException($this, $this->getExpectedResponseCode(), $e);
        }
    }
}
