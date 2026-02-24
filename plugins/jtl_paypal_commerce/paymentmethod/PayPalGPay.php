<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use JTL\Backend\NotificationEntry;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\frontend\GPayFrontend;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\frontend\PaymentFrontendInterface;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\AuthResult;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class PayPalGooglePay
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class PayPalGPay extends PayPalCommerce
{
    /**
     * @inheritDoc
     */
    public function mappedLocalizedPaymentName(?string $isoCode = null): string
    {
        return FrontendHandler::getBackendTranslation('Google Pay');
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
        return new GPayFrontend($this->plugin, $this, $smarty);
    }

    /**
     * @inheritDoc
     */
    public function getBackendNotification(PluginInterface $plugin, bool $force = false): ?NotificationEntry
    {
        return PayPalPayment::getBackendNotification($plugin, $force);
    }

    /**
     * @inheritDoc
     */
    public function renderBackendInformation(JTLSmarty $smarty, PluginInterface $plugin): void
    {
        PayPalPayment::renderBackendInformation($smarty, $plugin);
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
        return PaymentSourceBuilder::FUNDING_GOOGLEPAY;
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
        return PayPalPayment::validateMerchantIntegration($onlyCheck);
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
        $sca = $this->config->getPrefixedConfigItem(
            Settings::BACKEND_SETTINGS_SECTION_ACDCDISPLAY . '_activate3DSecure',
            'Y'
        ) !== 'Y' ? 'N' : $this->config->getPrefixedConfigItem(
            Settings::BACKEND_SETTINGS_SECTION_ACDCDISPLAY . '_mode3DSecure',
            'SCA_WHEN_REQUIRED'
        );
        if ($sca !== 'N') {
            $paymentSource->addAttribute('verification', new JSON((object)['method' => $sca]));
        }

        return (new Order())
            ->addPurchase($purchase)
            ->setPaymentSource($this->getFundingSource(), $paymentSource)
            ->setIntent(Order::INTENT_CAPTURE);
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
            $paymentSource = $ppOrder->getPaymentSource(PaymentSourceBuilder::FUNDING_GOOGLEPAY)
                ?? (new PaymentSourceBuilder(PaymentSourceBuilder::FUNDING_GOOGLEPAY))->build();
            $authResult    = $paymentSource->getCard()->getAuthResult();

            if (
                $authResult !== null
                && $this->get3DSAuthResult($authResult->getAuthAction()) !== AuthResult::AUTHACTION_CONTINUE
            ) {
                $this->unsetCache();
                Shop::Container()->getAlertService()->addError(
                    $this->plugin->getLocalization()->getTranslation('acdc_3dserror_occured'),
                    'preparePaymentProcess',
                    ['saveInSession' => true]
                );

                Helper::redirectAndExit($this->getPaymentCancelURL($ppOrder));
                exit();
            }
        }

        parent::preparePaymentProcess($order);
    }
}
