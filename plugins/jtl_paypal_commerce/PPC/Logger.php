<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use Exception;
use JTL\Helpers\Text;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

class Logger
{
    public const JTL_PAYPAL_COMMERCE = 'JTL_PAYPAL_COMMERCE';
    public const TYPE_ONBOARDING     = 'ONBOARDING';
    public const TYPE_PAYMENT        = 'PAYMENT';
    public const TYPE_INFORMATION    = 'INFORMATION';

    /** @var PayPalPaymentInterface|null */
    private ?PayPalPaymentInterface $method;

    /** @var string */
    private string $type;

    /** @var int */
    private int $logLevel;

    /**
     * Logger constructor
     * @param string                      $type
     * @param PayPalPaymentInterface|null $method
     * @param int|null                    $minLogLevel
     */
    public function __construct(string $type, ?PayPalPaymentInterface $method = null, ?int $minLogLevel = null)
    {
        $this->type   = $type;
        $this->method = $method;
        $minLogLevel  = $minLogLevel ?? (int)Shop::getSettingValue(\CONF_GLOBAL, 'systemlog_flag');

        if ($minLogLevel >= \JTLLOG_LEVEL_ERROR) {
            $this->logLevel = \LOGLEVEL_ERROR;
        } elseif ($minLogLevel >= \JTLLOG_LEVEL_NOTICE) {
            $this->logLevel = \LOGLEVEL_NOTICE;
        } else {
            $this->logLevel = \LOGLEVEL_DEBUG;
        }
    }

    /**
     * @return PayPalPaymentInterface|null
     */
    public function getMethod(): ?PayPalPaymentInterface
    {
        return $this->method;
    }

    /**
     * @param PayPalPaymentInterface $method
     * @return Logger
     */
    public function setMethod(PayPalPaymentInterface $method): Logger
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Logger
     */
    public function setType(string $type): Logger
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getLogLevel(): int
    {
        return $this->logLevel;
    }

    /**
     * @param int    $logLevel
     * @param string $messageText
     * @param mixed  $messagePayload
     */
    public function write(int $logLevel, string $messageText, mixed $messagePayload = null): void
    {
        if ($logLevel > $this->logLevel) {
            return;
        }
        if ($messagePayload !== null) {
            switch ($messagePayload) {
                case $messagePayload instanceof PPCRequestException:
                    $payload = \print_r((object)[
                        'Debug-Id' => $messagePayload->getDebugId(),
                        'Detail'   => $messagePayload->getDetail(),
                    ], true);

                    break;
                case $messagePayload instanceof Exception:
                    $payload = \print_r((object)[
                        'error-msg'  => $messagePayload->getMessage(),
                        'error-code' => $messagePayload->getCode(),
                        'error-prev' => $messagePayload->getPrevious(),
                    ], true);

                    break;
                case $messagePayload instanceof JSONResponse:
                    try {
                        $payload = \print_r($messagePayload->getData() ?? '', true);
                    } catch (Exception $e) {
                        $payload = $e->getMessage();
                    }
                    break;
                case $messagePayload instanceof SerializerInterface:
                    $payload = $messagePayload->stringify() ?? '';
                    break;
                case \is_array($messagePayload):
                case \is_object($messagePayload):
                    $payload = \print_r($messagePayload, true);
                    break;
                default:
                    $payload = (string)$messagePayload;
            }
        } else {
            $payload = '';
        }

        switch ($this->type) {
            case self::TYPE_INFORMATION:
            case self::TYPE_ONBOARDING:
                try {
                    $this->logOnboarding($logLevel, $messageText, $payload);
                } catch (Exception) {
                    return;
                }
                break;
            case self::TYPE_PAYMENT:
            default:
                if (!isset($this->method)) {
                    \trigger_error('PaymentMethod to log for is missing!');

                    return;
                }
                $this->logPayment($logLevel, $messageText, $payload);
        }
    }

    /**
     * @param int    $logLevel
     * @param string $messageText
     * @param string $messagePayload
     */
    private function logPayment(int $logLevel, string $messageText, string $messagePayload): void
    {
        $messageHtml = $this->formatMessage($messageText, $messagePayload);
        try {
            $this->method->doLog($this->prepareOutput($messageHtml), $logLevel);
        } catch (Exception) {
            return;
        }
    }

    /**
     * @param int    $logLevel
     * @param string $messageText
     * @param string $messagePayload
     */
    private function logOnboarding(int $logLevel, string $messageText, string $messagePayload): void
    {
        try {
            $logService  = Shop::Container()->getLogService();
            $messageHtml = $this->formatMessage($messageText, $messagePayload);
            switch ($logLevel) {
                case \LOGLEVEL_ERROR:
                    $logService->error($this->prepareOutput($messageHtml));
                    break;
                case \LOGLEVEL_NOTICE:
                    $logService->notice($this->prepareOutput($messageHtml));
                    break;
                case \LOGLEVEL_DEBUG:
                default:
                    $logService->debug($this->prepareOutput($messageHtml));
                    break;
            }
        } catch (Exception) {
            return;
        }
    }

    /**
     * @param string $messageText
     * @param string $messagePayload
     * @return string
     */
    private function formatMessage(string $messageText, string $messagePayload): string
    {
        return Text::filterXSS(\trim($messageText))
            . ($messagePayload !== '' ? '<br /><pre>' . Text::htmlentities($messagePayload) . '</pre>' : '');
    }

    /**
     * @param string $message
     * @return string
     */
    private function prepareOutput(string $message): string
    {
        return '[' . self::JTL_PAYPAL_COMMERCE . '] ' . $this->type . ': ' . $message;
    }
}
