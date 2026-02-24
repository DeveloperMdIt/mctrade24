<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Price;

/**
 * Class CaptureCharge
 *
 * Capture payment on a Charge in the Authorized state.
 * A successful Capture will move the Charge from Authorized to Captured state.
 * The Captured state may be preceded by a temporary CaptureInitiated state if payment was captured more than 7 days after authorization.
 *
 * See asynchronous processing for more information. An unsuccessful Charge will move to a Declined state if payment was declined.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class CaptureCharge extends AbstractOperation {

    /**
     * Idempotency key to safely retry requests
     * @var string $idempotencyKey
     */
    protected $idempotencyKey;

    /**
     * Charge identifier
     * @var string $chargeId
     */
    protected $chargeId;

    /**
     * Amount to capture
     * @var Price $captureAmount
     */
    protected $captureAmount;

    /**
     * Description shown on the buyer's payment instrument statement.
     *
     * The soft descriptor sent to the payment processor is: "AMZ* <soft descriptor specified here>"
     *
     * Max length: 16 characters
     * @var string|null $softDescriptor
     */
    protected $softDescriptor;

    public function __construct(string $chargeId, Price $captureAmount, $softDescriptor = null) {
        $this->chargeId = $chargeId;
        $this->captureAmount = $captureAmount;
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
            'captureAmount' => $this->captureAmount->toArray(),
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
        return $this->chargeId;
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'captureCharge';
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