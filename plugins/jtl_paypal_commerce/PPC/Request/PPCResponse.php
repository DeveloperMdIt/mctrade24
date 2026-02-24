<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class PPCResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class PPCResponse extends Response
{
    /**
     * PPCResponse constructor.
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody(),
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }
}
