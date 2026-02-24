<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Exportformat;

use DateTime;
use Exception;
use Illuminate\Support\Collection;
use JTL\Cron\QueueEntry;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Export\AsyncCallback;
use JTL\Plugin\PluginInterface;
use JTL\Plugin\PluginLoader;
use JTL\Router\Route;
use JTL\Shop;
use ZipArchive;

/**
 * Class GoogleShoppingExport
 * @package Plugin\jtl_google_shopping\Exportformat
 */
class GoogleShoppingExport
{
    protected PluginInterface $plugin;

    /**
     * @var object
     */
    protected $exportFormat;

    /**
     * @var QueueEntry
     */
    protected $queueEntry;

    protected string $tmpFilename;

    protected Collection $settings;

    /**
     * @var resource
     */
    protected $tmpFile;

    protected string $exportSQL;

    protected bool $isCron = false;

    protected bool $finished = false;

    protected DbInterface $db;

    protected const EXPORT_PATH = \PFAD_ROOT . \PFAD_EXPORT;

    protected int $cacheHits = 0;

    protected int $cacheMisses = 0;

    /**
     * @param object      $exportFormat
     * @param array       $settings
     * @param DbInterface $db
     */
    public function __construct(object $exportFormat, array $settings, DbInterface $db)
    {
        $file               = \basename($exportFormat->cDateiname);
        $dot                = \mb_strrpos($file, '.');
        $this->exportFormat = $exportFormat;
        $this->settings     = new Collection();
        $this->db           = $db;
        $this->plugin       = (new PluginLoader($db, Shop::Container()->getCache()))->init($exportFormat->kPlugin);
        $this->tmpFilename  = $dot === false
            ? ($file . '.xml')
            : (\mb_substr($file, 0, $dot) . '.xml');
        $this->init($settings);
    }

    /**
     * @param array $settings
     */
    protected function init(array $settings): void
    {
        foreach ($this->plugin->getConfig()->getOptions() as $option) {
            $this->settings->put($option->valueID, $option->value);
        }
        foreach ($settings as $key => $value) {
            $this->settings->put($key, $value);
        }
        $maxItems = (int)$this->settings->get('maxItem');
        if ($maxItems < 1) {
            $this->settings->put('maxItem', 20000);
        } else {
            $this->settings->put('maxItem', $maxItems);
        }
    }

    public function getQueueEntry(): QueueEntry
    {
        return $this->queueEntry;
    }

    public function setQueueEntry(QueueEntry $queueEntry, bool $isCron): self
    {
        $this->queueEntry = $queueEntry;
        $this->isCron     = $isCron;
        if ($isCron) {
            $this->tmpFilename = 'cron_' . $this->tmpFilename;
        }
        $maxItems    = $this->settings->get('maxItem');
        $doneItems   = $queueEntry->tasksExecuted + $queueEntry->taskLimit;
        $idxFilename = (int)($doneItems / $maxItems);
        if ($doneItems % $maxItems !== 0) {
            $idxFilename++;
        }
        if ($idxFilename > 1) {
            $this->exportFormat->cDateiname = $idxFilename . '_' . $this->exportFormat->cDateiname;
        }

        return $this;
    }

    public function isFinished(): bool
    {
        return $this->finished;
    }

    /**
     * @param int $estimatedCount
     * @return array
     */
    protected function getExportProductIDs(int &$estimatedCount): array
    {
        $result = [];
        $sql    = $this->getExportSQL();
        if (!empty($sql)) {
            $sql = \str_replace('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $sql);

            $result         = $this->db->query($sql, ReturnType::ARRAY_OF_ASSOC_ARRAYS);
            $estimatedCount = (int)$this->db->query('SELECT FOUND_ROWS() AS count', ReturnType::SINGLE_OBJECT)->count;
        }

        return $result;
    }

    protected function writeZipFile(): void
    {
        if (\file_exists(self::EXPORT_PATH . $this->exportFormat->cDateiname)) {
            \unlink(self::EXPORT_PATH . $this->exportFormat->cDateiname);
        }
        $zipArchive = new ZipArchive();
        $open       = $zipArchive->open(
            self::EXPORT_PATH . $this->exportFormat->cDateiname,
            (\is_file(self::EXPORT_PATH . $this->exportFormat->cDateiname) ? null : ZipArchive::CREATE)
        );
        if ($open === true) {
            $zipArchive->addFile(self::EXPORT_PATH . $this->tmpFilename, $this->tmpFilename);
            $zipArchive->close();
        }
        \unlink(self::EXPORT_PATH . $this->tmpFilename);
    }

    protected function start(): void
    {
        if ($this->queueEntry->tasksExecuted === 0 && \file_exists(self::EXPORT_PATH . $this->tmpFilename)) {
            \unlink(self::EXPORT_PATH . $this->tmpFilename);
        }
        $this->tmpFile = \fopen(self::EXPORT_PATH . $this->tmpFilename, 'ab');
    }

    protected function stop(): void
    {
        if (\is_resource($this->tmpFile)) {
            \fclose($this->tmpFile);
        }
        if ($this->isCron) {
            return;
        }
        if (!$this->finished) {
            $this->db->queryPrepared(
                'UPDATE texportqueue SET
                    nLimit_n       = nLimit_n + :nLimitM,
                    nLastArticleID = :nLastArticleID
                    WHERE kExportqueue = :kExportqueue',
                [
                    'nLimitM'        => $this->queueEntry->taskLimit,
                    'nLastArticleID' => $this->queueEntry->lastProductID,
                    'kExportqueue'   => (int)$this->queueEntry->jobQueueID,
                ]
            );
        }
        if (($this->exportFormat->async ?? false) === true) {
            $target   = Route::EXPORT_START;
            $callback = new AsyncCallback();
            $callback->setExportID($this->exportFormat->kExportformat)
                ->setQueueID($this->queueEntry->jobQueueID)
                ->setCacheMisses($this->cacheMisses)
                ->setCacheHits($this->cacheHits)
                ->setUrl(Shop::getAdminURL() . '/' . $target)
                ->setTasksExecuted($this->queueEntry->tasksExecuted)
                ->setLastProductID($this->queueEntry->lastProductID)
                ->setProductCount($this->exportFormat->max)
                ->setIsFinished($this->finished)
                ->setIsFirst(((int)$this->queueEntry->tasksExecuted === 0))
                ->output();
            if (!$this->finished) {
                exit;
            }
        }

        if (!$this->finished) {
            $target = Route::EXPORT_START;
            $loc    = Shop::getAdminURL() . '/' . $target . '?e=' . $this->queueEntry->jobQueueID
                . '&back=admin'
                . '&lid=' . $this->queueEntry->lastProductID
                . '&lmt=' . $this->queueEntry->taskLimit
                . '&token=' . $_SESSION['jtl_token'];
            \header('Location: ' . $loc);
            exit;
        }
    }

    protected function finish(): void
    {
        $this->finished = true;
        try {
            $this->exportFormat->dZuletztErstellt = (new DateTime())->format('Y-m-d H:i:s');
        } catch (Exception) {
            $this->exportFormat->dZuletztErstellt = '';
        }
    }

    /**
     * @throws Exception
     */
    protected function doExport(): bool
    {
        $this->start();
        $estimatedCount = 0;
        $productIDs     = $this->getExportProductIDs($estimatedCount);
        if ($estimatedCount === 0) {
            $this->finish();
            $this->stop();
            \unlink(self::EXPORT_PATH . $this->tmpFilename);

            return false;
        }
        $xml = new GoogleShoppingXML(
            $this->exportFormat,
            $this->tmpFile,
            $this->settings,
            $this->db
        );
        $xml->setLocalization($this->plugin->getLocalization())
            ->setExportProductIds($productIDs);
        $this->queueEntry->lastProductID = (int)\end($productIDs)['kArtikel'];
        unset($productIDs);

        if (
            $this->queueEntry->tasksExecuted === 0
            || $this->queueEntry->tasksExecuted % $this->settings->get('maxItem') === 0
        ) {
            $xml->writeHead();
        }
        $xml->writeContent();
        $this->queueEntry->tasksExecuted += \min($this->queueEntry->taskLimit, $estimatedCount);

        if ($this->queueEntry->taskLimit >= $estimatedCount) {
            $xml->writeFooter();
            $this->writeZipFile();
            $this->finish();
        }
        if ($this->queueEntry->tasksExecuted % $this->settings->get('maxItem') === 0) {
            $xml->writeFooter();
            $this->writeZipFile();
        }
        $this->cacheMisses = $xml->cacheMisses;
        $this->cacheHits   = $xml->cacheHits;
        $this->stop();

        return true;
    }

    public function getExportSQL(): string
    {
        return $this->exportSQL;
    }

    public function setExportSQL(string $sql): self
    {
        $this->exportSQL = $sql;

        return $this;
    }

    /**
     * @param object      $exportFormat
     * @param array       $settings
     * @param DbInterface $db
     * @return static
     */
    public static function export(object $exportFormat, array $settings, DbInterface $db): self
    {
        return new self($exportFormat, $settings, $db);
    }

    /**
     * @throws Exception
     */
    public function run(): bool
    {
        return $this->doExport();
    }
}
