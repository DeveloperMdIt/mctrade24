<?php

namespace Plugin\jtl_search;

use Exception;
use JTL\DB\DbInterface;
use JTL\Network\Communication;
use Plugin\jtl_search\ExportModules\JTLSearchExportQueue;
use Plugin\jtl_search\ExportModules\JTLShopExport;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class ManageExport
 * @package Plugin\jtl_search
 */
class ManageExport extends ManageBase
{
    /**
     * @inheritdoc
     */
    public function __construct(LoggerInterface $logger, DbInterface $db, ?stdClass $serverInfo)
    {
        $this->logger          = $logger;
        $this->db              = $db;
        $this->serverInfo      = $serverInfo;
        $this->contentTemplate = \JTLSEARCH_ADMIN_TPL_PATH . 'manage_export.tpl';
        $this->cssFile         = \JTLSEARCH_ADMIN_CSS_URL_PATH . 'export.css';
    }

    /**
     * @inheritdoc
     */
    public function generateContent(bool $force = false): void
    {
        if ($force === true || $this->getIssetContent() === false) {
            $this->setIssetContent(true)
                ->setSort(2)
                ->setName('Export')
                ->setContentVar('importStatus', $this->getImportStatus())
                ->setContentVar('importHistory', $this->getImportHistory());
        }
    }

    /**
     * @param int $exportMethod
     */
    public function newQueue(int $exportMethod): void
    {
        if ($exportMethod <= 0) {
            $this->logger->debug(\sprintf(\__('loggerErrorMethodID'), __CLASS__, $exportMethod));

            return;
        }
        if (\is_writable(\JTLSEARCH_PFAD_EXPORTFILE_DIR)) {
            if (\is_dir(\JTLSEARCH_PFAD_EXPORTFILE_DIR . 'tmpSearchExport' . $exportMethod)) {
                if (\is_writable(\JTLSEARCH_PFAD_EXPORTFILE_DIR . 'tmpSearchExport' . $exportMethod)) {
                    $this->rrmdir(\JTLSEARCH_PFAD_EXPORTFILE_DIR . 'tmpSearchExport' . $exportMethod);
                } else {
                    $logWarning = \sprintf(
                        \__('loggerErrorExportWritePermissions'),
                        __CLASS__,
                        \JTLSEARCH_PFAD_EXPORTFILE_DIR,
                        $exportMethod
                    );
                    $this->logger->warning($logWarning);
                    die($logWarning);
                }
            }
            \mkdir(\JTLSEARCH_PFAD_EXPORTFILE_DIR . 'tmpSearchExport' . $exportMethod);
        } else {
            $logWarning = \sprintf(
                \__('loggerErrorExportFileDirWritePermissions'),
                __CLASS__,
                \JTLSEARCH_PFAD_EXPORTFILE_DIR
            );
            $this->logger->warning($logWarning);
            die($logWarning);
        }

        try {
            if (JTLSearchExportQueue::generateNew($exportMethod, $this->db)) {
                $this->logger->debug(\sprintf(\__('loggerSuccessQueueCreated'), __CLASS__));
            }
        } catch (Exception $e) {
            $this->logger->warning(\sprintf(\__('loggerErrorQueueCreate'), __CLASS__, $e->getMessage()));
            die();
        }

        // $nExportMethod 2 bedeutet, dass es über ein Ajax-Request aufgerufen wurde und 1 als Antwort erwartet
        if ($exportMethod === 2) {
            echo 1;
        }
    }

    /**
     * @param int $exportMethod
     * @return stdClass|null
     */
    public function doExport(int $exportMethod = 1)
    {
        $this->logger->debug(__METHOD__ . ': Starte Export-Durchgang');
        $export = new JTLShopExport($this->db, $this->logger, $exportMethod);
        $export->setExportPath(\JTLSEARCH_PFAD_EXPORTFILE_DIR);
        $res = $export->exportAll();
        $this->logger->debug(__METHOD__ . ': Export-Durchgang beendet (Res: ' . \print_r($res, true) . ').');

        return $res;
    }

    /**
     * @return bool|mixed
     */
    private function getImportHistory()
    {
        return $this->getImportData('getimporthistory');
    }

    /**
     * @return bool|mixed
     */
    private function getImportError()
    {
        return $this->getImportData('getimporterror');
    }

    /**
     * @return bool|mixed
     */
    private function getImportStatus()
    {
        return $this->getImportData('getimportstatus');
    }

    /**
     * @param string $action
     * @return bool|mixed
     */
    private function getImportData(string $action)
    {
        $security = new SecurityIntern();

        $data['a']   = $action;
        $data['pid'] = $this->serverInfo->cProjectId;

        $security->setParams([$data['a'], $data['pid']]);
        $data['p'] = $security->createKey();

        try {
            $res = Communication::postData(
                \urldecode($this->serverInfo->cServerUrl) . 'servermanager/index.php',
                $data
            );

            return \json_decode($res);
        } catch (Exception $e) {
            $this->logger->warning(__CLASS__ . '->' . __METHOD__ . '; ' . \__('loggerErrorServerCommunication'));
        }

        return false;
    }

    /**
     * @param string $dir
     */
    private function rrmdir(string $dir): void
    {
        foreach (\glob($dir . '/*') as $file) {
            if (\is_dir($file)) {
                $this->rrmdir($file);
            } else {
                \unlink($file);
            }
        }
        \rmdir($dir);
    }
}
