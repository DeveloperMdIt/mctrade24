<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend\Handler;

use JsonException;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\Order\Capture;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;
use Plugin\jtl_paypal_commerce\PPC\Webhook\EventType;
use Plugin\jtl_paypal_commerce\PPC\Webhook\Webhook;
use Plugin\jtl_paypal_commerce\PPC\Webhook\WebhookCallResponse;
use Plugin\jtl_paypal_commerce\PPC\Webhook\WebhookVerifier;

/**
 * Class WebhookHandler
 * @package Plugin\jtl_paypal_commerce\frontend\Handler
 */
class WebhookHandler
{
    protected PluginInterface $plugin;

    protected Configuration $config;

    private Logger $logger;

    /**
     * WebhookHandler constructor
     */
    public function __construct(PluginInterface $plugin, Configuration $config, ?Logger $logger = null)
    {
        $this->plugin = $plugin;
        $this->config = $config;
        $this->logger = $logger ?? new Logger(Logger::TYPE_INFORMATION);
    }

    /**
     * @noinspection PhpNoReturnAttributeCanBeAddedInspection
     */
    public static function exitResult(int $exitCode = 200, ?string $content = null): void
    {
        $headers = [
            200 => 'OK',
            400 => 'Bad Request',
            500 => 'Internal Server Error',
        ];
        if (!\array_key_exists($exitCode, $headers)) {
            $exitCode = 500;
        }
        \ob_end_clean();
        \header(\sprintf('%s %d %s', $_SERVER['SERVER_PROTOCOL'], $exitCode, $headers[$exitCode]));
        if ($content !== null) {
            echo $content;
        }

        exit();
    }

    private function handleCaptureWebhook(WebhookCallResponse $response, string $eventType): void
    {
        try {
            $capture = new Capture($response->getData());
        } catch (JsonException | UnexpectedResponseException $e) {
            $this->logger->write(\LOGLEVEL_ERROR, 'Webhook::handleCall - Unexpected data for payment process id: '
                . $e->getMessage());

            self::exitResult(400, 'Unexpected data for payment process id.');
            exit();
        }

        $db      = Shop::Container()->getDB();
        $txnId   = $capture->getRelatedOrderId();
        $payment = $db->getSingleObject(
            'SELECT tbestellung.kBestellung, COALESCE(tzahlungsid.kZahlungsart,
                    tbestellung.kZahlungsart) AS kZahlungsart,
                    tzahlungsid.cId, tzahlungsid.txn_id
                FROM tbestellung
                LEFT JOIN tzahlungsid ON tbestellung.kBestellung = tzahlungsid.kBestellung
                    AND tzahlungsid.txn_id = :txnId
                WHERE cBestellNr = :oderNumber',
            [
                'txnId'      => $txnId,
                'oderNumber' => $capture->getInvoiceId(),
            ]
        );

        if ($payment === null || (int)$payment->kZahlungsart === 0) {
            // payment process id not found
            // - there is no session hash created for the captured payment or payment is already processed
            $this->logger->write(
                \LOGLEVEL_NOTICE,
                'Webhook::handleCall - No payment for order id ' . $txnId . ' found'
            );

            self::exitResult();
            exit();
        }

        $paymentHelper = Helper::getInstance($this->plugin);
        $payMethod     = $paymentHelper->getPaymentFromID((int)$payment->kZahlungsart);
        if ($payMethod === null) {
            $this->logger->write(
                \LOGLEVEL_NOTICE,
                'Webhook::handleCall - No payment method for order id ' . $txnId . ' found'
            );

            self::exitResult();
            exit();
        }
        $this->logger->setMethod($payMethod);

        if (!$payMethod->handleCaptureWebhook($eventType, $capture, $payment)) {
            $this->logger->write(
                \LOGLEVEL_ERROR,
                'Webhook::handleCall - capture failed for method ' . $payMethod->getMethod()->getName()
            );
        }

        self::exitResult();
        exit();
    }

    private function handlePaymentTokenWebhook(WebhookCallResponse $response, string $eventType): void
    {
        if ($eventType !== EventType::VAULT_TOKEN_DELETED) {
            $this->logger->write(\LOGLEVEL_DEBUG, \sprintf(
                'Webhook::handlePaymentTokenWebhook - "%s" not supported',
                $eventType
            ));
            self::exitResult();
            exit();
        }

        try {
            $tokenId = $response->getData()->id ?? null;
        } catch (JsonException | UnexpectedResponseException) {
            $tokenId = null;
        }
        if ($tokenId === null) {
            $this->logger->write(\LOGLEVEL_ERROR, 'Webhook::handlePaymentTokenWebhook - Unexpected token id');

            self::exitResult(400, 'Unexpected token id.');
            exit();
        }

        $vaultHelper = new VaultingHelper($this->config);
        $vaultHelper->deleteVault($tokenId);

        self::exitResult();
        exit();
    }

    protected function checkDataValidation(bool|string $content, string $webhookId): void
    {
        if (empty($content)) {
            self::exitResult(500, 'No data received');
            exit();
        }
        if (empty($webhookId)) {
            self::exitResult(500, 'Webhook not configured');
            exit();
        }
    }

    protected function checkRegistration(string $eventType, string $resourceType): void
    {
        if (!\in_array($resourceType, ['capture', 'payment_token'], true)) {
            $this->logger->write(
                \LOGLEVEL_NOTICE,
                'Webhook::handleCall - resource type (' . $resourceType . ') not supported'
            );

            self::exitResult();
            exit;
        }

        if (!\in_array($eventType, Webhook::REGISTERED_EVENTS, true)) {
            $this->logger->write(
                \LOGLEVEL_NOTICE,
                'Webhook::handleCall - Event (' . $eventType . ') not registered'
            );

            self::exitResult();
            exit;
        }
    }

    public function handleCall(string $webhookId, bool|string $content): void
    {
        \ob_start();
        $this->checkDataValidation($content, $webhookId);

        $response     = new WebhookCallResponse($content);
        $eventType    = $response->getEventType() ?? 'unknown';
        $resourceType = $response->getResourceType() ?? '';

        $this->logger->write(\LOGLEVEL_DEBUG, 'Webhook::handleCall(' . $eventType . ') received: ', $response);
        $this->checkRegistration($eventType, $resourceType);
        (new WebhookVerifier($this->config, $this->logger))->verifyWebhook($webhookId, $response);

        match ($resourceType) {
            'capture'       => $this->handleCaptureWebhook($response, $eventType),
            'payment_token' => $this->handlePaymentTokenWebhook($response, $eventType),
        };
    }
}
