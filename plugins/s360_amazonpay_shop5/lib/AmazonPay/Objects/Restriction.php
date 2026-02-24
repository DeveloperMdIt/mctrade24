<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Restriction
 *
 * Detailed restrictions "within" a specific country.
 * Note that this object may also be empty.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects
 */
class Restriction extends AbstractObject {
    /**
     * List of country-specific states that should or should not be restricted based on addressRestrictions.type parameter
     *
     * Note:
     * US addresses - Use 2-character state codes (for example: WA, CA, IL)
     * All other countries - This element is free text. Include all applicable variants: 2-character code, fully spelled out, and abbreviated
     *
     * @var string[]|null
     */
    protected $statesOrRegions;

    /**
     * List of country-specific zip codes that should or should not be restricted based on addressRestrictions.type parameter.
     *
     * @var string[]|null
     */
    protected $zipCodes;

    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    private function fillFromArray($data): void {
        $this->statesOrRegions = $data['statesOrRegions'] ?? null;
        $this->zipCodes = $data['zipCodes'] ?? null;
    }

    public function toArray() {
        $result = [];
        if(null !== $this->statesOrRegions && \count($this->statesOrRegions) > 0) {
            $result['statesOrRegions'] = $this->statesOrRegions;
        }
        if(null !== $this->zipCodes && \count($this->zipCodes) > 0) {
            $result['zipCodes'] = $this->zipCodes;
        }
        // Note: empty restrictions are defined as empty object in json, not empty array, therefore we cast this
        return \count($result) > 0 ? $result : (object)$result;
    }
}