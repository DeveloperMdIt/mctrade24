<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Models;

use JTL\DB\DbInterface;
use JTL\Shop;
use Plugin\s360_clerk_shop5\src\Entities\Entity;

abstract class Model
{
    protected DbInterface $database;

    public function __construct()
    {
        $this->database = Shop::Container()->getDB();
    }

    /**
     * Get the table name for the model
     * @return string
     */
    abstract public function getTableName(): string;

    /**
     * Get the filled entity for associated with this repository
     * @param array $data
     * @return Entity
     */
    abstract public function getEntity(array $data): Entity;

    /**
     * Select all entities from table
     * @param array $where
     * @param string $orderBy
     * @return array|Entity[]
     */
    public function all(array $where = [], string $orderBy = ''): array
    {
        $whereKeys = [];
        $whereVals = [];

        if (!empty($where)) {
            $whereKeys = array_keys($where);
            $whereVals = array_values($where);
        }

        $rows = $this->database->selectAll($this->getTableName(), $whereKeys, $whereVals, '*', $orderBy);
        $collection = [];

        foreach ($rows as $row) {
            $collection[] = $this->getEntity((array) $row);
        }

        return $collection;
    }

    /**
     * Find a specific entity by its id
     * @param int $id
     * @return null|Entity
     */
    public function find(int $id): ?Entity
    {
        return $this->getBy('id', $id);
    }

    /**
     * Find a specific entity
     * @param string $key
     * @param mixed $value
     * @return null|Entity
     */
    public function getBy(string $key, $value): ?Entity
    {
        $row = $this->database->select($this->getTableName(), $key, $value);

        if (empty($row)) {
            return null;
        }

        return $this->getEntity((array) $row);
    }

    /**
     * Update an entity
     * @param int $id
     * @param Entity $entity
     * @return int
     */
    public function update(int $id, Entity $entity): int
    {
        return $this->database->update($this->getTableName(), 'id', $id, (object) $entity->toArray());
    }

    /**
     * Insert a new entity
     * @param array $data
     * @return int
     */
    public function insert(array $data): int
    {
        return $this->database->insert($this->getTableName(), (object) $data);
    }

    /**
     * Delete an entry
     * @param int $id
     * @return int
     */
    public function delete(int $id): int
    {
        return $this->database->delete($this->getTableName(), 'id', $id);
    }
}
