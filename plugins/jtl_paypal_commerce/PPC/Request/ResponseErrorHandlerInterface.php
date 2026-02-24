<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Message\ResponseInterface;

/**
 * interface ResponseErrorHandlerInterface
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
interface ResponseErrorHandlerInterface
{
    /**
     * @param ClientException $error
     * @return ResponseInterface
     * @throws ServerException | PPCRequestException
     */
    public function handleClientError(ClientException $error): ResponseInterface;

    /**
     * @param ServerException $error
     * @return ResponseInterface
     * @throws ServerException
     */
    public function handleServerError(ServerException $error): ResponseInterface;
}
