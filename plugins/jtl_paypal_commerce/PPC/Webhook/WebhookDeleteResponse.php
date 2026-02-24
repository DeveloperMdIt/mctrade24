<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Webhook;

use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Class WebhookDeleteResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Webhook
 */
class WebhookDeleteResponse extends JSONResponse
{
    /**
     * WebhookDeleteResponse constructor
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([204]);
    }
}
