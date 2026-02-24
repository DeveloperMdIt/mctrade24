<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking;

use Exception;
use Plugin\jtl_paypal_commerce\PPC\Request\JSONResponse;
use Psr\Http\Message\ResponseInterface;

use function Functional\first;

/**
 * Class TrackingResponse
 * @package Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking
 */
class TrackingResponse extends JSONResponse
{
    /**
     * @inheritDoc
     */
    public function __construct(ResponseInterface $response)
    {
        parent::__construct($response);

        $this->setExpectedResponseCode([200, 201]);
    }

    public function getId(): string
    {
        try {
            return $this->getData()->id ?? '';
        } catch (Exception) {
            return '';
        }
    }

    /**
     * @return string[]
     */
    public function getTrackerIds(): array
    {
        $trackers = [];
        try {
            $purchase = $this->getData()->purchase_units[0] ?? null;
        } catch (Exception) {
            return $trackers;
        }

        $shipping = $purchase->shipping ?? null;
        if ($shipping === null) {
            return $trackers;
        }

        foreach (($shipping->trackers ?? []) as $tracker) {
            $trackers[] = $tracker->id ?? '';
        }

        return \array_filter($trackers, static function (string $id) {
            return $id !== '';
        });
    }

    public function getTrackerId(): string
    {
        return first($this->getTrackerIds()) ?? '';
    }
}
