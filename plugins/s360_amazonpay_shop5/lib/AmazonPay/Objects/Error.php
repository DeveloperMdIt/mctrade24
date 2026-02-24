<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Error
 *
 * Errors returned by endpoints.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations
 */
class Error extends AbstractObject {
    // HTTP 400
    public const REASON_CODE_CURRENCY_MISMATCH = 'CurrencyMismatch';
    public const REASON_CODE_DUPLICATE_IDEMPOTENCY_KEY = 'DuplicateIdempotencyKey';
    public const REASON_CODE_INVALID_HEADER_VALUE = 'InvalidHeaderValue';
    public const REASON_CODE_INVALID_PARAMETER_VALUE = 'InvalidParameterValue';
    public const REASON_CODE_INVALID_REQUEST_FORMAT = 'InvalidRequestFormat';
    public const REASON_CODE_INVALID_SANDBOX_SIMULATION_SPECIFIED = 'InvalidSandboxSimulationSpecified';
    public const REASON_CODE_MISSING_HEADER_VALUE = 'MissingHeaderValue';
    public const REASON_CODE_MISSING_PARAMETER_VALUE = 'MissingParameterValue';
    public const REASON_CODE_UNRECOGNIZED_FIELD = 'UnrecognizedField';
    public const REASON_CODE_TRANSACTION_AMOUNT_EXCEEDED = 'TransactionAmountExceeded'; // Create Charge only / Capture Charge / Create Refund only
    public const REASON_CODE_CHECKOUT_SESSION_CANCELED = 'CheckoutSessionCanceled';

    // HTTP 401
    public const REASON_CODE_UNAUTHORIZED_ACCESS = 'UnauthorizedAccess';

    // HTTP 403
    public const REASON_CODE_INVALID_ACCOUNT_STATUS = 'InvalidAccountStatus';
    public const REASON_CODE_INVALID_REQUEST_SIGNATURE = 'InvalidRequestSignature';

    // HTTP 404
    public const REASON_CODE_RESOURCE_NOT_FOUND = 'ResourceNotFound';

    // HTTP 405
    public const REASON_CODE_UNSUPPORTED_OPERATION = 'UnsupportedOperation';
    public const REASON_CODE_REQUEST_NOT_SUPPORTED = 'RequestNotSupported';

    // HTTP 409
    public const REASON_CODE_AMOUNT_MISMATCH = 'AmountMismatch';

    // HTTP 422
    public const REASON_CODE_AMAZON_REJECTED = 'AmazonRejected'; // Create Charge / Capture Charge / Create Refund only
    public const REASON_CODE_CHARGE_PERMISSION_NOT_MODIFIABLE = 'ChargePermissionNotModifiable'; // Update Charge Permission only
    public const REASON_CODE_INVALID_CHECKOUT_SESSION_STATUS = 'InvalidCheckoutSessionStatus'; // Update Checkout Session only
    public const REASON_CODE_INVALID_CHARGE_PERMISSION_STATUS = 'InvalidChargePermissionStatus'; // Close Charge Permission, Create Charge, Capture Charge, Cancel Charge only
    public const REASON_CODE_INVALID_CHARGE_STATUS = 'InvalidChargeStatus'; // Capture Charge, Cancel Charge, Create Refund only
    public const REASON_CODE_HARD_DECLINED = 'HardDeclined'; // Create Charge only
    public const REASON_CODE_MFA_NOT_COMPLETED = 'MFANotCompleted'; // Create Charge only
    public const REASON_CODE_PAYMENT_METHOD_NOT_ALLOWED = 'PaymentMethodNotAllowed'; // Create Charge only
    public const REASON_CODE_SOFT_DECLINED = 'SoftDeclined'; // Create Charge only
    public const REASON_CODE_TRANSACTION_COUNT_EXCEEDED = 'TransactionCountExceeded'; // Create Charge / Capture Charge / Create Refund only
    public const REASON_CODE_TRANSACTION_TIMED_OUT = 'TransactionTimedOut'; // Create Charge only

    // HTTP 429
    public const REASON_CODE_TOO_MANY_REQUESTS = 'TooManyRequests';

    // HTTP 500
    public const REASON_CODE_INTERNAL_SERVER_ERROR = 'InternalServerError';
    public const REASON_CODE_PROCESSING_FAILURE = 'ProcessingFailure'; // Create Charge / Capture Charge / Create Refund only

    // HTTP 503
    public const REASON_CODE_SERVICE_UNAVAILABLE = 'ServiceUnavailable';

    // GENERIC, not returned by Amazon but by ourselves.
    public const REASON_CODE_UNKNOWN = 'Unknown';

    // GENERIC WHEN JSON DECODING THE RESPONSE FAILED
    public const REASON_CODE_JSON_DECODE_FAILED = 'JsonDecodeFailed';

    /**
     * @var int $httpErrorCode
     */
    protected $httpErrorCode;

    /**
     * @var string $reasonCode
     */
    protected $reasonCode;

    /**
     * @var string $message
     */
    protected $message;


    /**
     * Error constructor.
     * @param int $httpErrorCode
     * @param string $reasonCode
     * @param string $message
     */
    public function __construct(int $httpErrorCode, string $reasonCode, string $message = '') {
        $this->httpErrorCode = $httpErrorCode;
        $this->reasonCode = $reasonCode;
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getHttpErrorCode(): int {
        return $this->httpErrorCode;
    }

    /**
     * @return string
     */
    public function getReasonCode(): string {
        return $this->reasonCode;
    }

    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }



}