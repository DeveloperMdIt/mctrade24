<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Utils;

use JTL\Plugin\Helper;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Shop;

trait LoggerTrait
{

    public function debugLog(mixed $message, string $context = ''): void
    {
        $this->writeLog($message, JTLLOG_LEVEL_DEBUG, $context);
    }

    public function noticeLog(mixed $message, string $context = ''): void
    {
        $this->writeLog($message, JTLLOG_LEVEL_NOTICE, $context);
    }

    public function errorLog(mixed $message, string $context = ''): void
    {
        $this->writeLog($message, JTLLOG_LEVEL_ERROR, $context);
    }

    private function writeLog(mixed $message, int $level, string $context = ''): void
    {
        try {
            // fallback for shop versions < 5.3.0
            $logger = Shop::Container()->getLogService();

            if (Compatibility::isShopAtLeast53()) {
                $logger = Helper::getPluginById(Config::PLUGIN_ID)->getLogger();
            }

            if ($context !== '') {
                $context .= ': ';
            }

            if ($logger->isHandling($level)) {
                if (\is_array($message)) {
                    foreach ($message as $msg) {
                        $logger->log($level, Logger::LOG_PREFIX . $context . $msg);
                    }

                    return;
                }
                elseif (\is_string($message)) {
                    $logger->log($level, Logger::LOG_PREFIX . $context . $message);

                    return;
                }

                $logger->log($level, Logger::LOG_PREFIX . $context . print_r($message, true));
            }
        } catch (ServiceNotFoundException $ex) {
            // Too bad, no logging service exists. We cannot log this - ignore it.
        } catch (CircularReferenceException $ex) {
            // This should not be possible, unless this trait is used within a Logging service.
            // We cannot log this - ignore it.
        }
    }
}
