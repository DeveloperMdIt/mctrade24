<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Repositories;

use JTL\DB\DbInterface;

/**
 * Class AbstractPluginRepository
 * @package Plugin\jtl_paypal_commerce\Repositories
 */
abstract class AbstractPluginRepository
{
    protected readonly DbInterface $db;

    /**
     * AbstractPluginRepository constructor
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    abstract public function getTableName(): string;
}
