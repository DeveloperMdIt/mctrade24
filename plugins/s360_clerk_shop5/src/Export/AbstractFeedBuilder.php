<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JTL\Events\Dispatcher;
use JTL\Plugin\Helper;
use JTL\Plugin\PluginInterface;
use mysqli;
use Plugin\s360_clerk_shop5\src\Entities\StoreEntity;
use Plugin\s360_clerk_shop5\src\Utils\Config;

/**
 * Basic Feed Builder
 * @package Plugin\s360_clerk_shop5\src\Export
 */
abstract class AbstractFeedBuilder
{
    public const EVENT_BOOT = 'boot_specific_feed_builder';

    protected PluginInterface $plugin;

    public function __construct(protected mysqli $connection, protected StoreEntity $store)
    {
        $this->plugin = Helper::getPluginById(Config::PLUGIN_ID);
        $this->boot();
    }

    /**
     * Process the current row
     *
     * @param array $row
     * @return array
     */
    abstract public function processRow(array $row): array;

    /**
     * Get the SQL Query for the feed data.
     *
     * @return string
     */
    abstract public function getSqlQuery(): string;

    /**
     * Perform some setup before building the feed.
     *
     * Is called on boot up
     *
     * @return void
     */
    public function boot(): void
    {
        Dispatcher::getInstance()->fire('s360_clerk_shop5.' . self::EVENT_BOOT, ['builder' => $this]);
    }

    public function getStore(): StoreEntity
    {
        return $this->store;
    }

    public function getConnection(): mysqli
    {
        return $this->connection;
    }
}
