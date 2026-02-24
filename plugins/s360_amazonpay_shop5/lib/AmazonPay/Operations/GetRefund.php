<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Refund;

/**
 * Class GetRefund
 *
 * Get refund details.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class GetRefund extends AbstractOperation  {
    /**
     * Refund identifier
     * @var string $refundId
     */
    protected $refundId;

    public function __construct(string $refundId) {
        $this->refundId = $refundId;
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
        return null;
    }

    /**
     * Returns the object id if applicable or null if none such id is required for the operation.
     * @return string|null
     */
    public function getObjectId(): ?string {
        return $this->refundId;
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'getRefund';
    }

    /**
     * Returns the expected response object for the operation.
     * The object should never be an Error (this is handled by the Adapter already).
     * @param array $response
     * @return AbstractObject
     */
    public function createObjectFromResponse(array $response): AbstractObject {
        return new Refund($response);
    }

    public function getSandbox(): ?bool {
        return $this->refundId !== null && stripos($this->refundId, 'S') === 0;
    }
}