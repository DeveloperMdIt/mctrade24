<?php

declare(strict_types=1);

namespace JTL\Abstracts;

use JTL\DataObjects\AbstractDomainObject;
use JTL\Interfaces\RepositoryInterface;

/**
 * Class AbstractService
 * @package JTL\Abstracts
 */
abstract class AbstractService
{
    abstract protected function getRepository(): RepositoryInterface;

    /**
     * @param array<string, int|float|string> $filters
     * @return object[]
     */
    public function getList(array $filters): array
    {
        return $this->getRepository()->getList($filters);
    }

    public function insert(AbstractDomainObject $insertDTO): int
    {
        return $this->getRepository()->insert($insertDTO);
    }

    public function update(AbstractDomainObject $updateDO): bool
    {
        return $this->getRepository()->update($updateDO);
    }
}
