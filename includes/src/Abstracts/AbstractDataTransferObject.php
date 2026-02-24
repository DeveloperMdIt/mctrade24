<?php

declare(strict_types=1);

namespace JTL\Abstracts;

use InvalidArgumentException;
use JTL\Shop;
use stdClass;

/**
 * Class AbstractDataTransferObject
 * @package JTL\Abstracts
 */
abstract class AbstractDataTransferObject
{
    protected const CACHE_ID            = '';
    protected const ALLOW_DYNAMIC_PROPS = false;

    /**
     * @var array<string, string>
     */
    protected const MAPPING = [];

    public function __construct(public ?stdClass $legacyObject = null)
    {
    }

    /**
     * @comment Needed for interacting with legacy code
     */
    public function __set(string $offset, mixed $value): void
    {
        if (
            $this->legacyObject !== null
            && (
                static::ALLOW_DYNAMIC_PROPS
                || \property_exists($this->legacyObject, $offset)
            )
        ) {
            $this->legacyObject->$offset = $value;
        }
    }

    /**
     * @comment Needed for interacting with legacy code
     */
    public function __get(string $offset): mixed
    {
        if (
            !\property_exists($this, $offset)
            && ($this->legacyObject !== null
                && !\property_exists($this->legacyObject, $offset))
        ) {
            throw new InvalidArgumentException("Property $offset does not exist");
        }

        return $this->$offset ?? $this->legacyObject->$offset;
    }

    /**
     * @comment Needed for interacting with legacy code
     */
    public function __isset(string $offset): bool
    {
        return \property_exists($this, $offset)
            || ($this->legacyObject !== null
                && \property_exists($this->legacyObject, $offset));
    }

    /**
     * @description Instantiate DTOs with data from objects created by our legacy codebase.
     * @comment Hopefully, this will be removed in the future.
     */
    abstract public static function fromLegacyObject(stdClass $data): self;

    protected static function castInt(object $data, string $offset): int
    {
        return (int)($data->$offset ?? 0);
    }

    protected static function castString(object $data, string $offset): string
    {
        return (string)($data->$offset ?? '');
    }

    protected static function castFloat(object $data, string $offset): float
    {
        return (float)($data->$offset ?? 0.00);
    }

    protected static function castArray(object $data, string $offset): array
    {
        return (array)($data->$offset ?? []);
    }

    /**
     * @param array<string> $trueValues
     */
    protected static function castBool(
        object $data,
        string $offset,
        array $trueValues = [],
    ): bool {
        if (
            $trueValues !== []
            && \in_array($data->$offset ?? false, $trueValues, true)
        ) {
            return true;
        }

        return \in_array(
            $data->$offset ?? false,
            ['1', 1, true, 'true', 'on', 'yes', 'Y', 'y'],
            true,
        );
    }

    public static function fromObject(object $data): static
    {
        return static::fromArray((array)$data);
    }

    public static function fromArray(array $data): static
    {
        if (isset($data['legacyObject'])) {
            $data['legacyData'] = $data['legacyObject'];
            unset($data['legacyObject']);
        }

        if (!isset($data['legacyData'])) {
            $data['legacyData'] = new stdClass();
            foreach (static::MAPPING as $legacyOffset) {
                if (!isset($data[$legacyOffset])) {
                    continue;
                }
                $data['legacyData']->{static::MAPPING[$legacyOffset]} = $data[$legacyOffset];
            }
        }

        return new static(...$data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return \get_object_vars($this);
    }

    /**
     * @return array<string, mixed>
     */
    public function toLegacyArray(): array
    {
        if ($this->legacyObject === null) {
            return [];
        }

        return \get_object_vars($this->legacyObject);
    }

    public function toObject(): object
    {
        return (object)\get_object_vars($this);
    }

    public function toLegacyObject(): stdClass
    {
        $result = $this->legacyObject ?? new stdClass();
        foreach (static::MAPPING as $offset => $legacyOffset) {
            if (isset($result->$legacyOffset)) {
                continue;
            }
            $result->$legacyOffset = $this->$offset;
        }

        return $result;
    }

    public function toJson(int $flags = JSON_THROW_ON_ERROR): string
    {
        return (string)\json_encode($this->toArray(), $flags);
    }

    public function getCacheID(): string
    {
        return static::CACHE_ID . Shop::Container()->getCache()->getBaseID();
    }

    /**
     * @param array<string, string> $offsetTypeArray
     */
    protected static function typecastLegacyData(
        stdClass $data,
        array $offsetTypeArray
    ): stdClass {
        foreach ($offsetTypeArray as $offset => $type) {
            if (isset($data->$offset)) {
                $data->$offset = match ($type) {
                    'int'   => (int)$data->$offset,
                    'float' => (float)$data->$offset,
                    default => $data->$offset,
                };
            }
        }

        return $data;
    }
}
