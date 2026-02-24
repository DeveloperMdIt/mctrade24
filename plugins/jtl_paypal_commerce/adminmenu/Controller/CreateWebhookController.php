<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use Plugin\jtl_paypal_commerce\PPC\Webhook\Webhook;
use Plugin\jtl_paypal_commerce\PPC\Webhook\WebhookException;

/**
 * Class CreateWebhookController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class CreateWebhookController extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $config = $this->getConfig();
        $alert  = $this->getAlertService();

        try {
            $webhook = (new Webhook($this->getPlugin(), $config))->createWebhook();
            if (empty($webhook)) {
                throw new WebhookException('Webhook::create returned no data!');
            }
            $config->setWebhookId($webhook->id);
            $config->setWebhookUrl($webhook->url);
            $alert->addSuccess(
                \__('Webhook efolgreich erstellt.'),
                'runCreateWebhook'
            );
        } catch (WebhookException) {
            $alert->addError(
                \__('Webhook konnte nicht angelegt werden. (Siehe Systemlog für Details)'),
                'runCreateWebhook'
            );
        }

        $this->redirectSelf();
    }
}
