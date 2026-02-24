<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Limits
 *
 * Charge Permission transaction limits
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects
 */
class Limits extends AbstractObject {

    /**
     * Total amount that can be charged using this Charge Permission
     * @var Price $amountLimit
     */
    protected $amountLimit;

    /**
     * Remaining balance that can be charged using this Charge Permission
     * @var Price amountBalance
     */
    protected $amountBalance;

    public function __construct(array $data = null) {
        if ($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    /**
     * @return mixed
     */
    public function getAmountLimit() {
        return $this->amountLimit;
    }

    /**
     * @param mixed $amountLimit
     */
    public function setAmountLimit($amountLimit) {
        $this->amountLimit = $amountLimit;
    }

    /**
     * @return mixed
     */
    public function getAmountBalance() {
        return $this->amountBalance;
    }

    /**
     * @param mixed $amountBalance
     */
    public function setAmountBalance($amountBalance) {
        $this->amountBalance = $amountBalance;
    }



    protected function fillFromArray($data) {
        $this->amountLimit = isset($data['amountLimit']) && \is_array($data['amountLimit']) ? new Price($data['amountLimit']) : null;
        $this->amountBalance = isset($data['amountBalance']) && \is_array($data['amountBalance']) ? new Price($data['amountBalance']) : null;
    }
}