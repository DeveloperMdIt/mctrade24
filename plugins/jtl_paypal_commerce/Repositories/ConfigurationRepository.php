<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Repositories;

use JTL\DB\DbInterface;
use JTL\Plugin\Helper;
use stdClass;

/**
 * Class ConfigurationRepository
 * @package Plugin\jtl_paypal_commerce\Repositories
 */
class ConfigurationRepository extends AbstractPluginRepository
{
    private readonly int $pluginId;

    /**
     * ConfigurationRepository constructor
     * @param DbInterface $db
     * @param int         $pluginId
     */
    public function __construct(DbInterface $db, int $pluginId)
    {
        parent::__construct($db);

        $this->pluginId = $pluginId;
    }

    public function getTableName(): string
    {
        return 'tplugineinstellungen';
    }

    public function get(string $valueName): ?stdClass
    {
        return $this->db->select($this->getTableName(), ['kPlugin', 'cName'], [$this->pluginId, $valueName]);
    }

    public function getValue(string $valueName, ?string $default = null): ?string
    {
        $result = $this->get($valueName);
        if ($result === null) {
            return $default;
        }

        return $result->cWert ?? $default;
    }

    public function delete(string $valueName): bool
    {
        $delete = $this->db->deleteRow($this->getTableName(), ['kPlugin', 'cName'], [$this->pluginId, $valueName]);

        return $delete > -1;
    }

    public function insert(string $valueName, string $value): int
    {
        return $this->db->insertRow($this->getTableName(), (object)[
            'kPlugin' => $this->pluginId,
            'cName'   => $valueName,
            'cWert'   => $value,
        ]);
    }

    /**
     * @return stdClass[]
     */
    public function getConfig(): array
    {
        return Helper::getConfigByID($this->pluginId);
    }
}
