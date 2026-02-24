<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class PaymentPreference
 *
 * Payment instrument selected by the buyer
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class PaymentPreference extends AbstractObject {
    /**
     * Amazon Pay-provided description for buyer-selected payment instrument
     * @var string $paymentDescriptor
     */
    protected $paymentDescriptor;

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->paymentDescriptor = $data['paymentDescriptor'] ?? null;
    }

    /**
     * @return string
     */
    public function getPaymentDescriptor(): string {
        return $this->paymentDescriptor;
    }
}