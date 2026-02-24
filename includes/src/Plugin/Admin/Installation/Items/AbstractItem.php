<?php

declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation\Items;

use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use stdClass;

/**
 * Class AbstractItem
 * @package JTL\Plugin\Admin\Installation\Items
 */
abstract class AbstractItem implements ItemInterface
{
    protected ?DbInterface $db;

    protected ?stdClass $plugin;

    protected ?PluginInterface $oldPlugin = null;

    protected ?array $baseNode;

    /**
     * @inheritdoc
     */
    public function __construct(
        ?DbInterface $db = null,
        ?array $baseNode = null,
        ?stdClass $plugin = null,
        ?PluginInterface $oldPlugin = null
    ) {
        $this->db        = $db;
        $this->baseNode  = $baseNode;
        $this->plugin    = $plugin;
        $this->oldPlugin = $oldPlugin;
    }

    /**
     * @inheritdoc
     */
    abstract public function install(): int;

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getPlugin(): stdClass
    {
        return $this->plugin;
    }

    /**
     * @inheritdoc
     */
    public function setPlugin(stdClass $plugin): void
    {
        $this->plugin = $plugin;
    }

    /**
     * @inheritdoc
     */
    public function getOldPlugin(): ?PluginInterface
    {
        return $this->oldPlugin;
    }

    /**
     * @inheritdoc
     */
    public function setOldPlugin($plugin): void
    {
        $this->oldPlugin = $plugin;
    }

    /**
     * @inheritdoc
     */
    public function getBaseNode(): array
    {
        return $this->baseNode;
    }

    /**
     * @inheritdoc
     */
    public function setBaseNode(array $baseNode): void
    {
        $this->baseNode = $baseNode;
    }
}
