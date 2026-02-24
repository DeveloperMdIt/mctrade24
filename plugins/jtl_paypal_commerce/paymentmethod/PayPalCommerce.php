<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod;

use JTL\Alert\Alert;
use JTL\Backend\NotificationEntry;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Plugin\Data\PaymentMethod;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\frontend\Handler\FrontendHandler;
use Plugin\jtl_paypal_commerce\frontend\PaymentFrontendInterface;
use Plugin\jtl_paypal_commerce\frontend\PPCPFrontend;
use Plugin\jtl_paypal_commerce\LegacyHelper;
use Plugin\jtl_paypal_commerce\paymentmethod\PPCP\OrderNotFoundException;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCaptureDecline;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCCapturePending;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCInvalidOrderState;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCPayerActionRequired;
use Plugin\jtl_paypal_commerce\paymentmethod\TestCase\TCServerError;
use Plugin\jtl_paypal_commerce\PPC\APM;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Order\Purchase\PurchaseUnit;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class PayPalCommerce
 * @package Plugin\jtl_paypal_commerce\paymentmethod
 */
class PayPalCommerce extends PayPalPayment
{
    /**
     * @inheritDoc
     */
    protected function initPayAgain(): void
    {
        parent::initPayAgain();

        $payAgainOrder = Request::getInt('kBestellung') === 0
            ? $this->sessionCache->getInt('payAgain.shopOrderId')
            : Request::getInt('kBestellung');
        if ($payAgainOrder === 0) {
            return;
        }

        $shopOrder = new Bestellung($payAgainOrder);
        $ppOrder   = $this->helper->getPPOrder($this, $shopOrder);
        $ppSource  = $this->helper->getShopOrderAttribute($shopOrder, 'PAYPAL_FUNDING_SOURCE');
        if ($ppOrder !== null) {
            $this->payAgainProcess = true;
            $this->sessionCache->setOrderId($ppOrder->getId());
            $this->sessionCache->setFundingSource($ppSource ?? ($ppOrder->getPaymentSources()[0] ?? ''));
            $this->sessionCache->setOrderHash($ppOrder->getCustomId());
        }
    }

    /**
     * @inheritDoc
     */
    public function paymentDuringOrderSupported(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function paymentAfterOrderSupported(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function mappedLocalizedPaymentName(?string $isoCode = null): string
    {
        return FrontendHandler::getBackendTranslation('Standardzahlarten');
    }

    /**
     * @inheritDoc
     */
    protected function usePurchaseItems(): bool
    {
        return true;
    }

    protected function constructPurchase(): PurchaseUnit
    {
        return new PurchaseUnit();
    }

    /**
     * @inheritDoc
     */
    protected function isSessionPayed(object $paymentSession): bool
    {
        return (int)$paymentSession->nBezahlt > 0;
    }

    /**
     * @inheritDoc
     */
    public function isAutoCapture(?string $fundingSource = null): bool
    {
        $fundingSource ??= $this->getFundingSource();

        return \in_array($fundingSource, APM::APM_AC);
    }

    /**
     * @inheritDoc
     */
    public function canOrderPayedAgain(Bestellung $order): bool
    {
        $ppOrder  = $this->helper->getPPOrder($this, $order);
        $ppSource = $this->helper->getShopOrderAttribute($order, 'PAYPAL_FUNDING_SOURCE');
        if ($ppOrder === null || !$this->isAutoCapture($ppSource ?? ($ppOrder->getPaymentSources()[0] ?? ''))) {
            return false;
        }

        $paymentState = $this->getValidOrderState($ppOrder);

        return $paymentState === OrderStatus::STATUS_CREATED
            || $paymentState === OrderStatus::STATUS_PAYER_ACTION_REQUIRED;
    }

    /**
     * @param Order               $ppOrder
     * @param PPCRequestException $exception
     * @return void
     */
    protected function handleOvercharge(Order $ppOrder, PPCRequestException $exception): void
    {
        $detail = $exception->getDetail();
        if ($detail === null || $detail->getIssue() !== OrderStatus::STATUS_PAYER_ACTION_REQUIRED) {
            return;
        }

        $this->resetPPOrder($ppOrder->getId());
        $localization = $this->plugin->getLocalization();
        $this->helper->getAlert()->addError(
            $localization->getTranslation('jtl_paypal_commerce_payer_action_required_psd2overcharge'),
            'handlePayerActionRequired',
            ['linkText' => $localization->getTranslation('jtl_paypal_commerce_psd2overcharge')]
        );

        Helper::redirectAndExit(
            Shop::Container()->getLinkService()->getStaticRoute('bestellvorgang.php')
        );
        exit();
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
            return $this->method->getDuringOrder() && Token::getInstance()->getToken() !== null;
        } catch (AuthorizationException $e) {
            $this->logger->write(\LOGLEVEL_ERROR, 'AuthorizationException:' . $e->getMessage());

            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function isValid(object $customer, Cart $cart): bool
    {
        if (Request::postInt('resetPayment') === 1) {
            $this->resetPPOrder();
            unset($_POST['resetPayment']);

            return false;
        }

        return parent::isValid($customer, $cart);
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

        if (
            ($settings['vaultingDisplay_activateVaulting'] ?? 'N') === 'Y'
            && !$this->validateMerchantIntegration(true)
        ) {
            $settings['vaultingDisplay_activateVaulting'] = 'N';
            Shop::Container()->getAlertService()->addWarning(__(
                'Vaulting ist für Ihren PayPal-Account nicht aktiviert. Führen Sie das Onboarding erneut durch!'
            ), 'vaultingNotAvailable', ['saveInSession' => true]);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function validateMerchantIntegration(bool $onlyCheck = false): bool
    {
        if (!$onlyCheck && $this->config->getPrefixedConfigItem('vaultingDisplay_activateVaulting', 'N') !== 'Y') {
            return parent::validateMerchantIntegration($onlyCheck);
        }

        $mi = $this->getMerchantIntegration($this->config);
        if ($mi === null) {
            return false;
        }

        $vaultingAvail  = false;
        $paymentProduct = $mi->getProductByName('PPCP_CUSTOM');
        if (
            $paymentProduct !== null
            && \in_array('PAYPAL_WALLET_VAULTING_ADVANCED', $paymentProduct->getCapabilities(), true)
        ) {
            $vaulting      = $mi->getCapabilityByName('PAYPAL_WALLET_VAULTING_ADVANCED');
            $vaultingAvail = $vaulting !== null && $vaulting->isActive();
        }
        if (!$onlyCheck) {
            $this->config->saveConfigItems([
                'PaymentVaultingAvail' => $vaultingAvail ? '1' : '0',
            ]);
        }

        return $vaultingAvail;
    }

    /**
     * @inheritDoc
     */
    public function isValidExpressPayment(object $customer, Cart $cart): bool
    {
        if (!$this->isValid($customer, $cart)) {
            return false;
        }

        foreach ($cart->PositionenArr as $cartItem) {
            if ((int)($cartItem->Artikel->FunktionsAttribute['no_paypalexpress'] ?? 0) === 1) {
                return false;
            }
        }

        return $cart->gibGesamtsummeWaren() > 0.0 && $this->isAssigned(
            LegacyHelper::getShippingClasses($cart),
            (int)($customer->kKundengruppe ?? 0) > 0
                ? (int)$customer->kKundengruppe
                : CustomerGroup::getDefaultGroupID()
        );
    }

    private function copyCart(Cart $cart, Artikel $product): ?Cart
    {
        if (
            $product->bHasKonfig
            || $product->inWarenkorbLegbar <= 0
            || !$product->getCustomerGroup()->mayViewPrices()
        ) {
            return null;
        }

        /** @var Cart $copyCart */
        $copyCart   = GeneralObject::deepCopy($cart);
        $dispatcher = $this->helper->revokeHookDispatcher();
        $copyCart->fuegeEin($product->getID(), \max($product->fMindestbestellmenge, 1), $product->Attribute);
        $this->helper->restoreHookDispatcher($dispatcher);

        return $copyCart;
    }

    /**
     * @inheritDoc
     */
    public function isValidExpressProduct(object $customer, ?Artikel $product): bool
    {
        if (
            $product === null
            || !$this->isValidIntern()
            || ($product->gibPreis(\max($product->fMindestbestellmenge, 1), []) ?? 0.0) === 0.0
        ) {
            return false;
        }

        $cart = $this->copyCart(Frontend::getCart(), $product);

        return ($cart !== null) && $this->isValidExpressPayment($customer, $cart);
    }

    /**
     * @inheritDoc
     */
    public function isValidBannerPayment(object $customer, Cart $cart): bool
    {
        return $this->isValid($customer, $cart) && $this->isAssigned(
            LegacyHelper::getShippingClasses($cart),
            (int)($customer->kKundengruppe ?? 0) > 0
                ? (int)$customer->kKundengruppe
                : CustomerGroup::getDefaultGroupID()
        );
    }

    /**
     * @inheritDoc
     */
    public function isValidBannerProduct(object $customer, ?Artikel $product): bool
    {
        if ($product === null || !$this->isValidIntern()) {
            return false;
        }

        $cart = $this->copyCart(Frontend::getCart(), $product);

        return ($cart !== null) && $this->isValidBannerPayment($customer, $cart);
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
        $ppOrder = parent::constructOrder($customer, $cart, $shippingContext, $payAction, $orderHash);
        if ($this->isAutoCapture()) {
            $ppOrder->setProcessingInstruction(Order::PI_AUTO_COMPLETE);
        }

        return $ppOrder;
    }

    /**
     * @inheritDoc
     */
    public function getFrontendInterface(Configuration $config, JTLSmarty $smarty): PaymentFrontendInterface
    {
        return new PPCPFrontend($this->plugin, $this, $smarty);
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

        if (
            !($force || $this->isAssigned())
            || $this->config->getPrefixedConfigItem('vaultingDisplay_activateVaulting', 'N') !== 'Y'
        ) {
            return null;
        }

        if ((int)$this->config->getPrefixedConfigItem('PaymentVaultingAvail', '0') === 0) {
            $entry = new NotificationEntry(
                NotificationEntry::TYPE_WARNING,
                \__($this->method->getName()),
                \__('Vaulting ist für Ihren PayPal-Account nicht aktiviert!'),
                Shop::getAdminURL() . '/plugin/' . $this->plugin->getID()
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

        if (
            !$this->isAssigned()
            || $this->config->getPrefixedConfigItem('vaultingDisplay_activateVaulting', 'N') !== 'Y'
            || !$this->config->getConfigValues()->isAuthConfigured()
        ) {
            return;
        }

        if (!$this->validateMerchantIntegration(true)) {
            $this->helper->getAlert()->addWarning(
                \__('Vaulting ist für Ihren PayPal-Account nicht aktiviert. Führen Sie das Onboarding erneut durch!'),
                'vaultingNotSupported',
                [
                    'showInAlertListTemplate' => false,
                ]
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getFundingSource(): string
    {
        return $this->sessionCache->getFundingSource() ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getDefaultFundingSource(): string
    {
        return PaymentSourceBuilder::FUNDING_PAYPAL;
    }

    /**
     * @inheritDoc
     */
    public function preparePaymentProcess(Bestellung $order): void
    {
        parent::preparePaymentProcess($order);

        $ppOrder = $this->getPPOrder();
        if ($ppOrder === null || empty($ppOrder->getId())) {
            $this->getLogger()->write(
                \LOGLEVEL_NOTICE,
                'preparePaymentProcess: payment can not be processed, order does not exists'
            );
            $this->raisePaymentError('jtl_paypal_commerce_payment_error', $this->getPaymentRetryURL($ppOrder));
            exit();
        }

        try {
            $ppOrder = (new TCInvalidOrderState())->execute($this, $this->verifyPPOrder($ppOrder->getId()));
        } catch (PPCRequestException $e) {
            $this->showErrorResponse($e->getResponse(), new Alert(
                Alert::TYPE_ERROR,
                $this->plugin->getLocalization()->getTranslation('jtl_paypal_commerce_payment_error'),
                'preparePaymentProcess'
            ));
            Helper::redirectAndExit($this->getPaymentRetryURL($ppOrder));
        } catch (OrderNotFoundException) {
            $this->raisePaymentError('jtl_paypal_commerce_payment_error', $this->getPaymentRetryURL($ppOrder));
        }
        $this->getLogger()->write(\LOGLEVEL_DEBUG, 'preparePaymentProcess: verifyOrder', $ppOrder);

        if (!$this->isAutoCapture() && !$this->isValidOrderState($ppOrder, OrderStatus::STATUS_APPROVED)) {
            $this->getLogger()->write(\LOGLEVEL_NOTICE, 'preparePaymentProcess: UnexpectedOrderState get '
                . $ppOrder->getStatus() . ' expected ' . OrderStatus::STATUS_APPROVED);
            $this->handleOrder($ppOrder, $order);

            return;
        }

        $orderNumber = $ppOrder->getPurchase()->getInvoiceId() ?? LegacyHelper::baueBestellnummer();
        $ppOrder->getPurchase()->setInvoiceId($orderNumber);
        if (
            $this->config->getPrefixedConfigItem(
                Settings::BACKEND_SETTINGS_SECTION_GENERAL . '_purchaseDescription'
            ) === 'N'
        ) {
            $ppOrder->getPurchase()->setDescription(
                $this->helper->getSimpleDescription($orderNumber, $this->getShopTitle())
            );
        }
        try {
            (new TCPayerActionRequired())->execute($this, $ppOrder, Frontend::getCustomer(), Frontend::getCart());
            $ppOrder = $this->ppcpOrder->callPatch(new Order($ppOrder->getData()));
        } catch (PPCRequestException | OrderNotFoundException $e) {
            $this->handleOvercharge($ppOrder, $e);
            $this->getLogger()->write(
                \LOGLEVEL_NOTICE,
                'preparePaymentProcess: OrderPatchFailed - ' . $e->getMessage()
            );
            $this->raisePaymentError('jtl_paypal_commerce_payment_error', $this->getPaymentRetryURL($ppOrder));
        }

        if ($this->isAutoCapture()) {
            /* third party with auto capture - order before payment */
            $this->helper->persistOrder($order, $ppOrder, $this, [
                'invoiceId' => $orderNumber,
            ]);
            $this->handleOrder($ppOrder, $order);
            $this->sessionCache->clear(PaymentSession::ORDERID);

            return;
        }

        if ($this->helper->isCartCompleteAndValid($ppOrder) === false) {
            $redirectURL = $ppOrder->getLink('redirect')
                ?? Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php');
            Helper::redirectAndExit($redirectURL);

            exit();
        }

        try {
            (new TCServerError())->execute($this, $ppOrder, Frontend::getCustomer(), Frontend::getCart());
            $ppOrder = $this->ppcpOrder->callCapture($orderNumber, $this->getBNCode());
            (new TCServerError())->execute($this, $ppOrder, Frontend::getCustomer(), Frontend::getCart());
        } catch (PPCRequestException $e) {
            $this->handleOvercharge($ppOrder, $e);
            $this->getLogger()->write(
                \LOGLEVEL_NOTICE,
                'preparePaymentProcess: OrderCaptureFailed - ' . $e->getMessage()
            );
            // The API-Call failed - try to get the state in case of payment was still succesfully captured
            try {
                (new TCServerError(true))->execute($this, $ppOrder, Frontend::getCustomer(), Frontend::getCart());
                $ppOrder = $this->verifyPPOrder($ppOrder->getId());
                if (!$this->isValidOrderState($ppOrder, OrderStatus::STATUS_COMPLETED)) {
                    $this->helper->getAlert()->addError(
                        $this->plugin->getLocalization()
                                     ->getTranslation('jtl_paypal_commerce_payment_error'),
                        'orderCaptureFailed'
                    );
                }
            } catch (PPCRequestException | OrderNotFoundException) {
                // at this point the payment state is realy unknown - persist order to prevent payments without order
                $this->getLogger()->write(
                    \LOGLEVEL_ERROR,
                    'preparePaymentProcess: OrderCaptureFailed twice - persist order',
                    $order
                );
                $this->helper->persistOrder($order, $ppOrder, $this, [
                    'invoiceId' => $orderNumber,
                ]);
            }
        } catch (OrderNotFoundException) {
            $this->raisePaymentError('jtl_paypal_commerce_payment_error', $this->getPaymentRetryURL($ppOrder));
        }

        $ppOrder = (new TCCaptureDecline())->execute(
            $this,
            (new TCCapturePending())->execute($this, $ppOrder, Frontend::getCustomer(), Frontend::getCart()),
            Frontend::getCustomer(),
            Frontend::getCart()
        );
        if (
            $this->isValidOrderState($ppOrder, OrderStatus::STATUS_PENDING)
            || $this->isValidOrderState($ppOrder, OrderStatus::STATUS_COMPLETED)
        ) {
            $this->helper->persistOrder($order, $ppOrder, $this, [
                'invoiceId' => $orderNumber,
            ]);
        }

        $this->handleOrder($ppOrder, $order);
    }
}
