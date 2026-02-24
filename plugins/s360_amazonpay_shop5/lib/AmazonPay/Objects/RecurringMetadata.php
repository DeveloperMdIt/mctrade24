<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class RecurringMetadata
 *
 * Metadata about how the recurring Charge Permission will be used. Amazon Pay only uses this information to calculate the Charge Permission expiration date and in buyer communication.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class RecurringMetadata extends AbstractObject {

    /**
     * Frequency at which the buyer will be charged using a recurring Charge Permission. You should specify a frequency even if you expect ad hoc charges
     *
     * @var Frequency $frequency
     */
    protected $frequency;

    /**
     * Amount the buyer will be charged for each recurring cycle. Set to null if amount varies
     *
     * @var Price $amount
     */
    protected $amount;

    /**
     * @return Frequency
     */
    public function getFrequency(): Frequency {
        return $this->frequency;
    }

    /**
     * @param Frequency $frequency
     */
    public function setFrequency(Frequency $frequency): void {
        $this->frequency = $frequency;
    }

    /**
     * @return Price
     */
    public function getAmount(): Price {
        return $this->amount;
    }

    /**
     * @param Price $amount
     */
    public function setAmount(Price $amount): void {
        $this->amount = $amount;
    }

    /**
     * @return array
     */
    public function toArray(): array {
        $result = [];
        if(null !== $this->frequency) {
            $result['frequency'] = $this->frequency->toArray();
        }
        if(null !== $this->amount) {
            $result['amount'] = $this->amount->toArray();
        }
        return $result;
    }

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data): void {
        $this->frequency = isset($data['frequency']) ? new Frequency($data['frequency']) : null;
        $this->amount = isset($data['amount']) ? new Price($data['amount']) : null;
    }
}