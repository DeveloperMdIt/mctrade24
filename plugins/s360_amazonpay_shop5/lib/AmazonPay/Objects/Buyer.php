<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Buyer
 *
 * Details about the buyer, such as their unique identifer, name, and email
 *
 * This info will only be returned for a Checkout Session in the Open state
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class Buyer extends AbstractObject {
    /**
     * Unique Amazon Pay buyer identifier
     * @var string $buyerId
     */
    protected $buyerId;

    /**
     * Buyer name
     * @var string $name
     */
    protected $name;

    /**
     * Buyer email address
     * @var string $email
     */
    protected $email;

    /**
     * Buyer default shipping address postal code
     * @var string $postalCode
     */
    protected $postalCode;

    /**
     * Buyer default shipping address country (2 chars, Uppercase)
     * @var string $countryCode
     */
    protected $countryCode;

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray(array $data) {
        $this->buyerId = $data['buyerId'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->postalCode = $data['postalCode'] ?? null;
        $this->countryCode = $data['countryCode'] ?? null;
    }

    /**
     * @return string
     */
    public function getBuyerId(): ?string {
        return $this->buyerId;
    }

    /**
     * @param string $buyerId
     */
    public function setBuyerId(string $buyerId) {
        $this->buyerId = $buyerId;
    }

    /**
     * @return string
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email) {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPostalCode(): ?string {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode(string $postalCode) {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getCountryCode(): ?string {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode(string $countryCode) {
        $this->countryCode = $countryCode;
    }

}