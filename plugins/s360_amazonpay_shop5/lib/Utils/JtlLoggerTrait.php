<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Helpers\Text;
use JTL\Shop;
use Psr\Log\LoggerInterface;

/**
 * Class JtlLoggerTrait
 *
 * This trait logs messages to the JTL-Shop Log.
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
trait JtlLoggerTrait {

    /**
     * Log message as debug.
     * @param $message
     * @param string $context Additional string to add before log message for better identification.
     */
    public function debugLog($message, string $context = ''): void {
        $this->doLog(Text::filterXSS($message), JTLLOG_LEVEL_DEBUG, $context);
    }

    /**
     * Log message as notice.
     * @param $message
     * @param string $context Additional string to add before log message for better identification.
     */
    public function noticeLog($message, string $context = ''): void {
        $this->doLog(Text::filterXSS($message), JTLLOG_LEVEL_NOTICE, $context);
    }

    /**
     * Log message as error.
     * @param $message
     * @param string $context Additional string to add before log message for better identification.
     */
    public function errorLog($message, string $context = ''): void {
        $this->doLog(Text::filterXSS($message), JTLLOG_LEVEL_ERROR, $context);
    }

    /**
     * Do not call this method, if possible. Use the other trait methods instead to prevent dependencies.
     * @param $message
     * @param int $level
     * @param string $context
     */
    private function doLog($message, int $level, string $context = ''): void {
        try {

            /** @var LoggerInterface $logger */
            if (Compatibility::isShopAtLeast53()) {
                $logger = Plugin::getInstance()->getLogger();
            } else {
                $logger = Shop::Container()->getLogService();
            }

            if($context !== '') {
                $context .= ': ';
            }
            if ($logger->isHandling($level)) {
                if (\is_array($message)) {
                    foreach ($message as $msg) {
                        $logger->log($level, 'LPA: ' . $context . $msg);
                    }
                } elseif (\is_string($message)) {
                    $logger->log($level, 'LPA: ' . $context . $message);
                } else {
                    $logger->log($level, 'LPA: ' . $context . print_r($message, true));
                }
            }
        } catch(ServiceNotFoundException $ex) {
            // Too bad, no logging service exists. We cannot log this - ignore it.
        } catch(CircularReferenceException $ex) {
            // This should not be possible, unless this trait is used within a Logging service. We cannot log this - ignore it.
        }
    }
}