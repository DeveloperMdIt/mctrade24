<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\Nullable;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class OrderGetRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderGetRequest extends AuthorizedRequest
{
    /** @var string */
    protected string $orderId;

    /**
     * OrderGetRequest constructor.
     * @param string $token
     * @param string $orderId
     */
    public function __construct(string $token, string $orderId)
    {
        $this->orderId = $orderId;

        parent::__construct($token, MethodType::GET);
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return new Nullable();
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v2/checkout/orders/' . $this->orderId;
    }
}
