<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Entities;

abstract class Entity
{
    /**
     * Create entity from array/DB data
     * @param array $data
     * @return Entity
     */
    abstract public static function fromArray(array $data): self;

    /**
     * Transform entity to array/DB data
     * @return array
     */
    abstract public function toArray(): array;
}
