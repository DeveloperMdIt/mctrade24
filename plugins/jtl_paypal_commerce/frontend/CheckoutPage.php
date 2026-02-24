<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use Exception;
use JTL\Cart\Cart;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\LegacyHelper;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\PaymentSession;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Order\Transaction;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;

/**
 * Class CheckoutPage
 * @package Plugin\jtl_paypal_commerce\frontend
 */
final class CheckoutPage extends AbstractPayPalPage
{
    public const STEP_SHIPPING   = 1;
    public const STEP_CONFIRM    = 2;
    public const PRODUCT_DETAILS = 3;
    public const CART            = 4;
    public const STEP_ADDRESS    = 5;
    public const STEP_FINISH     = 6;
    public const STEP_PENDING    = 7;

    public const PAGE_SCOPE_PRODUCTDETAILS = 'productDetails';
    public const PAGE_SCOPE_MINICART       = 'miniCart';
    public const PAGE_SCOPE_CART           = 'cart';
    public const PAGE_SCOPE_ORDERPROCESS   = 'orderProcess';
    public const PAGE_SCOPES               = [
        self::PAGE_SCOPE_PRODUCTDETAILS,
        self::PAGE_SCOPE_MINICART,
        self::PAGE_SCOPE_CART,
        self::PAGE_SCOPE_ORDERPROCESS,
    ];

    private bool $payAgainProcess = false;

    /**
     * @param Bestellung $shopOrder
     * @return void
     */
    public function finishOrderFromStatePage(Bestellung $shopOrder): void
    {
        $helper  = Helper::getInstance($this->plugin);
        $payment = $helper->getPaymentFromID($shopOrder->kZahlungsart ?? 0);
        if ($payment === null) {
            return;
        }

        $ppOrder = $helper->getPPOrder($payment, $shopOrder);
        if ($ppOrder === null) {
            return;
        }

        $fundingSource = $helper->getShopOrderAttribute($shopOrder, 'PAYPAL_FUNDING_SOURCE');
        $paymentState  = $payment->getValidOrderState($ppOrder);
        if (
            (
                $paymentState !== OrderStatus::STATUS_CREATED &&
                $paymentState !== OrderStatus::STATUS_PAYER_ACTION_REQUIRED
            ) || !$payment->isAutoCapture($fundingSource)
        ) {
            return;
        }

        $localization = $this->plugin->getLocalization();
        $header       = $localization->getTranslation(
            'jtl_paypal_commerce_payment_pi_auto_complete_header'
        ) ?? '';
        $message      = $localization->getTranslation(
            'jtl_paypal_commerce_payment_pi_auto_complete_description'
        ) ?? '';
        $alertService = Shop::Container()->getAlertService();
        $alertService->addWarning('<b>' . $header . '</b><br>' . $message, 'orderNotPayed', [
            'saveInSession' => true,
            'linkText'      => Shop::Lang()->get('payNow', 'global'),
            'linkHref'      => Shop::Container()->getLinkService()->getStaticRoute('bestellabschluss.php')
                . '?payAgain=1&kBestellung=' . $shopOrder->kBestellung,
        ]);
    }

    /**
     * @param Bestellung|null $shopOrder
     * @return void
     */
    public function finishOrder(?Bestellung $shopOrder): void
    {
        $helper  = Helper::getInstance($this->plugin);
        $payment = $helper->getPaymentFromID($shopOrder->kZahlungsart ?? 0);
        if ($shopOrder === null || $payment === null) {
            return;
        }

        $ppOrder = $helper->getPPOrder($payment, $shopOrder);
        if ($ppOrder === null) {
            return;
        }

        $this->setPageStep(self::STEP_FINISH);
        $fundingSource = $helper->getShopOrderAttribute($shopOrder, 'PAYPAL_FUNDING_SOURCE');
        if (!$payment->isAutoCapture($fundingSource)) {
            return;
        }

        $db  = Shop::Container()->getDB();
        $cId = $db->getSingleObject(
            'SELECT cId FROM tbestellid WHERE kBestellung = :orderId',
            ['orderId' => $shopOrder->kBestellung]
        )->cId ?? \uniqid('', true);
        $db->upsert('tbestellid', (object)[
            'cId'         => $cId,
            'kBestellung' => $shopOrder->kBestellung,
            'dDatum'      => 'NOW()',
        ]);

        Transaction::instance()->clearAllTransactions();
        $payment->setFundingSource($fundingSource);
        $paymentState = $payment->getValidOrderState($ppOrder);
        if ($paymentState === OrderStatus::STATUS_PAYER_ACTION_REQUIRED) {
            $ppOrder      = $payment->recreatePPOrder(
                $ppOrder,
                $shopOrder,
                $payment->getBNCode()
            );
            $paymentState = $payment->getValidOrderState($ppOrder);
        } else {
            $payment->storePPOrder($ppOrder);
        }

        if (
            $paymentState === OrderStatus::STATUS_CREATED ||
            $paymentState === OrderStatus::STATUS_PAYER_ACTION_REQUIRED
        ) {
            $this->payAgainProcess = true;
            $sessionCache          = PaymentSession::instance($payment->getMethod()->getModuleID());
            $sessionCache->set('payAgain.shopOrderId', $shopOrder->kBestellung);

            $frontend = new PayPalFrontend($this->plugin, $this->config, Shop::Smarty());
            $frontend->renderPayAgainPage($fundingSource, $cId, $ppOrder);
        }
    }

    /**
     * @param JTLSmarty $smarty
     * @return void
     */
    public function validatePayment(JTLSmarty $smarty): void
    {
        $payment = Helper::getInstance($this->plugin)->getPaymentFromName('PayPalCommerce');
        if ($payment === null) {
            return;
        }

        $ppOrder = $payment->getPPOrder();
        if ($ppOrder === null || $ppOrder->getStatus() !== OrderStatus::STATUS_APPROVED) {
            return;
        }

        // Reset current shipping methods to usable with this payment
        $customer        = Frontend::getCustomer();
        $shippingMethods = $smarty->getTemplateVars('Versandarten');
        foreach (\array_keys($shippingMethods) as $key) {
            if (
                !$payment->isAssigned(
                    LegacyHelper::getShippingClasses(Frontend::getCart()),
                    $customer->getGroupID() > 0
                        ? $customer->getGroupID()
                        : CustomerGroup::getDefaultGroupID(),
                    $shippingMethods[$key]->kVersandart
                )
            ) {
                unset($shippingMethods[$key]);
            }
        }
        $smarty->assign('Versandarten', $shippingMethods);

        // Reset current payment methods to only this payment
        $paymentMethods = $smarty->getTemplateVars('Zahlungsarten');
        foreach (\array_keys($paymentMethods) as $key) {
            if ($payment->getMethod()->getMethodID() !== $paymentMethods[$key]->kZahlungsart) {
                unset($paymentMethods[$key]);
            }
        }
        $smarty->assign('Zahlungsarten', $paymentMethods);
    }

    /**
     * @param JTLSmarty                $smarty
     * @param PayPalPaymentInterface[] $ppcPayments
     * @param Customer                 $customer
     * @param Cart                     $cart
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function checkoutStepShipping(JTLSmarty $smarty, array $ppcPayments, Customer $customer, Cart $cart): void
    {
        foreach ($ppcPayments as $ppcPayment) {
            $ppcPayment->getFrontendInterface($this->config, $smarty)
                       ->renderShippingPage($customer, $cart);
        }
    }

    /**
     * @param JTLSmarty                $smarty
     * @param PayPalPaymentInterface[] $ppcPayments
     * @param Customer                 $customer
     * @param Cart                     $cart
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function checkoutStepAddress(JTLSmarty $smarty, array $ppcPayments, Customer $customer, Cart $cart): void
    {
        foreach ($ppcPayments as $ppcPayment) {
            $ppcPayment->getFrontendInterface($this->config, $smarty)
                       ->renderAddressPage($customer, $cart);
        }
    }

    /**
     * @param JTLSmarty                $smarty
     * @param PayPalPaymentInterface[] $ppcPayments
     * @param Customer                 $customer
     * @param Cart                     $cart
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function checkoutStepConfirm(JTLSmarty $smarty, array $ppcPayments, Customer $customer, Cart $cart): void
    {
        $paymentId = (int)(Frontend::get('Zahlungsart')->kZahlungsart ?? 0);
        foreach ($ppcPayments as $ppcPayment) {
            if ($ppcPayment->getMethod()->getMethodID() === $paymentId) {
                $shippingAddress = Address::createFromOrderAddress(Frontend::getDeliveryAddress());
                $ppcPayment->getFrontendInterface($this->config, $smarty)
                           ->renderConfirmationPage($paymentId, $customer, $cart, $shippingAddress);
            } else {
                $ppcPayment->unsetCache();
            }
        }
    }

    /**
     * @param JTLSmarty                $smarty
     * @param PayPalPaymentInterface[] $ppcPayments
     * @param Customer                 $customer
     * @param Cart                     $cart
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function checkoutStepProduct(JTLSmarty $smarty, array $ppcPayments, Customer $customer, Cart $cart): void
    {
        $product        = $smarty->getTemplateVars('Artikel');
        $vaultingHelper = new VaultingHelper($this->config);
        $ecs            = new ExpressCheckout();
        foreach ($ppcPayments as $ppcPayment) {
            $shippingAddress = $ecs->applyVaultingAddress($vaultingHelper, $customer, $ppcPayment);
            $ppcPayment->getFrontendInterface($this->config, $smarty)
                       ->renderProductDetailsPage($customer, $cart, $shippingAddress ?? Address::createFromOrderAddress(
                           Frontend::getDeliveryAddress()
                       ), $product);
        }
    }

    /**
     * @param JTLSmarty                $smarty
     * @param PayPalPaymentInterface[] $ppcPayments
     * @param Customer                 $customer
     * @param Cart                     $cart
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function checkoutStepCart(JTLSmarty $smarty, array $ppcPayments, Customer $customer, Cart $cart): void
    {
        $vaultingHelper = new VaultingHelper($this->config);
        $ecs            = new ExpressCheckout();
        foreach ($ppcPayments as $ppcPayment) {
            $shippingAddress = $ecs->applyVaultingAddress($vaultingHelper, $customer, $ppcPayment);
            $ppcPayment->getFrontendInterface($this->config, $smarty)
                       ->renderCartPage($customer, $cart, $shippingAddress ?? Address::createFromOrderAddress(
                           Frontend::getDeliveryAddress()
                       ));
        }
    }

    /**
     * @param JTLSmarty                $smarty
     * @param PayPalPaymentInterface[] $ppcPayments
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    private function checkoutStepFinish(JTLSmarty $smarty, array $ppcPayments): void
    {
        foreach ($ppcPayments as $ppcPayment) {
            $ppOrder = $ppcPayment->getPPOrder();
            if ($ppOrder === null) {
                continue;
            }
            $ppcPayment->getFrontendInterface($this->config, $smarty)
                       ->renderFinishPage($ppOrder, $this->payAgainProcess);
        }
    }

    /**
     * @param JTLSmarty                $smarty
     * @param PayPalPaymentInterface[] $ppcPayments
     * @return void
     */
    private function checkoutStepPending(JTLSmarty $smarty, array $ppcPayments): void
    {
        foreach ($ppcPayments as $ppcPayment) {
            $ppOrder = $ppcPayment->getPPOrder();
            if ($ppOrder === null) {
                continue;
            }
            $ppcPayment->getFrontendInterface($this->config, $smarty)
                       ->renderPendingPage($ppOrder);
        }
    }

    public function render(JTLSmarty $smarty): void
    {
        $ppcPayments = [];
        foreach ($this->plugin->getPaymentMethods()->getMethods() as $paymentMethod) {
            $ppcPayment = Helper::getInstance($this->plugin)->getPaymentFromID(
                $paymentMethod->getMethodID(),
                $this->payAgainProcess
            );
            if ($ppcPayment !== null) {
                $ppcPayments[] = $ppcPayment;
            }
        }
        $customer = Frontend::getCustomer();
        $cart     = Frontend::getCart();

        try {
            switch ($this->getPageStep()) {
                case self::STEP_SHIPPING:
                    $this->checkoutStepShipping($smarty, $ppcPayments, $customer, $cart);
                    break;
                case self::STEP_ADDRESS:
                    $this->checkoutStepAddress($smarty, $ppcPayments, $customer, $cart);
                    break;
                case self::STEP_CONFIRM:
                    $this->checkoutStepConfirm($smarty, $ppcPayments, $customer, $cart);
                    break;
                case self::PRODUCT_DETAILS:
                    $this->checkoutStepProduct($smarty, $ppcPayments, $customer, $cart);
                    break;
                case self::CART:
                    $this->checkoutStepCart($smarty, $ppcPayments, $customer, $cart);
                    break;
                case self::STEP_FINISH:
                    $this->checkoutStepFinish($smarty, $ppcPayments);
                    break;
                case self::STEP_PENDING:
                    $this->checkoutStepPending($smarty, $ppcPayments);
                    break;
                default:
                    return;
            }
        } catch (Exception $e) {
            $logger = Shop::Container()->getLogService();
            $logger->error('page can not be rendered (' . $e->getMessage() . ')');
        }
    }
}
