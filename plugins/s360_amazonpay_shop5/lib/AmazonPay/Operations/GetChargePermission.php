<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\ChargePermission;

/**
 * Class GetChargePermission
 *
 * Get Charge Permission to determine if this Charge Permission can be used to charge the buyer.
 * You can also use this operation to retrieve buyer details and their shipping address after a successful checkout.
 * You can only retrieve details for 30 days after the time that the Charge Permission was created.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class GetChargePermission extends AbstractOperation {
    /**
     * Charge Permission identifer
     * @var string $chargePermissionId
     */
    protected $chargePermissionId;

    public function __construct(string $chargePermissionId) {
        $this->chargePermissionId = $chargePermissionId;
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
        return $this->chargePermissionId;
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'getChargePermission';
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
