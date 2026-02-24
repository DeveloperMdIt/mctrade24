<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Psr\Http\Message\ResponseInterface;

/**
 * Class OrderCaptureResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderCaptureResponse extends OrderGetResponse
{
    /**
     * OrderCaptureResponse constructor
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([200, 201]);
    }
}
