<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\TestCase;

use JTL\Cart\Cart;
use JTL\Customer\Customer;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;

/**
 * Class TCCaptureDecline
 * @package Plugin\jtl_paypal_commerce\paymentmethod\TestCase
 */
class TCCaptureDecline extends AbstractTestCase
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

        $capture = $ppOrder->getPurchase()->getCapture();

        return parent::match($ppMethod, $ppOrder, $customer, $cart)
            && $shipping->getAddress()->getCity() === 'RESULT_DECLINE'
            && $capture !== null
            && $capture->getStatus() !== OrderStatus::STATUS_PENDING;
    }

    /**
     * @inheritDoc
     */
    public function run(): ?Order
    {
        $order = $this->getOrder();
        if ($order !== null) {
            $capture = $order->getPurchase()->getCapture();
            if ($capture !== null) {
                $data         = $capture->getData();
                $data->status = OrderStatus::STATUS_DECLINED;
                $capture->setData($data);
            }
        }

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
    ): ?Order {
        return parent::execute($ppMethod, $ppOrder, $customer, $cart) ?? $this->getOrder();
    }
}
