<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Request;

use GuzzleHttp\Psr7\Uri;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class BearerRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Request
 */
abstract class AuthorizedRequest extends PPCRequest
{
    /**
     * BearerRequest constructor.
     * @param string $token
     * @param string $method
     */
    public function __construct(string $token, string $method = MethodType::POST)
    {
        $body    = $this->initBody();
        $headers = $this->initHeaders([
            'Content-Type'  => $body->contentType(),
            'Authorization' => 'Bearer ' . $token,
        ]);

        parent::__construct(new Uri($this->getPath()), $method, $headers, $body->stringify());
    }

    /**
     * @param string[] $headers
     * @return string[]
     */
    protected function initHeaders(array $headers): array
    {
        return $headers;
    }

    /**
     * @return SerializerInterface
     */
    abstract protected function initBody(): SerializerInterface;

    /**
     * @return string
     */
    abstract protected function getPath(): string;
}
