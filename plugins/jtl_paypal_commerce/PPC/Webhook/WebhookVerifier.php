<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Plugin\jtl_paypal_commerce\frontend\Handler\WebhookHandler;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class WebhookVerifier
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookVerifier
{
    protected Configuration $config;

    private Logger $logger;

    /**
     * WebhookVerifier constructor
     */
    public function __construct(Configuration $config, ?Logger $logger = null)
    {
        $this->config = $config;
        $this->logger = $logger ?? new Logger(Logger::TYPE_INFORMATION);
    }

    public function verifyWebhook(string $webhookId, WebhookCallResponse $response): void
    {
        $environment = PPCHelper::getEnvironment($this->config);
        $client      = new PPCClient($environment);
        try {
            if (
                !\defined('PAYPAL_WEBHOOK_NOT_VERIFY')
                || \PAYPAL_WEBHOOK_NOT_VERIFY !== true
                || !$environment->isSandbox()
            ) {
                $verifyRequest = new WebhookVerifySignatureRequest(
                    Token::getInstance()->getToken(),
                    $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? '',
                    $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? '',
                    $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? '',
                    $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? '',
                    $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? '',
                    $webhookId,
                    $response->getOriginalData()
                );

                $verifyResponse = new WebhookVerifySignatureResponse($client->send($verifyRequest));
                if (!$verifyResponse->isVerified()) {
                    $this->logger->write(
                        \LOGLEVEL_ERROR,
                        'Webhook::handleCall - Webhook not verified:',
                        [
                            'response'  => $verifyResponse,
                            'webhookId' => $webhookId,
                            'content'   => $response->getOriginalData(),
                            'header'    => [
                                'PAYPAL_AUTH_ALGO'         => $_SERVER['HTTP_PAYPAL_AUTH_ALGO'] ?? 'NULL',
                                'PAYPAL_CERT_URL'          => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? 'NULL',
                                'PAYPAL_TRANSMISSION_ID'   => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? 'NULL',
                                'PAYPAL_TRANSMISSION_SIG'  => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? 'NULL',
                                'PAYPAL_TRANSMISSION_TIME' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? 'NULL',
                            ],
                        ]
                    );

                    WebhookHandler::exitResult(400, 'Webhook not verified.');
                    exit();
                }

                $this->logger->write(\LOGLEVEL_DEBUG, 'Webhook::handleCall - Webhook verified:', $verifyResponse);
                return;
            }
        } catch (Exception | GuzzleException $e) {
            $this->logger->write(
                \LOGLEVEL_ERROR,
                'Webhook::handleCall - verification failed: ' . $e->getMessage()
            );

            WebhookHandler::exitResult(400, 'Webhook verification failed.');
            exit();
        }

        $this->logger->write(\LOGLEVEL_DEBUG, 'Webhook::handleCall - no verification executed');
    }
}
