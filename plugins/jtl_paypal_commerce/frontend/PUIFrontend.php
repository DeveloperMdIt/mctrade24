<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use Exception;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\InvalidPayerDataException;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\Transaction;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class PUIFrontend
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class PUIFrontend extends AbstractPaymentFrontend
{
    /**
     * @inheritDoc
     */
    public function renderProductDetailsPage(
        Customer $customer,
        Cart $cart,
        Address $shippingAddr,
        ?Artikel $product
    ): void {
        // no action at product details page
    }

    /**
     * @inheritDoc
     */
    public function renderCartPage(Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        // no action at cart page
    }

    public function renderMiniCartPage(Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        // no action at mini cart
    }

    /**
     * @inheritDoc
     */
    public function renderAddressPage(Customer $customer, Cart $cart): void
    {
        // no action at shipping address page
    }

    /**
     * @inheritDoc
     */
    public function renderShippingPage(Customer $customer, Cart $cart): void
    {
        if (!$this->paymentMethod->isValid($customer, $cart)) {
            return;
        }

        Transaction::instance()->clearAllTransactions();
        $puiMethod    = $this->paymentMethod->getMethod();
        $localization = $this->plugin->getLocalization();
        try {
            \pq('#' . $puiMethod->getModuleID())
                ->append($this->smarty
                    ->assign('puiPaymentId', $puiMethod->getMethodID())
                    ->assign(
                        'legalInformation',
                        $localization->getTranslation('jtl_paypal_pui_legalinformation'),
                    )
                    ->assign(
                        'legalInformationHeader',
                        $localization->getTranslation('jtl_paypal_pui_legalinformation_header')
                    )
                    ->fetch($puiMethod->getAdditionalTemplate()));
        } catch (Exception) {
            $logger = Shop::Container()->getLogService();
            $logger->error('phpquery rendering failed: shippingPUI()');

            return;
        }
    }

    /**
     * @return void
     */
    private function renderPUIConfirmation(): void
    {
        $config      = PPCHelper::getConfiguration($this->plugin);
        $merchantID  = $config->getConfigValues()->getMerchantID();
        $environment = PPCHelper::getEnvironment($config);

        try {
            \pq('body')
                ->append($this->smarty
                    ->assign('fraudnetGUID', $environment->getMetaDataId())
                    ->assign('fraudnetPageID', $merchantID . '_checkout-page')
                    ->assign(
                        'ppcStateURL',
                        $this->paymentMethod->getPaymentStateURL($this->paymentMethod->getPPOrder()) ?? ''
                    )
                    ->assign('isSandbox', $environment->isSandbox())
                    ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/paypalFraudnet.tpl'));
        } catch (Exception) {
            $logger = Shop::Container()->getLogService();
            $logger->error('phpquery rendering failed: renderPUIConfirmation()');

            return;
        }
    }

    /**
     * @inheritDoc
     */
    public function renderConfirmationPage(int $paymentId, Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        try {
            $this->paymentMethod->validatePayerData($customer, Frontend::getDeliveryAddress(), $cart);
            $this->renderPUIConfirmation();
        } catch (InvalidPayerDataException $e) {
            $alerts = $this->getAlert();
            if (!$e->hasAlerts()) {
                $alerts->addError($e->getMessage(), 'confirmPUI');
            } else {
                foreach ($e->getAlerts() as $alert) {
                    $alerts->removeAlertByKey($alert->getKey());
                    $alerts->getAlertlist()->push($alert);
                }
            }

            if ($e->hasRedirectURL()) {
                Helper::redirectAndExit($e->getRedirectURL());
                exit();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function renderOrderDetailPage(Bestellung $shopOrder, PayPalPaymentInterface $method): void
    {
        parent::renderOrderDetailPage($shopOrder, $method);

        if (!empty($shopOrder->cPUIZahlungsdaten)) {
            $this->getAlert()->addInfo(
                \nl2br($shopOrder->cPUIZahlungsdaten),
                'paymentInformation'
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function renderFinishPage(Order $ppOrder, bool $payAgainProcess = false): void
    {
        // no action at finish page
    }
}
