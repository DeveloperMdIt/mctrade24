<?php

namespace Plugin\jtl_search\ExportModules;

use Exception;
use JTL\Network\Communication;
use JTL\Plugin\Helper;
use Plugin\jtl_search\ManageBase;
use Plugin\jtl_search\Security;
use stdClass;

/**
 * Class JTLShopExport
 * @package Plugin\jtl_search\ExportModules
 */
class JTLShopExport extends Export
{
    /**
     * @inheritdoc
     */
    protected function nextRun()
    {
        $this->oJTLSearchExportQueue->save();

        $return              = new stdClass();
        $return->nReturnCode = ManageBase::STATUS_NOT_DONE;
        $return->nCountAll   = $this->oJTLSearchExportQueue->getSumCount();
        $return->nExported   = $this->oJTLSearchExportQueue->getLimitN();

        switch ($this->oJTLSearchExportQueue->getExportMethod()) {
            case 2:
                echo \json_encode($return);
                die();
            case 1:
            default:
                return $return;
        }
    }

    /**
     * @inheritdoc
     */
    protected function lastRun()
    {
        $exports = [];
        // Delta-Export
        if ($this->oJTLSearchExportQueue->getExportMethod() === 3) {
            $exports = $this->db->selectAll('tjtlsearchdeltaexport', 'bDelete', 1);
            foreach ($exports as $export) {
                $keyName             = 'k' . \strtoupper($export->eDocumentType[0])
                    . \substr($export->eDocumentType, 1);
                $delete              = new stdClass();
                $delete->{$keyName}  = $export->kId;
                $delete->cObjectType = \strtoupper($export->eDocumentType[0]) . \substr($export->eDocumentType, 1);
                $delete->cObjectType = \str_replace(
                    'Plugin\\jtl_search\\ExportModules\\\\',
                    '',
                    $delete->cObjectType
                );
                $delete->cObjectType = \str_replace('Plugin\jtl_search\ExportModules\\', '', $delete->cObjectType);
                $delete->bDelete     = 1;
                \file_put_contents(
                    $this->getFileName(false, $this->cExportPath),
                    \json_encode($delete) . "\n",
                    \FILE_APPEND
                );
                unset($delete);
            }
        }

        $return              = new stdClass();
        $return->nReturnCode = ManageBase::STATUS_DONE;
        $return->nCountAll   = $this->oJTLSearchExportQueue->getSumCount();
        $return->nExported   = $this->oJTLSearchExportQueue->getLimitN();
        if (\count($exports) > 0) {
            $return->nExported += \count($exports);
        }

        $this->oJTLSearchExportQueue->setFinished(true)
            ->setLastRun(\date('Y-m-d H:i:s'))
            ->save();
        if ($return->nExported > 0) {
            if ($this->zipFile()) {
                $this->logger->debug(__CLASS__ . '->' . __METHOD__ . '; ' . \__('successDataZip'));

                $return->nServerResponse = $this->sendFileToImportQueue();
            } else {
                $this->logger->warning(__CLASS__ . '->' . __METHOD__ . '; ' . \__('errorDataZip'));
                $return->nServerResponse = 0;
                $return->cMessage        = \__('errorDataZip');
            }
        } else {
            $return->nServerResponse = 0;
        }
        switch ($this->oJTLSearchExportQueue->getExportMethod()) {
            case 2:
                echo \json_encode($return);
                break;
            case 3:
                $this->db->query('TRUNCATE TABLE tjtlsearchdeltaexport');

                return $return;
            case 1:
            default:
                return $return;
        }
    }

    /**
     * @return mixed
     */
    protected function sendFileToImportQueue()
    {
        if ($this->oJTLSearchExportQueue->getExportMethod() === 3) {
            $exportFile    = \JTLSEARCH_URL_DELTA_EXPORTFILE_ZIP;
            $data['delta'] = 1;
        } else {
            $exportFile = \JTLSEARCH_URL_EXPORTFILE_ZIP;
        }

        if (\JTLSEARCH_NO_SSL === true) {
            $exportFile = \str_replace('https://', 'http://', $exportFile);
        }

        $this->logger->debug(__CLASS__ . '->' . __METHOD__ . '; File an die Importqueue des Suchservers senden.');

        $plugin = Helper::getPluginById('jtl_search');
        if ($plugin !== null && $plugin->getConfig()->getValue('cProjectId')) {
            // Security Objekt erstellen und Parameter zum senden der Daten setzen
            $security = new Security(
                $plugin->getConfig()->getValue('cProjectId'),
                $plugin->getConfig()->getValue('cAuthHash')
            );
            $security->setParams(['getexport', \urlencode($exportFile)]);

            $data['a']   = 'getexport';
            $data['pid'] = $plugin->getConfig()->getValue('cProjectId');
            $data['url'] = \urlencode($exportFile);
            $data['p']   = $security->createKey();

            //Antwort-/Fehler-Codes:
            // 1 = Alles O.K.
            // 2 = Authentifikation fehlgeschlagen
            // 3 = Benutzer wurde nicht gefunden
            // 4 = Auftrag konnte nicht in die Queue gespeichert werden
            // 5 = Requester IP stimmt nicht mit der Domain aus der Datenbank ueberein
            try {
                $postData = Communication::postData(
                    \urldecode($plugin->getConfig()->getValue('cServerUrl')) . 'importdaemon/index.php',
                    $data
                );
                $this->logger->debug(
                    __CLASS__ . '->' . __METHOD__ . ': '
                    . \sprintf(\__('loggerDebugResponse'), $postData)
                );

                return \json_decode($postData);
            } catch (Exception $e) {
                $this->logger->warning(__CLASS__ . '->' . __METHOD__ . ': ' . \__('loggerErrorSendFile'));
            }
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function setExportPath(string $path): void
    {
        if (\strlen($path) > 0) {
            $this->cExportPath = $path;
        }
    }
}
