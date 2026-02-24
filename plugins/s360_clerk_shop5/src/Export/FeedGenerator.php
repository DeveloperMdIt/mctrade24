<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use mysqli;
use Plugin\s360_clerk_shop5\src\Entities\StoreEntity;
use Plugin\s360_clerk_shop5\src\Utils\Config;
use Plugin\s360_clerk_shop5\src\Utils\LoggerTrait;
use RuntimeException;
use Throwable;

class FeedGenerator
{
    use LoggerTrait;

    protected mysqli $connection;
    protected array $settings = [];
    protected float $profilingStart;
    protected array $profiling = [
        'memory' => 0,
        'memory_peak' => 0,
        'memory_limit' => 0,
        'time' => 0
    ];

    public function __construct()
    {
        $socket = \defined('DB_SOCKET') ? DB_SOCKET : null;
        $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, null, $socket);

        if ($this->connection->connect_error) {
            throw new RuntimeException('Connection failed: ' . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8");
        $this->connection->query('SET SESSION sql_mode=""');

        /**
         *! MySQL/MariaDB has a LIMIT of 1024 Bytes for GROUP_CONCAT -> this can mess with the order export etc
         *! Note: effective maximum length of the return value is constrained by the value of max_allowed_packet
         */
        $this->connection->query('SET SESSION group_concat_max_len = 9999999;');

        $this->profilingStart = microtime(true);

        // Load Settings
        $result = $this->connection->query(
            "SELECT settings.cName, settings.cWert FROM tplugineinstellungen as settings
            LEFT JOIN tplugin ON settings.kPlugin = tplugin.kPlugin
            WHERE tplugin.cPluginID = '" . Config::PLUGIN_ID . "'"
        );
        $this->settings = array_column($result->fetch_all(MYSQLI_ASSOC) ?? [], 'cWert', 'cName');
    }

    /**
     * Create a new feed for the store
     */
    public function createFeed(StoreEntity $store): void
    {
        // Init Streamwriter with tmp data feed file
        $filename = $store->getHash() . '.json';
        $tmpFile = '_tmp_' . $filename;
        $hasError = false;
        $path = __DIR__ . '/../../export/';
        $streamWriter = new JsonFeedStreamWriter($path . $tmpFile);

        try {
            // Create Products feed (even when not enabled we have to add an empty products key to the feed)
            $streamWriter->addProperty('products');

            if ($store->getSettings()?->getEnableProducts()) {
                $this->createProductFeed($streamWriter, $store);
            }

            // Create Categories feed (even when not enabled we have to add an empty categories key to the feed)
            $streamWriter->addProperty('categories');

            if ($store->getSettings()?->getEnableCategories()) {
                $this->createCategoryFeed($streamWriter, $store);
            }

            // Create Customers feed (even when not enabled we have to add an empty customers key to the feed)
            $streamWriter->addProperty('customers');

            if ($store->getSettings()?->getEnableCustomers()) {
                $this->createCustomerFeed($streamWriter, $store);
            }

            // Create pages feed (even when not enabled we have to add an empty customers key to the feed)
            $streamWriter->addProperty('pages');

            if ($store->getSettings()?->getEnableBlog()) {
                $this->createBlogFeed($streamWriter, $store);
            }

            if ($store->getSettings()?->getEnableCms()) {
                $this->createPageFeed($streamWriter, $store);
            }

            // Create sales feed (even when not enabled we have to add an empty customers key to the feed)
            $streamWriter->addProperty('orders');

            if ($store->getSettings()?->getEnableLastOrders()) {
                $this->createInitialOrderFeed($streamWriter, $store);
            }

            // Misc
            $streamWriter->addProperty('config')->setValue(['created' => time(), 'strict' => false]);
        } catch (Throwable $err) {
            $hasError = true;
            $error = sprintf(
                'Could no create feed %s: %s in %s on line %s',
                $filename,
                $err->getMessage(),
                $err->getFile(),
                $err->getLine(),
            );

            $this->errorLog($error);
            $this->connection->query(
                "UPDATE xplugin_s360_clerk_shop5_store
                SET `state` = 'ERROR', state_message = '{$this->connection->escape_string($error)}'
                WHERE id = {$store->getId()}"
            );
        }

        $streamWriter->close();

        if (!$hasError) {
            rename($path . $tmpFile, $path . $filename);
            $this->connection->query(
                "UPDATE xplugin_s360_clerk_shop5_store
                SET `state` = 'SUCCESS', state_message = NULL, updated_at = NOW()
                WHERE id = {$store->getId()}"
            );
        }

        if ($streamWriter->hasErrors()) {
            $this->connection->query(
                "UPDATE xplugin_s360_clerk_shop5_store
                SET `state` = 'WARNING', state_message = 'Encountered some JSON errors. See logfile for more information'
                WHERE id = {$store->getId()}"
            );
        }

        // Profiling
        $this->debugLog(
            "Profiling Info for Feed {$store->getId()} Generation: " . print_r($this->getProfiling(), true)
        );
    }

    /**
     * Get current Profiling Information
     * @return array
     */
    public function getProfiling(): array
    {
        $this->profiling['memory'] = round(memory_get_usage() / 1048576, 2) . 'MB';
        $this->profiling['memory_peak'] = round(memory_get_peak_usage() / 1048576, 2) . 'MB';
        $this->profiling['memory_limit'] = ini_get('memory_limit');
        $this->profiling['time'] = round(microtime(true) - $this->profilingStart, 4) . 's';

        return $this->profiling;
    }

     /**
     * Createe the Product Feed
     * @param JsonFeedStreamWriter $writer
     * @param StoreEntity $store
     * @return void
     */
    public function createProductFeed(JsonFeedStreamWriter $writer, StoreEntity $store): void
    {
        $productFeedBuilder = new ProductFeedBuilder($this->connection, $store);
        $sql = $productFeedBuilder->getSqlQuery();

        $total = $productFeedBuilder->getTotalProducts();
        $batchSize = (int) ($this->settings[Config::SETTING_BATCH_SIZE] ?? 0);

        if ($total === 0) {
            return;
        }

        $writer->startCollection();
        $this->profiling['products'] = 0;

        if ($batchSize <= 0) {
            $result = $this->connection->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $writer->push($productFeedBuilder->processRow($row));
                    $this->profiling['products'] += 1;
                    unset($row);
                }
            }
        } else {
            for ($offset = 0; $offset <= $total; $offset += $batchSize) {
                $query = $sql . " LIMIT {$batchSize} OFFSET {$offset}";
                $result = $this->connection->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $writer->push($productFeedBuilder->processRow($row));
                        $this->profiling['products'] += 1;
                        unset($row);
                    }
                }

                unset($result);
            }
        }

        $writer->endCollection();
    }

    /**
     * Create the Category Feed
     *
     * @param JsonFeedStreamWriter $writer
     * @param StoreEntity $store
     * @return void
     */
    public function createCategoryFeed(JsonFeedStreamWriter $writer, StoreEntity $store): void
    {
        $categoryFeedBuilder = new CategoryFeedBuilder($this->connection, $store);
        $sql = $categoryFeedBuilder->getSqlQuery();
        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            $writer->startCollection();
            $this->profiling['categories'] = 0;

            while ($row = $result->fetch_assoc()) {
                $writer->push($categoryFeedBuilder->processRow($row));
                $this->profiling['categories'] += 1;
            }

            $writer->endCollection();
        }
    }

    /**
     * Create the Customer Feed
     *
     * @param JsonFeedStreamWriter $writer
     * @param StoreEntity $store
     * @return void
     */
    public function createCustomerFeed(JsonFeedStreamWriter $writer, StoreEntity $store): void
    {
        $customerFeedBuilder = new CustomerFeedBuilder($this->connection, $store);
        $sql = $customerFeedBuilder->getSqlQuery();
        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            $writer->startCollection();
            $this->profiling['customers'] = 0;

            while ($row = $result->fetch_assoc()) {
                $writer->push($customerFeedBuilder->processRow($row));
                $this->profiling['customers'] += 1;
            }

            $writer->endCollection();
        }
    }

    /**
     * Create the Blog Feed
     *  @param JsonFeedStreamWriter $writer
     * @param StoreEntity $store
     * @return void
     */
    public function createBlogFeed(JsonFeedStreamWriter $writer, StoreEntity $store): void
    {
        $blogFeedBuilder = new BlogFeedBuilder($this->connection, $store);
        $sql = $blogFeedBuilder->getSqlQuery();
        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            $this->profiling['blog'] = 0;

            while ($row = $result->fetch_assoc()) {
                $writer->push($blogFeedBuilder->processRow($row));
                $this->profiling['blog'] += 1;
            }
        }
    }

    /**
     * Create the Page Feed
     *
     * @param StoreEntity $store
     * @return void
     */
    public function createPageFeed(JsonFeedStreamWriter $writer, StoreEntity $store): void
    {
        $pageFeedBuilder = new PageFeedBuilder($this->connection, $store);
        $sql = $pageFeedBuilder->getSqlQuery();
        $result = $this->connection->query($sql);

        if ($result->num_rows > 0) {
            $this->profiling['pages'] = 0;

            while ($row = $result->fetch_assoc()) {
                $writer->push($pageFeedBuilder->processRow($row));
                $this->profiling['pages'] += 1;
            }
        }
    }

    /**
     * Create the InitialOrder Feed
     *
     * @param StoreEntity $store
     * @return void
     */
    public function createInitialOrderFeed(JsonFeedStreamWriter $writer, StoreEntity $store): void
    {
        $orderFeedBuilder = new InitialOrderFeedBuilder($this->connection, $store);
        $sql = $orderFeedBuilder->getSqlQuery();
        $total = $orderFeedBuilder->getTotalOrders();
        $batchSize = (int) ($this->settings[Config::SETTING_BATCH_SIZE] ?? 0);

        if ($total === 0) {
            return;
        }

        $writer->startCollection();
        $this->profiling['sales'] = 0;

        $result = $this->connection->query($sql);

        if ($batchSize <= 0) {
            $result = $this->connection->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $writer->push($orderFeedBuilder->processRow($row));
                    $this->profiling['sales'] += 1;
                    unset($row);
                }
            }
        } else {
            for ($offset = 0; $offset <= $total; $offset += $batchSize) {
                $query = $sql . " LIMIT {$batchSize} OFFSET {$offset}";
                $result = $this->connection->query($query);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $writer->push($orderFeedBuilder->processRow($row));
                        $this->profiling['sales'] += 1;
                        unset($row);
                    }
                }

                unset($result);
            }
        }

        $writer->endCollection();
    }
}
