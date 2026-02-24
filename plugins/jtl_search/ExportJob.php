<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\Plugin\Helper;
use stdClass;

/**
 * Class ExportJob
 * @package Plugin\jtl_search
 */
class ExportJob extends Job
{
    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface
    {
        $queueEntry->isRunning = 1;
        parent::start($queueEntry);
        $plugin = Helper::getPluginById('jtl_search');
        if ($plugin === null) {
            $this->logger->warning('Could not find JTL Search plugin');

            return $this;
        }
        $serverInfo             = new stdClass();
        $serverInfo->cProjectId = $plugin->getConfig()->getValue('cProjectId');
        $serverInfo->cAuthHash  = $plugin->getConfig()->getValue('cAuthHash');
        $serverInfo->cServerUrl = $plugin->getConfig()->getValue('cServerUrl');
        $export                 = new ManageExport($this->logger, $this->db, $serverInfo);
        $this->logger->debug(\sprintf(\__('loggerCronStarted'), __CLASS__, $queueEntry->nLimitN));
        if ($queueEntry->tableName === 'JTLSearchDeltaExportCron') {
            $exportMethodType = 3;
            $this->logger->debug(\sprintf(\__('loggerExportMethodDelta'), __CLASS__, $exportMethodType));
        } else {
            if ($plugin->getConfig()->getValue('jtlsearch_export_updates') === 'delta') {
                // do not export if delta option after wawi sync is selected
                $queueEntry->isRunning = 0;
                $this->setFinished(true);

                return $this;
            }
            $exportMethodType = 1;
            $this->logger->debug(\sprintf(\__('loggerExportMethodPlanner'), __CLASS__, $exportMethodType));
        }
        $limit = $queueEntry->tasksExecuted ?? $queueEntry->nLimitN ?? 0;
        if ($limit === 0) {
            $this->logger->debug(\sprintf(\__('loggerNewQueue'), __CLASS__));
            $export->newQueue($exportMethodType);
        }
        $res = $export->doExport($exportMethodType);
        if (isset($res) && \is_object($res)) {
            if ($res->nReturnCode === ManageBase::STATUS_DONE) {
                $this->logger->debug(\sprintf(\__('loggerExportDone'), __CLASS__));
                $queueEntry->isRunning = 0;
                $this->setFinished(true);
            } else {
                $queueEntry->tasksExecuted = $res->nExported;
                $queueEntry->isRunning     = 0;
            }
        } else {
            $this->logger->debug(\sprintf(\__('loggerErrorCronResult'), __CLASS__));
            $queueEntry->isRunning = 0;
        }

        return $this;
    }
}
