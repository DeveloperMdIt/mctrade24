<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request\Serializer;

use InvalidArgumentException;

/**
 * Class Text
 * @package Plugin\jtl_paypal_commerce\PPC\Request\Serializer
 */
class Text implements SerializerInterface
{
    /** @var string|string[]|null */
    protected string|array|null $data = null;

    /**
     * Text constructor.
     * @param string|string[]|null $data
     */
    public function __construct(array|string|null $data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     * @param string|string[]|object $data
     * @return Text
     */
    public function setData(string|array|object $data): static
    {
        if (\is_object($data)) {
            $this->data = [];
            foreach (\get_object_vars($data) as $key => $value) {
                $this->data[$key] = $value;
            }
        } elseif (\is_array($data)) {
            $this->data = \array_map(static function ($item) {
                return (string)$item;
            }, $data);
        } elseif (\is_string($data)) {
            $this->data = $data;
        } else {
            throw new InvalidArgumentException(self::class . ': data must be string, array or object');
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'text.*';
    }

    /**
     * @inheritDoc
     */
    public function stringify(): ?string
    {
        if ($this->data === null) {
            return null;
        }

        if (\is_string($this->data)) {
            return $this->data;
        }

        $result = [];
        foreach ($this->data as $key => $value) {
            $result[] = \is_numeric($key) ? $value : $key . ':' . $value;
        }

        return \implode("\n", $result);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->stringify() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return $this->stringify() === '';
    }
}
