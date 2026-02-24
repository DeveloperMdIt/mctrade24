<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class OrderPatchRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderPatchRequest extends AuthorizedRequest
{
    /** @var string */
    protected string $orderId;

    /** @var JSON */
    protected JSON $patches;

    /**
     * OrderPatchRequest constructor.
     * @param string  $token
     * @param string  $orderId
     * @param Patch[] $patches
     */
    public function __construct(string $token, string $orderId, array $patches)
    {
        $this->orderId = $orderId;
        $this->patches = new JSON($patches);

        parent::__construct($token, MethodType::PATCH);
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return $this->patches;
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v2/checkout/orders/' . $this->orderId;
    }
}
