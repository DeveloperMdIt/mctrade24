<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC\Order\Payment;

use Plugin\jtl_paypal_commerce\PPC\Request\Serializer\JSON;

/**
 * Class AuthResult
 * @package Plugin\jtl_paypal_commerce\PPC\Order\Payment
 */
class AuthResult extends JSON
{
    public const LB_POSSIBLE = 'POSSIBLE';  // Liability might shift to the card issuer.
    public const LB_NO       = 'NO';        // Liability is with the merchant.
    public const LB_UNKNOWN  = 'UNKNOWN';   // The authentication system is not available.

    public const AUTHACTION_CONTINUE = 'Y';  // Continue with authorization.
    public const AUTHACTION_REJECT   = 'N';  // Do not continue with authorization.

    public const AUTHACTION_ERROR            = '3DSError';
    public const AUTHACTION_CANCEL           = '3DSCancel';
    public const AUTHACTION_SKIP             = '3DSSkip';
    public const AUTHACTION_NOTSUPPORTED     = '3DSNotSupported';
    public const AUTHACTION_UNABLETOCOMPLETE = '3DSUnableToComplete';
    public const AUTHACTION_NOTELIGIBLE      = '3DSNotEligible';

    /**
     * AuthResult constructor
     * @param object|null $data
     */
    public function __construct(?object $data = null)
    {
        parent::__construct($data ?? (object)[]);
    }

    /**
     * @inheritDoc
     */
    public function setData(string|array|object $data): static
    {
        parent::setData($data);

        $threeDSecure = $this->getData()->three_d_secure ?? null;
        if ($threeDSecure !== null && !($threeDSecure instanceof ThreeDSecure)) {
            $this->setThreeDSecure(new ThreeDSecure($threeDSecure));
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLiabilityShift(): string
    {
        return $this->getData()->liability_shift ?? self::LB_UNKNOWN;
    }

    /**
     * @param string $liabilityShift
     * @return self
     */
    public function setLiabilityShift(string $liabilityShift): self
    {
        $this->data->liability_shift = $liabilityShift;

        return $this;
    }

    /**
     * @return ThreeDSecure|null
     */
    public function getThreeDSecure(): ?ThreeDSecure
    {
        return $this->getData()->three_d_secure ?? null;
    }

    /**
     * @param ThreeDSecure $threeDSecure
     * @return self
     */
    public function setThreeDSecure(ThreeDSecure $threeDSecure): self
    {
        $this->data->three_d_secure = $threeDSecure;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthAction(): string
    {
        $threeDSecure = $this->getThreeDSecure();
        if ($threeDSecure === null) {
            return self::AUTHACTION_REJECT;
        }

        $enrollment = $threeDSecure->getEnrollmentStatus();
        $authState  = $threeDSecure->getAuthenticationStatus();
        $liability  = $this->getLiabilityShift();

        /*
         * https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/
         * Continue with authorization in this cases:
         * Enrollment=Y & Authstate=Y & Liability=POSSIBLE
         * Enrollment=Y & Authstate=A & Liability=POSSIBLE
         * Enrollment=N & Liability=NO
         * Enrollment=U & Liability=NO
         * Enrollment=B & Liability=NO
         *
         * can be simplified to
         * Enrollment=Y & Authstate=Y|A & Liability=POSSIBLE
         * Enrollment=N|U|B & Liability=NO
         */
        $validEnrollment = $enrollment === ThreeDSecure::EM_READY_TO_COMPLETE
            && \in_array($authState, [ThreeDSecure::AS_SUCCESS, ThreeDSecure::AS_ATTEMPTED], true)
            && $liability === self::LB_POSSIBLE;
        $validLiability  = \in_array($enrollment, [
                ThreeDSecure::EM_NOT_READY,
                ThreeDSecure::EM_UNAVAILABLE,
                ThreeDSecure::EM_BYPASSED
            ], true)
            && $liability === self::LB_NO;
        if ($validEnrollment || $validLiability) {
            return self::AUTHACTION_CONTINUE;
        }

        return self::AUTHACTION_REJECT;
    }
}
