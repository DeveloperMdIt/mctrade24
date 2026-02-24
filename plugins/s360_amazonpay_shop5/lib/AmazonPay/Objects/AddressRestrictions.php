<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class AddressRestrictions
 *
 * Used to define country based restrictions for delivery addresses.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects
 */
class AddressRestrictions extends AbstractObject {
    public const TYPE_ALLOWED = 'Allowed';
    public const TYPE_NOT_ALLOWED = 'NotAllowed';
    protected const TYPE_UNDEFINED = 'Undefined';

    /**
     * Specifies whether addresses that match restrictions configuration should or should not be restricted
     *
     * Note: Amazon will only validate this value in Sandbox. This parameter is ignored in the Live environment if an unsupported value is used
     *
     * @var string $type
     */
    protected $type;

    /**
     * Hash (Assoc Array!) of country-level restrictions that determine which addresses should or should not be restricted based on addressRestrictions.type parameter.
     *
     * CountryCode is a string that represents the country code of the address in ISO 3166 format. (This is the key of the assoc array.)
     * Amazon will only validate CountryCode in Sandbox.
     * CountryCode is ignored in the Live environment if an unsupported value is used
     *
     * @var Restriction[] $restrictions
     */
    protected $restrictions;

    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    private function fillFromArray($data): void {
        $this->type = $data['type'] ?? self::TYPE_UNDEFINED;
        $this->restrictions = [];
        if(isset($data['restrictions']) && \is_array($data['restrictions'])) {
            foreach($data['restrictions'] as $key => $value) {
                $this->restrictions[$key] = new Restriction($value);
            }
        }
    }

    public function toArray(): array {
        $result = [];
        if(null !== $this->type) {
            $result['type'] = $this->type;
        }
        if(null !== $this->restrictions && \count($this->restrictions) > 0) {
            $result['restrictions'] = [];
            foreach($this->restrictions as $key => $value) {
                $result['restrictions'][$key] = $value->toArray();
            }
        }
        return $result;
    }

}