<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Vaulting;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\MethodType;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\Nullable;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class PaymentTokenGetRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Vaulting
 */
class PaymentTokenGetRequest extends AuthorizedRequest
{
    private string $tokenId;

    /**
     * PaymentTokenGetRequest constructor
     */
    public function __construct(string $token, string $tokenId)
    {
        $this->tokenId = $tokenId;

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
        return \sprintf('/v3/vault/payment-tokens/%s', $this->tokenId);
    }
}
