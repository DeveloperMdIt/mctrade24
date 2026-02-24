<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Price;

/**
 * Class CompleteCheckoutSession
 *
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class CompleteCheckoutSession extends AbstractOperation  {


    /**
     * Idempotency key to safely retry requests
     * @var string $idempotencyKey
     */
    protected $idempotencyKey;

    /**
     * Transaction amount
     *
     * @var Price $chargeAmount
     */
    protected $chargeAmount;

    /**
     * Checkout Session identifier
     * @var string $checkoutSessionId
     */
    protected $checkoutSessionId;


    public function __construct($checkoutSessionId, $chargeAmount) {
        $this->checkoutSessionId = $checkoutSessionId;
        $this->chargeAmount = $chargeAmount;
        $this->idempotencyKey = $this->generateIdempotencyKey();
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'completeCheckoutSession';
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
            'chargeAmount' => $this->chargeAmount->toArray()
        ];
        return $result;
    }

    /**
     * Returns the object id if applicable or null if none such id is required for the operation.
     * @return string|null
     */
    public function getObjectId(): ?string {
        return $this->checkoutSessionId;
    }

    /**
     * Returns the expected response object for the operation.
     * The object should never be an Error (this is handled by the Adapter already).
     * @param array $response
     * @return AbstractObject
     */
    public function createObjectFromResponse(array $response): AbstractObject {
        return new CheckoutSession($response);
    }

    public function getSandbox(): ?bool {
        // This does not play a role for this operation.
        return null;
    }
}