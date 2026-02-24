<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking;

use Plugin\jtl_paypal_commerce\PPC\Request\AuthorizedRequest;
use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\SerializerInterface;

/**
 * Class TrackingRequest
 * @package Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking
 */
class TrackingRequest extends AuthorizedRequest
{
    protected Tracking $tracker;

    /**
     * @inheritDoc
     */
    public function __construct(string $token, Tracking $tracker)
    {
        $this->tracker = $tracker;

        parent::__construct($token);
    }

    /**
     * @inheritDoc
     */
    protected function initBody(): SerializerInterface
    {
        return $this->tracker;
    }

    /**
     * @inheritDoc
     */
    protected function getPath(): string
    {
        return '/v2/checkout/orders/' . $this->tracker->getOrderId() . '/track';
    }
}
