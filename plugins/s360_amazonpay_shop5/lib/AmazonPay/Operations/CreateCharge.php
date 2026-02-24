<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Price;

/**
 * Class CreateCharge
 *
 * You can create a Charge to authorize payment, if you have a Charge Permission in a Chargeable state. You can optionally capture payment immediately by setting captureNow to true. The response for Create Charge will include a Charge ID.
 * This is the only time this value will ever be returned, so you must store the ID in order to capture payment, Get Charge details, or Create Refund at a later date.
 * If the request fails, you should reengage the buyer and ask them to go through checkout again.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class CreateCharge extends AbstractOperation {
    // DOCS ARE MISSING ON http://amazonpaycheckoutintegrationguide.s3.amazonaws.com/amazon-pay-api-v2/charge.html#create-charge

    /**
     * @var string $idempotencyKey
     */
    protected $idempotencyKey;
    /**
     * @var string $chargePermissionId
     */
    protected $chargePermissionId;

    /**
     * @var Price $chargeAmount
     */
    protected $chargeAmount;

    /**
     * @var bool $captureNow
     */
    protected $captureNow;

    /**
     * @var string $softDescriptor
     */
    protected $softDescriptor;

    /**
     * @var bool $canHandlePendingAuthorization
     */
    protected $canHandlePendingAuthorization;

    public function __construct(string $chargePermissionId, Price $chargeAmount, bool $captureNow = false, bool $canHandlePendingAuthorization = false, $softDescriptor = null) {
        $this->chargePermissionId = $chargePermissionId;
        $this->chargeAmount = $chargeAmount;
        $this->captureNow = $captureNow;
        $this->canHandlePendingAuthorization = $canHandlePendingAuthorization;
        $this->softDescriptor = $softDescriptor;
        $this->idempotencyKey = $this->generateIdempotencyKey();
    }


    /**
     * Gets the headers to set on the request.
     * This usually contains the idempotency key for requests that create new objects.
     *
     * @return array|null
     */
    public function getHeaders(): ?array {
        return [
            self::HEADER_AMAZONPAY_IDEMPOTENCY_KEY => $this->idempotencyKey
        ];
    }

    /**
     * Returns the body payload for the operation as assoc array (that may be transformed to JSON by the adapter).
     * @return array|null
     */
    public function getPayload(): ?array {
        $result = [
            'chargePermissionId' => $this->chargePermissionId,
            'chargeAmount' => $this->chargeAmount->toArray(),
            'captureNow' => $this->captureNow,
            'canHandlePendingAuthorization' => $this->canHandlePendingAuthorization,
        ];
        if(null !== $this->softDescriptor) {
            $result['softDescriptor'] = $this->softDescriptor;
        }
        return $result;
    }

    /**
     * Returns the object id if applicable or null if none such id is required for the operation.
     * @return string|null
     */
    public function getObjectId(): ?string {
        return null;
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'createCharge';
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
        if($this->chargePermissionId !== null && stripos($this->chargePermissionId, 'C') === 0) {
            // This is a subscription charge permission - we cannot identify if it is a sandbox or production mode permission
            return null;
        }
        return $this->chargePermissionId !== null && stripos($this->chargePermissionId, 'S') === 0;
    }
}
