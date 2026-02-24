<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use Exception;

/**
 * Class PPCRequestException
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
class PPCRequestException extends Exception
{
    /** @var array */
    private array $debugId;

    /** @var ClientErrorResponse */
    private ClientErrorResponse $response;

    /**
     * PPCRequestException constructor.
     * @param ClientErrorResponse $response
     * @param array               $debugId
     */
    public function __construct(ClientErrorResponse $response, array $debugId)
    {
        $this->response = $response;
        $this->debugId  = $debugId;

        parent::__construct($response->getMessage(), $response->getStatusCode());
    }

    /**
     * @return string
     */
    public function getDebugId(): string
    {
        $debugId = $this->response->getDebugId();

        return $debugId === '' ? ($this->debugId[0] ?? '') : $debugId;
    }

    /**
     * @return ClientErrorResponse
     */
    public function getResponse(): ClientErrorResponse
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->response->getName();
    }

    /**
     * @param int $pos
     * @return object|null
     */
    public function getDetail(int $pos = 0): ?ClientErrorDetail
    {
        return $this->response->getDetail($pos);
    }

    /**
     * @param string $rel
     * @return string|null
     */
    public function getLink(string $rel): ?string
    {
        return $this->response->getLink($rel);
    }
}
