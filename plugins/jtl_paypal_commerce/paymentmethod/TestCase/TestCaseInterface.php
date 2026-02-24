<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\TestCase;

use JTL\Cart\Cart;
use JTL\Customer\Customer;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;

/**
 * Class TestCaseInterface
 * @package Plugin\jtl_paypal_commerce\paymentmethod\TestCase
 */
interface TestCaseInterface
{
    /**
     * @param PayPalPaymentInterface $ppMethod
     * @param Order|null             $ppOrder
     * @param Customer|null          $customer
     * @param Cart|null              $cart
     * @return bool
     */
    public function match(
        PayPalPaymentInterface $ppMethod,
        ?Order $ppOrder = null,
        ?Customer $customer = null,
        ?Cart $cart = null
    ): bool;

    /**
     * @return mixed
     */
    public function run(): mixed;

    /**
     * @param PayPalPaymentInterface $ppMethod
     * @param Order|null             $ppOrder
     * @param Customer|null          $customer
     * @param Cart|null              $cart
     * @return mixed
     */
    public function execute(
        PayPalPaymentInterface $ppMethod,
        ?Order $ppOrder = null,
        ?Customer $customer = null,
        ?Cart $cart = null
    ): mixed;
}
