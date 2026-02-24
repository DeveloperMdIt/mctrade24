<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Price
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class Price extends AbstractObject {
    /**
     * Transaction amount
     * @var string $amount
     */
    protected $amount;

    /**
     * Transaction currency code in ISO 4217 format. Example: USD, EUR, GBP
     * @var string $currencyCode
     */
    protected $currencyCode;

    /**
     * @return string
     */
    public function getAmount(): string {
        return $this->amount;
    }

    /**
     * @param string $amount
     */
    public function setAmount(string $amount) {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCurrencyCode(): string {
        return $this->currencyCode;
    }

    /**
     * @param string $currencyCode
     */
    public function setCurrencyCode(string $currencyCode) {
        $this->currencyCode = $currencyCode;
    }

    public function toArray(): array {
        return [
            'amount' => $this->amount,
            'currencyCode' => $this->currencyCode
        ];
    }

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->amount = $data['amount'] ?? null;
        $this->currencyCode = $data['currencyCode'] ?? null;
    }

}