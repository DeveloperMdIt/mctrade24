<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use Exception;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Customer\Customer;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\ApplePayLineItem;
use Plugin\jtl_paypal_commerce\PPC\Order\ApplePayPaymentContact;
use Plugin\jtl_paypal_commerce\PPC\Order\AppleTransactionInfo;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class ApplePayFrontend
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class ApplePayFrontend extends AbstractPaymentFrontend
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
        // no action at address page
    }

    /**
     * @inheritDoc
     */
    public function renderShippingPage(Customer $customer, Cart $cart): void
    {
        if (!$this->paymentMethod->isValid($customer, $cart)) {
            return;
        }

        $applepayMethod = $this->paymentMethod->getMethod();
        try {
            \pq('#' . $applepayMethod->getModuleID())
                ->append($this->smarty
                    ->assign('applepayModuleId', $applepayMethod->getModuleID())
                    ->fetch($applepayMethod->getAdditionalTemplate()));
        } catch (Exception) {
            $logger = Shop::Container()->getLogService();
            $logger->error('phpquery rendering failed: ApplePayFrontend::renderShippingPage()');

            return;
        }
    }

    /**
     * @inheritDoc
     */
    public function renderConfirmationPage(int $paymentId, Customer $customer, Cart $cart, Address $shippingAddr): void
    {
        if ($this->paymentMethod->getMethod()->getMethodID() !== $paymentId) {
            return;
        }

        $ppcOrderId = $this->paymentMethod->createPPOrder(
            $customer,
            $cart,
            $this->paymentMethod->getFundingSource(),
            ExperienceContext::SHIPPING_PROVIDED,
            ExperienceContext::USER_ACTION_PAY_NOW,
            $this->paymentMethod->getBNCode()
        );
        $ppOrder    = $this->paymentMethod->getPPOrder($ppcOrderId);
        if ($ppcOrderId === null || $ppOrder === null) {
            Helper::redirectAndExit($this->paymentMethod->getPaymentCancelURL());
            exit();
        }

        $purchase             = $ppOrder->getPurchase();
        $shipping             = $purchase->getShipping();
        $appleTransactionInfo = (new AppleTransactionInfo())
            ->setBillingContact(ApplePayPaymentContact::fromCustomer($customer))
            ->setShippingContact(ApplePayPaymentContact::fromShippingAddress(Frontend::getDeliveryAddress()))
            ->setCountryCode($shipping !== null ? $shipping->getAddress()->getCountryCode() : 'DE')
            ->setCurrencyCode($purchase->getAmount()->getCurrencyCode())
            ->setTotal((ApplePayLineItem::fromAmount($purchase->getAmount()))
                ->setLabel(Helper::getInstance($this->plugin)->getDescriptionFromCart($cart, 64)));

        $components = [
            Settings::COMPONENT_APPLE_PAY,
        ];
        $this->frontend->renderPayPalJsSDK($components, [], $this->paymentMethod->getBNCode());
        $this->frontend->renderApplePay($this->paymentMethod, $ppcOrderId, $appleTransactionInfo);
    }

    /**
     * @inheritDoc
     */
    public function renderFinishPage(Order $ppOrder, bool $payAgainProcess = false): void
    {
        // no action at finish page
    }
}
