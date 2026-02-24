<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class OrdersCreateRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderCreateRequest extends AuthorizedRequest
{
    /** @var string|null */
    protected ?string $bnCode;

    /** @var Order */
    protected Order $order;

    /**
     * OrdersCreateRequest constructor.
     * @param string      $token
     * @param Order       $order
     * @param string|null $bnCode
     */
    public function __construct(string $token, Order $order, ?string $bnCode = null)
    {
        $this->order  = $order;
        $this->bnCode = $bnCode;

        parent::__construct($token);
    }

    /**
     * @inheritDoc
     */
    protected function initHeaders(array $headers): array
    {
        if ($this->bnCode !== null) {
            $headers['PayPal-Partner-Attribution-Id'] = $this->bnCode;
        }
        $headers['Prefer'] = 'return=representation';

        return $headers;
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return $this->order;
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v2/checkout/orders';
    }
}
