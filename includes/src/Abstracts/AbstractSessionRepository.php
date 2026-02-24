<?php

declare(strict_types=1);

namespace JTL\Abstracts;

/**
 * Class AbstractSessionRepository
 * @package JTL\Abstracts
 */
abstract class AbstractSessionRepository
{
    protected const ALLOWED_SESSION_VARS = [];

    protected function hasCorrectType(string $offset, mixed $value): bool
    {
        if (!isset(static::ALLOWED_SESSION_VARS[$offset])) {
            return false;
        }
        $type = static::ALLOWED_SESSION_VARS[$offset];
        if (
            \is_object($value)
            && \get_class($value) === $type
        ) {
            return true;
        }

        return match ($type) {
            'int'    => \is_int($value),
            'string' => \is_string($value),
            'bool'   => \is_bool($value),
            'float'  => \is_float($value),
            'array'  => \is_array($value),
            'object' => \is_object($value),
            default  => false,
        };
    }

    public function isset(string $offset): bool
    {
        return isset(static::ALLOWED_SESSION_VARS[$offset], $_SESSION[$offset]);
    }

    public function set(string $offset, mixed $value): void
    {
        if (isset(static::ALLOWED_SESSION_VARS[$offset]) && $this->hasCorrectType($offset, $value)) {
            $_SESSION[$offset] = $value;
        }
    }

    public function get(string $offset): mixed
    {
        if (!isset(static::ALLOWED_SESSION_VARS[$offset])) {
            return null;
        }

        return $_SESSION[$offset] ?? null;
    }
}
