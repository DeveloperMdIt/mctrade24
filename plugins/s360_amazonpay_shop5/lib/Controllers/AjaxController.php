<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use Exception;
use JTL\Cart\Cart;
use JTL\Catalog\Product\Preise;
use JTL\CheckBox;
use JTL\Customer\CustomerGroup;
use JTL\Events\Dispatcher;
use JTL\Extensions\Download\Download;
use JTL\Helpers\Form;
use JTL\Helpers\Order;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\IO\IO;
use JTL\Language\LanguageHelper;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\AddressRestrictions;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\DeliverySpecifications;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\MerchantMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\PaymentDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\RecurringMetadata;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\StatusDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\WebCheckoutDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CreateCheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetCheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\UpdateCheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Price;
use Plugin\s360_amazonpay_shop5\lib\Utils\Compatibility;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Currency;
use Plugin\s360_amazonpay_shop5\lib\Utils\Interval;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlCartHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;
use Plugin\s360_amazonpay_shop5\paymentmethod\AmazonPay;

/**
 * Class AjaxController
 *
 * Handles AJAX requests which come in via io.php.
 *
 * NOTE: The methods in this controller MUST return assoc arrays which will automatically be turned into JSON by io.php. (Or null if the result would be empty.)
 * Actually, IO would expect an IOResponse, but we do not care about that structure and all we need is a JsonSerializable.
 *
 * An example Call must look like this: https://<shop-url>/io.php?io={"name":"functionNameAsGivenInBootstrap","params":{}} (This also works with a POST instead of GET)
 * "name" and "params" must be included in every request, even if "params" are empty!
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class AjaxController {

    use JtlLoggerTrait;

    public const IO_FUNCTION_SHIPPING_METHOD_SELECTED = 'lpaAjaxShippingMethodSelected';
    public const IO_FUNCTION_CONFIRM_ORDER = 'lpaAjaxConfirmOrder';
    public const IO_FUNCTION_CREATE_CHECKOUT_SESSION = 'lpaCreateCheckoutSession';
    public const IO_FUNCTION_GET_ESTIMATED_ORDER_AMOUNT = 'lpaGetEstimatedOrderAmount';

    private $io;
    private $request;
    private $plugin;
    private $config;

    /**
     * AjaxController constructor.
     * @param IO $io - The JTL io object
     * @param string $request - the raw request data as it was given in the io-parameter of the request
     * @param PluginInterface $plugin
     */
    public function __construct(IO $io, string $request, PluginInterface $plugin) {
        $this->io = $io;
        $this->request = json_decode($request, true);
        $this->plugin = $plugin;
        $this->config = Config::getInstance();
    }

    /**
     * Handles the registration of functions with the IO Controller of the shop.
     * The actual call happens afterwards.
     *
     * @throws \Exception
     */
    public function handle(): void {
        $this->io->register(self::IO_FUNCTION_SHIPPING_METHOD_SELECTED, function ($shippingMethodId, $packagingIds, $useShopCredit) {
            try {
                return $this->executeShippingMethodSelected($shippingMethodId, $packagingIds, $useShopCredit);
            } catch (Exception $e) {
                return $this->handleException($e);
            }
        });
        $this->io->register(self::IO_FUNCTION_CONFIRM_ORDER, function () {
            try {
                return $this->executeConfirmOrder();
            } catch (Exception $e) {
                return $this->handleException($e);
            }
        });
        $this->io->register(self::IO_FUNCTION_CREATE_CHECKOUT_SESSION, function ($interval = '') {
            try {
                return $this->executeCreateCheckoutSession($interval);
            } catch (Exception $e) {
                return $this->handleException($e);
            }
        });
        $this->io->register(self::IO_FUNCTION_GET_ESTIMATED_ORDER_AMOUNT, function() {
            try {
                return $this->executeGetEstimatedOrderAmount();
            } catch (Exception $e) {
                return $this->handleException($e);
            }
        });
    }

    private function handleException(Exception $e): array {
        return ['result' => 'exception', 'code' => $e->getCode(), 'message' => $e->getMessage()];
    }

    /**
     * Sets the shipping method (and optional packagings) in the session
     * - also updates the cart
     * - also updates the order reference amount (also for the first time!)
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\ParameterValidationException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidParameterException
     */
    private function executeShippingMethodSelected($shippingMethodId, $packagingIds, $useShopCredit): array {
        if (empty($shippingMethodId)) {
            return ['result' => 'error', 'code' => 'missingShippingMethodId'];
        }
        if (null === $packagingIds) {
            // While the actual packaging ids are optional, the parameter must be sent!
            return ['result' => 'error', 'code' => 'missingPackagingIds'];
        }

        $isSubscription = SessionController::get(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL) !== null;
        if($isSubscription && $useShopCredit) {
            // This should not happen! We cannot accept using credit for a recurring order.
            return ['result' => 'error', 'code' => 'usingCreditOnRecurringOrder'];
        }

        /** @var CheckoutSession $checkoutSession */
        $checkoutSession = SessionController::getActiveCheckoutSession();

        /** @var int $shippingMethodId */
        $shippingMethodId = (int)$shippingMethodId;
        /** @var int[] $packagingIds */
        $packagingIds = array_map('\intval', $packagingIds); // this might be an empty array, though

        // Unset session data
        $this->resetSessionData();

        // We have to re-apply product specific basket shipping method cost positions
        // Add product specific shipping costs to the basket
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
        if(Compatibility::isShopAtLeast55()) {
            $arrArtikelabhaengigeVersandkosten = Shop::Container()->getShippingService()->getCustomShippingCostsByCart(
                $_SESSION['Lieferadresse']->cLand,
                Frontend::getCustomerGroup(),
                Frontend::getCurrency(),
                Frontend::getCart()->PositionenArr
            );
            $taxRateId = Frontend::getCart()->getShippingService()->getTaxRateIDs(
                '',
                Frontend::getCart()->PositionenArr,
                $_SESSION['Lieferadresse']->cLand
            )[0]->taxRateID ?? 0;
            foreach ($arrArtikelabhaengigeVersandkosten as $oVersandPos) {
                Frontend::getCart()->erstelleSpezialPos(
                    $oVersandPos->cName, 1, $oVersandPos->fKosten, $taxRateId, C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG, false
                );
            }
        } else {
            $arrArtikelabhaengigeVersandkosten = ShippingMethod::gibArtikelabhaengigeVersandkostenImWK(
                $_SESSION['Lieferadresse']->cLand,
                Frontend::getCart()->PositionenArr
            );
            foreach ($arrArtikelabhaengigeVersandkosten as $oVersandPos) {
                Frontend::getCart()->erstelleSpezialPos(
                    $oVersandPos->cName, 1, $oVersandPos->fKosten, Frontend::getCart()->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand), C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG, false
                );
            }
        }


        // We use the shop function versandartKorrekt which adds the shipping method and packings by itself
        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
        $jtlCheckoutController = null;
        if(Compatibility::isShopAtLeast52()) {
            /** @noinspection PhpUndefinedClassInspection - Class exists only in 5.2.0+*/
            $jtlCheckoutController = new \JTL\Router\Controller\CheckoutController(
                Shop::Container()->getDB(),
                Shop::Container()->getCache(),
                Shop::getState(),
                Shopsetting::getInstance()->getAll(),
                Shop::Container()->getAlertService()
            );
            $jtlCheckoutController->init(); // Not doing this will result in an uninitialized cart object
        }
        if(Compatibility::isShopAtLeast52()) {
            $success = $jtlCheckoutController->shippingMethodIsValid((int)$shippingMethodId, ['kVerpackung' => $packagingIds]);
        } else {
            /** @noinspection PhpDeprecationInspection */
            $success = versandartKorrekt($shippingMethodId, ['kVerpackung' => $packagingIds]);
        }
        if (!$success) {
            // no luck, but it's hard to determine what actually went wrong
            return ['result' => 'error', 'code' => 'failedToSetShippingMethod'];
        }

        if (null !== $useShopCredit && (int)$useShopCredit === 1) {
            if(Compatibility::isShopAtLeast52()) {
                Order::checkBalance(['guthabenVerrechnen' => 1]);
                Order::setUsedBalance();
            } else {
                /** @noinspection PhpDeprecationInspection */
                plausiGuthaben(['guthabenVerrechnen' => 1]);
                /** @noinspection PhpDeprecationInspection */
                pruefeGuthabenNutzen();
            }
        } else {
            if(isset($_SESSION['Bestellung'])) {
                // Un-use credit
                $_SESSION['Bestellung']->GuthabenNutzen = 0;
                $_SESSION['Bestellung']->fGuthabenGenutzt = 0;
                $_SESSION['Bestellung']->GutscheinLocalized = Preise::getLocalizedPriceString(0);
            }
        }

        // make sure that using the credits did not send us to a sum of 0 - this cant be paid with Amazon Pay
        if (Frontend::getCart()->gibGesamtsummeWaren(true) < 0.01) {
            return ['result' => 'error', 'code' => 'cartSumZero'];
        }

        // we now set ourselves as payment method, too.
        $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
        if(Compatibility::isShopAtLeast52()) {
            $paymentSetResult = $jtlCheckoutController->checkPaymentMethod($paymentMethodModule->getPaymentMethodId());
        } else {
            /** @noinspection PhpDeprecationInspection */
            $paymentSetResult = zahlungsartKorrekt($paymentMethodModule->getPaymentMethodId());
        }
        if ($paymentSetResult !== 2) {
            // no luck, but it's hard to determine what actually went wrong
            return ['result' => 'error', 'code' => 'failedToSetPaymentMethod'];
        }

        if($isSubscription && $paymentMethodModule->getAbsoluteSubscriptionDiscountForCart() > 0) {
            // We are in recurring order mode and have a discount to apply. Add it to the cart.
            $cart = Frontend::getCart();
            if(Compatibility::isShopAtLeast55()) {
                $taxRateId = Frontend::getCart()->getShippingService()->getTaxRateIDs(
                    '',
                    Frontend::getCart()->PositionenArr,
                    $_SESSION['Lieferadresse']->cLand
                )[0]->taxRateID ?? 0;
                $cart->erstelleSpezialPos(
                    Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_DISCOUNT_CART_POSITION),
                    1,
                    -1 * $paymentMethodModule->getAbsoluteSubscriptionDiscountForCart(),
                    $taxRateId,
                    C_WARENKORBPOS_TYP_ZAHLUNGSART,
                    false
                );
            } else {
                $cart->erstelleSpezialPos(
                    Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_DISCOUNT_CART_POSITION),
                    1,
                    -1 * $paymentMethodModule->getAbsoluteSubscriptionDiscountForCart(),
                    $cart->gibVersandkostenSteuerklasse($_SESSION['Lieferadresse']->cLand),
                    C_WARENKORBPOS_TYP_ZAHLUNGSART,
                    false
                );
            }
            Cart::refreshChecksum($cart);
        }

        if ($checkoutSession === null) {
            return ['result' => 'error', 'code' => 'checkoutSessionNotFound'];
        }

        // set the current basket checksum to our session - it must not change anymore between here and the confirmation and the on-success return
        SessionController::set(SessionController::KEY_CART_CHECKSUM, Cart::getChecksum(Frontend::getCart()));

        Dispatcher::getInstance()->fire(Constants::EVENT_AFTER_SET_SHIPPING_PAYMENT_METHOD);

        // update the checkout session with the new payment information
        try {
            $this->updateCheckoutSession();
            return ['result' => 'success', 'data' => ['shippingMethodId' => $_SESSION['AktiveVersandart'] ?? 0, 'packagingIds' => $_SESSION['AktiveVerpackung'] ?? []]];
        } catch (Exception $exception) {
            // Handle failure
            return ['result' => 'error', 'code' => 'failedToUpdateCheckoutSession', 'message' => $exception->getMessage()];
        }
    }

    /**
     * Confirm order is called when the customer clicks the liable to pay button.
     *
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\ParameterValidationException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidParameterException
     */
    private function executeConfirmOrder(): array {
        // Recreate a post-like array which we will need when Amazon Pay returns to the success or failure page
        $postArray = [];
        if (!isset($this->request['params']) || !\is_array($this->request['params'])) {
            return ['result' => 'error', 'code' => 'missingParameters', 'message' => Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC)];
        }
        /** @var array $this->request['params'] */
        foreach ($this->request['params'] as $param) {
            $postArray[$param['name']] = Text::filterXSS($param['value']);
        }
        if (!isset($postArray['jtl_token']) || empty($postArray['jtl_token'])) {
            return ['result' => 'error', 'code' => 'csrfTokenMissing', 'message' => LanguageHelper::getInstance()->get('csrfValidationFailed', 'global')];
        }
        // Fake the POST array so the following JTL functions will work properly
        $_POST = $postArray;

        // Check CSRF token
        if (!Form::validateToken()) {
            return ['result' => 'error', 'code' => 'csrfTokenInvalid', 'message' => LanguageHelper::getInstance()->get('csrfValidationFailed', 'global')];
        }

        // Check basket checksum
        $currentCartChecksum = Cart::getChecksum(Frontend::getCart());
        if (SessionController::get(SessionController::KEY_CART_CHECKSUM) !== $currentCartChecksum) {
            return ['result' => 'error', 'code' => 'cartChecksumInvalid', 'message' => Translation::getInstance()->get(Translation::KEY_CHECKOUT_CART_CHANGED_REDIRECT)];
        }

        // Check if Amazon Pay is still the selected payment method
        $paymentMethodId = (int)$_SESSION['AktiveZahlungsart'];
        $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
        if ($paymentMethodId !== $paymentMethodModule->getPaymentMethodId()) {
            return ['result' => 'error', 'code' => 'cartChecksumInvalid', 'message' => Translation::getInstance()->get(Translation::KEY_CHECKOUT_CART_CHANGED_REDIRECT)];
        }

        // Check JTL checkboxes
        $customerGroupId = CustomerGroup::getCurrent();

        // Register on the checkbox loaded event to remove the RightsToWithdrawalCheckbox from the check on non-download carts, the check itself is performed through $checkBox->validateCheckbox(...)
        if(!Download::hasDownloads(Frontend::getCart())) {
            try {
                Dispatcher::getInstance()->listen('shop.hook.' . \HOOK_CHECKBOX_CLASS_GETCHECKBOXFRONTEND, function ($args) {
                    $args['oCheckBox_arr'] = array_filter($args['oCheckBox_arr'] ?? [], static function ($item) {
                        return empty($item->cName) || $item->cName !== 'RightOfWithdrawalOfDownloadItems';
                    });
                });
            } catch(\Throwable $t) {
                // doesn't matter
            }
        }
        $checkBox = new CheckBox();
        $checkBoxValidation = $checkBox->validateCheckBox(CHECKBOX_ORT_BESTELLABSCHLUSS, $customerGroupId, $postArray, true);
        if (!empty($checkBoxValidation)) {
            return ['result' => 'error', 'code' => 'requiredCheckboxMissing', 'details' => array_keys($checkBoxValidation), 'message' => LanguageHelper::getInstance()->get('mandatoryFieldNotification', 'errorMessages')];
        }

        // Check immediate capture checkbox
        if ((!isset($postArray['confirmImmediateCapture']) || $postArray['confirmImmediateCapture'] !== 'Y') && $this->config->getCaptureMode() === Config::CAPTURE_MODE_IMMEDIATE) {
            return ['result' => 'error', 'code' => 'requiredCheckboxMissing', 'details' => ['confirmImmediateCapture'], 'message' => LanguageHelper::getInstance()->get('mandatoryFieldNotification', 'errorMessages')];
        }

        // Internal checks done, update checkout session to amazon for a final time to be sure it has the right data
        try {
            $this->updateCheckoutSession();
        } catch (Exception $exception) {
            return ['result' => 'error', 'code' => $exception->getCode(), 'message' => $exception->getMessage()];
        }
        // get updated session and check if it has the correct state and no constraints and all required data
        /** @var CheckoutSession $checkoutSession */
        $checkoutSession = SessionController::getActiveCheckoutSession();
        if (null === $checkoutSession) {
            return ['result' => 'error', 'code' => 'noCheckoutSessionExists', 'message' => Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC)];
        }
        if($checkoutSession->getStatusDetails()->getState() !== StatusDetails::STATUS_OPEN) {
            return ['result' => 'error', 'code' => 'checkoutSessionNotOpen', 'message' => Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC)];
        }
        if (!empty($checkoutSession->getConstraints())) {
            return ['result' => 'error', 'code' => 'constraintsExist', 'message' => Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC)];
        }
        if($checkoutSession->getWebCheckoutDetails() === null || empty($checkoutSession->getWebCheckoutDetails()->getAmazonPayRedirectUrl())) {
            return ['result' => 'error', 'code' => 'noAmazonPayRedirectUrl', 'message' => Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC)];
        }
        // success, the order is now confirmed remember the post array for our success/failure handlers
        SessionController::set(SessionController::KEY_CONFIRM_POST_ARRAY, $postArray);
        return ['result' => 'success', 'amazonPayRedirectUrl' => $checkoutSession->getWebCheckoutDetails()->getAmazonPayRedirectUrl()];
    }

    private function executeCreateCheckoutSession($interval = ''): array {
        // determine if we need a recurring order or a regular one
        $isSubscription = !empty($interval);
        $requestedInterval = null;
        if($isSubscription) {
            $requestedInterval = Interval::fromString($interval);
            if($requestedInterval === null) {
                return ['result' => 'error', 'code' => 'invalidIntervalFormat', 'message' => ''];
            }
            SessionController::set(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL, $requestedInterval);
        } else {
            SessionController::clear(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
        }


        $adapter = new ApiAdapter();

        $checkoutSession = SessionController::getActiveCheckoutSession();

        if (null !== $checkoutSession) {
            // Load the existing checkout session from Amazon Pay to determine its validity - if it is not valid (= exists and is OPEN), we will need to create a new one
            /** @var CheckoutSession $checkoutSession */
            $request = new GetCheckoutSession($checkoutSession->getCheckoutSessionId());
            $response = $adapter->execute($request);
            if ($response instanceof CheckoutSession && $response->getStatusDetails()->getState() === StatusDetails::STATUS_OPEN) {
                $isUnusableSession = false;
                // we can possibly use that session!
                if($isSubscription) {
                    // ... but first we must check and if it is necessary to update the recurring metadata
                    $recurringMetadata = $checkoutSession->getRecurringMetadata();
                    $currentInterval = null;
                    if($recurringMetadata !== null) {
                        $currentInterval = Interval::fromAmazonFrequency($recurringMetadata->getFrequency());
                    }
                    if($currentInterval === null || !$requestedInterval->equals($currentInterval)) {
                        if($recurringMetadata === null) {
                            $recurringMetadata = new RecurringMetadata();
                        }
                        $recurringMetadata->setFrequency($requestedInterval->toAmazonFrequency());
                        // We have to unset some data from the response to be able to use this in a new request
                        $response->getWebCheckoutDetails()->setAmazonPayRedirectUrl(null);
                        $response->getPaymentDetails()->setPresentmentCurrency(null);
                        // Note how we do not set the amount for the frequency here - it does not seem to be necessary, yet.
                        $updateCheckoutSessionRequest = new UpdateCheckoutSession($response->getCheckoutSessionId(), $response->getWebCheckoutDetails(), $response->getPaymentDetails(), $response->getMerchantMetadata(), $recurringMetadata);
                        // perform the checkout session update call
                        $responseAfterUpdate = $adapter->execute($updateCheckoutSessionRequest);
                        if ($responseAfterUpdate instanceof CheckoutSession) {
                            // Save the updated checkout session object to the shop/customer session
                            SessionController::setActiveCheckoutSession($responseAfterUpdate);
                            return ['result' => 'success', 'checkoutSessionId' => $checkoutSession->getCheckoutSessionId()];
                        }
                        /** @var Error $responseAfterUpdate */
                        return ['result' => 'error', 'code' => $responseAfterUpdate->getReasonCode(), 'message' => $responseAfterUpdate->getMessage()];
                    }
                } else {
                    if($checkoutSession->getChargePermissionType() === CheckoutSession::CHARGE_PERMISSION_TYPE_RECURRING) {
                        // We cannot reuse the checkout session as it has the wrong charge permission type.
                        // Note that there is no cancel operation for checkout sessions, we will just have to "forget" about this.
                        $isUnusableSession = true;
                    }
                }

                if(!$isUnusableSession) {
                    // no subscription or no update necessary,  return the freshly loaded session data
                    SessionController::setActiveCheckoutSession($response);
                    return ['result' => 'success', 'checkoutSessionId' => $checkoutSession->getCheckoutSessionId()];
                }
            }
        }

        // something went wrong (either we got an error or the checkout session is not open anymore. assume that the checkout session must be re-created)
        SessionController::clearActiveCheckoutSession();

        // Create a new session (we only need to send the review return url here)
        $checkoutReviewReturnUrl = JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_RETURN);
        $webCheckoutDetails = new WebCheckoutDetails();
        $webCheckoutDetails->setCheckoutReviewReturnUrl($checkoutReviewReturnUrl);

        $deliverySpecifications = $this->determineDeliverySpecifications();

        $merchantMetadata = new MerchantMetadata();
        $merchantMetadata->setCustomInformation($this->config->getCustomInformation());
        $storeName = Shop::getSettings([CONF_GLOBAL])['global']['global_shopname'];
        if(!empty($storeName)) {
            $merchantMetadata->setMerchantStoreName(mb_substr($storeName, 0, 50));
        }

        $paymentDetails = null;
        if($this->config->isMultiCurrencyEnabled()) {
            /**
             * (Only) In multicurrency mode we have to determine what the actual checkout will use as currency, so we can set it as the presentment currency for Amazon Pay.
             * Note that we only set that value, if it is NOT the ledger currency, anyway.
             *
             * In update calls the presentment currency will NOT be changed directly, but it can only be changed via the charge amount.
             *
             * Setting the presentment currency here achieves that the customer will only be allowed to use visa or mastercard payments from the start (which is a requirement by Amazon Pay if the currency used for checkout is NOT the ledger currency.)
             * Also note that Amazon Pay does not do any currency conversions for us.
             */
            $currency = Frontend::getCurrency();
            $presentmentCurrency = null;
            if ($currency !== null && Currency::getInstance()->isSupportedCurrency($currency->getCode()) && !Currency::getInstance()->isLedgerCurrency($currency->getCode())) {
                // the current currency is allowed and not the ledger currency
                $presentmentCurrency = mb_strtoupper($currency->getCode());
            } else {
                // current currency is not allowed, get the fallback currency
                $fallbackCurrency = Currency::getInstance()->getFallbackCurrency();
                if ($fallbackCurrency !== null && !Currency::getInstance()->isLedgerCurrency($fallbackCurrency->getCode())) {
                    // if we got a fallback and the fallback currency is not the ledger currency, set it.
                    $presentmentCurrency = mb_strtoupper($fallbackCurrency->getCode());
                }
            }
            if($presentmentCurrency !== null) {
                $paymentDetails = new PaymentDetails();
                $paymentDetails->setPresentmentCurrency($presentmentCurrency);
            }
        }

        $recurringMetadata = null;
        if($isSubscription) {
            $recurringMetadata = new RecurringMetadata();
            $recurringMetadata->setFrequency($requestedInterval->toAmazonFrequency());
            // Note how we do not set the amount for the frequency here - it does not seem to be necessary, yet.
        }

        $request = new CreateCheckoutSession($webCheckoutDetails, $deliverySpecifications, $paymentDetails, $merchantMetadata, $isSubscription ? CheckoutSession::CHARGE_PERMISSION_TYPE_RECURRING : CheckoutSession::CHARGE_PERMISSION_TYPE_ONE_TIME, $recurringMetadata);
        $response = $adapter->execute($request);
        if ($response instanceof CheckoutSession) {
            // Expected result
            /** @var CheckoutSession $response */
            SessionController::setActiveCheckoutSession($checkoutSession);
            return ['result' => 'success', 'checkoutSessionId' => $response->getCheckoutSessionId()];
        }
        if ($response instanceof Error) {
            // Error response from Amazon Pay
            /** @var Error $response */
            return ['result' => 'error', 'code' => $response->getReasonCode(), 'message' => $response->getMessage()];
        }
        // Completely unexpected response
        return ['result' => 'error', 'code' => 'Unknown Error', 'response' => print_r($response ?? 'null', true)];
    }

    /**
     * Updates the checkout session with currency and price data, as well as return urls etc.
     * Note that this function is only called on our own checkout page, hence the currency is already decided to be a supported currency.
     * (Otherwise, trying to update the session would result in a failure anyway, e.g. if some wise-guy tries to switch around currencies in a different tab.
     * As we are using the actual current currency in the session to compute amounts, this should not affect us.)
     * @throws Exception
     */
    private function updateCheckoutSession(): void {

        /** @var Interval $selectedSubscriptionInterval */
        $selectedSubscriptionInterval = SessionController::get(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
        $isSubscription = $selectedSubscriptionInterval !== null;

        $checkoutSession = SessionController::getActiveCheckoutSession();

        if(null === $checkoutSession) {
            throw new Exception('CheckoutSession does not exist');
        }
        /** @var CheckoutSession $checkoutSession */

        // success so far, let's update the checkout session such that Amazon Pay knows the appropriate amount
        $currency = Frontend::getCurrency();
        // first, check if the user messed with the currency in a different tab and managed to switch currencies around even though multicurrency is disabled
        if(!$this->config->isMultiCurrencyEnabled() && !Currency::getInstance()->isLedgerCurrency($currency->getCode())) {
            // this is an error
            throw new \Exception('CurrencyMismatch');
        }

        // Note: if multicurrency is enabled, we actually do not care - if the new currency is supported, this request will work as expected, switching the checkout session to the other currency..
        // If it is not supported, it will result in an InvalidRequestParameter error which will be displayed to the user anyway.
        $price = new Price();
        $price->setAmount(Currency::convertToAmazonString(Frontend::getCart()->gibGesamtsummeWaren(true) * $currency->getConversionFactor()));
        $price->setCurrencyCode($currency->getCode());

        $paymentDetails = new PaymentDetails();
        $paymentDetails->setChargeAmount($price);

        // set can handle pending only if the authorization mode is configured as such
        $paymentDetails->setCanHandlePendingAuthorization($this->config->getAuthorizationMode() === Config::AUTHORIZATION_MODE_OMNI);
        /* TODO LATER: SET THIS CAPTURE MODE ACCORDINGLY, FOR NOW, IT IS NOT SUPPORTED BECAUSE IT PREVENTS US FROM UPDATING THE SHOPS ORDER NUMBER AFTER THE CHECKOUT
            if($this->config->getCaptureMode() === Config::CAPTURE_MODE_IMMEDIATE) {
                $paymentDetails->setPaymentIntent($paymentDetails::PAYMENT_INTENT_AUTHORIZED_WITH_CAPTURE);
            } else {
                $paymentDetails->setPaymentIntent($paymentDetails::PAYMENT_INTENT_AUTHORIZE);
            }
        */
        $paymentDetails->setPaymentIntent(PaymentDetails::PAYMENT_INTENT_AUTHORIZE);

        $webCheckoutDetails = new WebCheckoutDetails();
        $webCheckoutDetails->setCheckoutReviewReturnUrl(JtlLinkHelper::getInstance()->getFullReturnUrl());
        $webCheckoutDetails->setCheckoutResultReturnUrl(JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_CALLBACK_RESULT));

        $updateCheckoutSession = new UpdateCheckoutSession($checkoutSession->getCheckoutSessionId());
        $updateCheckoutSession->setPaymentDetails($paymentDetails);
        $updateCheckoutSession->setWebCheckoutDetails($webCheckoutDetails);

        if($isSubscription) {
            $recurringMetadata = new RecurringMetadata();
            $recurringMetadata->setFrequency($selectedSubscriptionInterval->toAmazonFrequency());
            $recurringMetadata->setAmount($price);
            $updateCheckoutSession->setRecurringMetadata($recurringMetadata);
        }

        // perform the checkout session update call
        $adapter = new ApiAdapter();
        $response = $adapter->execute($updateCheckoutSession);
        if ($response instanceof CheckoutSession) {
            // Save the updated checkout session object to the shop/customer session
            SessionController::setActiveCheckoutSession($response);
        } else {
            /** @var Error $response */
            throw new \Exception($response->getReasonCode());
        }
    }

    /**
     * Resets all basket and session information regarding shipping method, packagings and payment method (it depends on the shipping method!)
     * Note that this does not reset the use of shop credit like the initial load of the checkout page because the credit does not depend on the shipping method.
     */
    private function resetSessionData(): void {

        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG);

        unset(
            $_SESSION['Verpackung'],
            $_SESSION['Versandart'],
            $_SESSION['Zahlungsart'],
            $_SESSION['AktiveVersandart'],
            $_SESSION['AktiveVerpackung'],
            $_SESSION['AktiveZahlungsart']
        );
    }

    /**
     * Determines the delivery specifications, i.e. to which countries we may deliver, if packstation is allowed and if PO boxes are allowed
     */
    private function determineDeliverySpecifications(): ?DeliverySpecifications {
        $data = [];

        if(!$this->config->isAllowPackstation()) {
            $data['specialRestrictions'] = [DeliverySpecifications::SPECIAL_RESTRICTION_PACKSTATIONS];
        }
        if(!$this->config->isAllowPoBox()) {
            if(!isset($data['specialRestrictions'])) {
                $data['specialRestrictions'] = [DeliverySpecifications::SPECIAL_RESTRICTION_PO_BOXES];
            } else {
                $data['specialRestrictions'][] = DeliverySpecifications::SPECIAL_RESTRICTION_PO_BOXES;
            }
        }

        $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
        $shippableCountries = $paymentMethodModule->getShippableCountries();
        $restrictions = [];
        foreach($shippableCountries as $countryIso) {
            // MAYBE SOMEDAY: We may some day add enabled zip codes or regions here, for now the iso codes as keys are all we need to "enable" these countries for the selection in Amazon Pay.
            $restrictions[$countryIso] = [];
        }
        if(!empty($shippableCountries)) {
            $data['addressRestrictions'] = [
                'type' => AddressRestrictions::TYPE_ALLOWED,
                'restrictions' => $restrictions
            ];
        }
        return \count($data) > 0 ? new DeliverySpecifications($data) : null;
    }

    /**
     * Gets the currently estimated order amount.
     */
    private function executeGetEstimatedOrderAmount(): array {
        ['amount' => $estimatedOrderAmountAmout, 'currency' => $estimatedOrderAmountCurrency] = JtlCartHelper::getInstance()->getEstimatedOrderAmount();
        return ['result' => 'success', 'estimatedOrderAmount' => [ 'amount' => empty($estimatedOrderAmountAmout) ? null : number_format($estimatedOrderAmountAmout, 2, '.', ''), 'currencyCode' => empty($estimatedOrderAmountCurrency) ? null : $estimatedOrderAmountCurrency ]];
    }

}
