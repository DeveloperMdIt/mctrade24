<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class Constraint
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class Constraint extends AbstractObject {

    /* checkoutResultReturnURL has not been set on the Checkout Session */
    public const CODE_CHECKOUT_RESULT_RETURN_URL_NOT_SET = 'CheckoutResultReturnUrlNotSet';
    /* chargeAmount has not been set on the Checkout Session */
    public const CODE_CHARGE_AMOUNT_NOT_SET = 'ChargeAmountNotSet';
    /* paymentIntent has not been set on the Checkout Session */
    public const CODE_PAYMENT_INTENT_NOT_SET = 'PaymentIntentNotSet';
    /* Buyer-preferred payment instrument or shipping address has not been set on the Checkout Session */
    public const CODE_BUYER_NOT_ASSOCIATED = 'BuyerNotAssociated';

    /**
     * Code for any constraint
     * @var string $constraintId
     */
    protected $constraintId;

    /**
     * Description of the constraint
     * @var string $description
     */
    protected $description;

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    protected function fillFromArray($data) {
        $this->constraintId = $data['constraintId'] ?? null;
        $this->description = $data['description'] ?? null;
    }

    /**
     * @return string
     */
    public function getConstraintId(): string {
        return $this->constraintId;
    }

    /**
     * @param string $constraintId
     */
    public function setConstraintId(string $constraintId) {
        $this->constraintId = $constraintId;
    }

    /**
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description) {
        $this->description = $description;
    }


}