<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Plugin\jtl_paypal_commerce\PPC\Webhook\Webhook;
use Plugin\jtl_paypal_commerce\PPC\Webhook\WebhookException;

/**
 * Class DeleteWebhookController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class DeleteWebhookController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $logger = $this->getLogger();
        $alert  = $this->getAlertService();

        $webhook = new Webhook($this->getPlugin(), $this->getConfig());
        try {
            $webhookId = $webhook->loadWebhook()->getId();
            if (!empty($webhookId)) {
                $webhook->deleteWebhook($webhookId);
                $logger->write(\LOGLEVEL_DEBUG, 'Webhook removed: ' . $webhookId);
            }
            $alert->addSuccess(
                \__('Webhook erfolgreich gelöscht.'),
                'runDeleteWebhook'
            );
        } catch (WebhookException | UnexpectedResponseException $e) {
            $alert->addError(
                \__('Webhook konnte nicht gelöscht werden. (Siehe Systemlog für Details)'),
                'runDeleteWebhook'
            );
            $logger->write(\LOGLEVEL_NOTICE, 'Errors during webhook remove: ' . $e);
        }

        $this->redirectSelf();
    }
}
