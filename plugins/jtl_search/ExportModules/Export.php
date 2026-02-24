<?php

namespace Plugin\jtl_search\ExportModules;

use Exception;
use JTL\DB\DbInterface;
use JTL\Language\LanguageModel;
use Psr\Log\LoggerInterface;
use stdClass;
use ZipArchive;

/**
 * Class Export
 * @package Plugin\jtl_search\ExportModules
 */
abstract class Export
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array<string, CategoryData|ManufacturerData|ProductData>
     */
    protected array $dataObjects;

    /**
     * @var JTLSearchExportQueue
     */
    protected $oJTLSearchExportQueue;

    /**
     * @var array
     */
    protected $counts = [];

    /**
     * @var string
     */
    protected $cExportPath = '';

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var int
     */
    protected $exportMethod;

    /**
     * @var LanguageModel[]
     */
    protected array $languages;

    /**
     * @var LanguageModel
     */
    protected LanguageModel $defaultLanguage;

    /**
     * Export constructor.
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     * @param int             $exportMethod
     */
    public function __construct(DbInterface $db, LoggerInterface $logger, int $exportMethod = 1)
    {
        try {
            $this->logger       = $logger;
            $this->db           = $db;
            $this->exportMethod = $exportMethod;
            $this->initLanguageData();
            $this->oJTLSearchExportQueue       = new JTLSearchExportQueue($logger, $this->db, $exportMethod);
            $this->dataObjects['category']     = new CategoryData(
                $logger,
                $this->db,
                $this->languages,
                $this->defaultLanguage
            );
            $this->dataObjects['manufacturer'] = new ManufacturerData(
                $logger,
                $this->db,
                $this->languages,
                $this->defaultLanguage
            );
            $this->dataObjects['product']      = new ProductData(
                $logger,
                $this->db,
                $this->languages,
                $this->defaultLanguage
            );
            $this->loadCountExportDataIntoQueue();
        } catch (Exception $e) {
            $this->logger->warning(__CLASS__ . '->' . __METHOD__ . '; ' . $e);
            die('');
        }
    }

    private function initLanguageData(): void
    {
        $langData = $this->db->getObjects(
            'SELECT tsprache.* 
                    FROM tsprache 
                    JOIN tjtlsearchexportlanguage 
                        ON tsprache.cISO = tjtlsearchexportlanguage.cISO 
                    ORDER BY cStandard DESC'
        );
        foreach ($langData as $item) {
            $this->languages[] = LanguageModel::load($item, $this->db);
        }
        $this->defaultLanguage = $this->getDefaultLanguage();
    }

    /**
     * @return LanguageModel
     * @throws Exception
     */
    private function getDefaultLanguage(): LanguageModel
    {
        foreach ($this->languages as $lang) {
            if ($lang->isDefault()) {
                return $lang;
            }
        }
        foreach ($this->languages as $lang) {
            if ($lang->isShopDefault()) {
                return $lang;
            }
        }

        throw new Exception(\__('errorStandardLanguage'), 1);
    }

    /**
     * @param string $path
     */
    abstract public function setExportPath(string $path): void;

    /**
     * @return $this
     */
    private function loadCountExportDataIntoQueue(): self
    {
        if ($this->oJTLSearchExportQueue->getExportMethod() === 3) {
            $exports = $this->db->getObjects(
                'SELECT COUNT(*) AS nCount, eDocumentType 
                    FROM tjtlsearchdeltaexport 
                    WHERE bDelete = 0 
                    GROUP BY eDocumentType'
            );
            foreach ($exports as $export) {
                $this->oJTLSearchExportQueue->setCount($export->eDocumentType, $export->nCount);
            }
        } else {
            foreach ($this->dataObjects as $key => $data) {
                $this->oJTLSearchExportQueue->setCount($key, $data->getCount());
            }
        }

        return $this;
    }

    /**
     * @param string{'category'|'manufacturer'|'product'} $type
     * @return ManufacturerData|CategoryData|ProductData
     */
    private function getDataObject(string $type)
    {
        switch ($type) {
            case 'manufacturer':
                return new ManufacturerData($this->logger, $this->db, $this->languages, $this->defaultLanguage);
            case 'category':
                return $this->dataObjects['category'];
            case 'product':
                return $this->dataObjects['product'];
            default:
                throw new \InvalidArgumentException('Invalid type: ' . $type);
        }
    }

    /**
     * @return stdClass|null
     */
    public function exportAll()
    {
        while (($exp = $this->oJTLSearchExportQueue->getNextExportObject()) !== null) {
            $this->logger->debug($exp[0]);
            $dataObject = $this->getDataObject($exp[0]);
            $dataObject->loadFromDB($exp[1]);
            $item = $dataObject->getFilledObject();
            if (\is_object($item)) {
                \file_put_contents($this->getFileName(false, $this->cExportPath), $item, \FILE_APPEND);
            } elseif ($this->oJTLSearchExportQueue->getExportMethod() === 3) {
                $where                = new stdClass();
                $where->kId           = $exp[1];
                $where->eDocumentType = $exp[0];
                $where->bDelete       = 1;
                $where->dLastModified = 'now()';

                $upd                = new stdClass();
                $upd->bDelete       = 1;
                $upd->dLastModified = 'now()';

                $keys = $this->db->update(
                    'tjtlsearchdeltaexport',
                    ['kId', 'eDocumentType'],
                    [$where->kId, $where->eDocumentType],
                    $upd
                );
                if (!$keys) {
                    $this->db->insert('tjtlsearchdeltaexport', $where);
                }
            } else {
                $this->logger->debug(
                    __FILE__ . ':' . __CLASS__ . '->' . __METHOD__
                    . '; ' . \sprintf(\__('loggerErrorCreateItem'), $exp[0], $exp[1])
                );
            }
        }
        if ($this->oJTLSearchExportQueue->getExportqueue()) {
            if (!$this->oJTLSearchExportQueue->isExportFinished()) {
                return $this->nextRun();
            }

            return $this->lastRun();
        }
        $this->logger->warning(__CLASS__ . '->' . __METHOD__ . '; ' . \__('loggerInvalidQueue'));

        return null;
    }

    /**
     * @return stdClass|void
     */
    abstract protected function nextRun();

    /**
     * @return stdClass|void
     */
    abstract protected function lastRun();

    /**
     * @return bool
     */
    protected function zipFile(): bool
    {
        $zipFile = $this->exportMethod === 3
            ? \JTLSEARCH_PFAD_DELTA_EXPORTFILE_ZIP
            : \JTLSEARCH_PFAD_EXPORTFILE_ZIP;
        if (\file_exists($zipFile)) {
            \unlink($zipFile);
        }
        $archive = new ZipArchive();
        if (($res = $archive->open($zipFile, ZipArchive::CREATE)) !== true) {
            throw new Exception('Cannot open zip archive ' . $zipFile . ' - got res ' . $res);
        }
        $fileNames = $this->getFileName(true, $this->cExportPath);
        if (!\is_array($fileNames)) {
            $fileNames = [$fileNames];
        }
        foreach ($fileNames as $fileName) {
            if ($archive->addFile($fileName, \basename($fileName)) === false) {
                $this->logger->warning(
                    __FILE__ . ':'
                    . __CLASS__ . '->'
                    . __METHOD__ . '; ' . \__('loggerErrorZip')
                );

                return false;
            }
        }
        $archive->close();
        foreach ($fileNames as $fileName) {
            \unlink($fileName);
        }
        $dir = \JTLSEARCH_PFAD_EXPORTFILE_DIR . 'tmpSearchExport' . $this->oJTLSearchExportQueue->getExportMethod();
        if (\is_dir($dir)) {
            \rmdir($dir);
        }

        return true;
    }

    /**
     * @param bool        $all
     * @param null|string $fullPath
     * @return array|string
     */
    public function getFileName(bool $all = false, $fullPath = null)
    {
        return $this->oJTLSearchExportQueue->getFileName($all, $fullPath);
    }
}
