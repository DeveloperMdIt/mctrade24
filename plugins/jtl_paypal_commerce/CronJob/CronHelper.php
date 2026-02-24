<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\CronJob;

use DateTime;
use JTL\Cron\JobInterface;
use Plugin\jtl_paypal_commerce\AlertService;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class CronHelper
 * @package Plugin\jtl_paypal_commerce\CronJob
 */
final class CronHelper
{
    public const CRON_TYPE = 'plugin:jtl_paypal_commerce';

    private const SETTING_NAME = Settings::BACKEND_SETTINGS_SECTION_GENERAL . '_shipmenttracking';

    /** @var self */
    private static self $instance;

    /** @var Configuration */
    private Configuration $config;

    /**
     * CronHelper constructor
     */
    private function __construct(Configuration $config)
    {
        $this->config   = $config;
        self::$instance = $this;
    }

    /**
     * @param Configuration $config
     * @return self
     */
    public static function getInstance(Configuration $config): self
    {
        return self::$instance ?? new self($config);
    }

    /**
     * @param int    $frequency
     * @param string $startTime
     * @return void
     */
    public static function createCron(int $frequency = 6, string $startTime = '02:00'): void
    {
        $controller = new CronController();
        $alertSrvc  = AlertService::getInstance();
        $cron       = self::getCron($controller);
        if ($cron !== null) {
            return;
        }

        if (
            $controller->addQueueEntry([
                'type'      => self::CRON_TYPE,
                'frequency' => $frequency,
                'time'      => $startTime,
                'date'      => (new DateTime())->format('Y-m-d H:i:s'),
            ]) <= 0
        ) {
            $alertSrvc->addError(
                \__('Der Cron-Job für den Sendungsstatus konnte nicht erstellt werden'),
                'cronCreation'
            );
        }
    }

    /**
     * @return void
     */
    public static function dropCron(): void
    {
        $controller = new CronController();
        $alertSrvc  = AlertService::getInstance();
        $cron       = self::getCron($controller);
        if ($cron === null) {
            return;
        }

        if ($controller->deleteQueueEntry($cron->getCronID()) <= 0) {
            $alertSrvc->addError(
                \__('Der Cron-Job für den Sendungsstatus konnte nicht gelöscht werden'),
                'cronCreation'
            );
        }
    }

    /**
     * @param CronController|null $controller
     * @return JobInterface|null
     */
    public static function getCron(?CronController $controller = null): ?JobInterface
    {
        $controller = $controller ?? new CronController();
        $cron       = \array_filter($controller->getJobs(), static function (JobInterface $job) {
            return $job->getType() === self::CRON_TYPE;
        });

        return count($cron) === 0 ? null : \array_shift($cron);
    }

    /**
     * @param array $args
     * @return void
     */
    public function mappingCronjobType(array &$args): void
    {
        /** @var string $type */
        $type = $args['type'];
        if ($type === self::CRON_TYPE) {
            $args['mapping'] = CronJob::class;
        }
    }

    /**
     * @param array $args
     * @return void
     */
    public function availableCronjobType(array &$args): void
    {
        if (
            !\in_array(self::CRON_TYPE, $args['jobs'], true)
            && $this->config->getPrefixedConfigItem(self::SETTING_NAME, 'N') === 'Y'
        ) {
            $args['jobs'][] = self::CRON_TYPE;
        }
    }
}
