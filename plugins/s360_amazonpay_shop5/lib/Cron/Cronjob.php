<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5\lib\Cron;

use JTL\Cron\Job;
use JTL\Cron\JobInterface;
use JTL\Cron\QueueEntry;
use Plugin\s360_amazonpay_shop5\lib\Controllers\CronjobController;

class Cronjob extends Job {

    /**
     * @inheritdoc
     */
    public function start(QueueEntry $queueEntry): JobInterface {
        parent::start($queueEntry);
        $controller = new CronjobController();
        $controller->run();
        $this->setFinished(true); // we always pretend to be finished, it does not really matter, as we will run again with the next run after 1 hour.
        return $this;
    }
}