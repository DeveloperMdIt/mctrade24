<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JTL\Cron\Job;
use JTL\Cron\QueueEntry;
use JTL\Cron\JobInterface;
use Plugin\s360_clerk_shop5\src\Controllers\CronJobController;

final class Cronjob extends Job
{
    /**
     * @var int The array index of the feed in list of all feeds
     */
    private int $feedIndex;

    /**
     * @inheritDoc
     */
    public function saveProgress(QueueEntry $queueEntry): bool
    {
        parent::saveProgress($queueEntry);
        $this->db->update(
            'tjobqueue',
            'jobQueueID',
            $this->getQueueID(),
            (object)['foreignKey' => (string)$this->feedIndex]
        );

        return true;
    }

    public function start(QueueEntry $queueEntry): JobInterface
    {
        parent::start($queueEntry);

        $this->feedIndex = (int)$queueEntry->foreignKey;

        $controller = new CronJobController($queueEntry->tasksExecuted);
        $controller->handle($this->feedIndex);

        // If the cron finished (either due to error or because a feed was generated), increase the feed index,
        // so that the next feed in line will be handled in the next call
        if ($controller->isFinished()) {
            $this->setForeignKey((string) ++$this->feedIndex);
        }

        // If we have created all feeds we are finished
        $queueEntry->tasksExecuted = $controller->getNumberOfCreatedFeeds();
        $this->setFinished($this->feedIndex >= $controller->getTotalFeeds());

        return $this;
    }
}
