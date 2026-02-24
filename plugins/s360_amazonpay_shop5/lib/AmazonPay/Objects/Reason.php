<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Reason
 *
 * A single reason for a status.
 * Note that this kind of object may not exist in every payment object. the StatusDetails in CheckoutSessions, for example, has a single reasonCode and reasonDescription.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects
 */
class Reason {

    /**
     * Reason code for current state
     * @var string $reasonCode
     */
    protected $reasonCode;

    /**
     * Optional description for the reason
     * @var string|null $reasonDescription
     */
    protected $reasonDescription;

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    private function fillFromArray($data): void {
        $this->reasonCode = $data['reasonCode'] ?? null;
        $this->reasonDescription = isset($data['reasonDescription']) && \is_array($data['reasonDescription']) ? new Price($data['reasonDescription']) : null;
    }

    /**
     * @return string
     */
    public function getReasonCode(): string {
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


}