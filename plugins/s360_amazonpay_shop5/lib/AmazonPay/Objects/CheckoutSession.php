<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class CheckoutSession
 *
 * A Checkout Session represents a single active session (or engagement) for a buyer on your website.
 * The Checkout Session can be used to facilitate a new, one-time charge, or recovery from a declined payment.
 * Create a new Checkout Session each time you engage the buyer.
 *
 * The Checkout Session starts in an Open state and moves to a Canceled state, unless the buyer completes checkout within 24 hours.
 * In the Open state, you can use the Checkout Session to retrieve checkout details such as buyer profile, shipping address, and payment info.
 *
 * If the buyer completes checkout within 24 hours, the Checkout Session will move to either a Completed state or Canceled state, depending on whether or not the checkout was successful.
 * In the Completed state, you can use the Checkout Session to retrieve references to a Charge Permission.
 *
 * If payment authorization was requested, the response will also include references to a Charge.
 *
 * In the Canceled state, you can use the Checkout Session to retrieve why Checkout failed.
 *
 * Note that Amazon Pay permanently deletes Checkout Session objects and any associated info after 30 days.
 *
 * Supported operations:
 *
 * Create Checkout Session - POST https://pay-api.amazon.com/:environment/:version/checkoutSessions/
 * Get Checkout Session - GET https://pay-api.amazon.com/:environment/:version/checkoutSession/:checkoutSessionId
 * Update Checkout Session - PATCH https://pay-api.amazon.com/:environment/:version/checkoutSession/:checkoutSessionId
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class CheckoutSession extends AbstractObject {

    public const PRODUCT_TYPE_PAY_AND_SHIP = 'PayAndShip';

    public const CHARGE_PERMISSION_TYPE_ONE_TIME = 'OneTime';
    public const CHARGE_PERMISSION_TYPE_RECURRING = 'Recurring';

    /**
     * Checkout Session Identifier
     * @var string $checkoutSessionId
     */
    protected $checkoutSessionId;

    /**
     * URLs associated to the Checkout Session used for completing checkout
     * @var WebCheckoutDetails $webCheckoutDetails
     */
    protected $webCheckoutDetails;

    /**
     * Amazon Pay integration type
     *
     * Default: PayAndShip
     *
     * @var string $productType
     */
    protected $productType;

    /**
     * Payment details specified by the merchant, such as the amount and method for charging the buyer
     * @var PaymentDetails $paymentDetails
     */
    protected $paymentDetails;

    /**
     * Merchant-provided order info
     * @var MerchantMetadata $merchantMetadata
     */
    protected $merchantMetadata;

    /**
     * Merchant identifer of the Solution Provider (SP)- also known as ecommerce provider
     *
     * Only SPs should use this field
     *
     * @var string $platformId
     */
    protected $platformId;

    /**
     * Details about the buyer, such as their unique identifer, name, and email
     *
     * This info will only be returned for a Checkout Session in the Open state
     * @var Buyer $buyer
     */
    protected $buyer;

    /**
     * Billing address for buyer-selected payment instrument
     * @var Address $billingAddress
     */
    protected $billingAddress;

    /**
     * Shipping address selected by the buyer
     * @var Address $shippingAddress
     */
    protected $shippingAddress;

    /**
     * List of payment instruments selected by the buyer
     * @var PaymentPreference[]
     */
    protected $paymentPreferences;

    /**
     * State of the Checkout Session object
     *
     * Open
     * The initial Checkout Session state. Checkout Session state will return missing value constraints, until mandatory fields are provided by the merchant using Update Checkout Session.
     * After all constraints have been removed, the merchant will redirect the buyer to the AmazonPayRedirectUrl to complete checkout.
     * The Checkout Session state will then move to either Completed or Canceled state
     *
     * Note that the Checkout Session will move to Canceled state if the buyer doesn't complete checkout within 24 hours
     *
     * Allowed operation(s):
     * GET Checkout Session
     * UPDATE Checkout Session
     *
     * or
     *
     * Completed
     * Checkout was successfully completed. The buyer was redirected to the AmazonPayRedirectUrl and payment intent was successfully completed.
     * The Checkout Session can no longer be used to perform another payment, or retry a charge
     *
     * Note: if you set canHandlePendingAuthorization to true, the Checkout Session state will be in a Completed state, even though the Authorization might later fail. See asynchronous processing for more info
     *
     * Allowed operation(s):
     * GET Checkout Session (will return Charge Permission ID, Charge ID, and other Checkout Session details).
     *
     * or
     *
     * Canceled
     * Checkout was not successfully completed due to either buyer abandoment or payment decline. The payment intent was not successfully completed
     *
     * Allowed operation(s):
     * GET CheckoutSession (will only return state and reasonCode)
     *
     * Reasons:
     * BuyerCanceled - The buyer canceled the checkout by clicking the Return to previous page button
     *
     * Expired - The Checkout Session expired 24 hour after creation because there was no redirect to the amazonPayRedirectUrl or buyer did not complete payment
     *
     * AmazonCanceled - Amazon has canceled the transaction due to service unavailability. This is not a payment associated cancelation
     *
     * Declined - Generic payment decline reason code that includes fraud declines, failure to complete multi-factor authentication (MFA) challenge, and issues with the payment instrument
     *
     * @var StatusDetails $statusDetails
     */
    protected $statusDetails;

    /**
     * Constraints that must be addressed to complete Amazon Pay checkout
     * @var Constraint[] $constraints
     */
    protected $constraints;

    /**
     * Universal Time Coordinated (UTC) date and time when the Checkout Session was created in ISO 8601 format
     * @var string $creationTimestamp
     */
    protected $creationTimestamp;

    /**
     * UTC date and time when the Checkout Session will expire in ISO 8601 format
     * @var string $expirationTimestamp
     */
    protected $expirationTimestamp;

    /**
     * Charge permission identifier returned after Checkout Session is complete
     *
     * Used for creating charges for deferred transactions
     * @var string $chargePermissionId
     */
    protected $chargePermissionId;

    /**
     * Charge identifier returned after Checkout Session is complete
     *
     * Used for processing refunds
     * @var string $chargeId
     */
    protected $chargeId;

    /**
     * Login with Amazon client ID. Do not use the application ID
     *
     * Retrieve this value from "Login with Amazon" in Seller Central
     *
     * @var string $storeId
     */
    protected $storeId;

    /**
     * The releaseenvironment.
     * @var string $releaseEnvironment
     */
    protected $releaseEnvironment;

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

    /**
     * CheckoutSession constructor.
     * @param array|null $data
     */
    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->checkoutSessionId = $data['checkoutSessionId'] ?? null;
        $this->webCheckoutDetails = isset($data['webCheckoutDetails']) && \is_array($data['webCheckoutDetails']) ? new WebCheckoutDetails($data['webCheckoutDetails']) : null;
        $this->productType = $data['productType'] ?? null;
        $this->paymentDetails = isset($data['paymentDetails']) && \is_array($data['paymentDetails']) ? new PaymentDetails($data['paymentDetails']) : null;
        $this->merchantMetadata = isset($data['merchantMetadata']) && \is_array($data['merchantMetadata']) ? new MerchantMetadata($data['merchantMetadata']) : null;
        $this->platformId = $data['platformId'] ?? null;
        $this->buyer = isset($data['buyer']) && \is_array($data['buyer']) ? new Buyer($data['buyer']) : null;
        $this->billingAddress = isset($data['billingAddress']) && \is_array($data['billingAddress']) ? new Address($data['billingAddress']) : null;
        $this->shippingAddress = isset($data['shippingAddress']) && \is_array($data['shippingAddress']) ? new Address($data['shippingAddress']) : null;
        if (isset($data['paymentPreferences']) && \is_array($data['paymentPreferences'])) {
            $this->paymentPreferences = [];
            foreach ($data['paymentPreferences'] as $paymentPreference) {
                if (\is_array($paymentPreference)) {
                    $this->paymentPreferences[] = new PaymentPreference($paymentPreference);
                }
            }
        }
        $this->statusDetails = isset($data['statusDetails']) && \is_array($data['statusDetails']) ? new StatusDetails($data['statusDetails']) : null;
        if (isset($data['constraints']) && \is_array($data['constraints'])) {
            $this->constraints = [];
            foreach ($data['constraints'] as $constraint) {
                if (\is_array($constraint)) {
                    $this->constraints[] = new Constraint($constraint);
                }
            }
        }
        $this->creationTimestamp = $data['creationTimestamp'] ?? null;
        $this->expirationTimestamp = $data['expirationTimestamp'] ?? null;
        $this->chargePermissionId = $data['chargePermissionId'] ?? null;
        $this->chargeId = $data['chargeId'] ?? null;
        $this->storeId = $data['storeId'] ?? null;
        $this->releaseEnvironment = $data['releaseEnvironment'] ?? null;
        $this->recurringMetadata = isset($data['recurringMetadata']) && \is_array($data['recurringMetadata']) ? new RecurringMetadata($data['recurringMetadata']) : null;
        $this->chargePermissionType = $data['chargePermissionType'] ?? self::CHARGE_PERMISSION_TYPE_ONE_TIME;
    }

    /**
     * @return string
     */
    public function getCheckoutSessionId(): string {
        return $this->checkoutSessionId;
    }

    /**
     * @param string $checkoutSessionId
     */
    public function setCheckoutSessionId(string $checkoutSessionId) {
        $this->checkoutSessionId = $checkoutSessionId;
    }

    /**
     * @return WebCheckoutDetails
     */
    public function getWebCheckoutDetails(): WebCheckoutDetails {
        return $this->webCheckoutDetails;
    }

    /**
     * @param WebCheckoutDetails $webCheckoutDetails
     */
    public function setWebCheckoutDetails(WebCheckoutDetails $webCheckoutDetails) {
        $this->webCheckoutDetails = $webCheckoutDetails;
    }

    /**
     * @return string
     */
    public function getProductType(): string {
        return $this->productType;
    }

    /**
     * @param string $productType
     */
    public function setProductType(string $productType) {
        $this->productType = $productType;
    }

    /**
     * @return PaymentDetails
     */
    public function getPaymentDetails(): PaymentDetails {
        return $this->paymentDetails;
    }

    /**
     * @param PaymentDetails $paymentDetails
     */
    public function setPaymentDetails(PaymentDetails $paymentDetails) {
        $this->paymentDetails = $paymentDetails;
    }

    /**
     * @return MerchantMetadata
     */
    public function getMerchantMetadata(): MerchantMetadata {
        return $this->merchantMetadata;
    }

    /**
     * @param MerchantMetadata $merchantMetadata
     */
    public function setMerchantMetadata(MerchantMetadata $merchantMetadata) {
        $this->merchantMetadata = $merchantMetadata;
    }

    /**
     * @return string
     */
    public function getPlatformId(): string {
        return $this->platformId;
    }

    /**
     * @param string $platformId
     */
    public function setPlatformId(string $platformId) {
        $this->platformId = $platformId;
    }

    /**
     * @return Buyer
     */
    public function getBuyer(): ?Buyer {
        return $this->buyer;
    }

    /**
     * @param Buyer $buyer
     */
    public function setBuyer(Buyer $buyer) {
        $this->buyer = $buyer;
    }

    /**
     * @return Address
     */
    public function getBillingAddress(): Address {
        return $this->billingAddress;
    }

    /**
     * @return Address
     */
    public function getShippingAddress(): ?Address {
        return $this->shippingAddress;
    }

    /**
     * @param Address $shippingAddress
     */
    public function setShippingAddress(Address $shippingAddress) {
        $this->shippingAddress = $shippingAddress;
    }

    /**
     * @return PaymentPreference[]
     */
    public function getPaymentPreferences(): ?array {
        return $this->paymentPreferences;
    }

    /**
     * @param PaymentPreference[] $paymentPreferences
     */
    public function setPaymentPreferences(array $paymentPreferences) {
        $this->paymentPreferences = $paymentPreferences;
    }

    /**
     * @return StatusDetails
     */
    public function getStatusDetails(): StatusDetails {
        return $this->statusDetails;
    }

    /**
     * @param StatusDetails $statusDetails
     */
    public function setStatusDetails(StatusDetails $statusDetails) {
        $this->statusDetails = $statusDetails;
    }

    /**
     * @return Constraint[]
     */
    public function getConstraints(): array {
        return $this->constraints;
    }

    /**
     * @param Constraint[] $constraints
     */
    public function setConstraints(array $constraints) {
        $this->constraints = $constraints;
    }

    /**
     * @return string
     */
    public function getCreationTimestamp(): string {
        return $this->creationTimestamp;
    }

    /**
     * @param string $creationTimestamp
     */
    public function setCreationTimestamp(string $creationTimestamp) {
        $this->creationTimestamp = $creationTimestamp;
    }

    /**
     * @return string
     */
    public function getExpirationTimestamp(): string {
        return $this->expirationTimestamp;
    }

    /**
     * @param string $expirationTimestamp
     */
    public function setExpirationTimestamp(string $expirationTimestamp) {
        $this->expirationTimestamp = $expirationTimestamp;
    }

    /**
     * @return string
     */
    public function getChargePermissionId(): string {
        return $this->chargePermissionId;
    }

    /**
     * @param string $chargePermissionId
     */
    public function setChargePermissionId(string $chargePermissionId) {
        $this->chargePermissionId = $chargePermissionId;
    }

    /**
     * @return string
     */
    public function getChargeId(): string {
        return $this->chargeId;
    }

    /**
     * @param string $chargeId
     */
    public function setChargeId(string $chargeId) {
        $this->chargeId = $chargeId;
    }

    /**
     * @return string
     */
    public function getStoreId(): string {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     */
    public function setStoreId(string $storeId) {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getReleaseEnvironment(): string {
        return $this->releaseEnvironment;
    }

    /**
     * @param string $releaseEnvironment
     */
    public function setReleaseEnvironment(string $releaseEnvironment) {
        $this->releaseEnvironment = $releaseEnvironment;
    }

    /**
     * @return string
     */
    public function getChargePermissionType(): string {
        return $this->chargePermissionType;
    }

    /**
     * @param string $chargePermissionType
     */
    public function setChargePermissionType(string $chargePermissionType): void {
        $this->chargePermissionType = $chargePermissionType;
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
    public function setRecurringMetadata(RecurringMetadata $recurringMetadata): void {
        $this->recurringMetadata = $recurringMetadata;
    }


}