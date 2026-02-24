<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\Nullable;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class OrderCaptureRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Order
 */
class OrderCaptureRequest extends AuthorizedRequest
{
    /** @var string */
    protected string $orderId;

    /** @var string|null */
    protected ?string $bnCode;

    /**
     * OrderCaptureRequest constructor
     * @param string      $token
     * @param string      $orderId
     * @param string|null $bnCode
     */
    public function __construct(string $token, string $orderId, ?string $bnCode = null)
    {
        $this->orderId = $orderId;
        $this->bnCode  = $bnCode;

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

        return parent::initHeaders($headers);
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
        return '/v2/checkout/orders/' . $this->orderId . '/capture';
    }
}
