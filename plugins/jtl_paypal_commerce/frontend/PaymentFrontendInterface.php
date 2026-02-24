<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use JTL\Cart\Cart;
use JTL\Catalog\Product\Artikel;
use JTL\Checkout\Bestellung;
use JTL\Customer\Customer;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Address;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;

/**
 * Class PaymentFrontendInterface
 * @package Plugin\jtl_paypal_commerce\frontend
 */
interface PaymentFrontendInterface
{
    /**
     * @param Customer     $customer
     * @param Cart         $cart
     * @param Address      $shippingAddr
     * @param Artikel|null $product
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function renderProductDetailsPage(
        Customer $customer,
        Cart $cart,
        Address $shippingAddr,
        ?Artikel $product
    ): void;

    /**
     * @param Customer $customer
     * @param Cart     $cart
     * @param Address  $shippingAddr
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function renderCartPage(Customer $customer, Cart $cart, Address $shippingAddr): void;

    /**
     * @param Customer $customer
     * @param Cart     $cart
     * @return void
     * @throws CircularReferenceException | ServiceNotFoundException
     */
    public function renderAddressPage(Customer $customer, Cart $cart): void;

    /**
     * @param Customer $customer
     * @param Cart     $cart
     * @return void
     * @throws CircularReferenceException | ServiceNotFoundException
     */
    public function renderShippingPage(Customer $customer, Cart $cart): void;

    /**
     * @param int      $paymentId
     * @param Customer $customer
     * @param Cart     $cart
     * @param Address  $shippingAddr
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function renderConfirmationPage(int $paymentId, Customer $customer, Cart $cart, Address $shippingAddr): void;

    /**
     * @param Order $ppOrder
     * @param bool  $payAgainProcess
     * @return void
     * @throws CircularReferenceException
     * @throws ServiceNotFoundException
     */
    public function renderFinishPage(Order $ppOrder, bool $payAgainProcess = false): void;

    public function renderPendingPage(Order $ppOrder): void;

    public function renderAccountPage(): void;

    /**
     * @param Bestellung             $shopOrder
     * @param PayPalPaymentInterface $method
     * @return void
     */
    public function renderOrderDetailPage(Bestellung $shopOrder, PayPalPaymentInterface $method): void;

    public function renderMiniCartPage(Customer $customer, Cart $cart, Address $shippingAddr): void;

    /**
     * @return PayPalPaymentInterface
     */
    public function getPaymentMethod(): PayPalPaymentInterface;

    /**
     * @return PayPalFrontend
     */
    public function getPayPalFrontend(): PayPalFrontend;
}
