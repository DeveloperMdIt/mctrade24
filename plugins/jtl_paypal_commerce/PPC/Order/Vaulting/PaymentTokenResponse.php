<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Vaulting;

use Exception;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Class PaymentTokenResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Vaulting
 */
class PaymentTokenResponse extends JSONResponse
{
    /**
     * PaymentTokenResponse constructor
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([200]);
    }

    public function getPaymentToken(): PaymentToken
    {
        try {
            return new PaymentToken($this->getData());
        } catch (Exception) {
            return new PaymentToken();
        }
    }
}
