<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Frequency. A frequency, for example as used for recurring payments.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class Frequency extends AbstractObject {

    public const UNIT_YEAR = 'Year';
    public const UNIT_MONTH = 'Month';
    public const UNIT_WEEK = 'Week';
    public const UNIT_DAY = 'Day';
    public const UNIT_VARIABLE = 'Variable';

    /**
     *
     * Frequency unit for each billing cycle. For multiple subscriptions, specify the frequency unit for the shortest billing cycle.
     * Only use Variable if you charge the buyer on an irregular cadence, see handling variable cadence for more info
     *
     * Supported values: 'Year', 'Month', 'Week', 'Day', 'Variable'
     *
     * @var string
     */
    protected $unit;

    /**
     * Number of frequency units per billing cycle.
     * For example, to specify a weekly cycle set unit to Week and value to 1.
     *
     * You must set value to 0 if you're using variable unit
     *
     * @var string value
     */
    protected $value;

    public function toArray(): array {
        return [
            'unit' => $this->unit,
            'value' => $this->value
        ];
    }

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data): void {
        $this->unit = $data['unit'] ?? null;
        $this->value = $data['value'] ?? null;
    }

    /**
     * @return string
     */
    public function getUnit(): string {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit(string $unit): void {
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function getValue(): string {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void {
        $this->value = $value;
    }


}