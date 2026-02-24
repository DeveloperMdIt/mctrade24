<?php

declare(strict_types=1);

namespace JTL\Update;

use JTL\DB\DbInterface;
use stdClass;

/**
 * Trait MigrationTrait
 * @package JTL\Update
 */
trait MigrationTrait
{
    protected DbInterface $db;

    /**
     * @return array<mixed>|stdClass[]|stdClass|\PDOStatement|int|bool
     * @deprecated since 5.4.0
     */
    protected function exec(string $query, int $return): mixed
    {
        return $this->getDB()->query($query, $return);
    }

    public function getDB(): DbInterface
    {
        return $this->db;
    }

    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    public function execute(string $query): int
    {
        return $this->getDB()->getAffectedRows($query);
    }

    public function fetchOne(string $query): ?stdClass
    {
        return $this->getDB()->getSingleObject($query);
    }

    /**
     * @return stdClass[]
     */
    public function fetchAll(string $query): array
    {
        return $this->getDB()->getObjects($query);
    }

    /**
     * @return array<int, array<mixed>>
     */
    public function fetchArray(string $query): array
    {
        return $this->getDB()->getArrays($query);
    }
}
