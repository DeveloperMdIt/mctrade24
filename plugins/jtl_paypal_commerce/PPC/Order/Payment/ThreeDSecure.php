<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class ThreeDSecure
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class ThreeDSecure extends JSON
{
    public const EM_READY_TO_COMPLETE = 'Y';  // Bank are ready to complete a 3D Secure authentication.
    public const EM_NOT_READY         = 'N';  // Bank are not ready to complete a 3D Secure authentication.
    public const EM_UNAVAILABLE       = 'U';  // System is unavailable at the time of the request.
    public const EM_BYPASSED          = 'B';  // System has bypassed authentication.

    public const AS_SUCCESS            = 'Y';  // Successful authentication.
    public const AS_FAILED             = 'N';  // Failed authentication.
    public const AS_REJECTED           = 'R';  // Rejected authentication.
    public const AS_ATTEMPTED          = 'A';  // Attempted authentication.
    public const AS_NOT_COMPLETED      = 'U';  // Unable to complete authentication.
    public const AS_CHALLENGE_REQUIRED = 'C';  // Challenge required for authentication.
    public const AS_INFORMATION        = 'I';  // Information only.
    public const AS_DECOUPLED          = 'D';  // Decoupled authentication.

    /**
     * ThreeDSecure constructor
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @return string
     */
    public function getEnrollmentStatus(): string
    {
        return $this->getData()->enrollment_status ?? self::EM_UNAVAILABLE;
    }

    /**
     * @param string $enrollmentStatus
     * @return self
     */
    public function setEnrollmentStatus(string $enrollmentStatus): self
    {
        $this->data->enrollment_status = $enrollmentStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthenticationStatus(): string
    {
        return $this->getData()->authentication_status ?? self::AS_NOT_COMPLETED;
    }

    /**
     * @param string $authenticationStatus
     * @return self
     */
    public function setAuthenticationStatus(string $authenticationStatus): self
    {
        $this->data->authentication_status = $authenticationStatus;

        return $this;
    }
}
