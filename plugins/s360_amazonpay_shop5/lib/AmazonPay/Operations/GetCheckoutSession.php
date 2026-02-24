<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;

/**
 * Class GetCheckoutSession
 *
 * Get Checkout Session details includes buyer info, payment instrument details, and shipping address.
 * Use this operation to determine if checkout was successful after the buyer returns from the AmazonPayRedirectUrl to the specified checkoutResultReturnUrl.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class GetCheckoutSession extends AbstractOperation {

    /**
     * Checkout session identifier
     * @var string $checkoutSessionId
     */
    protected $checkoutSessionId;

    public function __construct(string $checkoutSessionId) {
        $this->checkoutSessionId = $checkoutSessionId;
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
        return $this->checkoutSessionId;
    }

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'getCheckoutSession';
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
        // Checkout session IDs cannot be used to discern between production and sandbox
        return null;
    }
}