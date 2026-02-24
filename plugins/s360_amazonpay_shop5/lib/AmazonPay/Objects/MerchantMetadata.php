<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class MerchantMetadata
 *
 * Merchant-provided order info
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class MerchantMetadata extends AbstractObject {
    /**
     * External merchant order identifer. The merchant order identifer is shared in buyer communication and in the buyer transaction history on the Amazon Pay website
     *
     * Max length: 256 characters
     * @var string|null $merchantReferenceId
     */
    protected $merchantReferenceId;

    /**
     * Merchant store name.
     * Setting this parameter will override the default value configured in Seller Central (the account management tool for merchants).
     * The store name is shared in buyer communication and in the buyer transaction history on the Amazon Pay website
     *
     * Max length: 50 characters
     * @var string|null $merchantStoreName
     */
    protected $merchantStoreName;

    /**
     * Description of the order that is shared in buyer communication
     *
     * You should not store sensitive data about the buyer or the transaction in this field
     *
     * Max length: 255 characters
     * @var string|null $noteToBuyer
     */
    protected $noteToBuyer;

    /**
     * Custom info for the order. This data is not shared in any buyer communication
     *
     * You should not store sensitive data about the buyer or the transaction in this field
     *
     * Max length: 4096 characters
     * @var string|null $customInformation
     */
    protected $customInformation;

    /**
     * @return null|string
     */
    public function getMerchantReferenceId(): ?string {
        return $this->merchantReferenceId;
    }

    /**
     * @param null|string $merchantReferenceId
     */
    public function setMerchantReferenceId($merchantReferenceId): void {
        $this->merchantReferenceId = $merchantReferenceId;
    }

    /**
     * @return null|string
     */
    public function getMerchantStoreName(): ?string {
        return $this->merchantStoreName;
    }

    /**
     * @param null|string $merchantStoreName
     */
    public function setMerchantStoreName($merchantStoreName): void {
        $this->merchantStoreName = $merchantStoreName;
    }

    /**
     * @return null|string
     */
    public function getNoteToBuyer(): ?string {
        return $this->noteToBuyer;
    }

    /**
     * @param null|string $noteToBuyer
     */
    public function setNoteToBuyer($noteToBuyer): void {
        $this->noteToBuyer = $noteToBuyer;
    }

    /**
     * @return null|string
     */
    public function getCustomInformation(): ?string {
        return $this->customInformation;
    }

    /**
     * @param null|string $customInformation
     */
    public function setCustomInformation($customInformation): void {
        $this->customInformation = $customInformation;
    }

    public function toArray(): array {
        $result = [];
        if(null !== $this->merchantReferenceId) {
            $result['merchantReferenceId'] = $this->merchantReferenceId;
        }
        if(null !== $this->merchantStoreName) {
            $result['merchantStoreName'] = $this->merchantStoreName;
        }
        if(null !== $this->noteToBuyer) {
            $result['noteToBuyer'] = $this->noteToBuyer;
        }
        if(null !== $this->customInformation) {
            $result['customInformation'] = $this->customInformation;
        }
        return $result;
    }

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->merchantReferenceId = $data['merchantReferenceId'] ?? null;
        $this->merchantStoreName = $data['merchantStoreName'] ?? null;
        $this->noteToBuyer = $data['noteToBuyer'] ?? null;
        $this->customInformation = $data['customInformation'] ?? null;
    }
}