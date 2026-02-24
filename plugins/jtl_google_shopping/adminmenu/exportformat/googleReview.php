<?php

declare(strict_types=1);

use JTL\Cron\QueueEntry;
use JTL\Export\Exporter\PluginExporter;
use JTL\Shop;
use Plugin\jtl_google_shopping\Exportformat\GoogleReviewExport;

/** @var PluginExporter $this */
global $exportformat, $queue, $oJobQueue, $ExportEinstellungen;

try {
    $exporter = new GoogleReviewExport($exportformat, $ExportEinstellungen, Shop::Container()->getDB());
    $started  = $exporter->setQueueEntry(is_a($queue, QueueEntry::class) ? $queue : $oJobQueue, isset($oJobQueue))
        ->setExportSQL($this->getExportSQL())
        ->run();

    if ($started) {
        $this->setZuletztErstellt((new DateTime())->format('Y-m-d H:i:s'));
        $this->update();
    }
} catch (Exception $e) {
    Shop::Container()->getAlertService()->addError($e->getMessage(), 'googleShopping');
}
