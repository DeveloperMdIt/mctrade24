<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class PaymentDetails
 *
 * Payment details specified by the merchant, such as the amount and method for charging the buyer
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class PaymentDetails extends AbstractObject {

    public const PAYMENT_INTENT_CONFIRM = 'Confirm';
    public const PAYMENT_INTENT_AUTHORIZE = 'Authorize';
    // this is the immediate capture intent
    public const PAYMENT_INTENT_AUTHORIZED_WITH_CAPTURE = 'AuthorizeWithCapture';

    /**
     * Payment flow for charging the buyer
     *
     * Possible values: Confirm or Authorize
     * @var string|null $paymentIntent
     */
    protected $paymentIntent;

    /**
     * Boolean that indicates whether merchant can handle pending response
     *
     * See asynchronous processing for more info
     *
     * @var bool|null $canHandlePendingAuthorization
     */
    protected $canHandlePendingAuthorization;

    /**
     * @var Price|null $chargeAmount
     */
    protected $chargeAmount;

    /**
     * The currency that the buyer will be charged in ISO 4217 format. Example: USD
     *
     * NOTE: This value can ONLY be set on CREATE checkout session. Changes to this value during updates have to happen via the chargeAmount's currency!
     *
     * @var string $presentmentCurrency
     */
    protected $presentmentCurrency;

    /**
     * @return null|string
     */
    public function getPaymentIntent(): ?string {
        return $this->paymentIntent;
    }

    /**
     * @param null|string $paymentIntent
     */
    public function setPaymentIntent($paymentIntent): void {
        $this->paymentIntent = $paymentIntent;
    }

    /**
     * @return bool|null
     */
    public function getCanHandlePendingAuthorization(): ?bool {
        return $this->canHandlePendingAuthorization;
    }

    /**
     * @param bool|null $canHandlePendingAuthorization
     */
    public function setCanHandlePendingAuthorization($canHandlePendingAuthorization): void {
        $this->canHandlePendingAuthorization = $canHandlePendingAuthorization;
    }

    /**
     * @return null|Price
     */
    public function getChargeAmount(): ?Price {
        return $this->chargeAmount;
    }

    /**
     * @param null|Price $chargeAmount
     */
    public function setChargeAmount($chargeAmount): void {
        $this->chargeAmount = $chargeAmount;
    }

    /**
     * @return mixed
     */
    public function getPresentmentCurrency() {
        return $this->presentmentCurrency;
    }

    /**
     * @param mixed $presentmentCurrency
     */
    public function setPresentmentCurrency($presentmentCurrency) {
        $this->presentmentCurrency = $presentmentCurrency;
    }


    public function toArray(): array {
        $result = [];
        if(null !== $this->paymentIntent) {
            $result['paymentIntent'] = $this->paymentIntent;
        }
        if(null !== $this->canHandlePendingAuthorization) {
            $result['canHandlePendingAuthorization'] = $this->canHandlePendingAuthorization;
        }
        if(null !== $this->chargeAmount) {
            $result['chargeAmount'] = $this->chargeAmount->toArray();
        }
        if(null !== $this->presentmentCurrency) {
            $result['presentmentCurrency'] = $this->presentmentCurrency;
        }
        return $result;
    }

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->paymentIntent = $data['paymentIntent'] ?? null;
        $this->canHandlePendingAuthorization = $data['canHandlePendingAuthorization'] ?? null;
        $this->chargeAmount = isset($data['chargeAmount']) && \is_array($data['chargeAmount']) ? new Price($data['chargeAmount']) : null;
        $this->presentmentCurrency = $data['presentmentCurrency'] ?? null;
    }
}