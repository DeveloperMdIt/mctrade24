<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\CronJob;

use JTL\Cron\JobInterface;
use JTL\Router\Controller\Backend\CronController as BackendCronController;
use JTL\Shop;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class CronController
 * @package Plugin\jtl_paypal_commerce\CronJob
 */
class CronController
{
    /** @var mixed */
    private mixed $controller = null;

    /**
     * CronController constructor
     */
    public function __construct()
    {
        try {
            $this->controller = Shop::Container()->get(BackendCronController::class);
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface) {
            $this->controller = null;
        }
    }

    /**
     * @param int $cronId
     * @return int
     */
    public function deleteQueueEntry(int $cronId): int
    {
        if ($this->controller === null) {
            return -1;
        }

        return $this->controller->deleteQueueEntry($cronId);
    }

    /**
     * @param array $post
     * @return int
     */
    public function addQueueEntry(array $post): int
    {
        if ($this->controller === null) {
            return -1;
        }

        return $this->controller->addQueueEntry($post);
    }

    /**
     * @return JobInterface[]
     */
    public function getJobs(): array
    {
        if ($this->controller === null) {
            return [];
        }

        return $this->controller->getJobs();
    }
}
