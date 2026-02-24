<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\ChargePermission;

/**
 * Class CloseChargePermission
 *
 * Moves the Charge Permission to a Closed state.
 * No future charges can be made and pending charges will be canceled if you set cancelPendingCharges to true.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class CloseChargePermission extends AbstractOperation {
    /**
     * @var string $chargePermissionId
     */
    protected $chargePermissionId;

    /**
     * @var string $closureReason
     */
    protected $closureReason;

    /**
     * @var bool $cancelPendingCharges
     */
    protected $cancelPendingCharges;

    public function __construct(string $chargePermissionId, string $closureReason, bool $cancelPendingCharges = false) {
        $this->chargePermissionId = $chargePermissionId;
        $this->closureReason = $closureReason;
        $this->cancelPendingCharges = $cancelPendingCharges;
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
        $result = [
            'closureReason' => $this->closureReason,
            'cancelPendingCharges' => $this->cancelPendingCharges
        ];
        return $result;
    }

    /**
     * Returns the object id if applicable or null if none such id is required for the operation.
     * @return string|null
     */
    public function getObjectId(): ?string {
        return $this->chargePermissionId;
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'closeChargePermission';
    }


    /**
     * Returns the expected response object for the operation.
     * The object should never be an Error (this is handled by the Adapter already).
     * @param array $response
     * @return AbstractObject
     */
    public function createObjectFromResponse(array $response): AbstractObject {
        return new ChargePermission($response);
    }

    public function getSandbox(): ?bool {
        if($this->chargePermissionId !== null && stripos($this->chargePermissionId, 'C') === 0) {
            // This is a subscription charge permission - we cannot identify if it is a sandbox or production mode permission
            return null;
        }
        return $this->chargePermissionId !== null && stripos($this->chargePermissionId, 'S') === 0;
    }
}
