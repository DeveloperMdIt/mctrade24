<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JTL\Link\LinkInterface;
use JTL\Plugin\PluginInterface;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Logger;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

use function Functional\first;

/**
 * Class Webhook
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class Webhook
{
    /** @var Configuration */
    protected Configuration $config;

    /** @var PluginInterface */
    protected PluginInterface $plugin;

    /** @var Logger */
    private Logger $logger;

    public const REGISTERED_EVENTS = [
        EventType::CAPTURE_COMPLETED,
        EventType::CAPTURE_DENIED,
        EventType::CAPTURE_REVERSED,
        EventType::VAULT_TOKEN_DELETED,
    ];

    /**
     * Webhook constructor.
     * @param PluginInterface $plugin
     * @param Configuration   $config
     */
    public function __construct(PluginInterface $plugin, Configuration $config)
    {
        $this->plugin = $plugin;
        $this->config = $config;
        $this->logger = new Logger(Logger::TYPE_INFORMATION);
    }

    /**
     * @return string|null
     */
    public function getWebHookURL(): ?string
    {
        /** @var LinkInterface $link */
        $link = $this->plugin->getLinks()->getLinks()->first(static function (LinkInterface $link) {
            return $link->getTemplate() === ('webhook_PayPalCommerce.tpl');
        });
        if ($link === null || $link->getSEO() === \ltrim($_SERVER['REQUEST_URI'], '/')) {
            return null;
        }

        return first($link->getURLs());
    }

    /**
     * @return object|null
     * @throws WebhookException
     */
    public function createWebhook(): ?object
    {
        $client  = new PPCClient(PPCHelper::getEnvironment($this->config));
        $hookURL = $this->getWebHookURL();

        if ($hookURL === null) {
            $errorString = 'createWebhook failed: webhook URL is empty';
            $this->logger->write(\LOGLEVEL_ERROR, $errorString);

            throw new WebhookException($errorString);
        }
        try {
            $webhookTypes = [];
            foreach (self::REGISTERED_EVENTS as $type) {
                $webhookTypes[] = (new EventType())->setType($type);
            }
            $response = new WebhookCreateResponse($client->send(
                new WebhookCreateRequest(Token::getInstance()->getToken(), $hookURL, $webhookTypes)
            ));
            $this->logger->write(\LOGLEVEL_DEBUG, 'createWebhook::WebhookCreateResponse:', $response);

            return $response->getWebhook();
        } catch (Exception | GuzzleException $e) {
            $errorString = 'createWebhook failed: ' . $e->getMessage();
            $this->logger->write(\LOGLEVEL_ERROR, $errorString);

            throw new WebhookException($errorString);
        }
    }

    /**
     * @param string $webhookId
     * @throws WebhookException
     */
    public function deleteWebhook(string $webhookId): void
    {
        $client = new PPCClient(PPCHelper::getEnvironment($this->config));
        try {
            $response = new WebhookDeleteResponse($client->send(
                new WebhookDeleteRequest(Token::getInstance()->getToken(), $webhookId)
            ));
            $this->config->removeWebhookId();
            $this->config->removeWebhookUrl();

            $this->logger->write(
                \LOGLEVEL_DEBUG,
                'deleteWebhook::WebhookDeleteResponse (204 expected) : ' . $response->getStatusCode(),
                $response
            );
        } catch (AuthorizationException | Exception | GuzzleException $e) {
            $errorString = 'deleteWebhook failed: ' . $e->getMessage();
            $this->logger->write(\LOGLEVEL_ERROR, $errorString);

            throw new WebhookException($errorString);
        }
    }

    /**
     * @return WebhookDetailsResponse
     * @throws WebhookException
     */
    public function loadWebhook(): WebhookDetailsResponse
    {
        $client    = new PPCClient(PPCHelper::getEnvironment($this->config));
        $webhookId = $this->config->getWebhookId();

        try {
            if ($webhookId === '') {
                $listResponse = new WebhookListResponse($client->send(
                    new WebhookListRequest(Token::getInstance()->getToken())
                ));
                $webhookUrl   = $this->getWebHookURL();
                $webhookId    = $listResponse->getId($webhookUrl) ?? '';
                $this->config->setWebhookId($webhookId);
                $this->config->setWebhookUrl($webhookUrl);
            }
            $detailResponse = new WebhookDetailsResponse($client->send(
                new WebhookDetailsRequest(Token::getInstance()->getToken(), $webhookId)
            ));
            $this->logger->write(\LOGLEVEL_DEBUG, 'loadWebhook::WebhookListResponse:', $detailResponse);

            $webhook = $detailResponse->getWebhook();
        } catch (Exception | GuzzleException $e) {
            $this->logger->write(\LOGLEVEL_ERROR, 'loadWebhook failed: ' . $e->getMessage());

            throw new WebhookException('loadWebhook failed: ' . $e->getMessage());
        }
        if ($webhook === null) {
            throw new WebhookException('loadWebhook failed: no data found for ' . $webhookId);
        }

        return $detailResponse;
    }
}
