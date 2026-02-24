<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class ChargePermission
 *
 * A Charge Permission is a contract between you (the merchant) and the buyer. It represents the buyer consent to be charged. The Charge Permission is used to facilitate deferred transactions.
 * For simple payment use cases, such as immediate Authorization and Capture, you may not need to use the Charge Permission object at all.
 *
 * You can also use the Charge Permission to retrieve the relevant checkout details needed to complete the order such as buyer name, buyer email, and order shipping address.
 * Note that you can only retrieve checkout details for 30 days after the time that the Charge Permission was created.
 *
 * Successful completion of a Checkout Session returns a reference to a Charge Permission. You can use the Charge Permission for one successful Charge capture if the Charge Permission is in a Chargeable state.
 * You should review the reason code to determine why you can’t charge the buyer if the Charge Permission is in a Non-Chargeable state.
 * The Charge Permission will move to a Closed state after a successful Charge capture, if it’s canceled, or it expires after 180 days.
 *
 * Supported operations:
 *
 * Get Charge Permission - GET https://pay-api.amazon.com/:environment/:version/chargePermissions/:chargePermissionId
 * Update Charge Permission - PATCH https://pay-api.amazon.com/:environment/:version/chargePermissions/:chargePermissionId
 * Close Charge Permission - DELETE https://pay-api.amazon.com/:environment/:version/chargePermissions/:chargePermissionId/close
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class ChargePermission extends AbstractObject implements DatabaseObject {
    /**
     * Charge Permission identifer
     *
     * This value is returned at the end of a completed Checkout Session
     *
     * @var string $chargePermissionId
     */
    protected $chargePermissionId;

    /**
     * Charge Permission transaction limits
     * @var Limits $limits
     */
    protected $limits;

    /**
     * Amazon Pay environment
     *
     * Possible values: live, sandbox
     * @var string $releaseEnvironment
     */
    protected $releaseEnvironment;

    /**
     * Details about the buyer, such as their unique identifer, name, and email
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
     * @var PaymentPreference[] $paymentPreferences
     */
    protected $paymentPreferences;

    /**
     * Merchant-provided order details
     * @var MerchantMetadata $merchantMetadata
     */
    protected $merchantMetadata;

    /**
     * Merchant identifer of the Solution Provider (SP)
     *
     * Only SPs should use this field
     * @var string $platformId
     */
    protected $platformId;

    /**
     * UTC date and time when the Charge Permssion was created in ISO 8601 format
     * @var string $creationTimestamp
     */
    protected $creationTimestamp;

    /**
     * UTC date and time when the Charge Permission will expire in ISO 8601 format
     *
     * The Charge Permission will expire 180 days after it's confirmed
     * @var string $expirationTimestamp
     */
    protected $expirationTimestamp;

    /**
     * State of the Charge Permission object
     *
     * Chargeable
     * State in which there are no constraints on the Charge Permission and it can be used to charge the buyer
     *
     * Allowed operation(s):
     * GET Charge Permission
     * UPDATE Charge Permission
     * DELETE Charge Permission
     *
     * or
     *
     * NonChargeable
     * State in which there are constraints on the Charge Permission and it can't be used to charge the buyer
     *
     * Allowed operation(s):
     * GET Charge Permission
     * UPDATE Charge Permission
     * DELETE Charge Permission
     *
     * Reason codes:
     * PaymentMethodInvalid - The previous charge was declined. Ask the buyer to update the payment method
     *
     * PaymentMethodDeleted - The buyer has deleted the selected payment method
     *
     * BillingAddressDeleted - The buyer has deleted the billing address of the selected payment method
     *
     * PaymentMethodExpired - The selected payment method has expired
     *
     * PaymentMethodNotAllowed - The payment method selected by the buyer is not allowed for this Charge Permission
     *
     * PaymentMethodNotSet - There is no payment method associated with charge permission
     *
     * ChargeInProgress - A charge is already in progress. You cannot initiate a new charge unless previous charge is canceled
     *
     * MFAFailed - Buyer did not verify the transaction. Charge cannot be initiated unless buyer verifies the amount on the transaction
     *
     * or
     *
     * Closed
     * Charge Permission was closed or has expired
     *
     * Allowed operation(s):
     * GET Charge Permission
     *
     * Reason codes:
     * MerchantCanceled - You closed the Charge Permission by calling Cancel ChargePermission operation
     *
     * BuyerCanceled - The buyer closed the Charge Permission
     *
     * AmazonCanceled - Amazon closed the Charge Permission
     *
     * AmazonClosed - Amazon closed the Charge Permission since the Charge was Completed
     *
     * Expired - The Charge Permission expired after 180 days
     *
     * @var StatusDetails $statusDetails
     */
    protected $statusDetails;

    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->chargePermissionId = $data['chargePermissionId'] ?? null;
        $this->limits = isset($data['limits']) && \is_array($data['limits']) ? new Limits($data['limits']) : null;
        $this->releaseEnvironment = $data['releaseEnvironment'] ?? null;
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
        $this->merchantMetadata = isset($data['merchantMetadata']) && \is_array($data['merchantMetadata']) ? new MerchantMetadata($data['merchantMetadata']) : null;
        $this->platformId = $data['platformId'] ?? null;
        $this->creationTimestamp = $data['creationTimestamp'] ?? null;
        $this->expirationTimestamp = $data['expirationTimestamp'] ?? null;
        $this->statusDetails = isset($data['statusDetails']) && \is_array($data['statusDetails']) ? new StatusDetails($data['statusDetails']) : null;
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
     * @return Limits
     */
    public function getLimits(): Limits {
        return $this->limits;
    }

    /**
     * @param Limits $limits
     */
    public function setLimits(Limits $limits) {
        $this->limits = $limits;
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
     * @return Buyer
     */
    public function getBuyer(): Buyer {
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
    public function getShippingAddress(): Address {
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
    public function getPaymentPreferences(): array {
        return $this->paymentPreferences;
    }

    /**
     * @param PaymentPreference[] $paymentPreferences
     */
    public function setPaymentPreferences(array $paymentPreferences) {
        $this->paymentPreferences = $paymentPreferences;
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
     * Note: shopOrderId and shopOrderNumber are not contained here but set by the database class.
     *
     *`chargePermissionId` varchar(50) NOT NULL,
     * `buyerId` varchar(255) NOT NULL,
     * `buyerEmail` varchar(255) NOT NULL,
     * `buyerName` varchar(255) NOT NULL
     * `shopOrderId` int(10) NOT NULL,
     * `shopOrderNumber` varchar(50),
     * `status` varchar(50) NOT NULL,
     * `statusReason` text,
     * `chargeAmountLimitAmount` varchar(50) NOT NULL,
     * `chargeAmountLimitCurrencyCode` varchar(50) NOT NULL,
     * `creationTimestamp` varchar(50) NOT NULL,
     * `expirationTimestamp` varchar(50) NOT NULL,
     * `releaseEnvironment` varchar(50) NOT NULL,
     *
     * @param \stdClass $object
     * @return $this
     */
    public function fillFromDatabaseObject(\stdClass $object) {
        $this->chargePermissionId = $object->chargePermissionId;
        $this->buyer = new Buyer();
        $this->buyer->setBuyerId($object->buyerId);
        $this->buyer->setEmail($object->buyerEmail);
        $this->buyer->setName($object->buyerName);
        $this->statusDetails = new StatusDetails();
        $this->statusDetails->setState($object->status);
        if (isset($object->statusReason) && !empty($object->statusReason)) {
            $reasons = [];
            foreach (explode(',', $object->statusReason) as $reasonCode) {
                $reasons[] = new Reason(['reasonCode' => trim($reasonCode)]);
            }
            $this->statusDetails->setReasons($reasons);
        }
        $chargeAmountLimit = new Price();
        $chargeAmountLimit->setAmount($object->chargeAmountLimitAmount);
        $chargeAmountLimit->setCurrencyCode($object->chargeAmountLimitCurrencyCode);
        $limits = new Limits();
        $limits->setAmountLimit($chargeAmountLimit);
        $this->limits = $limits;
        $this->creationTimestamp = $object->creationTimestamp;
        $this->expirationTimestamp = $object->expirationTimestamp;
        $this->releaseEnvironment = $object->releaseEnvironment;
        return $this;
    }

    /**
     * Note: shopOrderId and shopOrderNumber are not contained here but set by the database class.
     *
     * `chargePermissionId` varchar(50) NOT NULL,
     * `buyerId` varchar(255) NOT NULL,
     * `buyerEmail` varchar(255) NOT NULL,
     * `buyerName` varchar(255) NOT NULL
     * `shopOrderId` int(10) NOT NULL,
     * `shopOrderNumber` varchar(50),
     * `status` varchar(50) NOT NULL,
     * `statusReason` text,
     * `chargeAmountLimitAmount` varchar(50) NOT NULL,
     * `chargeAmountLimitCurrencyCode` varchar(50) NOT NULL,
     * `creationTimestamp` varchar(50) NOT NULL,
     * `expirationTimestamp` varchar(50) NOT NULL,
     * `releaseEnvironment` varchar(50) NOT NULL,
     *
     * @return  \stdClass
     */
    public function getDatabaseObject(): \stdClass {
        $result = new \stdClass();
        $result->chargePermissionId = $this->chargePermissionId;
        $result->buyerId = $this->getBuyer()->getBuyerId();
        $result->buyerEmail = $this->getBuyer()->getEmail();
        $result->buyerName = $this->getBuyer()->getName();
        $result->status = $this->getStatusDetails()->getState();
        $result->statusReason = null;
        if (\is_array($this->getStatusDetails()->getReasons())) {
            $result->statusReason = implode(', ', array_map(function ($element) {
                /** @var Reason $element */
                return $element->getReasonCode() ?? '';
            }, $this->getStatusDetails()->getReasons()));
        }

        $limits = $this->getLimits();
        if($limits !== null && $limits->getAmountLimit() !== null) {
            $result->chargeAmountLimitAmount = $this->getLimits()->getAmountLimit()->getAmount();
            $result->chargeAmountLimitCurrencyCode = $this->getLimits()->getAmountLimit()->getCurrencyCode();
        } else {
            $result->chargeAmountLimitAmount = '';
            $result->chargeAmountLimitCurrencyCode = '';
        }
        $result->creationTimestamp = $this->getCreationTimestamp();
        $result->expirationTimestamp = $this->getExpirationTimestamp();
        $result->releaseEnvironment = $this->getReleaseEnvironment();
        return $result;
    }
}