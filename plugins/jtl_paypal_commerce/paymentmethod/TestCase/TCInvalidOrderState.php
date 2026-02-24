<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\TestCase;

use JTL\Cart\Cart;
use JTL\Customer\Customer;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;

/**
 * Class TCInvalidOrderState
 * @package Plugin\jtl_paypal_commerce\paymentmethod\TestCase
 */
class TCInvalidOrderState extends AbstractTestCase
{
    /**
     * @inheritDoc
     */
    public function match(
        PayPalPaymentInterface $ppMethod,
        ?Order $ppOrder = null,
        ?Customer $customer = null,
        ?Cart $cart = null
    ): bool {
        $shipping = $ppOrder !== null ? $ppOrder->getPurchase()->getShipping() : null;
        if ($shipping === null) {
            return false;
        }

        return parent::match($ppMethod, $ppOrder, $customer, $cart)
            && $shipping->getName() === 'FORCE INVALID_ORDER_STATE';
    }

    /**
     * @inheritDoc
     */
    public function run(): ?Order
    {
        $order    = $this->getOrder();
        $shipping = $order !== null ? $order->getPurchase()->getShipping() : null;
        if ($shipping === null) {
            return $order;
        }

        $order->setStatus($shipping->getAddress()->getCity());

        return $order;
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
        return parent::execute($ppMethod, $ppOrder, $customer, $cart) ?? $this->getOrder();
    }
}
