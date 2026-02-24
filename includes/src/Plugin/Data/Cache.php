<?php

declare(strict_types=1);

namespace JTL\Plugin\Data;

/**
 * Class Cache
 * @package JTL\Plugin\Data
 */
class Cache
{
    private string $id;

    private string $group;

    public function getID(): string
    {
        return $this->id;
    }

    public function setID(string $id): void
    {
        $this->id = $id;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group): void
    {
        $this->group = $group;
    }
}
