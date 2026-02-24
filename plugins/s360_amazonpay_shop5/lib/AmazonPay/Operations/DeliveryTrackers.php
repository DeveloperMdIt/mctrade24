<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\DeliveryTrackersPayload;

/**
 * Class DeliveryTrackers
 *
 * The Amazon Pay Delivery Tracker API lets you provide shipment tracking information to Amazon Pay so that Amazon Pay can notify buyers on Alexa when shipments are delivered.
 * This API is channel-agnostic. That means that anywhere you use Amazon Pay, you can use the Delivery Tracker API.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class DeliveryTrackers extends AbstractOperation {

    /**
     * @var DeliveryTrackersPayload $deliveryTrackersPayload
     */
    protected $deliveryTrackersPayload;

    public function __construct(DeliveryTrackersPayload $deliveryTrackersPayload) {
        $this->deliveryTrackersPayload = $deliveryTrackersPayload;
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'deliveryTrackers';
    }

    /**
     * Gets the headers to set on the request.
     * This usually contains the idempotency key for requests that create new objects.
     *
     * @return array|null
     */
    public function getHeaders(): ?array {
        return null;
    }

    /**
     * Returns the body payload for the operation as assoc array (that may be transformed to JSON by the adapter).
     * @return array|null
     */
    public function getPayload(): ?array {
        return $this->deliveryTrackersPayload->toArray();
    }

    /**
     * Returns the object id if applicable or null if none such id is required for the operation.
     * @return string|null
     */
    public function getObjectId(): ?string {
        return null;
    }

    /**
     * Returns the expected response object for the operation.
     * The object should never be an Error (this is handled by the Adapter already).
     * @param array $response
     * @return AbstractObject
     */
    public function createObjectFromResponse(array $response): AbstractObject {
        return new DeliveryTrackersPayload($response);
    }

    public function getSandbox(): ?bool {
        return null;
    }
}