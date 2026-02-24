<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class StatusDetails
 *
 * Information on the status of an object.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class StatusDetails extends AbstractObject {

    public const STATUS_CANCELED = 'Canceled';
    public const STATUS_DECLINED = 'Declined';

    // Checkout Session Only
    public const STATUS_OPEN = 'Open';
    public const STATUS_COMPLETED = 'Completed';

    // Charge Only:
    public const STATUS_AUTHORIZATION_INITIATED = 'AuthorizationInitiated';
    public const STATUS_AUTHORIZED = 'Authorized';
    public const STATUS_CAPTURE_INITIATED = 'CaptureInitiated';
    public const STATUS_CAPTURED = 'Captured';

    // Refund Only:
    public const STATUS_REFUND_INITIATED = 'RefundInitiated';
    public const STATUS_REFUNDED = 'Refunded';

    // Charge Permission Only:
    public const STATUS_CHARGEABLE = 'Chargeable';
    public const STATUS_NON_CHARGEABLE = 'NonChargeable';
    public const STATUS_CLOSED = 'Closed';



    public const REASON_CODE_AMAZON_CANCELED = 'AmazonCanceled';
    public const REASON_CODE_AMAZON_CLOSED = 'AmazonClosed';
    public const REASON_CODE_AMAZON_REJECTED = 'AmazonRejected';
    public const REASON_CODE_BILLING_ADDRESS_DELETED = 'BillingAddressDeleted';
    public const REASON_CODE_BUYER_CANCELED = 'BuyerCanceled';
    public const REASON_CODE_CHARGE_IN_PROGRESS = 'ChargeInProgress';
    public const REASON_CODE_CHARGE_PERMISSION_CANCELED = 'ChargePermissionCanceled';
    public const REASON_CODE_DECLINED = 'Declined';
    public const REASON_CODE_EXPIRED = 'Expired';
    public const REASON_CODE_EXPIRED_UNUSED = 'ExpiredUnused';
    public const REASON_CODE_HARD_DECLINED = 'HardDeclined';
    public const REASON_CODE_MERCHANT_CANCELED = 'MerchantCanceled';
    public const REASON_CODE_MFA_FAILED = 'MFAFailed';
    public const REASON_CODE_PAYMENT_METHOD_DELETED = 'PaymentMethodDeleted';
    public const REASON_CODE_PAYMENT_METHOD_EXPIRED = 'PaymentMethodExpired';
    public const REASON_CODE_PAYMENT_METHOD_INVALID = 'PaymentMethodInvalid';
    public const REASON_CODE_PAYMENT_METHOD_NOT_ALLOWED = 'PaymentMethodNotAllowed';
    public const REASON_CODE_PAYMENT_METHOD_NOT_SET = 'PaymentMethodNotSet';
    public const REASON_CODE_PROCESSING_FAILURE = 'ProcessingFailure';
    public const REASON_CODE_SOFT_DECLINED = 'SoftDeclined';
    public const REASON_CODE_TRANSACTION_TIMED_OUT = 'TransactionTimedOut';

    /**
     * Current object state
     * @var string $state
     */
    protected $state;

    /**
     * Reason code for current state
     * @var string $reasonCode
     */
    protected $reasonCode;

    /**
     * An optional description of the state
     * @var string|null $reasonDescription
     */
    protected $reasonDescription;

    /**
     * Multiple reasons.
     *
     * Unfortunately, it depends on the parent object / context of this StatusDetails if it has a single reasonCode/Description (e.g. in CheckoutSession) or an array of reasons (e.g. in ChargePermission).
     *
     * @var Reason[]|null $reasons
     */
    protected $reasons;

    /**
     * UTC date and time when the state was last updated in ISO 8601 format
     * @var string $lastUpdatedTimestamp
     */
    protected $lastUpdatedTimestamp;

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->state = $data['state'] ?? null;
        $this->reasonCode = $data['reasonCode'] ?? null;
        $this->reasonDescription = $data['reasonDescription'] ?? null;
        $this->lastUpdatedTimestamp = $data['lastUpdatedTimestamp'] ?? null;
        if (isset($data['reasons']) && \is_array($data['reasons'])) {
            $this->reasons = [];
            foreach ($data['reasons'] as $reason) {
                if (\is_array($reason)) {
                    $this->reasons[] = new Reason($reason);
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getState(): string {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state) {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getReasonCode(): ?string {
        return $this->reasonCode;
    }

    /**
     * @param string $reasonCode
     */
    public function setReasonCode(string $reasonCode) {
        $this->reasonCode = $reasonCode;
    }

    /**
     * @return string
     */
    public function getReasonDescription(): ?string {
        return $this->reasonDescription;
    }

    /**
     * @param string $reasonDescription
     */
    public function setReasonDescription(string $reasonDescription) {
        $this->reasonDescription = $reasonDescription;
    }

    /**
     * @return string
     */
    public function getLastUpdatedTimestamp(): string {
        return $this->lastUpdatedTimestamp;
    }

    /**
     * @param string $lastUpdatedTimestamp
     */
    public function setLastUpdatedTimestamp(string $lastUpdatedTimestamp) {
        $this->lastUpdatedTimestamp = $lastUpdatedTimestamp;
    }

    /**
     * @return Reason[]
     */
    public function getReasons(): ?array {
        return $this->reasons;
    }

    /**
     * @param Reason[] $reasons
     */
    public function setReasons(array $reasons) {
        $this->reasons = $reasons;
    }



}