<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects;

/**
 * Class WebCheckoutDetails
 *
 * URLs associated to the Checkout Session used for completing checkout
 *
 * @package Plugin\s360_amazonpay_shop5\lib\AmazonPayObjects
 */
class WebCheckoutDetails extends AbstractObject {
    /**
     * Checkout review URL provided by the merchant.
     * Amazon Pay will redirect to this URL after the buyer selects their preferred payment instrument and shipping address
     *
     * Note: In the Live environment, URLs must use HTTPS protocol. In Sandbox environment, you don't need a SSL certificate and can use the HTTP protocol
     *
     * Max length: 512 characters
     * @var string $checkoutReviewReturnUrl
     */
    protected $checkoutReviewReturnUrl;

    /**
     * Checkout result URL provided by the merchant. Amazon Pay will redirect to this URL after completing the transaction
     *
     * Note: In the Live environment, URLs must use HTTPS protocol. In Sandbox environment, you don't need a SSL certificate and can use the HTTP protocol
     *
     * Max length: 512 characters
     * @var string $checkoutResultReturnUrl
     */
    protected $checkoutResultReturnUrl;

    /**
     * URL provided by Amazon Pay. Merchant will redirect to this page after setting transaction details to complete checkout
     *
     * Max length: 256 characters
     *
     * @var string $amazonPayRedirectUrl
     */
    protected $amazonPayRedirectUrl;

    /**
     * Specify whether the buyer will return to your website to review their order before completing checkout.
     *
     * Supported values:
     * - 'ProcessOrder' - Buyer will complete checkout on the Amazon Pay hosted page immediately after clicking on the Amazon Pay button.
     * paymentDetails is required when using 'ProcessOrder'.
     * addressDetails is also required if you use 'ProcessOrder' with productType set to 'PayAndShip'
     *
     * @var string $checkoutMode
     */
    protected $checkoutMode;

    /**
     * @return string
     */
    public function getCheckoutReviewReturnUrl(): string {
        return $this->checkoutReviewReturnUrl;
    }

    /**
     * @param string $checkoutReviewReturnUrl
     */
    public function setCheckoutReviewReturnUrl(string $checkoutReviewReturnUrl): void {
        $this->checkoutReviewReturnUrl = $checkoutReviewReturnUrl;
    }

    /**
     * @return string
     */
    public function getCheckoutResultReturnUrl(): string {
        return $this->checkoutResultReturnUrl;
    }

    /**
     * @param string $checkoutResultReturnUrl
     */
    public function setCheckoutResultReturnUrl(string $checkoutResultReturnUrl): void {
        $this->checkoutResultReturnUrl = $checkoutResultReturnUrl;
    }

    /**
     * @return string
     */
    public function getAmazonPayRedirectUrl(): string {
        return $this->amazonPayRedirectUrl;
    }

    /**
     * @param null|string $amazonPayRedirectUrl
     */
    public function setAmazonPayRedirectUrl(?string $amazonPayRedirectUrl): void {
        $this->amazonPayRedirectUrl = $amazonPayRedirectUrl;
    }

    public function toArray(): array {
        $result = [];
        if(null !== $this->checkoutResultReturnUrl) {
            $result['checkoutResultReturnUrl'] = $this->checkoutResultReturnUrl;
        }
        if(null !== $this->checkoutReviewReturnUrl) {
            $result['checkoutReviewReturnUrl'] = $this->checkoutReviewReturnUrl;
        }
        if(null !== $this->amazonPayRedirectUrl) {
            $result['amazonPayRedirectUrl'] = $this->amazonPayRedirectUrl;
        }
        if(null !== $this->checkoutMode) {
            $result['checkoutMode'] = $this->checkoutMode;
        }
        return $result;
    }

    public function __construct(array $data = null) {
        if($data === null) {
            return;
        }
        $this->fillFromArray($data);
    }

    private function fillFromArray($data) {
        $this->checkoutResultReturnUrl = $data['checkoutResultReturnUrl'] ?? null;
        $this->checkoutReviewReturnUrl = $data['checkoutReviewReturnUrl'] ?? null;
        $this->amazonPayRedirectUrl = $data['amazonPayRedirectUrl'] ?? null;
        $this->checkoutMode = $data['checkoutMode'] ?? null;
    }

}