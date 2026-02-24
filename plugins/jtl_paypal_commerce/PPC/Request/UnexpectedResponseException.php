<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class UnexpectedResponseCodeException
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class UnexpectedResponseException extends Exception
{
    /** @var ResponseInterface */
    protected ResponseInterface $response;

    /**
     * UnexpectedResponseCodeException constructor.
     * @param ResponseInterface $response
     * @param array             $expected
     * @param Throwable|null    $e
     */
    public function __construct(ResponseInterface $response, array $expected = [200], ?Throwable $e = null)
    {
        $this->response = $response;

        parent::__construct(
            'Unexpected response code: get ' . $response->getStatusCode() . ', expected ' . \implode(', ', $expected),
            $response->getStatusCode(),
            $e
        );
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
