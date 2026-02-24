<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\TestCase;

use GuzzleHttp\Psr7\Response;
use JTL\Cart\Cart;
use JTL\Customer\Customer;
use JTL\Session\Frontend;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\Request\ClientErrorResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;

/**
 * Class TCServerError
 * @package Plugin\jtl_paypal_commerce\paymentmethod\TestCase
 */
class TCServerError extends AbstractTestCase
{
    /** @var bool */
    protected bool $throwTwice;

    /**
     * TCServerError constructor
     */
    public function __construct(bool $throwTwice = false)
    {
        $this->throwTwice = $throwTwice;
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
        $deliveryAdress = Frontend::getDeliveryAddress();
        $resultCapture  = \strcasecmp($deliveryAdress->cOrt ?? '', 'RESULT_CAPTURE') === 0;
        $throw          = !$this->throwTwice || (int)$deliveryAdress->cHausnummer === 2;
        $orderState     = $ppOrder !== null ? $ppOrder->getStatus() : OrderStatus::STATUS_UNKONWN;

        if (
            !parent::match($ppMethod, $ppOrder, $customer, $cart)
            || \strcasecmp($deliveryAdress->cNachname ?? '', 'SERVER_ERROR') !== 0
            || \strcasecmp($deliveryAdress->cVorname ?? '', 'THROW') !== 0
        ) {
            return false;
        }

        if ($throw && $resultCapture && $orderState === OrderStatus::STATUS_COMPLETED) {
            return true;
        }

        if ($throw && !$resultCapture && $orderState !== OrderStatus::STATUS_COMPLETED) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws PPCRequestException
     */
    public function run(): mixed
    {
        throw new PPCRequestException(new ClientErrorResponse(new Response(500)), []);
    }
}
