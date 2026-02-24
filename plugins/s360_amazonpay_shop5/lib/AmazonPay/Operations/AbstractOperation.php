<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;

/**
 * Class AbstractOperation
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
abstract class AbstractOperation {

    protected const HEADER_AMAZONPAY_IDEMPOTENCY_KEY = 'x-amz-pay-idempotency-key';

    /**
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    abstract public function getOperationName(): string;

    /**
     * Gets the headers to set on the request.
     * This usually contains the idempotency key for requests that create new objects.
     *
     * @return array|null
     */
    abstract public function getHeaders(): ?array;

    /**
     * Returns the body payload for the operation as assoc array (that may be transformed to JSON by the adapter).
     * @return array|null
     */
    abstract public function getPayload(): ?array;

    /**
     * Returns the object id if applicable or null if none such id is required for the operation.
     * @return string|null
     */
    abstract public function getObjectId(): ?string;

    /**
     * Returns the expected response object for the operation.
     * The object should never be an Error (this is handled by the Adapter already).
     * @param array $response
     * @return AbstractObject
     */
    abstract public function createObjectFromResponse(array $response): AbstractObject;

    /**
     * Returns if the operation should be performed in the sandbox or not.
     *
     * Iff this operation returns null, callers should ignore its result and use their global configuration, because it may be a creation operation or regarding objects that do not differentiate between sandbox/production
     *
     * @return bool|null
     */
    abstract public function getSandbox(): ?bool;

    /**
     * Generates a string containing the current unix timestamp (length = 10 chars) followed by 11 random bytes encoded to hex (length = 22 chars).
     * In total the string is 32 chars long.
     * @return string
     */
    protected function generateIdempotencyKey(): string {
        return time() . bin2hex(random_bytes(11));
    }

    /**
     * Login with Amazon client ID. Do not use the application ID
     *
     * Retrieve this value from "Login with Amazon" in Seller Central
     * @return string
     */
    protected function determineStoreId(): string {
        return Config::getInstance()->getClientId();
    }

    /**
     * Merchant identifier of the Solution Provider (SP).
     *
     * Only SPs should use this field.
     * @return null|string
     */
    protected function determinePlatformId(): ?string {
        return Config::getInstance()->getPlatformId();
    }
}