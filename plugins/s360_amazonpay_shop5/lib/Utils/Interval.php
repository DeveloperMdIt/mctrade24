<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use Exception;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Frequency;

/**
 * This class provides utility methods to handle intervals and frequencies and the basic data for an interval.
 */
class Interval {

    public const INTERVAL_UNIT_DAY = 'd';
    public const INTERVAL_UNIT_WEEK = 'w';
    public const INTERVAL_UNIT_MONTH = 'm';
    public const INTERVAL_UNIT_YEAR = 'y';

    /**
     * @var int $value
     */
    protected $value;

    /**
     * @var string $unit
     */
    protected $unit;

    public function __construct($value, $unit) {
        $this->value = (int)$value;
        $this->unit = $unit;
        $this->normalize();
    }

    /**
     * @return int
     */
    public function getValue(): int {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getUnit(): string {
        return $this->unit;
    }

    public static function attributeToIntervals($attributeString): array {
        $intervals = [];
        $intervalStrings = array_map('trim', explode(',', $attributeString));
        if (empty($intervalStrings)) {
            return [];
        }
        foreach ($intervalStrings as $intervalString) {
            if (preg_match('/\s*(\d+)([dwmy])\s*/i', $intervalString, $matches)) {
                $intervals[] = new Interval($matches[1], $matches[2]);
            }
        }
        return $intervals;
    }

    public static function unitToAmazonUnit(string $unit): string {
        if ($unit === self::INTERVAL_UNIT_DAY) {
            return Frequency::UNIT_DAY;
        }
        if ($unit === self::INTERVAL_UNIT_WEEK) {
            return Frequency::UNIT_WEEK;
        }
        if ($unit === self::INTERVAL_UNIT_MONTH) {
            return Frequency::UNIT_MONTH;
        }
        if ($unit === self::INTERVAL_UNIT_YEAR) {
            return Frequency::UNIT_YEAR;
        }
        return Frequency::UNIT_VARIABLE;
    }

    public static function unitFromAmazonUnit(string $unit): ?string {
        if ($unit === Frequency::UNIT_DAY) {
            return self::INTERVAL_UNIT_DAY;
        }
        if ($unit === Frequency::UNIT_WEEK) {
            return self::INTERVAL_UNIT_WEEK;
        }
        if ($unit === Frequency::UNIT_MONTH) {
            return self::INTERVAL_UNIT_MONTH;
        }
        if ($unit === Frequency::UNIT_YEAR) {
            return self::INTERVAL_UNIT_YEAR;
        }
        return null;
    }

    public function toAmazonFrequency(): Frequency {
        return new Frequency([
            'value' => (string)$this->value,
            'unit' => self::unitToAmazonUnit($this->unit)
        ]);
    }

    public static function fromAmazonFrequency(?Frequency $frequency): ?Interval {
        if($frequency === null) {
            return null;
        }
        $unit = self::unitFromAmazonUnit($frequency->getUnit());
        if($unit === null) {
            return null;
        }
        return new self($frequency->getValue(), $unit);
    }

    /**
     * Returns an integer less than, equal to, or greater than zero if this is considered to be respectively less than, equal to, or greater than the argument
     * @param Interval $interval
     * @return int
     */
    public function compareTo(Interval $interval): int {
        return $this->toDays() - $interval->toDays();
    }

    /**
     * Compares another Interval to this interval.
     * Intervals are always normalized, so we only need to compare the unit and value.
     *
     * @param Interval $other
     * @return bool
     */
    public function equals(Interval $other): bool {
        return $this->getUnit() === $other->getUnit() && $this->getValue() === $other->getValue();
    }

    /**
     * Checks if this interval contains another interval (e.g. 3w contains 1w, but also 3d, but not 2w, BUT: 1m does not contain 1w or 2w!)
     */
    public function contains(Interval $other): bool {
        // if both intervals have the same unit, comparing is easy
        if ($this->unit === $other->unit) {
            return $this->value >= $other->value && $this->value % $other->value === 0;
        }
        // Containing another interval works backwards from our own interval
        if ($this->unit === self::INTERVAL_UNIT_YEAR && $other->unit === self::INTERVAL_UNIT_MONTH && $other->value % 12 === 0) {
            $otherYears = (int)($other->value / 12);
            return $this->value >= $otherYears && $this->value % $otherYears === 0;
        }
        if ($this->unit === self::INTERVAL_UNIT_WEEK && $other->unit === self::INTERVAL_UNIT_DAY && $other->value % 7 === 0) {
            $otherWeeks = (int)($other->value / 7);
            return $this->value >= $otherWeeks && $this->value % $otherWeeks === 0;
        }
        return false;
    }

    /**
     * Normalize this interval such that:
     * n*12m => n*1y
     * n*7d => n*1w
     *
     * Note: We do not normalize 4w to 1m as this is NOT the same
     */
    protected function normalize(): Interval {
        if ($this->unit === self::INTERVAL_UNIT_DAY && $this->value % 7 === 0) {
            $this->unit = self::INTERVAL_UNIT_WEEK;
            $this->value = (int)($this->value / 7);
        }
        if ($this->unit === self::INTERVAL_UNIT_MONTH && $this->value % 12 === 0) {
            $this->unit = self::INTERVAL_UNIT_YEAR;
            $this->value = (int)($this->value / 12);
        }
        return $this;
    }

    /**
     * Creates an object of this for a single given interval (i.e. 1w)
     * Returns null for invalid formats.
     * @param $intervalString
     * @return Interval|null
     */
    public static function fromString($intervalString): ?Interval {
        if (preg_match('/\s*(\d+)([dwmy])\s*/i', trim($intervalString), $matches)) {
            return (new Interval($matches[1], $matches[2]))->normalize();
        }
        return null;
    }

    /**
     * Returns a string representation of this.
     * @return string
     */
    public function toString(): string {
        return (string)$this->value . $this->unit;
    }

    public function toDisplayString(): string {
        if($this->getValue() === 1) {
            return str_replace('#amount#', (string) $this->getValue(), Plugin::getInstance()->getLocalization()->getTranslation('subscription_interval_display_singular_' . $this->getUnit()));
        }
        return str_replace('#amount#', (string) $this->getValue(), Plugin::getInstance()->getLocalization()->getTranslation('subscription_interval_display_plural_' . $this->getUnit()));
    }

    public function toDateIntervalString(): string {
        return 'P' . $this->value . mb_strtoupper($this->unit);
    }

    /**
     * Adds this to the given timestamp and returns the resulting timestamp
     * @param int $sourceTimestamp
     * @return int
     * @throws Exception
     */
    public function addToTimestamp(int $sourceTimestamp): int {
        $targetDatetime = new \DateTime();
        $targetDatetime->setTimestamp($sourceTimestamp);
        $targetDatetime->add(new \DateInterval($this->toDateIntervalString()));
        return $targetDatetime->getTimestamp();
    }

    /**
     * Returns a single array of intervals after intersecting all arrays in the $intervalArray
     * @param array $intervalArrays
     * @return array
     */
    public static function intersect(array $intervalArrays): array {
        $result = [];
        /*
         * How do we intersect?
         * - Create a list of candidate intervals where ALL intervals are contained, but each interval is contained at max once.
         * - Then compare this list to each array of intervals and reduce the candidate intervals when an interval is not contained in the array (or is not a multiple of one of the intervals in the array!)
         */
        // Build list of all candidates; Note that normalizing and using an assoc array results in eliminating duplicates from the get-go.
        foreach($intervalArrays as $intervalArray) {
            foreach($intervalArray as $interval) {
                /** @var Interval $interval */
                $normalizedInterval = $interval->normalize();
                $result[$normalizedInterval->toString()] = $normalizedInterval;
            }
        }
        // Now compare the list of candidates to each array individually
        foreach($result as $key => $candidate) {
            foreach($intervalArrays as $intervalArray) {
                // we need to find at least one match in the current array of intervals
                foreach($intervalArray as $interval) {
                    /** @var Interval $interval */
                    $normalizedInterval = $interval->normalize();
                    if($candidate->equals($normalizedInterval) || $candidate->contains($normalizedInterval)) {
                        continue 2; // we found at least one match in this array, no need to check it further, move on to the next array of intervals
                    }
                }
                // When we got here, we did not find a match and have to remove this candidate
                unset($result[$key]);
                continue 2; // we removed this candidate, move on to the next candidate and skip all other arrays of intervals
            }
        }
        // Resolve the key mapping before returning
        return array_values($result);
    }

    /**
     * Returns the estimated number of days for this interval.
     * Estimated because months is not clearly defined.
     * @return int
     */
    protected function toDays(): int {
        $days = $this->getValue();
        if($this->getUnit() === self::INTERVAL_UNIT_WEEK) {
            $days = 7 * $days;
        }
        if($this->getUnit() === self::INTERVAL_UNIT_MONTH) {
            $days = 30 * $days;
        }
        if($this->getUnit() === self::INTERVAL_UNIT_YEAR) {
            $days = 365 * $days;
        }
        return $days;
    }

    public function getEstimatedMonthlyOccurrence(): float {
        $days = (float) $this->toDays();
        return 30.0 / $days;
    }
}