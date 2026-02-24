<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Refund
 *
 * A Refund allows you (the merchant) to refund some or all of a previously-captured Charge to the buyer.
 * A refund can only be initiated on a previously-captured Charge, and multiple Refunds can be initiated on a single Charge.
 *
 * Amazon Pay processes refunds asynchronously.
 * Refunds start in a Pending state before moving to a Completed or Declined state, depending on whether or not the operation was successful.
 * You must set up instant payment notifications (IPNs), or implement a polling mechanism to query Get Refund API for updates. See asynchronous processing for more information.
 *
 * Supported operations
 *
 * Create Refund - POST https://pay-api.amazon.com/:environment/:version/refunds/
 * Get Refund - GET https://pay-api.amazon.com/:environment/:version/refunds/:refundId
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class Refund extends AbstractObject implements DatabaseObject {
    /**
     * Amazon Pay Refund identifier
     * @var string $refundId
     */
    protected $refundId;

    /**
     * Amount to be refunded.
     * Refund amount can be either 15% or 75 USD/GBP/EUR (whichever is less) above the captured amount
     *
     * Maximum value: 150,000 USD/GBP/EUR
     * @var Price $refundAmount
     */
    protected $refundAmount;

    /**
     * Description shown on the buyer payment instrument statement
     *
     * The soft descriptor sent to the payment processor is: "AMZ* <soft descriptor here>"
     *
     * Max length: 16 characters
     * @var string $softDescriptor
     */
    protected $softDescriptor;

    /**
     * UTC date and time when the refund was created in ISO 8601 format
     * @var string $creationTimestamp
     */
    protected $creationTimestamp;

    /**
     * State of the refund object
     *
     * RefundInitiated
     * A Refund object is in Pending state until it is processed by Amazon
     *
     * Allowed operation:
     * GET Refund
     *
     * or
     *
     * Refunded
     * Refund request has been processed and funds will be refunded to the buyer
     *
     * Allowed operation:
     * GET Refund
     *
     *
     * or
     *
     * Declined
     * Amazon has declined the refund because maximum amount has been refunded or there was some other issue
     *
     * Allowed operation:
     * GET Refund
     *
     * Reason codes:
     * AmazonRejected - Amazon rejected the refund. You should issue a refund to the buyer in an alternate manner (for example, a gift card or store credit)
     *
     * ProcessingFailure - Amazon could not process the transaction because of an internal processing error, or because the buyer has already received a refund from an A-to-z claim, or a chargeback.
     * You should only retry the refund if the Capture object is in the Completed state.
     * Otherwise, you should refund the buyer in an alternative way (for example, a store credit or a check)
     *
     * @var StatusDetails $statusDetails
     */
    protected $statusDetails;

    /**
     * @var string|null $chargeId
     */
    protected $chargeId;

    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    private function fillFromArray($data) {
        $this->refundId = $data['refundId'] ?? null;
        $this->refundAmount = isset($data['refundAmount']) && \is_array($data['refundAmount']) ? new Price($data['refundAmount']) : null;
        $this->softDescriptor = $data['softDescriptor'] ?? null;
        $this->creationTimestamp = $data['creationTimestamp'] ?? null;
        $this->statusDetails = isset($data['statusDetails']) && \is_array($data['statusDetails']) ? new StatusDetails($data['statusDetails']) : null;
    }

    /**
     * Note: chargeId is not contained in this object, but set by the database class.
     *
     * `refundId` varchar(50) NOT NULL,
     * `chargeId` int(10) NOT NULL,
     * `status` varchar(50) NOT NULL,
     * `statusReason` text,
     * `refundAmountAmount` varchar(50) NOT NULL,
     * `refundAmountCurrencyCode` varchar(50) NOT NULL,
     * `creationTimestamp` varchar(50) NOT NULL,
     *
     * @param \stdClass $object
     * @return $this
     */
    public function fillFromDatabaseObject(\stdClass $object) {
        $this->refundId = $object->refundId;
        $statusDetails = new StatusDetails();
        $statusDetails->setState($object->status);
        $statusDetails->setReasonCode($object->statusReason ?? '');
        $this->statusDetails = $statusDetails;
        $refundAmount = new Price();
        $refundAmount->setAmount($object->refundAmountAmount);
        $refundAmount->setCurrencyCode($object->refundAmountCurrencyCode);
        $this->refundAmount = $refundAmount;
        $this->creationTimestamp = $object->creationTimestamp;
        $this->chargeId = $object->chargeId;
        return $this;
    }

    /**
     * Note: chargeId is not contained in this object, but set by the database class.
     *
     * `refundId` varchar(50) NOT NULL,
     * `chargeId` int(10) NOT NULL,
     * `status` varchar(50) NOT NULL,
     * `statusReason` text,
     * `refundAmountAmount` varchar(50) NOT NULL,
     * `refundAmountCurrencyCode` varchar(50) NOT NULL,
     * `creationTimestamp` varchar(50) NOT NULL,
     *
     * @return \stdClass
     */
    public function getDatabaseObject(): \stdClass {
        $result = new \stdClass();
        $result->refundId = $this->refundId;
        $result->status = $this->statusDetails->getState();
        $result->statusReason = $this->statusDetails->getReasonCode();
        $result->refundAmountAmount = $this->refundAmount->getAmount();
        $result->refundAmountCurrencyCode = $this->refundAmount->getCurrencyCode();
        $result->creationTimestamp = $this->creationTimestamp;
        $result->chargeId = $this->chargeId;
        return $result;
    }

    /**
     * @return string
     */
    public function getRefundId(): string {
        return $this->refundId;
    }

    /**
     * @param string $refundId
     */
    public function setRefundId(string $refundId) {
        $this->refundId = $refundId;
    }

    /**
     * @return Price
     */
    public function getRefundAmount(): Price {
        return $this->refundAmount;
    }

    /**
     * @param Price $refundAmount
     */
    public function setRefundAmount(Price $refundAmount) {
        $this->refundAmount = $refundAmount;
    }

    /**
     * @return string
     */
    public function getSoftDescriptor(): string {
        return $this->softDescriptor;
    }

    /**
     * @param string $softDescriptor
     */
    public function setSoftDescriptor(string $softDescriptor) {
        $this->softDescriptor = $softDescriptor;
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
     * @return null|string
     */
    public function getChargeId(): ?string {
        return $this->chargeId;
    }

    /**
     * @param null|string $chargeId
     */
    public function setChargeId($chargeId) {
        $this->chargeId = $chargeId;
    }


}