<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;


use JTL\Alert\Alert;
use JTL\Cart\Cart;
use JTL\Cart\CartHelper;
use JTL\Catalog\Product\Preise;
use JTL\Checkout\Adresse;
use JTL\Checkout\Lieferadresse;
use JTL\Checkout\CouponValidator; // From 5.2.0-beta onwards, only!
use JTL\Events\Dispatcher;
use JTL\Extensions\Download\Download;
use JTL\Extensions\Upload\Upload;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Shopsetting;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\StatusDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetCheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\Frontend\UserInfo;
use Plugin\s360_amazonpay_shop5\lib\Mappers\AddressMapper;
use Plugin\s360_amazonpay_shop5\lib\Utils\Compatibility;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Currency;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\Events;
use Plugin\s360_amazonpay_shop5\lib\Utils\Interval;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;
use Plugin\s360_amazonpay_shop5\paymentmethod\AmazonPay;

class CheckoutController {

    use JtlLoggerTrait;

    /**
     * @var array $request ;
     */
    private $request;

    /**
     * @var UserInfo $userInfo
     */
    private $userInfo;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var PluginInterface $plugin
     */
    private $plugin;

    /**
     * @var ApiAdapter $adapter
     */
    private $adapter;

    /**
     * @var CheckoutSession $checkoutSession
     */
    private $checkoutSession;

    /**
     * The available shipping methods that will be loaded for the given shipping address
     * @var array $shippingMethods
     */
    private $shippingMethods;

    /**
     * The available packaging options that will be loaded for the given shipping address and cart
     */
    private $packagings;

    /**
     * The displayed shipping address - note that this might not be the same as the shipping address in the session
     * if the shipping address could not be set to the session.
     *
     * @var Adresse $displayShippingAddress
     */
    private $displayShippingAddress;

    /**
     * CheckoutController constructor.
     * @param PluginInterface $plugin
     */
    public function __construct(PluginInterface $plugin) {
        $this->request = Text::filterXSS($_REQUEST);
        $this->config = Config::getInstance();
        $this->database = Database::getInstance();
        $this->adapter = new ApiAdapter();
        $this->plugin = $plugin;
        $this->userInfo = SessionController::get(SessionController::KEY_USER_INFO);
        $this->checkoutSession = SessionController::getActiveCheckoutSession();
        $this->shippingMethods = [];
        $this->packagings = [];
        $this->displayShippingAddress = new Lieferadresse();
    }

    /**
     * The main entry point when the frontend page is called.
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\ParameterValidationException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidParameterException
     */
    public function handle(): void {

        // Avoid ajax calls to this site altogether, special handling for dropper because it does not use regular ajax requests
        if (!empty($this->request['x-dropper-ajax-request']) || Request::isAjaxRequest()) {
            return;
        }

        $this->debugLog('Starting Execution.', __CLASS__);
        // Use custom template if it exists.
        if (file_exists($this->plugin->getPaths()->getFrontendPath() . 'template/checkout_custom.tpl')) {
            Shop::Smarty()->assign('cPluginTemplate', $this->plugin->getPaths()->getFrontendPath() . 'template/checkout_custom.tpl');
        }

        // Check if we may be called, in the first place
        $this->debugLog('Checking for valid invocation.', __CLASS__);
        $this->validateInvocation();

        $selectedSubscriptionInterval = SessionController::get(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
        if($selectedSubscriptionInterval !== null) {
            $this->validateSubscriptionInvocation($selectedSubscriptionInterval);
        }

        // Check if the user tried to cancel Amazon Pay.
        $this->handleCancelAmazonPay();

        // Check if the currency is valid, if not, we auto-switch by redirecting to ourselves.
        $this->debugLog('Checking for valid currency.', __CLASS__);
        $this->validateCurrency();


        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';

        $this->setShippingAddress($this->checkoutSession);

        // accept neu customer kupon, if applicable (this will be removed if the user re-enters the checkout from anywhere else)
        if(Compatibility::isShopAtLeast52()) {
            /** @noinspection PhpUndefinedClassInspection - Exists from Shop 5.2.0-beta onwards */
            CouponValidator::validateNewCustomerCoupon(Frontend::getCustomer());
        } else {
            /** @noinspection PhpDeprecationInspection */
            plausiNeukundenKupon();
        }

        $this->prepareSmartyVariables();
        // Check which step we need to display. The summary may be shown if gotoSummary was in the request or if we go here by a processing error from the CheckoutResultController
        if ((isset($this->request['gotoSummary']) && (int)$this->request['gotoSummary'] === 1) || null !== SessionController::get(SessionController::KEY_PROCESSING_ERROR_SOFT_DECLINE)) {
            $this->handleSummary();
        } else {
            $this->handleShippingPayment();
        }
    }

    /**
     * Sets globally needed Smarty variables, i.e. the paths to the included step templates.
     */
    private function prepareSmartyVariables(): void {

        $vars = [
            'useBillingAddressFromAmazonPay' => $this->config->isUseAmazonPayBillingAddress(),
            'sellerId' => $this->config->getMerchantId(),
            'scope' => Constants::DEFAULT_SCOPE,
            'allowPackstation' => $this->config->isAllowPackstation(),
            'presentmentCurrency' => Frontend::getCurrency()->getCode(),
            'isImmediateCapture' => $this->config->getCaptureMode() === Config::CAPTURE_MODE_IMMEDIATE,
            'checkoutUrl' => JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_CHECKOUT),
            'environment' => $this->config->getEnvironment(),
            // using credit is only possible with Amazon Pay if: the credit exists and if it is not exceeding the total order amount, because then the order could be paid completely with credit, anyway
            'creditPossible' => Frontend::getCustomer()->fGuthaben > 0 && Frontend::getCart()->gibGesamtsummeWaren(true, false) > Frontend::getCustomer()->fGuthaben,
            'creditLocalized' => Frontend::getCustomer()->fGuthaben > 0 ? Preise::getLocalizedPriceString(Frontend::getCustomer()->fGuthaben) : '',
            'checkoutSession' => $this->checkoutSession,
            'paymentDescription' => $this->checkoutSession->getPaymentPreferences()[0]->getPaymentDescriptor(),
            'amazonPayBillingAddress' => AddressMapper::mapAddressAmazonToJtl($this->checkoutSession->getBillingAddress(), AddressMapper::ADDRESS_TYPE_BILLING),
            'displayShippingAddress' => $this->displayShippingAddress,
            'showCommentField' => $this->config->isShowCommentField(),
            'templateMode' => $this->config->getTemplateMode(),
            'subscriptionInterval' => SessionController::get(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL),
            'hasDownloads' => Download::hasDownloads(Frontend::getCart()),
            'ioPath' => Compatibility::isShopAtLeast54() ? '/io' : '/io.php'
        ];
        $vars['templatePathStepShippingPayment'] = $this->plugin->getPaths()->getFrontendPath() . 'template/checkout_step_shipping_payment.tpl';
        if (file_exists($this->plugin->getPaths()->getFrontendPath() . 'template/checkout_step_shipping_payment_custom.tpl')) {
            $vars['templatePathStepShippingPayment'] = $this->plugin->getPaths()->getFrontendPath() . 'template/checkout_step_shipping_payment_custom.tpl';
        }
        $vars['templatePathStepSummary'] = $this->plugin->getPaths()->getFrontendPath() . 'template/checkout_step_summary.tpl';
        if (file_exists($this->plugin->getPaths()->getFrontendPath() . 'template/checkout_step_summary_custom.tpl')) {
            $vars['templatePathStepSummary'] = $this->plugin->getPaths()->getFrontendPath() . 'template/checkout_step_summary_custom.tpl';
        }
        $vars['templatePathShippingMethods'] = $this->plugin->getPaths()->getFrontendPath() . 'template/snippets/shipping_methods.tpl';
        if (file_exists($this->plugin->getPaths()->getFrontendPath() . 'template/snippets/shipping_methods_custom.tpl')) {
            $vars['templatePathShippingMethods'] = $this->plugin->getPaths()->getFrontendPath() . 'template/snippets/shipping_methods_custom.tpl';
        }
        $vars['templatePathCheckoutSteps'] = $this->plugin->getPaths()->getFrontendPath() . 'template/snippets/checkout_steps.tpl';
        if(file_exists($this->plugin->getPaths()->getFrontendPath() . 'template/snippets/checkout_steps_custom.tpl')) {
            $vars['templatePathCheckoutSteps'] = $this->plugin->getPaths()->getFrontendPath() . 'template/snippets/checkout_steps_custom.tpl';
        }

        Shop::Smarty()
            ->assign('Versandarten', $this->shippingMethods)
            ->assign('Verpackungsarten', $this->packagings)
            ->assign('Einstellungen', Shopsetting::getInstance()->getAll());

        Shop::Smarty()->assign('lpaCheckoutGlobal', $vars)
            // These assigns are necessary because the template checks against these variables instead of the actual constants ...
            ->assign('C_WARENKORBPOS_TYP_ARTIKEL', C_WARENKORBPOS_TYP_ARTIKEL)
            ->assign('C_WARENKORBPOS_TYP_VERSANDPOS', C_WARENKORBPOS_TYP_VERSANDPOS)
            ->assign('C_WARENKORBPOS_TYP_KUPON', C_WARENKORBPOS_TYP_KUPON)
            ->assign('C_WARENKORBPOS_TYP_GUTSCHEIN', C_WARENKORBPOS_TYP_GUTSCHEIN)
            ->assign('C_WARENKORBPOS_TYP_ZAHLUNGSART', C_WARENKORBPOS_TYP_ZAHLUNGSART)
            ->assign('C_WARENKORBPOS_TYP_VERSANDZUSCHLAG', C_WARENKORBPOS_TYP_VERSANDZUSCHLAG)
            ->assign('C_WARENKORBPOS_TYP_NEUKUNDENKUPON', C_WARENKORBPOS_TYP_NEUKUNDENKUPON)
            ->assign('C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR', C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR)
            ->assign('C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG', C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG)
            ->assign('C_WARENKORBPOS_TYP_VERPACKUNG', C_WARENKORBPOS_TYP_VERPACKUNG)
            ->assign('C_WARENKORBPOS_TYP_GRATISGESCHENK', C_WARENKORBPOS_TYP_GRATISGESCHENK)
            ->assign('C_WARENKORBPOS_TYP_ZINSAUFSCHLAG', C_WARENKORBPOS_TYP_ZINSAUFSCHLAG)
            ->assign('C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR', C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
    }

    /**
     * Displays the first checkout page, where the user selects shipping address, shipping method and payment method.
     */
    private function handleShippingPayment(): void {
        $this->debugLog('Handling shipping method and payment selection step.', __CLASS__);
        Shop::Smarty()->assign('lpaCheckoutStep', 'shippingPayment');
        $this->resetSessionData();
    }

    /**
     * Checks if the user wants to cancel on us.
     * In that case we just unset the session data to the state "before Amazon Pay checkout" - but leave the checkout session itself intact - and then redirect to the basket.
     */
    private function handleCancelAmazonPay() {
        if(isset($this->request['cancelAmazonPay'])) {
            $this->resetSessionData();
            $this->forwardToCart();
        }
    }

    private function resetSessionData() {
        // when this page is loaded, we reset all shipping related information, these are added again via AJAX Call when the user selects the respective options
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_BEARBEITUNGSGEBUEHR);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_NACHNAHMEGEBUEHR);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERPACKUNG);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSAND_ARTIKELABHAENGIG);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDPOS);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_VERSANDZUSCHLAG);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZAHLUNGSART);
        Frontend::getCart()->loescheSpezialPos(C_WARENKORBPOS_TYP_ZINSAUFSCHLAG);

        // Note we must not unset the Bestellung object in the session or we lose the delivery address mapping!
        unset(
            $_SESSION['Verpackung'],
            $_SESSION['Versandart'],
            $_SESSION['Zahlungsart'],
            $_SESSION['AktiveVersandart'],
            $_SESSION['AktiveVerpackung'],
            $_SESSION['AktiveZahlungsart']
        );
        // "Un-use" credit
        if (isset($_SESSION['Bestellung'])) {
            $_SESSION['Bestellung']->GuthabenNutzen = 0;
            $_SESSION['Bestellung']->fGuthabenGenutzt = 0;
            $_SESSION['Bestellung']->GutscheinLocalized = Preise::getLocalizedPriceString(0);
        }
        // Re-calculate Taxes just in case
        Tax::setTaxRates();
        Dispatcher::getInstance()->fire(Constants::EVENT_AFTER_RESET_SESSION);
    }

    /**
     * Displays the second checkout page, where the user sees the summary of the order, can enter a comment and check checkboxes.
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\ParameterValidationException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidParameterException
     */
    private function handleSummary(): void {
        $this->debugLog('Handling and validating summary step.', __CLASS__);

        $processingError = [];
        if (null !== SessionController::get(SessionController::KEY_PROCESSING_ERROR_SOFT_DECLINE)) {
            // We were returned by a processing error - this equals a soft decline. Remember this information, but we still need to validate afterwards, if we should show the summary.
            $processingError = SessionController::get(SessionController::KEY_PROCESSING_ERROR_SOFT_DECLINE);
            // and unset the processing error in the session controller
            SessionController::clear(SessionController::KEY_PROCESSING_ERROR_SOFT_DECLINE);
        }

        /**
         * Before we actually show the summary, we do a validation check.
         * If it fails, we fall back to displaying the shippingPayment step!
         */
        if (!$this->validateSummary($processingError)) {
            $this->debugLog('Summary step is not valid, handing over to shipping and payment step.', __CLASS__);
            $this->handleShippingPayment();
            return;
        }

        /**
         * The summary step may be called, do additional handling before showing the summary page.
         *
         * The summary page shows the order positions, checkboxes, comment field.
         * Note: It also shows the checkbox for immediate capture!
         */
        $this->debugLog('Summary step is valid. Continuing.', __CLASS__);

        Shop::Smarty()->assign('AGB', Shop::Container()->getLinkService()->getAGBWRB(
            Shop::getLanguage(),
            Frontend::getCustomerGroup()->getID()
        ));
        Shop::Smarty()->assign('lpaCheckoutStep', 'summary');
    }

    /**
     * Checks if we may show the summary page, also adds messages about potential problems.
     *
     * To display the summary page, the following must be true:
     * - The CSRF token is correct (unless returned from a processing error)
     * - A shipping method must be set in the session.
     * - A shipping address must be set in the session.
     * - An order reference id must be given. (from request or from a processing error)
     * - The order reference must be free of certain constraints. (unless given by a processing error)
     *
     * @param array $processingError
     * @return bool true iff the summary page may be shown.
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\TechnicalException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\ParameterValidationException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\MethodNotImplementedException
     * @throws \Plugin\s360_amazonpay_shop5\lib\Exceptions\InvalidParameterException
     */
    private function validateSummary(array $processingError = []): bool {
        $isProcessingError = !empty($processingError);
        if (!$isProcessingError && !Form::validateToken()) {
            $this->debugLog('Summary step not valid: CSRF token is invalid.', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC), 'lpaCsrfError', ['dismissable' => true]);
            return false;
        }
        if (empty($_SESSION['Versandart'])) {
            $this->debugLog('Summary step not valid: Shipping method missing in session..', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Shop::Lang()->get('fillShipping', 'checkout'), 'lpaCheckoutShippingMethodMissing', ['dismissable' => true]);
            return false;
        }
        if (!$isProcessingError) {
            if (empty($_SESSION['AktiveVersandart']) || empty($this->request['Versandart']) || (int)$_SESSION['AktiveVersandart'] !== (int)$this->request['Versandart']) {
                $this->debugLog('Summary step not valid: Form submitted shipping method is not the expected shipping method.', __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Shop::Lang()->get('fillShipping', 'checkout'), 'lpaCheckoutChangedShippingPositions', ['dismissable' => true]);
                return false;
            }
        } else {
            if (empty($_SESSION['AktiveVersandart'])) {
                $this->debugLog('Summary step not valid: Processing error returned but shipping method is not set.', __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Shop::Lang()->get('fillShipping', 'checkout'), 'lpaCheckoutChangedShippingPositions', ['dismissable' => true]);
                return false;
            }
        }
        if (empty(SessionController::getActiveCheckoutSession())) {
            $this->debugLog('Summary step not valid: Checkout Session missing.', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC), 'lpaCheckoutSessionMissing', ['dismissable' => true]);
            return false;
        }
        if (SessionController::get(SessionController::KEY_CART_CHECKSUM) !== Cart::getChecksum(Frontend::getCart())) {
            $this->debugLog('Summary step not valid: Basket is mutating.', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Shop::Lang()->get('yourbasketismutating', 'checkout'), 'lpaCheckoutChangedCart', ['dismissable' => true]);
            return false;
        }
        $paymentMethodId = (int) $_SESSION['AktiveZahlungsart'];
        $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
        if($paymentMethodId !== $paymentMethodModule->getPaymentMethodId()) {
            $this->debugLog('Summary step not valid: Payment method has changed.', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Shop::Lang()->get('yourbasketismutating', 'checkout'), 'lpaCheckoutChangedCart', ['dismissable' => true]);
            return false;
        }

        if (!$isProcessingError) {
            // lets refresh the checkout session from Amazon Pay
            $request = new GetCheckoutSession($this->checkoutSession->getCheckoutSessionId());
            $response = $this->adapter->execute($request);
            if ($response instanceof Error) {
                // This should not have happened. Display a generic error.
                /** @var Error $response */
                $this->errorLog('Summary step not valid: API Error occured while trying to refresh checkout session for id "' . $this->checkoutSession->getCheckoutSessionId() . '": ' . $response->getMessage(), __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC), 'lpaCheckoutError', ['dismissable' => true]);
                return false;
            }

            /** @var CheckoutSession $response */


            if($response->getStatusDetails()->getState() !== StatusDetails::STATUS_OPEN) {
                SessionController::clearActiveCheckoutSession();
                $this->debugLog('Summary step not valid: Checkout session id "' . $this->checkoutSession->getCheckoutSessionId() . ' is not OPEN anymore. Aborting.', __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC), 'lpaCheckoutError', ['dismissable' => true]);
                $this->forwardToCart();
            }

            // check if the loaded checkout session has the same recurring meta data information as we expect.
            $selectedInterval = SessionController::get(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
            /** @var Interval|null $selectedInterval */
            if( ($selectedInterval === null && $response->getRecurringMetadata() !== null)
                || ($selectedInterval !== null && !$selectedInterval->equals(Interval::fromAmazonFrequency($response->getRecurringMetadata()->getFrequency())))) {
                SessionController::clearActiveCheckoutSession();
                $this->debugLog('Summary step not valid: Checkout session id "' . $this->checkoutSession->getCheckoutSessionId() . ' subscription interval mismatch. Aborting.', __CLASS__);
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC), 'lpaCheckoutError', ['dismissable' => true]);
                $this->forwardToCart();
            }

            // update the session with the refreshed response
            SessionController::setActiveCheckoutSession($response);
            $constraints = $response->getConstraints();
            if (!empty($constraints)) {
                // The order reference id has constraints against it. We may not confirm it at the moment.
                // This is unexpected and should not happen, in APIV2 all constraints are related to missing input from us or unset fields in APIV2
                foreach($constraints as $constraint) {
                    $this->debugLog('Summary step not valid: Unexpected constraint on checkout session id "' . $this->checkoutSession->getCheckoutSessionId() . ': ' . $constraint->getConstraintId() . ' ' . $constraint->getDescription() ?? '', __CLASS__);
                }
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC), 'lpaCheckoutError', ['dismissable' => true]);
                // in any case, we do not allow the customer to view the summary page.
                return false;
            }
        } else {
            // a processing error can always be considered a soft decline, else we would not have been redirected here, but to the basket page
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_SOFT_DECLINE), 'lpaSoftDecline', ['dismissable' => true]);
            $this->debugLog('Summary step: Soft decline from processing error for AmazonOrderReferenceId: ' . $processingError['orid'], __CLASS__);
            // While this is a constraint, it may be resolved by selecting a different paymethod in the widget and then trying again.
            Shop::Smarty()->assign('lpaCheckoutSummary', [
                'isSoftDecline' => true
            ]);
        }
        return true;
    }


    /**
     * Checks if this site may be called, in the first place.
     * - A user must be logged in with Amazon Pay
     * - Checkout must be possible
     *
     * If this is not the case, the user will be redirected to the basket page with a respective message.
     */
    private function validateInvocation(): void {
        // check our own semantics
        if (!isset($_SESSION['Kunde']) || $this->userInfo === null) {
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_ERROR_NOT_LOGGED_IN), 'lpaCheckoutErrorNotLoggedIn', ['dismissable' => true, 'saveInSession' => true]);
            $this->forwardToCart();
        }

        // check for the checkout session
        if(null === $this->checkoutSession) {
            $this->debugLog('Calling checkout failed, no checkout session in session.', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_NO_CHECKOUT), 'lpaNoSessionError', ['dismissable' => true, 'saveInSession' => true]);
            $this->forwardToCart();
        }

        // check if checkout session is open
        if($this->checkoutSession->getStatusDetails()->getState() !== StatusDetails::STATUS_OPEN) {
            $this->debugLog('Calling checkout failed, checkout session in session is not open.', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_NO_CHECKOUT), 'lpaSessionNotOpenError', ['dismissable' => true, 'saveInSession' => true]);
            $this->forwardToCart();
        }

        // check if checkout session has an address and a payment preference
        if($this->checkoutSession->getShippingAddress() === null || empty($this->checkoutSession->getPaymentPreferences())) {
            $this->debugLog('Calling checkout failed, checkout session in session is not in a usable state - missing shipping method or payment preference.', __CLASS__);
            SessionController::clearActiveCheckoutSession(); // unset the invalid session to force renewal
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_NO_CHECKOUT), 'lpaSessionInvalidError', ['dismissable' => true, 'saveInSession' => true]);
            $this->forwardToCart();
        }

        // check basket semantics
        $cart = Frontend::getCart();
        $checkoutIsPossibleResult = $cart->istBestellungMoeglich();
        if($checkoutIsPossibleResult === 10 && class_exists('Upload') && !Upload::pruefeWarenkorbUploads($cart)) {
            $checkoutIsPossibleResult = UPLOAD_ERROR_NEED_UPLOAD;
        }
        if ($checkoutIsPossibleResult !== 10) {
            // int 10 is the result value of istBestellungMoeglich if it is possible, else it is a negative result
            switch ($checkoutIsPossibleResult) {
                case 3:
                    // Cart is empty
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Shop::Lang()->get('yourbasketisempty', 'checkout'), 'lpaCheckoutErrorEmptyBasket', ['dismissable' => true, 'saveInSession' => true]);
                    break;
                case 8:
                    // Antispam protection
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Shop::Lang()->get('orderNotPossibleNow', 'checkout'), 'lpaCheckoutErrorSpamProtection', ['dismissable' => true, 'saveInSession' => true]);
                    break;
                case 9:
                    // Minimum order amount for customer group not met
                    $mbw = Frontend::getCustomerGroup()->getAttribute(KNDGRP_ATTRIBUT_MINDESTBESTELLWERT);
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Shop::Lang()->get('minordernotreached', 'checkout') . ' ' . Preise::getLocalizedPriceString($mbw), 'lpaCheckoutErrorOrderAmountCustomerGroup', ['dismissable' => true, 'saveInSession' => true]);
                    break;
                case 12:
                    // Upload articles with missing uploads
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Shop::Lang()->get('missingFilesUpload', 'checkout'), 'lpaCheckoutErrorMissingFileUpload', ['dismissable' => true, 'saveInSession' => true]);
                    break;
            }
            $this->forwardToCart();
        }
    }

    /**
     * Check if the invocation for us is valid for the given subscription interval.
     */
    protected function validateSubscriptionInvocation(Interval $selectedInterval) {
        $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
        if(!$paymentMethodModule->isSubscriptionPossibleForCart()) {
            // The current cart does not allow subscription, in the first place
            SessionController::clear(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
            SessionController::clearActiveCheckoutSession();
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_ERROR_REDIRECT), 'lpaSubscriptionErrorRedirect', ['dismissable' => true, 'saveInSession' => true]);
            $this->forwardToCart();
        }
        $possibleIntervalsForCart = $paymentMethodModule->getPossibleSubscriptionIntervalsForCart();
        if(empty($possibleIntervalsForCart)) {
            // No intervals possible for current cart
            SessionController::clear(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
            SessionController::clearActiveCheckoutSession();
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_ERROR_REDIRECT), 'lpaSubscriptionErrorRedirect', ['dismissable' => true, 'saveInSession' => true]);
            $this->forwardToCart();
        }
        $intervalMatched = false;
        foreach($possibleIntervalsForCart as $possibleInterval) {
            /** @var Interval $possibleInterval */
            if($possibleInterval->equals($selectedInterval)) {
                $intervalMatched = true;
                break;
            }
        }
        if(!$intervalMatched) {
            // None of the possible intervals matches the selected interval (maybe some manipulation attempt)
            SessionController::clear(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL);
            SessionController::clearActiveCheckoutSession();
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_SUBSCRIPTION_ERROR_REDIRECT), 'lpaSubscriptionErrorRedirect', ['dismissable' => true, 'saveInSession' => true]);
            $this->forwardToCart();
        }
    }

    /**
     * Forwards the customer to the cart page.
     */
    private function forwardToCart(): void {
        header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('warenkorb.php', true, true));
        exit();
    }

    /**
     * Checks the session currency. If it is not supported, redirects to ourselves and shows an information to the customer that the currency has changed.
     */
    private function validateCurrency(): void {
        $currency = Frontend::getCurrency();
        if (!Currency::getInstance()->isSupportedCurrency($currency->getCode())) {
            $fallbackCurrency = Currency::getInstance()->getFallbackCurrency();
            if ($fallbackCurrency !== null) {
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_CHECKOUT_CURRENCY_CHANGED) . $fallbackCurrency->getCode(), 'lpaCurrencyChanged', ['dismissable' => true, 'saveInSession' => true]);
                header('Location: ' . JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_CHECKOUT) . '?curr=' . $fallbackCurrency->getCode());
                exit;
            }
            $this->errorLog('No currency configured to be allowed with Amazon Pay.', __CLASS__);
            $this->forwardToCart();
        }
    }

    /**
     * Sets the shipping address from the given CheckoutSession
     * @param CheckoutSession $checkoutSession
     */
    private function setShippingAddress(CheckoutSession $checkoutSession): void {
        unset($_SESSION['Lieferadresse'], $_SESSION['cLieferlandISO'], $_SESSION['preferredDeliveryCountryCode']);

        if (isset($_SESSION['Bestellung'], $_SESSION['Bestellung']->kLieferadresse)) {
            unset($_SESSION['Bestellung']->kLieferadresse);
        }

        $resultAddress = null;
        $mailAddedFromCustomer = false;
        if (null !== $checkoutSession->getShippingAddress()) {
            $resultAddress = AddressMapper::mapAddressAmazonToJtl($checkoutSession->getShippingAddress(), AddressMapper::ADDRESS_TYPE_SHIPPING);
            if(empty($resultAddress->cMail) && !empty(Frontend::getCustomer()->cMail)) {
                // Explicitly set customer's mail address on the shipping address, too
                $mailAddedFromCustomer = true;
                $resultAddress->cMail = Frontend::getCustomer()->cMail;
            }
        }

        if($resultAddress === null) {
            $this->debugLog('Selected shipping address was not loaded/mapped.', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_NO_SHIPPING_ADDRESS), 'lpaShippingAddressMissingError', ['dismissable' => true]);
            $this->shippingMethods = [];
            $this->packagings = [];
            return;
        }


        // Check if the user selected a packstation address
        if (!$this->config->isAllowPackstation() && AddressMapper::isPackstation($checkoutSession->getShippingAddress())) {
            $this->debugLog('Selected shipping address is not valid, as packstation is not allowed.', __CLASS__);
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Translation::getInstance()->get(Translation::KEY_PACKSTATION_NOT_ALLOWED), 'lpaPackstationNotAllowedError', ['dismissable' => true]);
            $this->shippingMethods = [];
            $this->packagings = [];
            $this->displayShippingAddress = $resultAddress;
            return;
        }

        // set delivery address in session so we can apply the free shipping voucher if it exists
        $_SESSION['Lieferadresse'] = $resultAddress;
        $this->displayShippingAddress = $resultAddress;
        $_SESSION['cLieferlandISO'] = $resultAddress->cLand;

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

        // NOTE: At this point a bug occurs - the check works perfectly fine, removes $_SESSION['Kupon'] from the session and tries to apply the VersandKupon - however, it never unsets the $_SESSION['VersandKupon'], so if it is applied correctly once,
        // Therefore, we unset this ourselves.
        unset($_SESSION['VersandKupon']); // let the shop re-apply the coupon in the next step
        if(Compatibility::isShopAtLeast52()) {
            /** @noinspection PhpUndefinedMethodInspection - This method only exists in Shop 5.2.0-beta or higher which we explicitly check for. */
            CartHelper::applyShippingFreeCoupon();
        } else {
            /** @noinspection PhpDeprecationInspection */
            pruefeVersandkostenfreiKuponVorgemerkt();
        }

        // This looks good so far, collect the possible shipping methods
        $paymethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
        $configuredShippingMethodIds = $paymethodModule->getConfiguredShippingMethodIds($resultAddress->cLand);
        $this->debugLog('Configured shipping method IDs: ' . print_r($configuredShippingMethodIds, true), __CLASS__);

        if(Compatibility::isShopAtLeast55()) {
            $shippingMethods = Shop::Container()->getShippingService()->getPossibleShippingMethods(Frontend::getCustomer(), Frontend::getCustomerGroup(), $resultAddress->cLand, Frontend::getCurrency(), $resultAddress->cPLZ, Frontend::getCart()->PositionenArr);
        } else {
            $shippingMethods = ShippingMethod::getPossibleShippingMethods($resultAddress->cLand, $resultAddress->cPLZ, ShippingMethod::getShippingClasses(Frontend::getCart()), Frontend::getCustomerGroup()->getID());
        }
        $this->debugLog('Available shipping methods for destination: ' . print_r($shippingMethods, true), __CLASS__);

        // as the result we need the possible shipping methods filtered by those that are configured to allow Amazon Pay
        $remainingShippingMethods = array_filter($shippingMethods, static function ($element) use ($configuredShippingMethodIds) {
            return \in_array((int)$element->kVersandart, $configuredShippingMethodIds, true);
        });

        Dispatcher::getInstance()->fire(Events::AFTER_GET_POSSIBLE_SHIPPING_METHODS, ['shippingMethods' => $remainingShippingMethods, 'deliveryAddress' => $resultAddress]);

        $this->debugLog('Remaining shipping methods for destination: ' . print_r($remainingShippingMethods, true), __CLASS__);

        // set tax rates and update cart
        Tax::setTaxRates();

        // also set the shipping address key - for this we have to determine if the customer used this delivery address before, else we set it to -1
        if (!isset($_SESSION['Bestellung'])) {
            $_SESSION['Bestellung'] = new \stdClass();
        }
        $_SESSION['Bestellung']->kLieferadresse = $this->database->determineKeyForShippingAddress(Frontend::getCustomer(), Frontend::getDeliveryAddress(), $mailAddedFromCustomer);

        if(Compatibility::isShopAtLeast55()) {
            $packagings = Shop::Container()->getShippingService()->getPossiblePackagings(Frontend::getCustomerGroup()->getID(), Frontend::getCurrency(), Frontend::getCart()->PositionenArr);
        } else {
            $packagings = ShippingMethod::getPossiblePackagings(Frontend::getCustomerGroup()->getID());
        }

        $this->shippingMethods = $remainingShippingMethods;
        $this->packagings = $packagings;
        Dispatcher::getInstance()->fire(Constants::EVENT_AFTER_SET_SHIPPING_ADDRESS);
    }

}