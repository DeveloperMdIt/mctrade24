<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Exportformat;

use Exception;
use JTL\DB\DbInterface;

/**
 * Class GoogleReviewExport
 * @package Plugin\jtl_google_shopping\Exportformat
 */
class GoogleReviewExport extends GoogleShoppingExport
{
    /**
     * @inheritdoc
     */
    public function __construct(object $exportFormat, array $settings, DbInterface $db)
    {
        parent::__construct($exportFormat, $settings, $db);
        $this->tmpFilename = 'tmp_' . \basename($this->exportFormat->cDateiname);
    }

    protected function writeZipFile(): void
    {
        if (\file_exists(self::EXPORT_PATH . $this->exportFormat->cDateiname)) {
            \unlink(self::EXPORT_PATH . $this->exportFormat->cDateiname);
        }
        \rename(self::EXPORT_PATH . $this->tmpFilename, self::EXPORT_PATH . $this->exportFormat->cDateiname);
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
            if (\file_exists(self::EXPORT_PATH . $this->tmpFilename)) {
                \unlink(self::EXPORT_PATH . $this->tmpFilename);
            }

            return false;
        }
        $xml = new GoogleReviewXML(
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
        $this->stop();

        return true;
    }
}
