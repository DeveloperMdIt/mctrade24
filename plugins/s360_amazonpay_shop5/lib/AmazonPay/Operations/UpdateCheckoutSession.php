<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations;

use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AbstractObject;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\MerchantMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\PaymentDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\RecurringMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\WebCheckoutDetails;

/**
 * Class UpdateCheckoutSession
 *
 * Update the Checkout Session with transaction details.
 * You can keep updating the Checkout Session, as long as it’s in an Open state.
 * Once all mandatory parameters have been set, the Checkout Session object will respond with an unique amazonPayRedirectUrl that you will use to redirect the buyer to complete checkout.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class UpdateCheckoutSession extends AbstractOperation {

    /**
     * Checkout Session identifier
     * @var string $checkoutSessionId
     */
    protected $checkoutSessionId;

    /**
     * Checkout result URL provided by the merchant. Amazon Pay will redirect to this URL after completing the transaction
     *
     * Modifiable: Multiple times before the buyer is redirected to the AmazonPayReturnUrl
     * @var WebCheckoutDetails|null $webCheckoutDetails
     */
    protected $webCheckoutDetails;

    /**
     * Payment details specified by the merchant such as the amount and method for charging the buyer
     *
     * Modifiable: Multiple times before the buyer is redirected to the AmazonPayReturnUrl
     * @var PaymentDetails|null $paymentDetails
     */
    protected $paymentDetails;

    /**
     * External order details provided by the merchant
     *
     * Modifiable: Multiple times before the buyer is redirected to the AmazonPayReturnUrl
     * @var MerchantMetadata|null $merchantMetadata
     */
    protected $merchantMetadata;

    /**
     * Merchant identifier of the Solution Provider (SP).
     *
     * Only SPs should use this field.
     *
     * Modifiable: Multiple times before the buyer is redirected to the AmazonPayReturnUrl.
     * @var string|null $platformId
     */
    protected $platformId;

    /**
     * Metadata about how the recurring Charge Permission will be used. Amazon Pay only uses this information to calculate the Charge Permission expiration date and in buyer communication
     *
     * Note that it is still your responsibility to call Create Charge to charge the buyer for each billing cycle.
     *
     * @var RecurringMetadata $recurringMetadata
     */
    protected $recurringMetadata;

    public function __construct(string $checkoutSessionId, $webCheckoutDetails = null, $paymentDetails = null, $merchantMetadata = null, $recurringMetadata = null) {
        $this->checkoutSessionId = $checkoutSessionId;
        $this->webCheckoutDetails = $webCheckoutDetails;
        $this->paymentDetails = $paymentDetails;
        $this->merchantMetadata = $merchantMetadata;
        $this->recurringMetadata = $recurringMetadata;
        $this->platformId = $this->determinePlatformId();
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
        $result = [];
        if(null !== $this->webCheckoutDetails) {
            $result['webCheckoutDetails'] = $this->webCheckoutDetails->toArray();
        }
        if(null !== $this->paymentDetails) {
            $result['paymentDetails'] = $this->paymentDetails->toArray();
        }
        if(null !== $this->merchantMetadata) {
            $result['merchantMetadata'] = $this->merchantMetadata->toArray();
        }
        if(null !== $this->merchantMetadata) {
            $result['recurringMetadata'] = $this->recurringMetadata->toArray();
        }
        if(null !== $this->platformId) {
            $result['platformId'] = $this->platformId;
        }
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
     * Gets the operation name. The adapter uses this to decide which function to call.
     * @return string
     */
    public function getOperationName(): string {
        return 'updateCheckoutSession';
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

    /**
     * @return null|WebCheckoutDetails
     */
    public function getWebCheckoutDetails(): ?WebCheckoutDetails {
        return $this->webCheckoutDetails;
    }

    /**
     * @param null|WebCheckoutDetails $webCheckoutDetails
     */
    public function setWebCheckoutDetails($webCheckoutDetails) {
        $this->webCheckoutDetails = $webCheckoutDetails;
    }

    /**
     * @return null|PaymentDetails
     */
    public function getPaymentDetails(): ?PaymentDetails {
        return $this->paymentDetails;
    }

    /**
     * @param null|PaymentDetails $paymentDetails
     */
    public function setPaymentDetails($paymentDetails) {
        $this->paymentDetails = $paymentDetails;
    }

    /**
     * @return null|MerchantMetadata
     */
    public function getMerchantMetadata(): ?MerchantMetadata {
        return $this->merchantMetadata;
    }

    /**
     * @param null|MerchantMetadata $merchantMetadata
     */
    public function setMerchantMetadata($merchantMetadata) {
        $this->merchantMetadata = $merchantMetadata;
    }

    /**
     * @return RecurringMetadata
     */
    public function getRecurringMetadata(): ?RecurringMetadata {
        return $this->recurringMetadata;
    }

    /**
     * @param RecurringMetadata $recurringMetadata
     */
    public function setRecurringMetadata($recurringMetadata): void {
        $this->recurringMetadata = $recurringMetadata;
    }


    public function getSandbox(): ?bool {
        // Checkout session IDs cannot be used to discern between production and sandbox
        return null;
    }
}