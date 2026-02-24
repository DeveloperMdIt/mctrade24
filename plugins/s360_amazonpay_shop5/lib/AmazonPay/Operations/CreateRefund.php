<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Price;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Refund;

/**
 * Class CreateRefund
 *
 * Initiate a full or partial refund for a charge.
 * At your discretion, you can also choose to overcompensate the buyer and refund more than the original Charge amount by either 15% or 75 USD/GBP/EUR (whichever is less).
 * The response for Create Refund will include a Refund ID. This is the only time this value will ever be returned.
 * You must store the ID in order to retrieve Refund details at a later date.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class CreateRefund extends AbstractOperation {
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
     * Amount to be refunded. Refund amount can be either 15% or 75 USD/GBP/EUR (whichever is less) above the captured amount
     *
     * Maximum value: 150,000 USD/GBP/EUR
     * @var Price $refundAmount
     */
    protected $refundAmount;

    /**
     * The description is shown on the buyer payment instrument (such as bank) statement
     *
     * Default: "AMZ*&lt;MerchantStoreName&gt; amzn.com/pmts"
     *
     * Max length: 16 characters
     *
     * @var string|null $softDescriptor
     */
    protected $softDescriptor;

    public function __construct(string $chargeId, Price $refundAmount, $softDescriptor = null) {
        $this->chargeId = $chargeId;
        $this->refundAmount = $refundAmount;
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
            'chargeId' => $this->chargeId,
            'refundAmount' => $this->refundAmount->toArray()
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
        return 'createRefund';
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
        return $this->chargeId !== null && stripos($this->chargeId, 'S') === 0;
    }
}