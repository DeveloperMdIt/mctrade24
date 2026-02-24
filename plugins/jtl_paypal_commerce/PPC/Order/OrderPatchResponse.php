<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Class OrderPatchResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderPatchResponse extends JSONResponse
{
    /**
     * OrderPatchResponse constructor
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([204]);
    }
}
