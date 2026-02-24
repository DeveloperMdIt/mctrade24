<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Customer\Customer;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\ExperienceContext;
use Plugin\jtl_paypal_commerce\PPC\Order\GoogleTransactionInfo;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class GPayFrontend
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class GPayFrontend extends AbstractPaymentFrontend
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
        // no action at product details page
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
        // no action at shipping page
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

        $purchase              = $ppOrder->getPurchase();
        $shipping              = $purchase->getShipping();
        $googleTransactionInfo = (new GoogleTransactionInfo($purchase->getAmount()->getData()))
            ->setTotalPriceStatus(GoogleTransactionInfo::PRICESTATUS_FINAL)
            ->setLabel(GoogleTransactionInfo::ITEMTYPE_TOTAL, Shop::Lang()->get('totalSum', 'global'))
            ->setCountryCode($shipping !== null ? $shipping->getAddress()->getCountryCode() : 'DE');

        $components = [
            Settings::COMPONENT_GOOGLE_PAY,
        ];
        $this->frontend->renderPayPalJsSDK($components, [], $this->paymentMethod->getBNCode());
        $this->frontend->renderGooglePay($this->paymentMethod, $ppcOrderId, $googleTransactionInfo);
    }

    /**
     * @inheritDoc
     */
    public function renderFinishPage(Order $ppOrder, bool $payAgainProcess = false): void
    {
        // no action at finish page
    }
}
