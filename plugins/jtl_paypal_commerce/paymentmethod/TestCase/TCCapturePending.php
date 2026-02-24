<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\TestCase;

use JTL\Cart\Cart;
use JTL\Checkout\Lieferadresse;
use JTL\Customer\Customer;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\paymentmethod\PaymentSession;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;

/**
 * Class TCCapturePending
 * @package Plugin\jtl_paypal_commerce\paymentmethod\TestCase
 */
class TCCapturePending extends AbstractTestCase
{
    private const SESSION_KEY = 'FORCE_PAYMENT_CAPTURE_PENDING';

    /** @var bool */
    private bool $useSession;

    /**
     * TCCapturePending constructor
     */
    public function __construct(bool $useSession = true)
    {
        $this->useSession = $useSession;
    }

    /**
     * @param Order $ppOrder
     * @return Lieferadresse
     */
    private function getAdressFromPayment(Order $ppOrder): Lieferadresse
    {
        $deliveryAdress = clone Frontend::getDeliveryAddress();
        $shipping       = $ppOrder->getPurchase()->getShipping();
        if ($shipping === null) {
            return $deliveryAdress;
        }

        if ($shipping->getName() === 'FORCE PAYMENT_CAPTURE_PENDING') {
            $deliveryAdress->cVorname  = 'FORCE';
            $deliveryAdress->cNachname = 'PAYMENT_CAPTURE_PENDING';
        }
        if (\preg_match('/(\d+)$/', $shipping->getAddress()->getAddress()[0], $match)) {
            $deliveryAdress->cHausnummer = $match[1];
        }

        return $deliveryAdress;
    }

    private function isForcePermanent(Order $ppOrder): bool
    {
        $shipping = $ppOrder->getPurchase()->getShipping();

        return (
            $shipping !== null
            && $shipping->getName() === 'FORCE PAYMENT_CAPTURE_PENDING'
            && $shipping->getAddress()->getPostalCode() === '99999'
        );
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
        if (
            $ppOrder === null
            || !parent::match($ppMethod, $ppOrder, $customer, $cart)
            || $ppOrder->getStatus() !== OrderStatus::STATUS_COMPLETED
        ) {
            return false;
        }

        if ($this->isForcePermanent($ppOrder)) {
            return true;
        }

        $deliveryAdress = $this->getAdressFromPayment($ppOrder);
        $sessionCache   = PaymentSession::instance($ppMethod->getMethod()->getModuleID());
        $forceCPending  = \strcasecmp($deliveryAdress->cNachname ?? '', 'PAYMENT_CAPTURE_PENDING') === 0
            && \strcasecmp($deliveryAdress->cVorname ?? '', 'FORCE') === 0;
        $pendingCount   = (int)$deliveryAdress->cHausnummer;

        if (!$forceCPending) {
            $sessionCache->clear(self::SESSION_KEY);
            $sessionCache->clear(self::SESSION_KEY . '_MAX');

            return false;
        }

        if ($pendingCount === 99) {
            return true;
        }

        if ($this->useSession && Shop::isFrontend()) {
            $sessionCache->set(self::SESSION_KEY . '_MAX', $pendingCount);
            if ($sessionCache->getInt(self::SESSION_KEY) === 0) {
                $sessionCache->set(self::SESSION_KEY, 1);
            }
        }

        if (
            !$this->useSession || !Shop::isFrontend()
            || $sessionCache->getInt(self::SESSION_KEY) > $sessionCache->getInt(self::SESSION_KEY . '_MAX')
        ) {
            $sessionCache->clear(self::SESSION_KEY);
            $sessionCache->clear(self::SESSION_KEY . '_MAX');

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function run(): ?Order
    {
        $sessionCache = PaymentSession::instance($this->getMethod()->getMethod()->getModuleID());
        $sessionCache->set(self::SESSION_KEY, $sessionCache->getInt(self::SESSION_KEY) + 1);

        $order = $this->getOrder();
        if ($order !== null) {
            $capture = $order->getPurchase()->getCapture();
            if ($capture !== null) {
                $data         = $capture->getData();
                $data->status = OrderStatus::STATUS_PENDING;
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
