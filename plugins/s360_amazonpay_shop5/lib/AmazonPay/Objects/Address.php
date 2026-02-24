<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Address
 *
 * A shipping or billing address.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class Address extends AbstractObject {
    /**
     * Address name
     *
     * Max length: 50 characters
     * @var string $name
     */
    protected $name;
    /**
     * The first line of the address
     *
     * Max length: 180 characters
     * @var string $addressLine1
     */
    protected $addressLine1;
    /**
     * The second line of the address
     *
     * Max length: 60 characters
     * @var string $addressLine2
     */
    protected $addressLine2;
    /**
     * The third line of the address
     *
     * Max length: 60 characters
     * @var string $addressLine3
     */
    protected $addressLine3;
    /**
     * City of the address
     *
     * Max length: 50 characters
     * @var string $city
     */
    protected $city;
    /**
     * County of the address
     *
     * Max length: 50 characters
     * @var string $county
     */
    protected $county;
    /**
     * District of the address
     *
     * Max length: 50 characters
     * @var string $district
     */
    protected $district;
    /**
     * The state or region.
     * This element is free text and can be either a 2-character code, fully spelled out, or abbreviated.
     *
     * Max length: 50 characters
     * @var string $stateOrRegion
     */
    protected $stateOrRegion;
    /**
     * Postal code of the address
     *
     * Max length: 20 characters
     * @var string $postalCode
     */
    protected $postalCode;
    /**
     * Country code of the address in ISO 3166 format
     *
     * Max length: 3 characters
     * @var string $countryCode
     */
    protected $countryCode;

    /**
     * Phone number
     *
     * Max length: 20 characters/bytes
     * @var string $phoneNumber
     */
    protected $phoneNumber;

    /**
     * Address constructor.
     * @param array|null $data
     */
    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray(array $data) {
        $this->name = $data['name'] ?? null;
        $this->addressLine1 = $data['addressLine1'] ?? null;
        $this->addressLine2 = $data['addressLine2'] ?? null;
        $this->addressLine3 = $data['addressLine3'] ?? null;
        $this->city = $data['city'] ?? null;
        $this->county = $data['country'] ?? null;
        $this->district = $data['district'] ?? null;
        $this->stateOrRegion = $data['stateOrRegion'] ?? null;
        $this->postalCode = $data['postalCode'] ?? null;
        $this->countryCode = $data['countryCode'] ?? null;
        $this->phoneNumber = $data['phoneNumber'] ?? null;
    }

    /**
     * @return string
     */
    public function getName(): ?string {
        return $this->name ?? '';
    }

    /**
     * @return string
     */
    public function getAddressLine1(): string {
        return $this->addressLine1 ?? '';
    }

    /**
     * @return string
     */
    public function getAddressLine2(): string {
        return $this->addressLine2 ?? '';
    }

    /**
     * @return string
     */
    public function getAddressLine3(): string {
        return $this->addressLine3 ?? '';
    }

    /**
     * @return string
     */
    public function getCity(): string {
        return $this->city ?? '';
    }

    /**
     * @return string
     */
    public function getCounty(): string {
        return $this->county ?? '';
    }

    /**
     * @return string
     */
    public function getDistrict(): string {
        return $this->district ?? '';
    }

    /**
     * @return string
     */
    public function getStateOrRegion(): string {
        return $this->stateOrRegion ?? '';
    }

    /**
     * @return string
     */
    public function getPostalCode(): string {
        return $this->postalCode ?? '';
    }

    /**
     * @return string
     */
    public function getCountryCode(): string {
        return $this->countryCode ?? '';
    }

    /**
     * @return string
     */
    public function getPhoneNumber(): string {
        return $this->phoneNumber ?? '';
    }


}