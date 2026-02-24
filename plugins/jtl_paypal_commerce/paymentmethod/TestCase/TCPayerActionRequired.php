<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\paymentmethod\TestCase;

use GuzzleHttp\Psr7\Response;
use JTL\Cart\Cart;
use JTL\Customer\Customer;
use JTL\Session\Frontend;
use Plugin\jtl_paypal_commerce\paymentmethod\PaymentSession;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\Payment\PaymentSourceBuilder;
use Plugin\jtl_paypal_commerce\PPC\Request\ClientErrorResponse;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;

/**
 * Class CapturePayerActionRequired
 * @package Plugin\jtl_paypal_commerce\paymentmethod\TestCase
 */
class TCPayerActionRequired extends AbstractTestCase
{
    private const SESSION_KEY = 'THROW_PAYER_ACTION_REQUIRED';

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
        $sessionCache   = PaymentSession::instance($ppMethod->getMethod()->getModuleID());
        $isKeyPayer     = \strcasecmp($deliveryAdress->cNachname ?? '', 'PAYER_ACTION_REQUIRED') === 0
            && \strcasecmp($deliveryAdress->cVorname ?? '', 'THROW') === 0;

        return parent::match($ppMethod, $ppOrder, $customer, $cart)
            && $isKeyPayer
            && $ppMethod->getFundingSource() === PaymentSourceBuilder::FUNDING_PAYPAL
            && ($sessionCache->get(self::SESSION_KEY) ?? 'N') === 'N';
    }

    /**
     * @inheritDoc
     * @throws PPCRequestException
     */
    public function run(): mixed
    {
        $sessionCache = PaymentSession::instance($this->getMethod()->getMethod()->getModuleID());
        $sessionCache->set(self::SESSION_KEY, 'Y');
        throw new PPCRequestException(
            new ClientErrorResponse(new Response(
                422,
                [],
                '{"name": "UNPROCESSABLE_ENTITY",'
                . '"message": "The requested action could not be performed, is semantically incorrect, '
                    . 'or failed business validation . ",'
                . '"debug_id": "0815",'
                . '"details": [{'
                    . '"issue": "PAYER_ACTION_REQUIRED",'
                    . '"description": "Transaction cannot be completed successfully, '
                       . 'instruct the buyer to return to PayPal."'
                . '}]}'
            )),
            []
        );
    }
}
