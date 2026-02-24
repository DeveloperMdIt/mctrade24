<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Plugin\jtl_paypal_commerce\PPC\Webhook\Webhook;
use Plugin\jtl_paypal_commerce\PPC\Webhook\WebhookException;

/**
 * Class RefreshWebhookController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class RefreshWebhookController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $config = $this->getConfig();
        $alert  = $this->getAlertService();
        $hook   = new Webhook($this->getPlugin(), $config);

        try {
            $webhookId = $hook->loadWebhook()->getId();
            if ($webhookId !== null) {
                $hook->deleteWebhook($webhookId);
            }

            $webhook = $hook->createWebhook();
            if (empty($webhook)) {
                throw new WebhookException('Webhook::create returned no data!');
            }
            $config->setWebhookId($webhook->id);
            $config->setWebhookUrl($webhook->url);

            $alert->addSuccess(
                \__('Webhook erfolgreich aktualisiert.'),
                'runRefreshWebhook'
            );
        } catch (WebhookException | UnexpectedResponseException) {
            $alert->addError(
                \__('Webhook konnte nicht aktualisiert werden. (Siehe Systemlog für Details)'),
                'runRefreshWebhook'
            );
        }

        $this->redirectSelf();
    }
}
