<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request\Serializer;

use Exception;
use JsonException;
use JsonSerializable;
use Plugin\jtl_paypal_commerce\PPC\Logger;

/**
 * Class JSON
 * @package Plugin\jtl_paypal_commerce\PPC\Request\Serializer
 */
class JSON implements SerializerInterface, JsonSerializable
{
    /** @var string|array|object|null */
    protected string|array|object|null $data = null;

    protected const OPTIONS = \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE;

    /**
     * JSON constructor.
     * @param string|array|object|null $data
     */
    public function __construct(string|array|object|null $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * @param string|string[]|object $data
     * @return static
     */
    public function setData(string|array|object $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string|string[]|object|null
     */
    public function getData(): string|array|object|null
    {
        return $this->data;
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'application/json';
    }

    /**
     * @inheritDoc
     */
    public function stringify(): ?string
    {
        try {
            return $this->data === null ? '' : \json_encode($this, \JSON_THROW_ON_ERROR | self::OPTIONS);
        } catch (JsonException $e) {
            $logger = new Logger(Logger::TYPE_INFORMATION);
            $logger->write(\LOGLEVEL_ERROR, 'Paypal commerce: serialization failed (' . $e->getMessage() . ')');

            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        foreach ($this->data as $value) {
            if ($value instanceof SerializerInterface) {
                if (!$value->isEmpty()) {
                    return false;
                }
            } elseif (!empty($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        try {
            return $this->stringify() ?? '';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [$this->getData()];
    }
}
