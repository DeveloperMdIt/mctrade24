<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Controllers;

use Plugin\s360_clerk_shop5\src\Entities\StoreEntity;
use Plugin\s360_clerk_shop5\src\Export\FeedGenerator;
use Plugin\s360_clerk_shop5\src\Models\StoreModel;
use Plugin\s360_clerk_shop5\src\Utils\LoggerTrait;
use Throwable;

final class CronJobController
{
    use LoggerTrait;

    private bool $isFinished = false;
    private FeedGenerator $generator;
    private StoreModel $model;

    /**
     * @var StoreEntity[]
     */
    private array $feeds;

    public function __construct(private int $numberOfCreatedFeeds = 0)
    {
        $this->generator = new FeedGenerator();
        $this->model = new StoreModel();
        $this->feeds = $this->model->all();
    }

    public function isFinished(): bool
    {
        return $this->isFinished;
    }

    public function getNumberOfCreatedFeeds(): int
    {
        return $this->numberOfCreatedFeeds;
    }

    /**
     * @return StoreEntity[]
     */
    public function getFeeds(): array
    {
        return $this->feeds;
    }

    public function getTotalFeeds(): int
    {
        return \count($this->feeds);
    }

    /**
     * Hande the generation of a specific feed or all feeds.
     *
     * @param int|null $feedIndex If no index is provided all feeds will be created.
     * @return void
     */
    public function handle(?int $feedIndex = null): void
    {
        $this->debugLog('Generating Data Feeds');

        try {
            // No feeds - nothing to do
            if (empty($this->feeds)) {
                $this->debugLog('No feeds to generate. Abort!');
                $this->isFinished = true;
            }

            foreach ($this->feeds as $key => $feed) {
                // handle current feed in queue but only if we have specified a feed otherwise we will handle all feeds
                if ($feedIndex === null || $feedIndex === $key) {
                    $this->debugLog(sprintf(
                        'Feed ID %s [lang=%s;customer_group=%s]',
                        $feed->getId(),
                        $feed->getLanguage()->getLocalizedName(),
                        $feed->getCustomerGroup()->getName()
                    ));

                    $feed = $this->model->loadSettings($feed);
                    $this->generator->createFeed($feed);

                    // Increase feed index so that the next feed in line can be handled the next time the this is called
                    $this->numberOfCreatedFeeds++;
                    $this->isFinished = true;
                }
            }
        } catch (Throwable $err) {
            $this->errorLog(sprintf(
                'Error during feed generation: %s in %s on line %s',
                $err->getMessage(),
                $err->getFile(),
                $err->getLine()
            ));

            // If we encountered an error we are done for now
            $this->isFinished = true;
        }

        $this->debugLog('Finished generating Data Feed');
    }
}
