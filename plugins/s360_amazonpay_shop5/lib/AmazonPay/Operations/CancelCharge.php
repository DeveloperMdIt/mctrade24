<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;

/**
 * Class CancelCharge
 *
 * Moves Charge to Canceled state and releases any authorized payments.
 * You can call this operation until Capture is initiated while Charge is in an AuthorizationInitiated or Authorized state.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class CancelCharge extends AbstractOperation {
    /**
     * Charge identifier
     *
     * @var string $chargeId
     */
    protected $chargeId;

    /**
     * Merchant-provided reason for canceling Charge
     *
     * @var string $cancellationReason
     */
    protected $cancellationReason;

    public function __construct(string $chargeId, string $cancellationReason) {
        $this->chargeId = $chargeId;
        $this->cancellationReason = $cancellationReason;
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
        return [
            'cancellationReason' => $this->cancellationReason
        ];
    }

    /**
     * Returns the object id if applicable or null if none such id is required for the operation.
     * @return string|null
     */
    public function getObjectId(): ?string {
        return $this->chargeId;
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'cancelCharge';
    }

    /**
     * Returns the expected response object for the operation.
     * The object should never be an Error (this is handled by the Adapter already).
     * @param array $response
     * @return AbstractObject
     */
    public function createObjectFromResponse(array $response): AbstractObject {
        return new Charge($response);
    }

    public function getSandbox(): ?bool {
        return $this->chargeId !== null && stripos($this->chargeId, 'S') === 0;
    }
}