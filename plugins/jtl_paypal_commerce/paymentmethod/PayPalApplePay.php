<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use JTL\Alert\Alert;
use JTL\Backend\NotificationEntry;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Customer\Customer;
use JTL\Helpers\Text;
use JTL\Plugin\Data\PaymentMethod;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\frontend\ApplePayDAFController;
use Plugin\jtl_paypal_commerce\frontend\ApplePayFrontend;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\frontend\PaymentFrontendInterface;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class PayPalApplePay
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class PayPalApplePay extends PayPalCommerce
{
    /**
     * @inheritDoc
     */
    public function mappedLocalizedPaymentName(?string $isoCode = null): string
    {
        return FrontendHandler::getBackendTranslation('Apple Pay');
    }

    /**
     * @inheritDoc
     */
    public function getSettingPanel(): string
    {
        return Settings::BACKEND_SETTINGS_PANEL_APPLEPAY;
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
            return $this->config->getPrefixedConfigItem('ApplePayDisplay_activated', 'N') === 'Y'
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
    public function getBackendNotification(PluginInterface $plugin, bool $force = false): ?NotificationEntry
    {
        $entry = PayPalPayment::getBackendNotification($plugin, $force);
        if ($entry !== null) {
            return $entry;
        }

        if (!($force || $this->isAssigned())) {
            return null;
        }

        $applePayRegisterd = $this->config->getPrefixedConfigItem('ApplePayDisplay_activated', 'N');
        if ($applePayRegisterd !== 'Y') {
            $tabId   = $this->config->getAdminmenuSettingsId();
            $panelId = $this->config->getAdminmenuPanelId(
                Settings::BACKEND_SETTINGS_PANEL_APPLEPAY,
                [Settings::BACKEND_SETTINGS_PANEL_CREDENTIALS]
            );
            $entry   = new NotificationEntry(
                NotificationEntry::TYPE_DANGER,
                \__($this->method->getName()),
                \__('Sie müssen Ihren Shop für Apple Pay registrieren', \__($this->method->getName())),
                Shop::getAdminURL() . '/plugin/' . $this->plugin->getID()
                    . ($tabId !== 0 ? '?kPluginAdminMenu=' . $tabId . '&panelActive=' . $panelId : '')
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

        $applePayRegisterd = $this->config->getPrefixedConfigItem('ApplePayDisplay_activated', 'N');

        if ($applePayRegisterd !== 'Y') {
            $tabId   = $this->config->getAdminmenuSettingsId();
            $panelId = $this->config->getAdminmenuPanelId(
                Settings::BACKEND_SETTINGS_PANEL_APPLEPAY,
                [Settings::BACKEND_SETTINGS_PANEL_CREDENTIALS]
            );
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_DANGER,
                \__(
                    'Sie müssen Ihren Shop für Apple Pay registrieren',
                    '<strong>' . \__($this->method->getName()) . '</strong>',
                    \__($this->method->getName())
                ),
                'applepayNotSupported',
                [
                    'showInAlertListTemplate' => false,
                    'linkHref' => Shop::getAdminURL() . '/plugin/' . $this->plugin->getID()
                        . ($tabId !== 0 ? '?kPluginAdminMenu=' . $tabId . '&panelActive=' . $panelId : ''),
                    'linkText' => __('ApplePayDisplay'),
                ]
            );
        } elseif (!$this->validateMerchantIntegration(true)) {
            Shop::Container()->getAlertService()->addWarning(__(
                'Domainn association file existiert nicht',
                ApplePayDAFController::getDAFDownloadRoute(),
                ApplePayDAFController::getDAFRoute()
            ), 'merchantAssociationNotExists', ['showInAlertListTemplate' => false]);
        }
    }

    /**
     * @inheritDoc
     */
    public function validatePaymentConfiguration(PaymentMethod $method, ?array &$settings = null): bool
    {
        if (!PayPalPayment::validatePaymentConfiguration($method, $settings)) {
            return false;
        }

        if ($settings === null) {
            return true;
        }

        if (($settings['ApplePayDisplay_activated'] ?? 'N') === 'Y' && !$this->validateMerchantIntegration(true)) {
            $settings['ApplePayDisplay_activated'] = 'N';
            Shop::Container()->getAlertService()->addWarning(__(
                'Domainn association file existiert nicht',
                ApplePayDAFController::getDAFDownloadRoute(),
                ApplePayDAFController::getDAFRoute()
            ), 'merchantAssociationNotExists', ['saveInSession' => true]);
        }
        if ($settings['ApplePayDisplay_activated'] === 'N') {
            $settings['ApplePayDisplay_version'] = '';
        }
        $displayName = $settings['ApplePayDisplay_displayname'] ?? '';
        if (
            ($settings['ApplePayDisplay_activated'] ?? 'N') === 'Y'
            && ($displayName === ''
                || $displayName !== Text::replaceUmlauts($displayName)
                || \mb_strlen($displayName) > 64)
        ) {
            $settings['ApplePayDisplay_displayname'] = \mb_substr(Text::replaceUmlauts($displayName), 0, 64);
            Shop::Container()->getAlertService()->addWarning(__(
                'Apple Pay benötigt für die Händlerverifizierung die Angabe eines Anzeigenamens'
            ), 'displayNameNotClear', ['saveInSession' => true]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function validateMerchantIntegration(bool $onlyCheck = false): bool
    {
        $associationFileExists = ApplePayDAFController::testRoute(
            $this->config->getConfigValues()->getWorkingMode(),
            $this->config->getPrefixedConfigItem('ApplePayDisplay_version', ApplePayDAFController::DAF_VERSION)
        );
        if (!$associationFileExists && !$onlyCheck) {
            $this->config->saveConfigItems([
                'ApplePayDisplay_activated' => 'N',
            ]);
        }

        return $associationFileExists;
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
        return new ApplePayFrontend($this->plugin, $this, $smarty);
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
        return PaymentSourceBuilder::FUNDING_APPLEPAY;
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
    protected function constructOrder(
        Customer $customer,
        Cart $cart,
        string $shippingContext,
        string $payAction,
        string $orderHash
    ): Order {
        $purchase      = $this->createPurchase($orderHash, Frontend::getDeliveryAddress(), $cart);
        $paymentSource = (new PaymentSourceBuilder($this->getFundingSource()))->build();
        $paymentSource->applyPayer($this->createPayer($customer));

        return (new Order())
            ->addPurchase($purchase)
            ->setPaymentSource($this->getFundingSource(), $paymentSource)
            ->setIntent(Order::INTENT_CAPTURE);
    }
}
