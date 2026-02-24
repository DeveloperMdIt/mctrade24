<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Charge
 *
 * A Charge represents a single payment transaction.
 * Use a Charge to either authorize an amount and capture it later, or authorize and capture payment immediately.
 *
 * Depending on the integration pattern, you can either create a Charge using a valid Charge Permission, or create it as a result of a successful Checkout Session.
 * A successful Charge will move from Authorized to CaptureInitiated to Completed state.
 * The Authorized state may be preceded by a Pending state if you set canHandlePendingAuthorization to true, or payment was captured more than 7 days after authorization.
 *
 * See asynchronous processing for more information.
 *
 * An unsuccessful Charge will move to a Declined state if payment was declined, and move to a Canceled state if the Charge is explicitly canceled, or the Charge expires after 30 days in the Authorized state.
 *
 * Supported operations:
 *
 * Create Charge - POST https://pay-api.amazon.com/:environment/:version/charges/
 * Get Charge - GET https://pay-api.amazon.com/:environment/:version/charge/:chargeId
 * Capture - POST https://pay-api.amazon.com/:environment/:version/charges/:chargeId/capture
 * Cancel Charge - DELETE https://pay-api.amazon.com/:environment/:version/charge/:chargeId/cancel
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class Charge extends AbstractObject implements DatabaseObject {
    /**
     * Charge identifier
     *
     * This value is returned at the end of a completed Checkout Session or you can create a new charge from a Charge Permission in a Chargeable state
     * @var string $chargeId
     */
    protected $chargeId;

    /**
     * Represents the amount to be charged/authorized
     *
     * Maximum value:
     * US: $150,000
     * UK: £150,000
     * Germany: €150,000
     *
     * @var Price $chargeAmount
     */
    protected $chargeAmount;

    /**
     * The total amount that has been captured using this Charge
     * @var Price $captureAmount
     */
    protected $captureAmount;

    /**
     * Documentation missing but implied: http://amazonpaycheckoutintegrationguide.s3.amazonaws.com/amazon-pay-api-v2/charge.html#charge-object
     * @var Price $refundedAmount
     */
    protected $refundedAmount;

    /**
     * Description shown on the buyer payment instrument statement, if CaptureNow is set to true. Do not set this value if CaptureNow is set to false
     *
     * The soft descriptor sent to the payment processor is: "AMZ* <soft descriptor specified here>"
     *
     * Max length: 16 characters
     * @var string $softDescriptor
     */
    protected $softDescriptor;

    /**
     * Boolean that indicates whether or not Charge should be captured immediately after a successful authorization
     *
     * Default: false
     * @var bool $captureNow
     */
    protected $captureNow;

    /**
     * Boolean that indicates whether merchant can handle pending response
     *
     * See asynchronous processing for more information
     * @var bool $canHandlePendingAuthorization
     */
    protected $canHandlePendingAuthorization;

    /**
     * UTC date and time when the Charge was created in ISO 8601 format
     * @var string $creationTimestamp
     */
    protected $creationTimestamp;

    /**
     * UTC date and time when the Charge will expire in ISO 8601 format
     * @var string $expirationTimestamp
     */
    protected $expirationTimestamp;

    /**
     * State of the Charge object
     *
     * AuthorizationInitiated
     * Charge is in a pending state. See asynchronous processing for more information
     *
     * Allowed operation(s):
     * GET Charge
     * DELETE Charge
     *
     * or
     *
     * Authorized
     * Charge was successfully authorized
     *
     * Allowed operation(s):
     * GET Charge
     * POST Capture
     * DELETE Charge
     *
     * or
     *
     * CaptureInitated
     * Charge capture processing, will move to either Captured or Declined state depending on outcome
     *
     * Allowed operation(s):
     * GET Charge
     *
     * or
     *
     * Captured
     * Charge was successfully captured
     *
     * Allowed operation(s):
     * GET Charge
     * POST Refund
     *
     * or
     *
     * Canceled
     * Charge was canceled by Amazon or by the merchant
     *
     * Allowed operation(s):
     * GET Charge
     *
     * Reason codes:
     * ExpiredUnused - The Charge has been in the Authorized state for 30 days without being captured
     *
     * AmazonCanceled - Amazon canceled the Charge
     *
     * MerchantCanceled - You canceled the Charge using the Cancel Charge operation. You can specify the reason for the closure in the CancellationReason request parameter
     *
     * ChargePermissionCanceled - You have canceled the ChargePermission by calling Cancel ChargePermission operation with cancelPendingCharges set to true
     *
     * BuyerCanceled - The buyer canceled the Charge
     *
     * or
     *
     * Declined
     * The authorization or capture was declined
     *
     * Allowed operation(s):
     * GET Charge
     *
     * Reason codes:
     * SoftDeclined - Charge was soft declined. Retry attempts may or may not by succesful. If repeated retry attempts are unsuccessful, please contact the buyer and have them choose a different payment instrument
     *
     * HardDeclined - Charge was hard declined. Retry attempts will not succeed. Please contact the buyer and have them choose a different payment instrument
     *
     * AmazonRejected - Charge was declined by Amazon. The associated Charge Permission will also be canceled
     *
     * ProcessingFailure - Amazon could not process the Charge because of an internal processing error. You should retry the charge only if the Charge Permission is in the Chargeable state
     *
     * TransactionTimedOut -
     * If you set canHandlePendingAuthorization to false, the Charge was declined because Amazon Pay did not have enough time to process the authorization.
     * Please contact the buyer and have them choose another payment method.
     * If you frequently encounter this decline, consider setting canHandlePendingAuthorization to true
     *
     * If you set canHandlePendingAuthorization to true, the Charge was declined because Amazon Pay was unable to determine the validity of the order.
     * Please contact the buyer and have them choose another payment method.
     * See asynchronous processing for more information
     *
     * @var StatusDetails $statusDetails
     */
    protected $statusDetails;

    /**
     * This is not actually a part of the Charge as it is provided by Amazon Pay!
     * Instead we save this in the database for easier referencing.
     *
     * @var ?string $chargePermissionId
     */
    protected $chargePermissionId;

    /**
     * This is not actually a part of the Charge as it is provided by Amazon Pay!
     * We need it to map incoming payments for recurring orders, though, where charges have identical chargePermissionIds but belong to different orders.
     * @param ?int
     */
    protected $shopOrderId;

    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->chargeId = $data['chargeId'] ?? null;
        $this->chargeAmount = isset($data['chargeAmount']) && \is_array($data['chargeAmount']) ? new Price($data['chargeAmount']) : null;
        $this->captureAmount = isset($data['captureAmount']) && \is_array($data['captureAmount']) ? new Price($data['captureAmount']) : null;
        $this->refundedAmount = isset($data['refundedAmount']) && \is_array($data['refundedAmount']) ? new Price($data['refundedAmount']) : null;
        $this->statusDetails = isset($data['statusDetails']) && \is_array($data['statusDetails']) ? new StatusDetails($data['statusDetails']) : null;
        $this->softDescriptor = $data['softDescriptor'] ?? null;
        $this->captureNow = $data['captureNow'] ?? false;
        $this->canHandlePendingAuthorization = $data['canHandlePendingAuthorization'] ?? false;
        $this->creationTimestamp = $data['creationTimestamp'] ?? null;
        $this->expirationTimestamp = $data['expirationTimestamp'] ?? null;
        $this->chargePermissionId = $data['chargePermissionId'] ?? null;
        $this->shopOrderId = isset($data['shopOrderId']) ? (int) $data['shopOrderId'] : null;
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
     * @return Price
     */
    public function getChargeAmount(): Price {
        return $this->chargeAmount;
    }

    /**
     * @param Price $chargeAmount
     */
    public function setChargeAmount(Price $chargeAmount) {
        $this->chargeAmount = $chargeAmount;
    }

    /**
     * @return Price
     */
    public function getCaptureAmount(): ?Price {
        return $this->captureAmount;
    }

    /**
     * @param Price $captureAmount
     */
    public function setCaptureAmount(Price $captureAmount) {
        $this->captureAmount = $captureAmount;
    }

    /**
     * @return Price
     */
    public function getRefundedAmount(): ?Price {
        return $this->refundedAmount;
    }

    /**
     * @param Price $refundedAmount
     */
    public function setRefundedAmount(Price $refundedAmount) {
        $this->refundedAmount = $refundedAmount;
    }

    /**
     * @return string
     */
    public function getSoftDescriptor(): ?string {
        return $this->softDescriptor;
    }

    /**
     * @param string $softDescriptor
     */
    public function setSoftDescriptor(string $softDescriptor) {
        $this->softDescriptor = $softDescriptor;
    }

    /**
     * @return boolean
     */
    public function isCaptureNow(): ?bool {
        return $this->captureNow;
    }

    /**
     * @param boolean $captureNow
     */
    public function setCaptureNow(bool $captureNow) {
        $this->captureNow = $captureNow;
    }

    /**
     * @return boolean
     */
    public function isCanHandlePendingAuthorization(): bool {
        return $this->canHandlePendingAuthorization;
    }

    /**
     * @param boolean $canHandlePendingAuthorization
     */
    public function setCanHandlePendingAuthorization(bool $canHandlePendingAuthorization) {
        $this->canHandlePendingAuthorization = $canHandlePendingAuthorization;
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
     * Note: chargePermissionId is not contained in this object, but a reference that by the database class.
     *
     * `chargeId` varchar(50) NOT NULL,
     * `chargePermissionId` int(10) NOT NULL,
     * `status` varchar(50) NOT NULL,
     * `statusReason` text,
     * `chargeAmountAmount` varchar(50) NOT NULL,
     * `chargeAmountCurrencyCode` varchar(50) NOT NULL,
     * `captureAmountAmount` varchar(50),
     * `captureAmountCurrencyCode` varchar(50),
     * `refundedAmountAmount` varchar(50),
     * `refundedAmountCurrencyCode` varchar(50)
     * `creationTimestamp` varchar(50) NOT NULL,
     * `expirationTimestamp` varchar(50) NOT NULL,
     * `shopOrderId` int(10)
     *
     * @param \stdClass $object
     * @return $this
     */
    public function fillFromDatabaseObject(\stdClass $object) {
        $this->chargeId = $object->chargeId;
        $statusDetails = new StatusDetails();
        $statusDetails->setState($object->status);
        $statusDetails->setReasonCode($object->statusReason ?? '');
        $this->statusDetails = $statusDetails;

        $chargeAmount = new Price();
        $chargeAmount->setAmount($object->chargeAmountAmount);
        $chargeAmount->setCurrencyCode($object->chargeAmountCurrencyCode);
        $this->chargeAmount = $chargeAmount;

        if($object->captureAmountAmount !== null) {
            $captureAmount = new Price();
            $captureAmount->setAmount($object->captureAmountAmount);
            $captureAmount->setCurrencyCode($object->captureAmountCurrencyCode);
            $this->captureAmount = $captureAmount;
        }

        if($object->refundedAmountAmount !== null) {
            $refundedAmount = new Price();
            $refundedAmount->setAmount($object->refundedAmountAmount);
            $refundedAmount->setCurrencyCode($object->refundedAmountCurrencyCode);
            $this->refundedAmount = $refundedAmount;
        }
        $this->creationTimestamp = $object->creationTimestamp;
        $this->expirationTimestamp = $object->expirationTimestamp;
        $this->chargePermissionId = $object->chargePermissionId;
        $this->shopOrderId = isset($object->shopOrderId) ? (int) $object->shopOrderId : null;

        return $this;
    }

    /**
     * Note: chargePermissionId is not contained in the original Amazon Pay object, but a reference used by the database class.
     *
     * `chargeId` varchar(50) NOT NULL,
     * `chargePermissionId` varchar(50) NOT NULL,
     * `status` varchar(50) NOT NULL,
     * `statusReason` text,
     * `chargeAmountAmount` varchar(50) NOT NULL,
     * `chargeAmountCurrencyCode` varchar(50) NOT NULL,
     * `captureAmountAmount` varchar(50),
     * `captureAmountCurrencyCode` varchar(50),
     * `refundedAmountAmount` varchar(50),
     * `refundedAmountCurrencyCode` varchar(50),
     * `creationTimestamp` varchar(50) NOT NULL,
     * `expirationTimestamp` varchar(50) NOT NULL,
     * @return \stdClass
     */
    public function getDatabaseObject(): \stdClass {
        $result = new \stdClass();
        $result->chargeId = $this->chargeId;
        $result->status = $this->getStatusDetails()->getState();
        $result->statusReason = $this->getStatusDetails()->getReasonCode();
        $result->chargeAmountAmount = $this->getChargeAmount()->getAmount();
        $result->chargeAmountCurrencyCode = $this->getChargeAmount()->getCurrencyCode();
        if(null !== $this->getCaptureAmount()) {
            $result->captureAmountAmount = $this->getCaptureAmount()->getAmount();
            $result->captureAmountCurrencyCode = $this->getCaptureAmount()->getCurrencyCode();
        } else {
            $result->captureAmountAmount = null;
            $result->captureAmountCurrencyCode = null;
        }
        if(null !== $this->getRefundedAmount()) {
            $result->refundedAmountAmount = $this->getRefundedAmount()->getAmount();
            $result->refundedAmountCurrencyCode = $this->getRefundedAmount()->getCurrencyCode();
        } else {
            $result->refundedAmountAmount = null;
            $result->refundedAmountCurrencyCode = null;
        }
        $result->creationTimestamp = $this->creationTimestamp;
        $result->expirationTimestamp = $this->expirationTimestamp;
        $result->chargePermissionId = $this->chargePermissionId;
        $result->shopOrderId = $this->shopOrderId;
        return $result;
    }

    public function getChargePermissionId(): ?string {
        return $this->chargePermissionId;
    }

    /**
     * @param mixed $chargePermissionId
     */
    public function setChargePermissionId($chargePermissionId) {
        $this->chargePermissionId = $chargePermissionId;
    }

    /**
     * @return mixed
     */
    public function getShopOrderId() {
        return $this->shopOrderId;
    }

    /**
     * @param mixed $shopOrderId
     */
    public function setShopOrderId($shopOrderId): void {
        $this->shopOrderId = $shopOrderId;
    }



}