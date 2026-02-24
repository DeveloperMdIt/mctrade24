<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class DeliverySpecifications
 *
 * Specify shipping restrictions and limit which addresses your buyer can select from their Amazon address book.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects
 */
class DeliverySpecifications extends AbstractObject {

    /* Marks PO box addresses in US, CA, GB, FR, DE, ES, PT, IT, AU as restricted */
    public const SPECIAL_RESTRICTION_PO_BOXES = 'RestrictPOBoxes';

    /* Marks packstation addresses in DE as restricted */
    public const SPECIAL_RESTRICTION_PACKSTATIONS = 'RestrictPackstations';

    /**
     * Rule-based restrictions
     *
     * Note: Amazon will only validate this value in Sandbox. This parameter is ignored in the Live environment if an unsupported value is used
     *
     * @var string[] $specialRestrictions
     */
    protected $specialRestrictions;

    /**
     * Country-based restrictions
     * @var AddressRestrictions|null $addressRestrictions
     */
    protected $addressRestrictions;

    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    private function fillFromArray($data): void {
        $this->specialRestrictions = $data['specialRestrictions'] ?? [];
        if(isset($data['addressRestrictions'])) {
            $this->addressRestrictions = new AddressRestrictions($data['addressRestrictions']);
        }
    }

    public function toArray(): array {
        $result = [];
        if(null !== $this->specialRestrictions && \count($this->specialRestrictions) > 0) {
            $result['specialRestrictions'] = $this->specialRestrictions;
        }
        if(null !== $this->addressRestrictions) {
            $result['addressRestrictions'] = $this->addressRestrictions->toArray();
        }
        return $result;
    }

}