<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\TestCase;

use JTL\Cart\Cart;
use JTL\Customer\Customer;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

abstract class AbstractTestCase implements TestCaseInterface
{
    /** @var PayPalPaymentInterface */
    protected PayPalPaymentInterface $ppMethod;

    /** @var Order|null */
    protected ?Order $ppOrder = null;

    /** @var Customer|null */
    protected ?Customer $customer = null;

    /** @var Cart|null */
    protected ?Cart $cart = null;

    /**
     * @return PayPalPaymentInterface
     */
    protected function getMethod(): PayPalPaymentInterface
    {
        return $this->ppMethod;
    }

    /**
     * @param PayPalPaymentInterface $ppMethod
     */
    protected function setMethod(PayPalPaymentInterface $ppMethod): void
    {
        $this->ppMethod = $ppMethod;
    }

    /**
     * @return Order|null
     */
    protected function getOrder(): ?Order
    {
        return $this->ppOrder;
    }

    /**
     * @param Order|null $ppOrder
     */
    protected function setOrder(?Order $ppOrder): void
    {
        $this->ppOrder = $ppOrder;
    }

    /**
     * @return Customer|null
     */
    protected function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    /**
     * @param Customer|null $customer
     */
    protected function setCustomer(?Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return Cart|null
     */
    protected function getCart(): ?Cart
    {
        return $this->cart;
    }

    /**
     * @param Cart|null $cart
     */
    protected function setCart(?Cart $cart): void
    {
        $this->cart = $cart;
    }

    /**
     * @inheritDoc
     */
    public function match(
        PayPalPaymentInterface $ppMethod,
        ?Order $ppOrder = null,
        ?Customer $customer = null,
        ?Cart $cart = null
    ): bool {
        $this->setMethod($ppMethod);
        $this->setOrder($ppOrder);
        $this->setCustomer($customer);
        $this->setCart($cart);

        return \defined('PPC_DEBUG') && \PPC_DEBUG && PPCHelper::getEnvironment()->isSandbox();
    }

    /**
     * @inheritDoc
     */
    public function execute(
        PayPalPaymentInterface $ppMethod,
        ?Order $ppOrder = null,
        ?Customer $customer = null,
        ?Cart $cart = null
    ): mixed {
        $this->setMethod($ppMethod);
        $this->setOrder($ppOrder);
        $this->setCustomer($customer);
        $this->setCart($cart);

        return $this->match($ppMethod, $ppOrder, $customer, $cart) ? $this->run() : null;
    }
}
