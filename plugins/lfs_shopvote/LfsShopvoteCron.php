<?php declare(strict_types=1);

namespace Plugin\lfs_shopvote;

use JTL\Catalog\Product\Artikel;
use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use JTL\DB\ReturnType;
use JTL\Plugin\Helper;
use JTL\Shop;
use Plugin\lfs_shopvote\classes\LfsShopvote;

/**
 *
 */
class LfsShopvoteCron extends Job
{
    public CONST shopvoteCronRunnerFile = 'shopvote_cron.lock';

    public CONST itemsPerCall = 50;

    public CONST NumberOfReviewDays = 2;

    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);

        $logger = Shop::Container()->getLogService();

        $lfsShopvotePlugin = Helper::getPluginById('lfs_shopvote');
        $lfsShopvote       = new LfsShopvote($lfsShopvotePlugin, Shop::Container()->getDB());

        $logger->debug(strtoupper($lfsShopvotePlugin->getPluginID()) . ": Cron-Aufruf");

        if ($lockFile = fopen(self::getLockFileName(), 'x')) {
            try {
                $logger->debug(strtoupper($lfsShopvotePlugin->getPluginID()) . ": Neue Bewertungen wurden abgerufen");

                $lfsShopvote->syncNewReviews();
            }
            catch (\Exception $e) {
                $logger->error(strtoupper($lfsShopvotePlugin->getPluginID()) . ": Fehler! - " . $e->getMessage());
            }
            finally {
                fclose($lockFile);

                self::removeLockFile();
            }
        }

        return $this;
    }

    protected static function getLockFileName()
    {
        return sprintf('%stemplates_c/%s', PFAD_ROOT, self::shopvoteCronRunnerFile);
    }

    public static function removeLockFile()
    {
        if (file_exists(self::getLockFileName())) {
            unlink(self::getLockFileName());
        }
    }
}
