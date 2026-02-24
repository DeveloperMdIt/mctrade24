<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use Exception;
use JTL\Alert\Alert;
use JTL\Cart\Cart;
use JTL\Cart\PersistentCart;
use JTL\Checkout\Bestellung;
use JTL\Checkout\OrderHandler; // From 5.2.0-beta onwards
use JTL\Events\Dispatcher;
use JTL\Helpers\Date;
use JTL\Helpers\Text;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Charge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\ChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\MerchantMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Price;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\StatusDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CaptureCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CompleteCheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetCharge;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetChargePermission;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\UpdateChargePermission;
use Plugin\s360_amazonpay_shop5\lib\Mappers\AddressMapper;
use Plugin\s360_amazonpay_shop5\lib\Utils\Compatibility;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Currency;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;
use Plugin\s360_amazonpay_shop5\paymentmethod\AmazonPay;

/**
 * Class CheckoutResultController
 *
 * Handles returned users from Amazon Pay's SCA.
 * This controller is responsible for creating orders.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class CheckoutResultController {

    use JtlLoggerTrait;

    public const MODE_FAILURE = 'failure';
    public const MODE_SUCCESS = 'success';
    public const MODE_ERROR = 'error';

    /**
     * The mode we are operating in (success or failure).
     * @var string $mode
     */
    private $mode;
    private $isAdditionalPayButtonMode; // in this mode, we are an actual payment method and the source of delivery and billing address is the JTL Default logic!
    private $selectedSubscriptionInterval;
    private $request;
    private $config;
    private $database;
    private $paymentMethodModule;
    /** @var  ChargePermission $chargePermission */
    private $chargePermission;
    /** @var  Charge $charge */
    private $charge;

    public function __construct() {
        $this->request = Text::filterXSS($_REQUEST);
        $this->config = Config::getInstance();
        $this->database = Database::getInstance();
        $this->paymentMethodModule = new AmazonPay(AmazonPay::getModuleId(Plugin::getInstance()));
        $this->isAdditionalPayButtonMode = false;
        $this->selectedSubscriptionInterval = null;
    }

    public function handle(): void {
        Dispatcher::getInstance()->fire(Constants::EVENT_HANDLE_RESULT_START);
        $this->prepareMode();
        if ($this->mode === self::MODE_FAILURE) {
            $this->handleFailure();
        }
        if ($this->mode === self::MODE_SUCCESS) {
            $this->handleSuccess();
        }
        $this->handleError();
    }

    /**
     * Determines if we returned with a success or a failure.
     * We get the amazonCheckoutSessionId as Parameter in the request.
     */
    private function prepareMode(): void {
        if (empty($this->request['amazonCheckoutSessionId'])) {
            // this is a technical error, we do not know what happened, but most likely somebody just called this URL from a browser?
            $this->debugLog('Error: Called without amazonCheckoutSessionId', __CLASS__);
            $this->mode = self::MODE_ERROR;
            return;
        }
        $checkoutSessionId = $this->request['amazonCheckoutSessionId'];
        $checkoutSessionFromSession = SessionController::getActiveCheckoutSession();
        if (null === $checkoutSessionFromSession) {
            // This may be ok or not - depending on if Amazon Pay is displayed as normal payment method. We will load the checkout session from Amazon in the next steps, anyway.
            if($this->config->isHidePaymentMethod()) {
                // No, Amazon Pay should not be available as normal payment method.
                // This is another technical error - we were called but the user does not even have a checkout session active
                $this->debugLog('Error: Called without CheckoutSession in $_SESSION.', __CLASS__);
                $this->mode = self::MODE_ERROR;
                return;
            }
            $this->isAdditionalPayButtonMode = true;
        }
        /** @var CheckoutSession $checkoutSessionFromSession */
        if ($checkoutSessionFromSession !== null && $checkoutSessionFromSession->getStatusDetails()->getState() !== StatusDetails::STATUS_OPEN) {
            // the user does have a session but it is not open - did someone try to reload this page? In any case, we MUST handle this as error to prevent duplicate payments
            $this->debugLog('Error: Called with CheckoutSession in not OPEN state. Current state: ' . $checkoutSessionFromSession->getStatusDetails()->getState(), __CLASS__);
            $this->mode = self::MODE_ERROR;
            return;
        }
        /** @var CheckoutSession $checkoutSessionFromSession */
        if ($checkoutSessionFromSession !== null && $checkoutSessionFromSession->getCheckoutSessionId() !== $checkoutSessionId) {
            // the user does have a session but it is not the same id as in the request
            $this->debugLog('Error: Called with mismatched checkoutSessionId', __CLASS__);
            $this->mode = self::MODE_ERROR;
            return;
        }

        $this->selectedSubscriptionInterval = SessionController::get(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
        if($this->selectedSubscriptionInterval === null && $checkoutSessionFromSession !== null && $checkoutSessionFromSession->getChargePermissionType() === CheckoutSession::CHARGE_PERMISSION_TYPE_RECURRING) {
            // Recurring payment session but no interval selected in session - this can't be right.
            $this->debugLog('Error: Recurring charge permission, but no interval selected in session.', __CLASS__);
            $this->mode = self::MODE_ERROR;
            return;
        }

        /**
         * Now we get the checkout session from Amazon Pay by calling Complete Checkout Session.
         * By giving the "current" amount of the basket to this operation, we validate one final time that the charge we are going to charge is the same as the total basket amount and that the currency has not changed.
         * Else, this call will return an error!
         */
        $frontendCurrency = Frontend::getCurrency();
        $chargeAmount = new Price();
        $chargeAmount->setAmount(Currency::convertToAmazonString(Frontend::getCart()->gibGesamtsummeWaren(true) * $frontendCurrency->getConversionFactor()));
        $chargeAmount->setCurrencyCode($frontendCurrency->getCode());

        $adapter = new ApiAdapter();
        $request = new CompleteCheckoutSession($checkoutSessionId, $chargeAmount);
        try {
            $checkoutSessionFromAmazon = $adapter->execute($request);
            if ($checkoutSessionFromAmazon instanceof Error) {

                /**
                 * The new APIv2 handles all denial reasons by returning an Error.
                 * These all basically result in a cancel of the checkout, but with differing messages.
                 */
                $errorCode = $checkoutSessionFromAmazon->getHttpErrorCode();
                $errorReasonCode = $checkoutSessionFromAmazon->getReasonCode();
                if($errorCode >= 400 && $errorCode < 500) {
                    if($errorReasonCode === Error::REASON_CODE_CHECKOUT_SESSION_CANCELED) {
                        // The user canceled the transaction or was declined
                        $this->debugLog('Amazon Pay returned checkout session canceled by user. Aborting checkout.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
                    }
                    if($errorReasonCode === Error::REASON_CODE_CURRENCY_MISMATCH) {
                        $this->debugLog('Aborting checkout. Currency mismatch. Redirecting to basket.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_CHECKOUT_CURRENCY_CHANGED) . Frontend::getCurrency()->getCode());
                    }
                    if($errorReasonCode === Error::REASON_CODE_TRANSACTION_AMOUNT_EXCEEDED) {
                        $this->debugLog('Amazon Pay returned transaction amount exceeded. Aborting checkout.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
                    }
                    if($errorReasonCode === Error::REASON_CODE_AMOUNT_MISMATCH) {
                        $currentAmount = Frontend::getCart()->gibGesamtsummeWaren(true) * $frontendCurrency->getConversionFactor();
                        $this->debugLog('Aborting checkout. Amazon Pay returned amount mismatch. Tried to confirm amount: ' . $currentAmount . ', Redirecting to basket.', __CLASS__);
                        $this->abortCheckout(Shop::Lang()->get('yourbasketismutating', 'checkout'));
                    }
                    if($errorReasonCode === Error::REASON_CODE_INVALID_CHECKOUT_SESSION_STATUS) {
                        $this->debugLog('Amazon Pay returned invalid checkout session status. Aborting checkout.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
                    }
                    if($errorReasonCode === Error::REASON_CODE_SOFT_DECLINED) {
                        $this->debugLog('Amazon Pay returned soft decline. Aborting checkout.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
                    }
                    if($errorReasonCode === Error::REASON_CODE_HARD_DECLINED) {
                        $this->debugLog('Amazon Pay returned hard decline. Aborting checkout.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
                    }
                    if($errorReasonCode === Error::REASON_CODE_PAYMENT_METHOD_NOT_ALLOWED) {
                        $this->debugLog('Amazon Pay returned payment method not allowed. Aborting checkout.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
                    }
                    if($errorReasonCode === Error::REASON_CODE_MFA_NOT_COMPLETED) {
                        $this->debugLog('Amazon Pay returned MFA not completed. Aborting checkout.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
                    }
                    if($errorReasonCode === Error::REASON_CODE_TRANSACTION_TIMED_OUT) {
                        $this->debugLog('Amazon Pay returned transaction timed out. Aborting checkout.', __CLASS__);
                        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
                    }
                }

                /** @var Error $checkoutSessionFromAmazon */
                $this->debugLog('Error: Error returned while trying to get checkout session from Amazon Pay: ' . $checkoutSessionFromAmazon->getReasonCode(), __CLASS__);
                $this->mode = self::MODE_ERROR;
                return;
            }
            /** @var CheckoutSession $checkoutSessionFromAmazon */
            // refresh/set Checkout Session in Session now
            SessionController::setActiveCheckoutSession($checkoutSessionFromAmazon);

            // Check the state of the amazon pay session to decide how to proceed.
            if ($checkoutSessionFromAmazon->getStatusDetails()->getState() === StatusDetails::STATUS_COMPLETED) {
                $this->mode = self::MODE_SUCCESS;
                return;
            }

            // Special check - the checkout session might be still OPEN for PENDING orders - in that case, the charge permission id and charge id will be set, though.
            if(!empty($checkoutSessionFromAmazon->getChargeId()) && !empty($checkoutSessionFromAmazon->getChargePermissionId()) && $checkoutSessionFromAmazon->getStatusDetails()->getState() === StatusDetails::STATUS_OPEN) {
                $this->mode = self::MODE_SUCCESS;
                return;
            }

            if ($checkoutSessionFromAmazon->getStatusDetails()->getState() === StatusDetails::STATUS_CANCELED) {
                $this->mode = self::MODE_FAILURE;
                return;
            }
            // The checkout session is not in a final state on amazon pay's side. Then this page should not have been called, either.
            $this->debugLog('Error: Checkout result page called but CheckoutSession is not in a final state on Amazon Pay side. State is: ' . $checkoutSessionFromAmazon->getStatusDetails()->getState(), __CLASS__);
            $this->mode = self::MODE_ERROR;
            return;
        } catch (Exception $exception) {
            $this->debugLog('Error: Exception while trying to get checkout session from Amazon Pay: ' . $exception->getMessage() . "\n" . $exception->getTraceAsString(), __CLASS__);
            $this->mode = self::MODE_ERROR;
            return;
        }
    }

    /**
     * Handles a failure return.
     *
     * Checkout was not successful.
     * The buyer either canceled checkout or was unable to provide a valid payment instrument. See Checkout Session canceled state reason code for more info.
     *
     * BuyerCanceled - The buyer canceled the checkout by clicking the Return to previous page button
     * Expired - The Checkout Session expired 24 hour after creation because there was no redirect to the amazonPayRedirectUrl or buyer did not complete payment
     * AmazonCanceled - Amazon has canceled the transaction due to service unavailability. This is not a payment associated cancelation
     * Declined - Generic payment decline reason code that includes fraud declines, failure to complete multi-factor authentication (MFA) challenge, and issues with the payment instrument
     *
     * The buyer can no longer complete checkout using the same Checkout Session ID.
     *
     * You should:
     *
     * 1. Redirect the buyer to the start of checkout.
     * 2. Display a message such as: "Your payment was not successful. Please try another payment method.“
     * 3. Create a new Checkout Session if the buyer clicks on the Amazon Pay button again.
     *
     */
    private function handleFailure(): void {
        // clear the last post array and the checkout session.
        SessionController::clear(SessionController::KEY_CONFIRM_POST_ARRAY);
        /** @var CheckoutSession $checkoutSession */
        $checkoutSession = SessionController::getActiveCheckoutSession();
        if ($checkoutSession !== null) {
            $this->debugLog('The checkout session was Canceled with reason: ' . $checkoutSession->getStatusDetails()->getReasonCode(), __CLASS__);
            SessionController::clearActiveCheckoutSession();
        }
        $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_PAYMENT_NOT_SUCCESSFUL));
    }

    /**
     * Here we arrive after PSD2 was handled successfully:
     *
     * Payment intent was successful.
     * You can follow the instructions in the next step to capture payment immediately.
     * For all other payment scenarios, store the Charge Permission ID and/or Charge ID for future processing.
     *
     * See deferred transactions for more info.
     *
     * What happens now:
     *
     * - We check if all data required is set (someone could have opened this link without coming through the checkout)
     * - Load the order reference details
     * - Check if the order is really confirmed and now open (OPEN indicates that the confirmation flow was successful)
     * - Verify that the session currency and amount of the basket are the exact same as in the order amount
     * - Verify the cart checksum has not changed, either
     *
     * If everything is ok, we:
     * - Authorize on the order (either omni or sync)
     * - If the authorization was successful, we create the order in the shop
     * - We update the shop-internal order number towards Amazon Pay
     * - Handle the captures (e.g. if capture mode is immediate)
     *
     * At last, we redirect to bestellabschluss.php with GET-Parameter "i" which is the order id in tbestellid. This leads to the correct handling of the order within the JTL bestellabschluss (i.e. no re-creating the order)
     * NOTE: bestellabschluss.php will handle order uploads and cleanup of the JTL Session data! We MUST NOT clean this data here, but only our own.
     *
     */
    private function handleSuccess(): void {

        // save and clear the last post array
        $confirmPostArray = SessionController::get(SessionController::KEY_CONFIRM_POST_ARRAY) ?? [];
        SessionController::clear(SessionController::KEY_CONFIRM_POST_ARRAY);

        /** @var CheckoutSession $checkoutSession */
        $checkoutSession = SessionController::getActiveCheckoutSession();
        if (null === $checkoutSession) {
            $this->abortCheckout();
        }

        // Note: we now have to get the charge permission, because the checkout session does not contain any useful data anymore at this point. We can basically throw it away afterwards.
        $adapter = new ApiAdapter();
        $chargePermissionId = $checkoutSession->getChargePermissionId();
        $chargeId = $checkoutSession->getChargeId();

        try {
            $this->chargePermission = $adapter->execute(new GetChargePermission($chargePermissionId));
            $this->charge = $adapter->execute(new GetCharge($chargeId));
            if ($this->chargePermission instanceof Error) {
                throw new Exception('Failed to retrieve charge permission with error: ' . $this->chargePermission->getReasonCode());
            }
            if ($this->charge instanceof Error) {
                throw new Exception('Failed to retrieve charge with error: ' . $this->charge->getReasonCode());
            }
        } catch (Exception $exception) {
            $this->debugLog('Exception/Error while trying to load charge permission or charge: ' . $exception->getMessage(), __CLASS__);
            $this->abortCheckout();
        }

        // check for changed currency
        $frontendCurrency = Frontend::getCurrency();
        if ($frontendCurrency->getCode() !== $this->chargePermission->getLimits()->getAmountLimit()->getCurrencyCode()) {
            $this->debugLog('Aborting checkout. Currency mismatch. Redirecting to basket.', __CLASS__);
            $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_CHECKOUT_CURRENCY_CHANGED) . $frontendCurrency->getCode());
        }

        // check for changed order amount or cart checksum
        if (Cart::getChecksum(Frontend::getCart()) !== SessionController::get(SessionController::KEY_CART_CHECKSUM)
        ) {
            $this->debugLog('Aborting checkout. Basket checksum mismatch. Redirecting to basket.', __CLASS__);
            $this->abortCheckout(Shop::Lang()->get('yourbasketismutating', 'checkout'));
        }

        // Check for changed delivery address checksum
        $mappedShippingAddress = AddressMapper::mapAddressAmazonToJtl($this->chargePermission->getShippingAddress(), AddressMapper::ADDRESS_TYPE_SHIPPING);
        if(!empty($_SESSION['Lieferadresse']) && AddressMapper::getAddressChecksum($_SESSION['Lieferadresse']) !== AddressMapper::getAddressChecksum($mappedShippingAddress)) {
            $this->debugLog('Aborting checkout. Shipping address has changed unexpectedly. Redirecting to basket.', __CLASS__);
            if(defined(Constants::DEVELOPMENT_MODE_CONSTANT) && constant(Constants::DEVELOPMENT_MODE_CONSTANT) === true) {
                $this->debugLog('Mismatched shipping adresses: JTL: ' . print_r($_SESSION['Lieferadresse'], true) . ', Original from Amazon Pay: ' . print_r($this->chargePermission->getShippingAddress(), true) . ', Mapped from Amazon Pay: ' . print_r($mappedShippingAddress, true));
            }
            $this->abortCheckout(Translation::getInstance()->get(Translation::KEY_CHECKOUT_ADDRESS_CHANGED));
        }

        if($this->selectedSubscriptionInterval === null && Currency::convertToAmazonString(Frontend::getCart()->gibGesamtsummeWaren(true) * $frontendCurrency->getConversionFactor()) !== $this->chargePermission->getLimits()->getAmountLimit()->getAmount()) {
            $this->debugLog('Aborting checkout. Basket total sum mismatch. Redirecting to basket.', __CLASS__);
            $this->abortCheckout(Shop::Lang()->get('yourbasketismutating', 'checkout'));
        }

        // For recurring orders the limit on the charge permission is not the actual amount, but the limit on the charge permission as defined in the Amazon Pay class - so we can only check if the sum does not exceed the limit
        if($this->selectedSubscriptionInterval !== null && Currency::convertToAmazonString(Frontend::getCart()->gibGesamtsummeWaren(true) * $frontendCurrency->getConversionFactor()) > $this->chargePermission->getLimits()->getAmountLimit()->getAmount()) {
            $this->debugLog('Aborting checkout. Basket total sum exceeds the single possible charge for a recurring order. Redirecting to basket.', __CLASS__);
            $this->abortCheckout(Shop::Lang()->get('yourbasketismutating', 'checkout'));
        }


        // CHECKS PASSED
        try {
            $mappedBillingAddress = null;
            // as the checks passed and the order is confirmed, we may get the billing address from the order details, if we chose to do so.
            if (!$this->isAdditionalPayButtonMode && $this->config->isUseAmazonPayBillingAddress()) {
                // the actual overriding of the billing address is done in Hook 74 (HOOK_BESTELLABSCHLUSS_INC_BESTELLUNGINDB_RECHNUNGSADRESSE)
                $mappedBillingAddress = AddressMapper::mapAddressAmazonToJtl($this->chargePermission->getBillingAddress(), AddressMapper::ADDRESS_TYPE_BILLING);
                if (null !== $mappedBillingAddress && (!empty($mappedBillingAddress->cNachname) || !empty($mappedBillingAddress->cFirma)) && !empty($mappedBillingAddress->cStrasse)) {
                    Shop::set('lpaBillingAddressOverride', $mappedBillingAddress);
                }
            }

            // The authorization has already been triggered now and is contained in the Charge we loaded. Check it for error states.
            switch ($this->charge->getStatusDetails()->getState()) {
                case StatusDetails::STATUS_CANCELED:
                    // The charge was canceled - this should not be possible here, we handle it the same as a declined charge (technically, a cancel is not possible anymore after a capture was initiated)
                case StatusDetails::STATUS_DECLINED:
                    // The charge was declined by amazon pay - this should not be possible here, either, but we should handle it
                    $this->debugLog('Charge status is not what we expected. Aborting order. Charge status is: ' . $this->charge->getStatusDetails()->getState(), __CLASS__);
                    $this->abortCheckout();
                    break;
                default:
                    // all is good, further handling happens after we created the order
                    break;
            }

            // if the respective setting is set, we need to add the Amazon Pay Reference ID to the comment field
            if ($this->config->isAlwaysAddReferenceToComment()) {
                /*
                 * Add Amazon-Charge-Permission-ID to comment field.
                 */
                if (!empty($confirmPostArray['kommentar'])) {
                    $confirmPostArray['kommentar'] .= ' Amazon-Referenz: ' . $this->chargePermission->getChargePermissionId();
                } else {
                    $confirmPostArray['kommentar'] = 'Amazon-Referenz: ' . $this->chargePermission->getChargePermissionId();
                }
            }

            // the first thing we need to do now is to prevent the JTL Wawi from collecting an order that has the authorization in a PENDING state - therefore we now immediately set it pending via Hook 75, by signaling it here.
            if ($this->charge->getStatusDetails()->getState() === StatusDetails::STATUS_AUTHORIZATION_INITIATED) {
                // This is what was previously known as "PENDING" - we have to immediately prevent the wawi from collecting this order, but do nothing else, as we have to wait for the charge to become Authorized
                Shop::set('lpaForceOrderPending', true);
            }

            // At this point the Authorization was successful and we can consider the order valid.
            // Before we can call the shop internal functions, we need to manipulate some Post data, such that the JTL internal functions correctly handle checkboxes and order comments.
            $_POST = $confirmPostArray;

            // Now we let the core finalize the order. Godspeed, JTL ...
            require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
            if (!$this->isAdditionalPayButtonMode && $this->config->isUseAmazonPayBillingAddress()
                && $mappedBillingAddress !== null && (!empty($mappedBillingAddress->cNachname) || !empty($mappedBillingAddress->cFirma)) && !empty($mappedBillingAddress->cStrasse)) {
                // Do not let JTL send the mail - instead we trigger the sending ourselves with the correct billing address.
                // Save the customer because finalisiereBestellung / finalizeOrder will change it in the Session
                $customerForMail = Frontend::getCustomer();

                // Get availability information BEFORE finalizing the order to the database
                $obj = new \stdClass();
                $orderHandler = null;
                if(Compatibility::isShopAtLeast52()) {
                    /** @noinspection PhpUndefinedClassInspection - OrderHandler exists from Shop 5.2.0-beta onwards */
                    $orderHandler = new OrderHandler(Shop::Container()->getDB(), Frontend::getCustomer(), Frontend::getCart());
                    $obj->cVerfuegbarkeit_arr = $orderHandler->checkAvailability();
                } else {
                    /** @noinspection PhpDeprecationInspection */
                    $obj->cVerfuegbarkeit_arr = pruefeVerfuegbarkeit();
                }

                // Now we let the core finalize the order but without sending the mail. Godspeed, JTL ...
                if(Compatibility::isShopAtLeast52()) {
                    $order = $orderHandler->finalizeOrder(null, false);
                } else {
                    /** @noinspection PhpDeprecationInspection */
                    $order = finalisiereBestellung('', false);
                }

                // OVERRIDE ADDRESS DATA IN $customerForMail, $customerMail is an object, much like Kunde, but not the actual class itself.
                // Note that we don't get a gender from Amazon, but the mail tools will try to force some kind of gender - so we leave it untouched because it is probably correct in the original customer data.
                $customerForMail->cFirma = $mappedBillingAddress->cFirma;
                $customerForMail->cVorname = $mappedBillingAddress->cVorname;
                $customerForMail->cNachname = $mappedBillingAddress->cNachname;
                $customerForMail->cStrasse = $mappedBillingAddress->cStrasse;
                $customerForMail->cHausnummer = $mappedBillingAddress->cHausnummer;
                $customerForMail->cAdressZusatz = $mappedBillingAddress->cAdressZusatz;
                $customerForMail->cPLZ = $mappedBillingAddress->cPLZ;
                $customerForMail->cOrt = $mappedBillingAddress->cOrt;
                $customerForMail->cBundesland = $mappedBillingAddress->cBundesland;
                $customerForMail->cLand = $mappedBillingAddress->cLand;

                // This replicates the mailing from finalisiereBestellung:

                $obj->tkunde      = $customerForMail;
                $obj->tbestellung = $order;
                if (isset($order->oEstimatedDelivery->longestMin, $order->oEstimatedDelivery->longestMax)) {
                    $obj->tbestellung->cEstimatedDeliveryEx = Date::dateAddWeekday(
                            $order->dErstellt,
                            $order->oEstimatedDelivery->longestMin
                        )->format('d.m.Y') . ' - ' .
                        Date::dateAddWeekday($order->dErstellt, $order->oEstimatedDelivery->longestMax)->format('d.m.Y');
                }
                $mailer = Shop::Container()->get(Mailer::class);
                $mail   = new Mail();
                $mailer->send($mail->createFromTemplateID(MAILTEMPLATE_BESTELLBESTAETIGUNG, $obj));
            } else {
                // Now we let the core finalize the order WITH sending a mail. Godspeed, JTL ...
                if(Compatibility::isShopAtLeast52()) {
                    /** @noinspection PhpUndefinedClassInspection - OrderHandler exists from Shop 5.2.0-beta onwards */
                    $orderHandler = new OrderHandler(Shop::Container()->getDB(), Frontend::getCustomer(), Frontend::getCart());
                    $order = $orderHandler->finalizeOrder();
                } else {
                    /** @noinspection PhpDeprecationInspection */
                    $order = finalisiereBestellung();
                }
            }
            $this->debugLog('Order was placed with the shop.', __CLASS__);

            if($this->selectedSubscriptionInterval !== null) {
                try {
                    // Handle subscription specific things - if this fails, the recurring orders will not be processed - not a real bad problem.
                    $subscriptionController = new SubscriptionController(Plugin::getInstance());
                    $subscriptionController->addSubscription($order->kBestellung, $chargePermissionId, $this->selectedSubscriptionInterval);
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_CREATED_HINT), 'lpaSubscriptionCreated', ['saveInSession' => true, 'dismissable' => false, 'fadeOut' => Alert::FADE_NEVER]);
                } catch(Exception $ex) {
                    // subscription creation failed - but we do not want to let this fail the order itself - just add an alert
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_FAILED_HINT), 'lpaSubscriptionFailedHint', ['saveInSession' => true, 'dismissable' => false, 'fadeOut' => Alert::FADE_NEVER]);
                    $this->noticeLog($ex->getMessage());
                }
            }
        } catch (\Throwable $t) {
            // up to this point, any exception should mean that the order was *not* placed yet.
            $this->errorLog('Exception during checkout, order was NOT placed: ' . $t->getMessage() . "\n" . $t->getTraceAsString(), __CLASS__);
            $this->abortCheckout();
            exit(); // not really necessary, but it signals to the compiler that the fail safe catch-block below will always have a defined $order object.
        }

        try {
            // at this point the order has been successfully created. now we handle further things.

            // send order id to amazon, this try-block is embedded within the surrounding try-block because a failure here is not critical
            try {
                $merchantMetadata = new MerchantMetadata();
                if ($this->selectedSubscriptionInterval !== null) {
                    // on subscription orders we add the current date to keep track of when the last order on this charge permission was created
                    $merchantMetadata->setMerchantReferenceId(SubscriptionController::getMerchantReferenceIdPrefix() . $order->cBestellNr . SubscriptionController::getMerchantReferenceIdSuffix());
                } else {
                    $merchantMetadata->setMerchantReferenceId($order->cBestellNr);
                }
                $request = new UpdateChargePermission($this->chargePermission->getChargePermissionId(), $merchantMetadata);
                $response = $adapter->execute($request);
                if($response instanceof Error) {
                    // unlucky, but no problem
                    /** @var Error $response */
                    throw new Exception('Failed to update merchant meta data on charge permission: ' . $response->getReasonCode());
                }
                /** @var ChargePermission chargePermission */
                // replace our known charge permission with the one we got from the updated response
                $this->chargePermission = $response;
            } catch (Exception $ex) {
                // log exceptions but this is no fatal problem (we simply did not set optional information in Amazon Pay)
                $this->noticeLog('Exception while trying to set merchant meta data (order number) for charge permission id "' . $this->chargePermission->getChargePermissionId() . '": ' . $ex->getMessage() . "\n" . $ex->getTraceAsString(), __CLASS__);
            }

            $captureMode = $this->config->getCaptureMode();

            // handle immediate capture
            if ($this->charge->getStatusDetails()->getState() === StatusDetails::STATUS_AUTHORIZED) {
                // The charge is authorized, if we are in immediate capture mode, we cap it now
                if ($captureMode === Config::CAPTURE_MODE_IMMEDIATE) {
                    $captureChargeRequest = new CaptureCharge($this->charge->getChargeId(), $this->charge->getChargeAmount());
                    try {
                        $response = $adapter->execute($captureChargeRequest);
                        if ($response instanceof Error) {
                            // an error - ok, but we can live with that for now.
                            $this->debugLog('Failed to capture charge during order completion with Error: ' . $response->getReasonCode(), __CLASS__);
                            $this->paymentMethodModule->doLog('Immediate capture for charge ' . $this->charge->getChargeId() . ' (Charge Permission ' . $this->chargePermission->getChargePermissionId() .') has failed. Please observe this order carefully!', \LOGLEVEL_NOTICE);
                        } else {
                            // update our known charge with the newest result
                            /** @var Charge $response */
                            $this->charge = $response;
                            // also, reload the chargePermission
                            $updatedChargePermission = $adapter->execute(new GetChargePermission($this->chargePermission->getChargePermissionId()));
                            if($updatedChargePermission instanceof ChargePermission) {
                                $this->chargePermission = $updatedChargePermission;
                            }
                        }
                    } catch (Exception $exception) {
                        // an exception - ok, but we can live with that for now.
                        $this->debugLog('Failed to capture charge during order completion with Exception: ' . $exception->getMessage(), __CLASS__);
                    }
                }
            }


            /*
             * now the charge may be in one of the following states:
             * Authorized (unchanged from before, it will be capped later),
             * CaptureInitiated (for immediate capture only. but unlikely at this point in time, because it is not older than 7 days, but it would resolve later),
             * Captured (for immediate capture only. successfully immediately captured) or
             * Declined (for immediate capture only. the immediate capture failed)
             */
            if ($this->charge->getStatusDetails()->getState() === StatusDetails::STATUS_CAPTURED) {
                // this is an incoming payment - set it of we should do this.
                if($this->config->isAddIncomingPayments()) {
                    $this->paymentMethodModule->addIncomingPayment($order, (object)[
                        'fBetrag' => (float)$this->charge->getCaptureAmount()->getAmount(),
                        'cISO' => $this->charge->getCaptureAmount()->getCurrencyCode(),
                        'cHinweis' => $this->charge->getChargeId()
                    ]);
                    // A capture that is completed at this point implies a complete payment of the order
                    $this->paymentMethodModule->setOrderStatusToPaid($order);
                    $this->paymentMethodModule->sendConfirmationMail($order);
                }
            }
            if ($this->charge->getStatusDetails()->getState() === StatusDetails::STATUS_DECLINED) {
                // Log this to the payment module error log
                $this->paymentMethodModule->doLog('Immediate capture for charge ' . $this->charge->getChargeId() . ' (Charge Permission ' . $this->chargePermission->getChargePermissionId() .') was declined. Please handle manually!', \LOGLEVEL_ERROR);
            }
            // save order attributes to the jtl order (e.g. set the "AmazonPay-Referenz" order attribute that will be displayed in the wawi
            $this->database->saveOrderAttributes($order, $this->chargePermission, $this->charge);

            // save our charge and charge permission to the database
            $this->database->saveChargePermission($this->chargePermission, $order);
            $this->charge->setShopOrderId((int)$order->kBestellung);
            $this->charge->setChargePermissionId($this->chargePermission->getChargePermissionId());
            $this->database->saveCharge($this->charge);

            // our job is done, clear out our session data
            SessionController::clearAll();

            // prepare redirect to bestellabschluss.php?i=...
            $uid = $this->database->getUidForOrder($order);
            if (null === $uid) {
                // Uh oh... this is bad, because the order was created, but we cannot properly redirect to the order completion page without this id.
                $this->errorLog('Critical error: UID was not set or found for order "' . $order->cBestellNr . '". Customer could not be redirected to order completion page. Displaying Fail Safe Message instead.');
                $this->failSafe($order);
            }
            // do the redirect to order completion.
            /* The order completion page will:
             * - Load the order via the UID
             * - Save the uploads with the order
             * - Prepare the order/smarty for tracking
             * - clean up the JTL-related session
             * - display the normal order confirmation page
             * - optionally display our async hint 
             */
            if ($this->charge->getStatusDetails()->getState() === StatusDetails::STATUS_AUTHORIZATION_INITIATED) {
                // add a hint for charges in pending state
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_ASYNC_AUTH_HINT), 'lpaAsyncAuthHint', ['saveInSession' => true, 'dismissable' => false, 'fadeOut' => Alert::FADE_NEVER]);
            }
            header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('bestellabschluss.php', true, true) . '?i=' . $uid);
            exit();
        } catch (\Throwable $t) {
            // any exception here means the order was placed, but we failed afterwards. this is always bad, but we can still try to properly inform the customer.
            $this->errorLog('Exception after order creation, triggering failsafe mechanism: ' . $t->getMessage() . "\n" . $t->getTraceAsString(), __CLASS__);
            $this->failSafe($order);
        }
        exit();
    }

    private function abortCheckout(string $message = ''): void {
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, empty($message) ? Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC) : $message, 'lpaCheckoutAborted', ['dismissable' => true, 'saveInSession' => true]);
        SessionController::clearAllCheckoutSessions();
        header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php', true, true));
        exit();
    }

    private function failSafe(Bestellung $order): void {
        try {
            $this->errorLog('Failsafe mechanism was triggered for order: ' . print_r($order, true), __CLASS__);
            require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellabschluss_inc.php';
            // would be done by bestellabschluss.php
            if(Compatibility::isShopAtLeast52()) {
                /** @noinspection PhpUndefinedClassInspection - OrderHandler exists from Shop 5.2.0-beta onwards */
                $orderHandler = new OrderHandler(Shop::Container()->getDB(), Frontend::getCustomer(), Frontend::getCart());
                $orderHandler->saveUploads($order);
            } else {
                /** @noinspection PhpDeprecationInspection */
                speicherUploads($order);
            }
            // this is basically Frontend->cleanUp, but we do not want to trigger the session constructor
            if (isset($_SESSION['Kunde']->nRegistriert) && (int)$_SESSION['Kunde']->nRegistriert === 0) {
                unset($_SESSION['Kunde']);
            }
            unset(
                $_SESSION['Zahlungsart'],
                $_SESSION['Warenkorb'],
                $_SESSION['Versandart'],
                $_SESSION['Lieferadresse'],
                $_SESSION['VersandKupon'],
                $_SESSION['NeukundenKupon'],
                $_SESSION['Kupon'],
                $_SESSION['GuthabenLocalized'],
                $_SESSION['Bestellung'],
                $_SESSION['Warenkorb'],
                $_SESSION['IP'],
                $_SESSION['kommentar']
            );
            $_SESSION['Warenkorb'] = new Cart();
            // delete persisted cart
            $oWarenkorbPers = new PersistentCart($_SESSION['Kunde']->kKunde ?? 0);
            $oWarenkorbPers->entferneAlles();
        } catch (Exception $e) {
            $this->errorLog('Failsafe mechanism encountered an exception: ' . $e->getMessage() . "\n" . $e->getTraceAsString(), __CLASS__);
        }
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_FAIL_SAFE_CONFIRMATION), 'lpaFailSafe', ['dismissable' => true, 'saveInSession' => true]);
        header('Location: ' . Shop::getURL(true));
        exit();
    }

    /**
     * This function is called if something went technically wrong.
     */
    private function handleError(): void {
        $this->abortCheckout();
    }

}