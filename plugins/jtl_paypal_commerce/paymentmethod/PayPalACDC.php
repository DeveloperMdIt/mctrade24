<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use JTL\Backend\NotificationEntry;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\frontend\ACDCFrontend;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\frontend\PaymentFrontendInterface;
use Plugin\jtl_paypal_commerce\PPC\APM;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\AuthResult;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\CardDetails;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class PayPalACDC
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class PayPalACDC extends PayPalCommerce
{
    /**
     * @inheritDoc
     */
    public function mappedLocalizedPaymentName(?string $isoCode = null): string
    {
        return FrontendHandler::getBackendTranslation('Erweiterte Kartenzahlung');
    }

    /**
     * @inheritDoc
     */
    public function getSettingPanel(): string
    {
        return Settings::BACKEND_SETTINGS_PANEL_ACDC;
    }

    /**
     * @inheritDoc
     */
    public function isValidExpressPayment(object $customer, Cart $cart): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isValidExpressProduct(object $customer, ?Artikel $product): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isValidBannerPayment(object $customer, Cart $cart): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function isValidBannerProduct(object $customer, ?Artikel $product): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getFrontendInterface(Configuration $config, JTLSmarty $smarty): PaymentFrontendInterface
    {
        return new ACDCFrontend($this->plugin, $this, $smarty);
    }

    /**
     * @inheritDoc
     */
    public function getBackendNotification(PluginInterface $plugin, bool $force = false): ?NotificationEntry
    {
        $entry = PayPalPayment::getBackendNotification($plugin, $force);
        if ($entry !== null) {
            return $entry;
        }

        if (!($force || $this->isAssigned())) {
            return null;
        }

        $acdcAvail = (int)$this->config->getPrefixedConfigItem('PaymentACDCAvail', '0');
        if ($acdcAvail === 0) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_DANGER,
                \__($this->method->getName()),
                \__('Kreditkartenzahlung wird von Ihrem PayPal-Account nicht unterstützt.'),
                Shop::getAdminURL() . '/plugin/' . $this->plugin->getID()
            );
            $entry->setPluginId($plugin->getPluginID());

            return $entry;
        }

        $cardAvail = \in_array(APM::CREDIT_CARD, (new APM($this->config))->getEnabled(false));
        if ($cardAvail) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_WARNING,
                \__($this->method->getName()),
                \__('Bitte deaktivieren Sie die Standard-Kreditkartenzahlung.'),
                Shop::getAdminURL() . '/plugin/' . $this->plugin->getID(),
                $acdcAvail ? 'on' : 'off'
            );
            $entry->setPluginId($plugin->getPluginID());

            return $entry;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function renderBackendInformation(JTLSmarty $smarty, PluginInterface $plugin): void
    {
        PayPalPayment::renderBackendInformation($smarty, $plugin);

        if (!$this->isAssigned() || !$this->config->getConfigValues()->isAuthConfigured()) {
            return;
        }

        $acdcAvail = (int)$this->config->getPrefixedConfigItem('PaymentACDCAvail', '0');
        if ($acdcAvail === 0) {
            $this->helper->getAlert()->addDanger(
                \sprintf(
                    \__('Die Zahlungsart ACDC wird von Ihrem PayPal-Account nicht unterstützt'),
                    '<strong>' . \__($this->method->getName()) . '</strong>'
                ),
                'acdcNotSupported',
                [
                    'showInAlertListTemplate' => false,
                ]
            );

            return;
        }

        $cardAvail = \in_array(APM::CREDIT_CARD, (new APM($this->config))->getEnabled(false));
        if ($cardAvail) {
            $this->helper->getAlert()->addWarning(
                \sprintf(
                    \__('Bitte deaktivieren Sie die Standard-Kreditkartenzahlung, '
                      . 'um die erweiterte Kreditkartenzahlung nutzen zu können.'),
                    '<strong>' . \__($this->method->getName()) . '</strong><br>'
                ),
                'acdcAndCardEnabled',
                [
                    'showInAlertListTemplate' => false,
                ]
            );
        }

        $acdcLimit = (int)$this->config->getPrefixedConfigItem('PaymentACDCLimit', '0');
        if ($acdcLimit === 1) {
            $this->getLogger()->write(\LOGLEVEL_NOTICE, \sprintf(
                \__('Die Zahlungsart "%s" steht momentan nur eingeschränkt zur Verfügung'),
                \__($this->method->getName())
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function isValidIntern(array $args_arr = []): bool
    {
        if (!PayPalPayment::isValidIntern($args_arr)) {
            return false;
        }

        try {
            return (int)$this->config->getPrefixedConfigItem('PaymentACDCAvail', '0') > 0
                && $this->method->getDuringOrder()
                && Token::getInstance()->getToken() !== null;
        } catch (AuthorizationException $e) {
            $this->getLogger()->write(\LOGLEVEL_ERROR, 'AuthorizationException:' . $e->getMessage());

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function setFundingSource(string $fundingSource): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getFundingSource(): string
    {
        return $this->getDefaultFundingSource();
    }

    /**
     * @inheritDoc
     */
    public function getDefaultFundingSource(): string
    {
        return PaymentSourceBuilder::FUNDING_CARD;
    }

    /**
     * @inheritDoc
     */
    protected function validateFundingSource(string $fundingSource): string
    {
        return $fundingSource === $this->getFundingSource() ? $fundingSource : '';
    }

    /**
     * @inheritDoc
     */
    public function validateMerchantIntegration(bool $onlyCheck = false): bool
    {
        $mi = $this->getMerchantIntegration($this->config);
        if ($mi === null) {
            return false;
        }

        $acdcAvail      = false;
        $limited        = false;
        $paymentProduct = $mi->getProductByName('PPCP_CUSTOM');
        if ($paymentProduct !== null && \in_array('CUSTOM_CARD_PROCESSING', $paymentProduct->getCapabilities(), true)) {
            $acdc      = $mi->getCapabilityByName('CUSTOM_CARD_PROCESSING');
            $acdcAvail = $acdc !== null && $acdc->isActive();
            $limited   = $acdcAvail && $acdc->hasLimits();
        }
        if (!$onlyCheck) {
            $this->config->saveConfigItems([
                'PaymentACDCAvail' => $acdcAvail ? '1' : '0',
                'PaymentACDCLimit' => $limited ? '1' : '0',
            ]);
        }

        return $acdcAvail;
    }

    /**
     * @inheritDoc
     */
    protected function isValidOrderState(Order $order, string $state): bool
    {
        $orderState = $order->getStatus();
        if ($state === OrderStatus::STATUS_APPROVED) {
            return \in_array($orderState, [
                OrderStatus::STATUS_CREATED,
                OrderStatus::STATUS_APPROVED
            ], true);
        }

        return parent::isValidOrderState($order, $state);
    }

    /**
     * @inheritDoc
     */
    public function preparePaymentProcess(Bestellung $order): void
    {
        $ppOrder = $this->getPPOrder();
        if ($ppOrder === null || empty($ppOrder->getId())) {
            return;
        }

        if (
            $this->config->getPrefixedConfigItem(
                Settings::BACKEND_SETTINGS_SECTION_ACDCDISPLAY . '_activate3DSecure',
                'Y'
            )
        ) {
            $paymentSource = $ppOrder->getPaymentSource(PaymentSourceBuilder::FUNDING_CARD)
                ?? (new PaymentSourceBuilder(PaymentSourceBuilder::FUNDING_CARD))->build();
            $card          = $paymentSource->getCard() ?? new CardDetails();
            $authResult    = $card->getAuthResult();

            if (
                $authResult !== null
                && $this->get3DSAuthResult($authResult->getAuthAction()) !== AuthResult::AUTHACTION_CONTINUE
            ) {
                $this->unsetCache();
                $this->helper->getAlert()->addError(
                    $this->plugin->getLocalization()->getTranslation('acdc_3dserror_occured'),
                    'preparePaymentProcess'
                );

                Helper::redirectAndExit($this->getPaymentCancelURL($ppOrder));
                exit();
            }
        }

        parent::preparePaymentProcess($order);
    }
}
