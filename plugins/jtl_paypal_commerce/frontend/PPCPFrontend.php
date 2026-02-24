<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\PaymentSession;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Order\Transaction;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Settings;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;

/**
 * Class PPCPFrontend
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class PPCPFrontend extends AbstractPaymentFrontend
{
    protected function renderECSPage(
        Customer $customer,
        Cart $cart,
        Address $shippingAddr,
        bool $isValidBanner,
        bool $isValidExpress,
        string $scope,
        callable $renderButtons
    ): void {
        $components    = [];
        $fundingSource = $this->paymentMethod->getFundingSource();
        $vaultToken    = $this->paymentMethod->prepareVaultedPayment(
            $fundingSource === '' ? $this->paymentMethod->getDefaultFundingSource() : $fundingSource,
            $customer,
            VaultingHelper::buildShippingHashFromAdress($shippingAddr)
        );

        if ($isValidBanner && $this->frontend->renderInstalmentBanner($scope)) {
            $this->frontend->preloadInstalmentBannerJS($scope);
            $components[] = Settings::COMPONENT_MESSAGES;
        }
        if ($isValidExpress && $renderButtons($vaultToken !== '')) {
            $components[] = Settings::COMPONENT_BUTTONS;
            $components[] = Settings::COMPONENT_FUNDING_ELIGIBILITY;
        }

        $components    = $this->frontend->renderMiniCartComponents(
            $this->paymentMethod,
            $components,
            $customer,
            $cart,
            $vaultToken !== ''
        );
        $componentArgs = [
            'ppcCommit' => false,
            'isECS'     => true,
        ];
        if ($vaultToken !== '') {
            $componentArgs['vaultToken'] = $vaultToken;
        }

        if (!empty($components)) {
            $this->frontend->preloadECSJS($scope);
            $this->frontend->renderPayPalJsSDK($components, $componentArgs, $this->paymentMethod->getBNCode());
        }
    }

    /**
     * @inheritDoc
     */
    public function renderProductDetailsPage(
        Customer $customer,
        Cart $cart,
        Address $shippingAddr,
        ?Artikel $product
    ): void {
        if (!$this->paymentMethod->isValidIntern([$customer, $cart])) {
            return;
        }

        $this->renderECSPage(
            $customer,
            $cart,
            $shippingAddr,
            $this->paymentMethod->isValidBannerProduct($customer, $product),
            $this->paymentMethod->isValidExpressProduct($customer, $product),
            CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS,
            function (bool $isVaulting): bool {
                return $this->frontend->renderProductDetailsButtons($isVaulting);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function renderCartPage(Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        if (!$this->paymentMethod->isValid($customer, $cart)) {
            return;
        }

        $this->renderECSPage(
            $customer,
            $cart,
            $shippingAddr,
            $this->paymentMethod->isValidBannerPayment($customer, $cart),
            $this->paymentMethod->isValidExpressPayment($customer, $cart),
            CheckoutPage::PAGE_SCOPE_CART,
            function (bool $isVaulting): bool {
                return $this->frontend->renderCartButtons($isVaulting);
            }
        );
    }

    public function renderMiniCartPage(Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        if (!$this->paymentMethod->isValid($customer, $cart)) {
            return;
        }

        $fundingSource = $this->paymentMethod->getFundingSource();
        $vaultToken    = $this->paymentMethod->prepareVaultedPayment(
            $fundingSource === '' ? $this->paymentMethod->getDefaultFundingSource() : $fundingSource,
            $customer,
            VaultingHelper::buildShippingHashFromAdress($shippingAddr)
        );

        $components    = $this->frontend->renderMiniCartComponents(
            $this->paymentMethod,
            [],
            $customer,
            $cart,
            $vaultToken !== ''
        );
        $componentArgs = [
            'ppcCommit' => false,
            'isECS'     => true,
        ];
        if ($vaultToken !== '') {
            $componentArgs['vaultToken'] = $vaultToken;
        }

        if (!empty($components)) {
            $this->frontend->preloadInstalmentBannerJS(CheckoutPage::PAGE_SCOPE_MINICART);
            $this->frontend->preloadECSJS(CheckoutPage::PAGE_SCOPE_MINICART);
            $this->frontend->renderPayPalJsSDK($components, $componentArgs, $this->paymentMethod->getBNCode());
        }
    }

    /**
     * @inheritDoc
     */
    public function renderAddressPage(Customer $customer, Cart $cart): void
    {
        if (!$this->paymentMethod->isValid($customer, $cart)) {
            return;
        }

        $components = [];
        $this->frontend->preloadECSJS(CheckoutPage::PAGE_SCOPE_ORDERPROCESS);
        if (
            $this->paymentMethod->isValidExpressPayment($customer, $cart)
            && $this->frontend->renderOrderProcessButtons()
        ) {
            $components[] = Settings::COMPONENT_BUTTONS;
            $components[] = Settings::COMPONENT_FUNDING_ELIGIBILITY;
        }
        $componentArgs = [
            'ppcCommit' => false,
            'isECS'     => true,
        ];
        $this->frontend->renderPayPalJsSDK($components, $componentArgs, $this->paymentMethod->getBNCode());
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
        $components = [
            Settings::COMPONENT_BUTTONS,
            Settings::COMPONENT_FUNDING_ELIGIBILITY,
            Settings::COMPONENT_MARKS,
            Settings::COMPONENT_HOSTED_FIELDS,
            Settings::COMPONENT_PAYMENT_FIELDS,
        ];
        $this->frontend->renderPaymentButtons($this->paymentMethod);

        if (
            $this->paymentMethod->isValidBannerPayment($customer, $cart)
            && $this->frontend->renderInstalmentBanner(CheckoutPage::PAGE_SCOPE_ORDERPROCESS)
        ) {
            $this->frontend->preloadInstalmentBannerJS(CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS);
            $components[] = Settings::COMPONENT_MESSAGES;
        }

        $this->frontend->renderPayPalJsSDK($components, [], $this->paymentMethod->getBNCode());
    }

    /**
     * @inheritDoc
     */
    public function renderConfirmationPage(int $paymentId, Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        if ($this->paymentMethod->getMethod()->getMethodID() !== $paymentId) {
            return;
        }

        $fundingSource = Request::postVar('ppc-funding-source', '');
        if ($fundingSource === '' && Request::verifyGPCDataInt('wk') === 1) {
            // direct call from 1-click-checkout has no founding source and is not allowed
            Helper::redirectAndExit($this->paymentMethod->getPaymentCancelURL());
            exit();
        }

        $vaultingChecked = Request::postVar('ppc_vaulting_enable', []);
        $vaultingHelper  = new VaultingHelper(PPCHelper::getConfiguration($this->plugin));
        $vaultingHelper->enableVaulting(
            $fundingSource,
            $this->paymentMethod,
            (int)($vaultingChecked[$fundingSource] ?? 0) > 0
        );

        $ppcOrderId = $this->paymentMethod->createPPOrder(
            $customer,
            $cart,
            $fundingSource,
            ExperienceContext::SHIPPING_PROVIDED,
            ExperienceContext::USER_ACTION_PAY_NOW,
            $this->paymentMethod->getBNCode()
        );
        if ($ppcOrderId === null) {
            Helper::redirectAndExit($this->paymentMethod->getPaymentCancelURL());
            exit();
        }

        $fundingSource = $this->paymentMethod->getFundingSource();
        $ppOrder       = $this->paymentMethod->getPPOrder($ppcOrderId);
        $paymentSource = $ppOrder === null ? null : $ppOrder->getPaymentSource($fundingSource);
        $vaultToken    = '';
        if ($paymentSource !== null && $ppOrder->getStatus() !== OrderStatus::STATUS_APPROVED) {
            $accountId    = $paymentSource->getProperty('account_id') ?? '';
            $accountState = $paymentSource->getProperty('account_status') ?? '';
            $vaultToken   = $this->paymentMethod->prepareVaultedPayment(
                $fundingSource,
                $customer,
                VaultingHelper::buildShippingHashFromAdress($shippingAddr)
            );
            if ($accountId !== '' && ($vaultToken === '' || $accountState !== 'VERIFIED')) {
                $sessionCache = PaymentSession::instance($this->paymentMethod->getMethod()->getModuleID());
                $sessionCache->clear(PaymentSession::HASH)
                             ->clear(PaymentSession::ORDERID);

                Helper::redirectAndExit($this->paymentMethod->getPaymentRetryURL($ppOrder));
                exit();
            }
        }

        $components    = [Settings::COMPONENT_BUTTONS];
        $componentArgs = $vaultToken !== '' ? ['vaultToken' => $vaultToken] : [];

        $this->frontend->renderOrderConfirmationButtons($this->paymentMethod, $ppcOrderId);
        $this->frontend->renderPayPalJsSDK($components, $componentArgs, $this->paymentMethod->getBNCode());
    }

    /**
     * @inheritDoc
     */
    public function renderFinishPage(Order $ppOrder, bool $payAgainProcess = false): void
    {
        if ($this->paymentMethod->getValidOrderState($ppOrder) === OrderStatus::STATUS_COMPLETED) {
            return;
        }

        $this->frontend->renderOrderConfirmationButtons(
            $this->paymentMethod,
            $ppOrder->getId(),
            $payAgainProcess
        );
        if ($this->paymentMethod->isAutoCapture()) {
            $paymentSource = $ppOrder->getPaymentSource();
            $components    = [
                Settings::COMPONENT_BUTTONS,
                Settings::COMPONENT_PAYMENT_FIELDS,
                Settings::COMPONENT_MARKS,
                Settings::COMPONENT_FUNDING_ELIGIBILITY,
            ];
            $componentArgs = [
                'ppcCommit'   => true,
                'isECS'       => false,
                'countryCode' => $paymentSource !== null ? $paymentSource->getCountryCode() : '',
            ];
        } else {
            $components    = [
                Settings::COMPONENT_BUTTONS,
            ];
            $componentArgs = [];
        }

        $this->frontend->renderPayPalJsSDK($components, $componentArgs, $this->paymentMethod->getBNCode());
        $this->paymentMethod->resetPPOrder($ppOrder->getId());
        $this->frontend->renderFinishPage();
    }

    /**
     * @inheritDoc
     */
    public function renderOrderDetailPage(Bestellung $shopOrder, PayPalPaymentInterface $method): void
    {
        parent::renderOrderDetailPage($shopOrder, $method);

        if ($method->canOrderPayedAgain($shopOrder)) {
            $shopOrder->Zahlungsart->bPayAgain = true;
        }
    }
}
