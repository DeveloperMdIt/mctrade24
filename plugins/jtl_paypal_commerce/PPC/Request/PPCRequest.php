<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class PPCRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class PPCRequest extends Request implements ResponseErrorHandlerInterface
{
    /**
     * PPCRequest constructor.
     * @param UriInterface                         $uri
     * @param string                               $method
     * @param array                                $headers
     * @param string|null|resource|StreamInterface $body
     */
    public function __construct(UriInterface $uri, string $method = MethodType::POST, array $headers = [], $body = null)
    {
        parent::__construct($method, $uri, $headers, $body);
    }

    /**
     * @inheritDoc
     * @throws PPCRequestException
     */
    public function handleClientError(ClientException $error): ResponseInterface
    {
        $response = $error->getResponse();

        throw new PPCRequestException(new ClientErrorResponse($response), $response->getHeader('Paypal-Debug-Id'));
    }

    /**
     * @inheritDoc
     */
    public function handleServerError(ServerException $error): ResponseInterface
    {
        throw $error;
    }
}
