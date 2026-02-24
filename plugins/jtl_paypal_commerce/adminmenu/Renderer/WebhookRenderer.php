<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Renderer;

use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\adminmenu\TabNotAvailException;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Plugin\jtl_paypal_commerce\PPC\Webhook\Webhook;
use Plugin\jtl_paypal_commerce\PPC\Webhook\WebhookException;

/**
 * Class WebhookRenderer
 * @package Plugin\jtl_paypal_commerce\adminmenu\Renderer
 */
class WebhookRenderer extends AbstractRenderer
{
    /**
     * @inheritDoc
     */
    public function render(JTLSmarty $smarty): void
    {
        $this->checkRendering();

        $config    = $this->getConfig();
        $logger    = $this->getLogger();
        $plugin    = $this->getPlugin();
        $paymethod = Helper::getInstance($plugin)->getPaymentFromName('PayPalCommerce');
        if ($paymethod === null) {
            throw new TabNotAvailException(\sprintf(
                \__('Die Zahlungsmethode %s ist nicht verfügbar.'),
                'PayPalCommerce'
            ));
        }
        $isWebhookRegistred = false;
        $webhook            = new Webhook($plugin, $config);
        try {
            $webhookRemote = $webhook->loadWebhook();
            $webhookURL    = $webhookRemote->getUrl();
            $webhookEvents = $webhookRemote->getEventTypes();
            $webhookShopId = $config->getWebhookId();
            if (!empty($webhookURL)) {
                $isWebhookRegistred = ($webhookURL === $webhook->getWebHookURL());
            }
        } catch (WebhookException | UnexpectedResponseException $e) {
            $webhookShopId = null;
            $logger->write(\LOGLEVEL_ERROR, $e->getMessage());
        }

        $smarty
            ->assign('isWebhookConfigured', !empty($webhookShopId))
            ->assign('isWebhookRegistred', $isWebhookRegistred)
            ->assign('PaymentMethod', $paymethod->getMethod()->getName())
            ->assign('webhookID', $webhookShopId)
            ->assign('webhookURL', $webhook->getWebHookURL())
            ->assign('webhookEvents', $webhookEvents ?? []);
    }
}
