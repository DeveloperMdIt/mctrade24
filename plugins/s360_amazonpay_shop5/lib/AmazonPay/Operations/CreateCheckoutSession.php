<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\DeliverySpecifications;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\MerchantMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\PaymentDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\RecurringMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\WebCheckoutDetails;

/**
 * Class CreateCheckoutSession
 *
 * Create a new Amazon Pay Checkout Session to customize and manage the buyer experience, from when the buyer clicks the Amazon Pay button to when they complete checkout.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class CreateCheckoutSession extends AbstractOperation {

    /**
     * Idempotency key to safely retry requests
     * @var string $idempotencyKey
     */
    protected $idempotencyKey;

    /**
     * Checkout result URL provided by the merchant.
     * Amazon Pay will redirect to this URL after completing the transaction.
     *
     * Modifiable: Multiple times before the buyer is redirected to the AmazonPayReturnUrl
     * @var WebCheckoutDetails $webCheckoutDetails
     */
    protected $webCheckoutDetails;

    /**
     * Login with Amazon client ID. Do not use the application ID
     *
     * Retrieve this value from "Login with Amazon" in Seller Central
     *
     * @var string $storeId
     */
    protected $storeId;

    /**
     * Specify shipping restrictions and limit which addresses your buyer can select from their Amazon address book
     * @var DeliverySpecifications|null $deliverySpecifications
     */
    protected $deliverySpecifications;

    /**
     * Payment details specified by the merchant such as the amount and method for charging the buyer
     *
     * Modifiable: Multiple times before the buyer is redirected to the AmazonPayReturnUrl
     *
     * @var PaymentDetails $paymentDetails
     */
    protected $paymentDetails;

    /**
     * External order details provided by the merchant
     *
     * Modifiable: Multiple times before the buyer is redirected to the AmazonPayReturnUrl
     * @var MerchantMetadata $merchantMetadata
     */
    protected $merchantMetadata;

    /**
     * Merchant identifier of the Solution Provider (SP).
     *
     * Only SPs should use this field.
     *
     * Modifiable: Multiple times before the buyer is redirected to the AmazonPayReturnUrl.
     * @var string $platformId
     */
    protected $platformId;

    /**
     * The type of Charge Permission requested
     *
     * Supported values:
     *
     * 'OneTime' - The Charge Permission can only be used for a single order
     * 'Recurring' - The Charge Permission can be used for recurring orders.
     *
     * Default value: 'OneTime"
     *
     * @var $chargePermissionType string
     */
    protected $chargePermissionType;

    /**
     * Metadata about how the recurring Charge Permission will be used. Amazon Pay only uses this information to calculate the Charge Permission expiration date and in buyer communication
     *
     * Note that it is still your responsibility to call Create Charge to charge the buyer for each billing cycle.
     *
     * @var RecurringMetadata $recurringMetadata
     */
    protected $recurringMetadata;


    public function __construct(WebCheckoutDetails $webCheckoutDetails, $deliverySpecifications = null, $paymentDetails = null, $merchantMetadata = null, $chargePermissionType = CheckoutSession::CHARGE_PERMISSION_TYPE_ONE_TIME, $recurringMetadata = null) {
        $this->webCheckoutDetails = $webCheckoutDetails;
        $this->deliverySpecifications = $deliverySpecifications;
        $this->paymentDetails = $paymentDetails;
        $this->merchantMetadata = $merchantMetadata;
        $this->chargePermissionType = $chargePermissionType;
        $this->recurringMetadata = $recurringMetadata;
        $this->storeId = $this->determineStoreId();
        $this->platformId = $this->determinePlatformId();
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
            'webCheckoutDetails' => $this->webCheckoutDetails->toArray(),
            'storeId' => $this->storeId,
        ];
        if(null !== $this->deliverySpecifications) {
            $result['deliverySpecifications'] = $this->deliverySpecifications->toArray();
        }
        if(null !== $this->paymentDetails) {
            $result['paymentDetails'] = $this->paymentDetails->toArray();
        }
        if(null !== $this->merchantMetadata) {
            $result['merchantMetadata'] = $this->merchantMetadata->toArray();
        }
        if(null !== $this->platformId) {
            $result['platformId'] = $this->platformId;
        }
        if(null !== $this->chargePermissionType) {
            $result['chargePermissionType'] = $this->chargePermissionType;
        }
        if(null !== $this->recurringMetadata) {
            $result['recurringMetadata'] = $this->recurringMetadata->toArray();
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
        return 'createCheckoutSession';
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
        return null;
    }
}