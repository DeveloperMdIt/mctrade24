<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers;

use Exception;
use JTL\Alert\Alert;
use JTL\CheckBox;
use JTL\Customer\CustomerFields;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\ShippingMethod;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\CountryService;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Address;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\StatusDetails;
use Plugin\s360_amazonpay_shop5\lib\Frontend\Button;
use Plugin\s360_amazonpay_shop5\lib\Frontend\UserInfo;
use Plugin\s360_amazonpay_shop5\lib\Mappers\AddressMapper;
use Plugin\s360_amazonpay_shop5\lib\Utils\Compatibility;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\lib\Utils\Translation;
use Plugin\s360_amazonpay_shop5\lib\Entities\AccountMapping;

/**
 * Class LoginController
 *
 * Controller for the login page.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class LoginController {

    use JtlLoggerTrait;

    private const PASSWORD_GENERATION_LENGTH = 20; // length of auto-generated passwords

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var array $request
     */
    private $request;

    /**
     * @var Config $config
     */
    private $config;

    /**
     * @var string $context
     */
    private $context;

    /**
     * @var UserInfo $userInfo
     */
    private $userInfo;

    /**
     * @var PluginInterface $plugin
     */
    private $plugin;

    /**
     * @var CustomerAccountController $customerAccountController
     */
    private $customerAccountController;

    /**
     * @var array $prefillData
     */
    private $prefillData;


    /**
     * LoginController constructor.
     * @param PluginInterface $plugin
     */
    public function __construct(PluginInterface $plugin) {
        $this->plugin = $plugin;
        $this->request = $_REQUEST;
        $this->config = Config::getInstance();
        $this->database = Database::getInstance();
        $this->userInfo = SessionController::get(SessionController::KEY_USER_INFO);
        $this->context = SessionController::get(SessionController::KEY_CONTEXT);
        $this->customerAccountController = new CustomerAccountController();
        $this->prefillData = [];
    }

    /**
     * Handles the login page.
     */
    public function handle(): void {

        // Avoid ajax calls to this site altogether
        if (Request::isAjaxRequest()) {
            return;
        }

        $this->debugLog('Login controller handle execution started with context "' . $this->context . '"', __CLASS__);
        if (null === $this->userInfo) {
            $this->handleMissingUserInfo();
            return;
        }

        $this->handleCreateAccountSubmit();

        $this->debugLog('Processing login.', __CLASS__);
        $this->handleLogin();
    }

    private function handleLogin(): void {
        /**
         * Check what we need to do, this depends on:
         * - whether we know the amazon user id,
         * - whether a customer is logged in or not,
         * - whether we do check for account merges or not.
         */
        $mappingData = $this->database->findAccountMapping($this->userInfo->getUserId());
        if (empty($mappingData)) {
            // mapping data is unknown, create it.
            $this->debugLog('Creating new mapping data for ' . $this->userInfo->getUserId(), __CLASS__);
            $mappingData = new AccountMapping();
            $mappingData->setAmazonUserId($this->userInfo->getUserId());
            // insert it into the database
            $this->database->insertMappingData($mappingData);
            // and re-load it from the database
            $mappingData = $this->database->findAccountMapping($this->userInfo->getUserId());
            // (Note: at this point, the new mapping data is neither mapped to an existing account (customerId is NULL) nor is it verified yet)
        }
        // Check if the mapping data has an associated jtl customer id.
        if ($mappingData->getJtlCustomerId() === AccountMapping::DEFAULT_JTL_CUSTOMER_ID) {
            $this->handleUnmappedAmazonUserId($mappingData);
        } else {
            $this->handleMappedAmazonUserId($mappingData);
        }
    }

    /**
     * Handles an unmapped user id.
     *
     * @param AccountMapping $mappingData
     */
    private function handleUnmappedAmazonUserId(AccountMapping $mappingData): void {
        // we do not know who this amazon user id belongs to, yet
        if ($this->customerAccountController->isCustomerLoggedIn() && !$this->customerAccountController->isCustomerGuest()) {
            // a user is logged in -> alright, map the jtl customer id to the amazon user id
            $this->debugLog('New mapping for logged in user - saving mapping, forwarding user.', __CLASS__);
            // we have to update our mapping data in any case, as the user is logged in, we can consider him verified.
            $mappingData->setJtlCustomerId($this->customerAccountController->getCurrentCustomerId());
            $mappingData->setIsVerified(true);
            $this->database->updateMappingData($mappingData);
            // ... and send him to the intended target location
            $this->forwardToTargetLocation();
        } else {
            // no customer is logged in
            // Check if the email exists as actual customer (= not a guest account), then check if we need an account merge or an account creation
            $existingCustomer = $this->database->findCustomerByEmail($this->userInfo->getEmail());
            if (null !== $existingCustomer) {
                // We found an actual non-guest customer with that email address in the JTL Shop database
                $this->debugLog('New mapping for existing, but not logged in user - trying to merge and login user.', __CLASS__);
                $jtlCustomerId = (int)$existingCustomer->kKunde;
                // check if customer may be logged in, in the first place
                if ($existingCustomer->cSperre !== 'Y' && $existingCustomer->cAktiv !== 'N') {
                    $mappingData->setJtlCustomerId((int)$existingCustomer->kKunde);
                    if (!$this->config->isCheckAccountMerge()) {
                        // just go ahead, merge the customer and log him in
                        $mappingData->setIsVerified(true);
                        $this->database->updateMappingData($mappingData);
                        $this->customerAccountController->loginCustomer($jtlCustomerId);
                        $this->forwardToTargetLocation();
                    } else {
                        // customer needs to verify his posession of the account
                        $mappingData->setIsVerified(false);
                        $this->database->updateMappingData($mappingData);
                        $this->prepareMergeAccount();
                    }
                } else {
                    // the customer may not be logged in because his account is not active or blocked
                    $this->handleCustomerLoginNotAllowed();
                }
            } else {
                $this->debugLog('New mapping for new user - going for account creation.', __CLASS__);
                // customer is unknown, do an account creation
                $this->prepareCreateAccount();
            }
        }
    }

    /**
     * Handles an already mapped amazon user id.
     *
     * @param AccountMapping $mappingData
     */
    private function handleMappedAmazonUserId(AccountMapping $mappingData): void {
        // we know and have mapped this amazon user id already to a jtl customer id
        if ($this->customerAccountController->isCustomerLoggedIn() && !$this->customerAccountController->isCustomerGuest()) {
            // the user is already logged in -> all good.
            $this->debugLog('Already logged in user - forwarding user to target location.', __CLASS__);
            if (!$mappingData->isIsVerified() || $this->customerAccountController->getCurrentCustomerId() !== $mappingData->getJtlCustomerId()) {
                // we require an update to the mapping data, note that we assume a user may be loged in with a different shop account, too.
                // He still is authenticated against the shop as well as Amazon at this point, so we only update the mapping and proceed.
                $mappingData->setJtlCustomerId($this->customerAccountController->getCurrentCustomerId());
                $mappingData->setIsVerified(true);
                $this->database->updateMappingData($mappingData);
            }
            $this->forwardToTargetLocation();
        } else {
            // no customer is logged in
            // Load the real customer by his mapped id
            $existingCustomer = $this->database->findCustomerByJtlCustomerId($mappingData->getJtlCustomerId());
            if (null !== $existingCustomer && (int) $existingCustomer->nRegistriert > 0) {
                // check if customer may be logged in, in the first place
                $this->debugLog('Known mapping for not logged in user - checking for merge or logging in user.', __CLASS__);
                if ($existingCustomer->cSperre !== 'Y' && $existingCustomer->cAktiv === 'Y') {
                    // user may be logged in w.r.p. to JTL Shop, see if his account is verified.
                    $isVerified = $mappingData->isIsVerified();
                    if ($isVerified || !$this->config->isCheckAccountMerge()) {
                        $this->debugLog('Merge already done or not necessary - logging in user.', __CLASS__);
                        // just go ahead, merge the customer if needed and log him in
                        if (!$isVerified) {
                            // update verification status if we can skip the verification
                            $mappingData->setIsVerified(true);
                            $this->database->updateMappingData($mappingData);
                        }
                        // perform an automated login for the customer
                        $this->customerAccountController->loginCustomer((int)$existingCustomer->kKunde);
                        // ... and then send him to the original target url
                        $this->forwardToTargetLocation();
                    } else {
                        $this->debugLog('Merge is necessary - forwarding user to login page.', __CLASS__);
                        // user is not verified and account merge may not be skipped, prepare an account merge.
                        $this->prepareMergeAccount();
                    }
                } else {
                    // The user exists but may not be logged in due to his account being inactivated or blocked
                    $this->debugLog('Login not allowed for user because of inactive or blocked account.', __CLASS__);
                    $this->handleCustomerLoginNotAllowed();
                }
            } else {
                // Special case: Customer was not found in the database or the found customer was not actually registered. This is weird and should not actually happen, unless someone deletes their account and/or the account id gets somehow turned into a guest account,
                // We consider the mapping invalid, delete our mapping data and retry calling ourselves
                $this->debugLog('Known mapping for unknown user - mapping must be obsolete and will be deleted, retrying afterwards.', __CLASS__);
                $this->database->deleteAccountMappingForJtlCustomerId($mappingData->getJtlCustomerId());
                $this->redirectToSelf();
            }
        }
    }

    /**
     * Forwards the user to the target location in the session controller, also unsets the target location in the session.
     * If no target location was set, this forwards to the home page.
     */
    private function forwardToTargetLocation(): void {
        if (($targetLocation = SessionController::get(SessionController::KEY_CUSTOMER_TARGET_LOCATION)) !== null) {
            SessionController::clear(SessionController::KEY_CUSTOMER_TARGET_LOCATION);
            header('Location: ' . $targetLocation);
            exit();
        }
        header('Location: ' . Shop::getURL(true));
        exit();
    }

    /**
     * Redirect to ourselves.
     * This might be useful to retry after cleaning bogus data.
     */
    private function redirectToSelf(): void {
        header('Location: ' . JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_LOGIN));
        exit();
    }

    /**
     * Handles what to do when the user info is missing from the session.
     */
    private function handleMissingUserInfo(): void {
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_RETURN_ERROR_GENERIC), 'lpaMissingUserInfoErrorInfo', ['dismissable' => true, 'saveInSession' => true]);
        $this->debugLog('User called login controller without user info.', __CLASS__);
        header('Location: ' . Shop::getURL(true));
        exit();
    }

    /**
     * Checks for a create account request and handles it.
     * The following handleLogin might then redirect the user if everything is good.
     * @param bool $isInternalRequest - flag if this is an internal request, if so, certain checks are skipped
     */
    private function handleCreateAccountSubmit(bool $isInternalRequest = false): void {
        // check parameter
        if (!isset($this->request['createAccount']) || (int)$this->request['createAccount'] === 0) {
            return;
        }
        // Check CSRF token
        if (!$isInternalRequest && !Form::validateToken()) {
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, LanguageHelper::getInstance()->get('csrfValidationFailed', 'global'), 'lpaCsrfError', ['dismissable' => true, 'saveInSession' => false]);
            return;
        }
        require_once PFAD_ROOT . PFAD_INCLUDES . 'bestellvorgang_inc.php';
        // Check post data for missing or wrong inputs.
        $data = Text::filterXSS($this->request);

        // We may need a fix here - JTL does not recognize missing MANDATORY data if it is absent from the request completely, so we have to set those values empty
        $data = $this->addMissingRequiredFields($data);

        $accountCreationMode = $this->getEffectiveAccountCreationMode();
        $isFullCustomer = $accountCreationMode === Config::ACCOUNT_CREATION_MODE_ALWAYS || ($accountCreationMode === Config::ACCOUNT_CREATION_MODE_OPTIONAL && isset($data['createFullAccount']) && $data['createFullAccount'] === 'Y');

        if ($isFullCustomer && $this->config->getPasswordCreation() === Config::PASSWORD_CREATION_MODE_GENERATE) {
            // generate a pass and add it to the request data
            $generatedPass = Shop::Container()->getCryptoService()->randomString(self::PASSWORD_GENERATION_LENGTH);
            $data['pass'] = $generatedPass;
            $data['pass2'] = $generatedPass;
        }

        $customerGroupId = Frontend::getCustomerGroup()->getID();

        if(Compatibility::isShopAtLeast52()) {
            /** @noinspection PhpUndefinedClassInspection - Class exists only in 5.2.0+*/
            $registrationForm = new \JTL\Customer\Registration\Form();
            if ($isFullCustomer) {
                $customer = $registrationForm->getCustomerData($data, true, false); // get a customer object from the data array, including pass, not as htmlentities
                $missingOrInvalidInputs = $registrationForm->checkKundenFormularArray(Text::filterXSS($data), true, true);
            } else {
                $customer = $registrationForm->getCustomerData($data, false, false); // get a customer object from the data array, without pass, not as htmlentities
                $missingOrInvalidInputs = $registrationForm->checkKundenFormularArray(Text::filterXSS($data), false, false);
            }
        } else {
            if ($isFullCustomer) {
                /** @noinspection PhpDeprecationInspection */
                $customer = getKundendaten($data, 1, 0); // get a customer object from the data array, including pass, not as htmlentities
                /** @noinspection PhpDeprecationInspection */
                $missingOrInvalidInputs = checkKundenFormularArray(Text::filterXSS($data), 1, 1);
            } else {
                /** @noinspection PhpDeprecationInspection */
                $customer = getKundendaten($data, 0, 0); // get a customer object from the data array, without pass, not as htmlentities
                /** @noinspection PhpDeprecationInspection */
                $missingOrInvalidInputs = checkKundenFormularArray(Text::filterXSS($data), 0, 0);
            }
        }
        $oCheckBox = new CheckBox();
        $missingOrInvalidInputs = array_merge($missingOrInvalidInputs, $oCheckBox->validateCheckBox(
            CHECKBOX_ORT_REGISTRIERUNG,
            $customerGroupId,
            $data,
            true
        ));

        // override captcha stuff - we dont need this here, the customer logged in via amazon already
        if (isset($missingOrInvalidInputs['captcha'])) {
            unset($missingOrInvalidInputs['captcha']);
        }

        if(Compatibility::isShopAtLeast52()) {
            $checkResult = Form::hasNoMissingData($missingOrInvalidInputs);
        } else {
            /** @noinspection PhpDeprecationInspection */
            $checkResult = angabenKorrekt($missingOrInvalidInputs);
        }

        if ($isFullCustomer) {
            executeHook(HOOK_REGISTRIEREN_PAGE_REGISTRIEREN_PLAUSI, [
                'nReturnValue' => &$checkResult,
                'fehlendeAngaben' => &$missingOrInvalidInputs
            ]);
        } else {
            executeHook(HOOK_BESTELLVORGANG_INC_UNREGISTRIERTBESTELLEN_PLAUSI, [
                'nReturnValue' => &$checkResult,
                'fehlendeAngaben' => &$missingOrInvalidInputs,
                'Kunde' => &$customer,
                'cPost_arr' => &$data
            ]);
        }

        if (!$checkResult) {
            // this was not successful, we have to re-display the template, this is handled by Smarty
            $this->prepareCreateAccount(true);
            if(!$isInternalRequest) {
                // on internal requests we ignore missing inputs because the user would not understand the reason for them
                Shop::Smarty()->assign('fehlendeAngaben', $missingOrInvalidInputs);
            }
            $this->prefillData = $data;
            Shop::Smarty()->assign('cPost_var', $data);
            return;
        }

        // Successfully checked everything up to here. We can go ahead.
        $customer->kKundengruppe = $customerGroupId;
        $jtlCustomer = $this->customerAccountController->createCustomerAccount($customer, $data, $isFullCustomer);
        $jtlCustomerId = $jtlCustomer !== null && $isFullCustomer ? $jtlCustomer->kKunde : 0;
        if ($jtlCustomerId > 0) {
            // we now know a valid jtl customer id - update our mapping data
            $mappingData = $this->database->findAccountMapping($this->userInfo->getUserId());
            $mappingData->setIsVerified(true);
            $mappingData->setJtlCustomerId($jtlCustomerId);
            $this->database->updateMappingData($mappingData);
        }

        /**
         * In certain shop configurations, the user might not be logged in now, due to his account requiring activation.
         * We redirect him to the home page with a respective information.
         *
         * Note that guest accounts are always set to active.
         *
         */
        if ($jtlCustomer === null) {
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Translation::getInstance()->get(Translation::KEY_ERROR_GENERIC), 'lpaGenericError', ['dismissable' => true, 'saveInSession' => true]);
            $this->redirectToSelf();
        }
        if ($jtlCustomer->cAktiv !== 'Y') {
            Shop::Container()->getAlertService()->addAlert(Alert::TYPE_WARNING, Shop::Lang()->get('activateAccountDesc', 'global'), 'lpaActivateAccountNecessary', ['dismissable' => true, 'saveInSession' => true]);
            header('Location: ' . Shop::getURL(true));
            exit();
        }

        // forward the customer to the target location
        $this->forwardToTargetLocation();
    }

    /**
     * Informs the user that he cannot be logged in into the requested account (because it is not allowed), then clears all session data and returns the user to the shop front page.
     * (We cannot forward him to the intended target URL as this might be the checkout where he cannot go without logging in.)
     * @return void
     */
    private function handleCustomerLoginNotAllowed(): void {
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_DANGER, Translation::getInstance()->get(Translation::KEY_LOGIN_NOT_ALLOWED), 'lpaLoginNotAllowed', ['dismissable' => true, 'saveInSession' => true]);
        SessionController::clearAll();
        header('Location: ' . Shop::getURL(true));
        exit();
    }

    /**
     * This will result in the display of the merge account page.
     * We need to set the smarty variables accordingly.
     */
    private function prepareMergeAccount(): void {
        // An account merge is nothing more than requesting the user to login and then reacting to it.
        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_INFO, Translation::getInstance()->get(Translation::KEY_ACCOUNT_MERGE_REQUIRED), 'lpaAccountMergeRequired', ['dismissable' => false, 'saveInSession' => true]);
        header('Location: ' . Shop::Container()->getLinkService()->getStaticRoute('jtl.php', true, true));
        exit();
    }

    /**
     * This will result in the display of the create account page.
     * We need to set the smarty variables accordingly.
     *
     * Note that "account creation" in this context may also mean we just create a guest account, depending on the configuration.
     *
     * @param bool $ignoreSkip
     */
    private function prepareCreateAccount(bool $ignoreSkip = false): void {

        $explanationText = '';
        switch ($this->getEffectiveAccountCreationMode()) {
            case Config::ACCOUNT_CREATION_MODE_ALWAYS:
                if ($this->config->isUseAmazonPayBillingAddress()) {
                    $explanationText = Translation::getInstance()->get(Translation::KEY_CREATE_DESCRIPTION_BILLING_OVERRIDE);
                } else {
                    $explanationText = Translation::getInstance()->get(Translation::KEY_CREATE_DESCRIPTION);
                }
                break;
            case Config::ACCOUNT_CREATION_MODE_NEVER:
                if ($this->config->isUseAmazonPayBillingAddress()) {
                    $explanationText = Translation::getInstance()->get(Translation::KEY_CREATE_DESCRIPTION_GUEST_BILLING_OVERRIDE);
                } else {
                    $explanationText = Translation::getInstance()->get(Translation::KEY_CREATE_DESCRIPTION_GUEST);
                }
                break;
            case Config::ACCOUNT_CREATION_MODE_OPTIONAL:
                if ($this->config->isUseAmazonPayBillingAddress()) {
                    $explanationText = Translation::getInstance()->get(Translation::KEY_CREATE_DESCRIPTION_OPTIONAL_BILLING_OVERRIDE);
                } else {
                    $explanationText = Translation::getInstance()->get(Translation::KEY_CREATE_DESCRIPTION_OPTIONAL);
                }
                break;
        }

        $vars = [
            'askForPassword' => $this->config->getPasswordCreation() === Config::PASSWORD_CREATION_MODE_INPUT,
            'frontendTemplatePath' => $this->plugin->getPaths()->getFrontendPath() . 'template/',
            'accountCreationMode' => $this->getEffectiveAccountCreationMode(),
            'explanationText' => $explanationText,
            'requiredFieldsOnly' => $this->config->isLoginRequiredFieldsOnly()
        ];
        /** @var UserInfo $userInfo */
        $userInfo = SessionController::get(SessionController::KEY_USER_INFO);
        if ($userInfo !== null) {
            $vars['mail'] = $userInfo->getEmail();
            $fullName = $userInfo->getName();
            if(!empty($fullName)) {
                $splitName = AddressMapper::splitName($userInfo->getName());
                $vars['userInfoFirstName'] = $splitName['firstName'];
                $vars['userInfoLastName'] = $splitName['lastName'];
            }
        }


        if (empty($this->prefillData)) {
            try {
                $checkoutSession = SessionController::getActiveCheckoutSession();
                if (!empty($checkoutSession)) {
                    /** @var CheckoutSession $checkoutSession */
                    if ($checkoutSession->getStatusDetails()->getState() === StatusDetails::STATUS_OPEN) {
                        $billingAddress = $checkoutSession->getBillingAddress();
                        if (null !== $billingAddress) {
                            $this->prefillData = $this->prefillDataFromBillingAddress($billingAddress);
                        }
                    } else {
                        SessionController::clearActiveCheckoutSession();
                    }
                }
            } catch (Exception $e) {
                $this->debugLog('Failed to get checkout session information. Skipping prefill.', __CLASS__);
            }
        }

        if (!$ignoreSkip) {
            // try to check for the possiblity to skip the display of the page entirely. This is only possible if prefill from the checkout session is enabled.
            if (isset($userInfo, $billingAddress) && $this->getEffectiveAccountCreationMode() === Config::ACCOUNT_CREATION_MODE_NEVER && $this->config->isUseAmazonPayBillingAddress()) {
                // Special case - the user is a guest and we use the billing address from Amazon Pay anyway. We may be able to skip displaying the account creation page.
                // prepare an internal request
                $this->request = $this->prefillDataFromBillingAddress($billingAddress);
                $this->request['createAccount'] = 1; // imitate submit
                $this->request['email'] = $userInfo->getEmail();

                // call handle create account - note that this method will forward directly if it succeeds, else the page will be still shown (e.g. for mandatory checkboxes, etc.).
                $this->handleCreateAccountSubmit(true);
            }
        }

        $customerGroupId = isset($_SESSION['Kunde'], $_SESSION['Kunde']->kKundengruppe) ? (int)$_SESSION['Kunde']->kKundengruppe : 0;

        Shop::Smarty()->assign('lpaCreate', $vars)
            ->assign('lpaDisplayMode', 'create')
            ->assign('laender', Shop::Container()->getCountryService()->getCountrylist()->toArray())
            ->assign('oKundenfeld_arr', $this->getCustomCustomerFields())
            ->assign('cPost_var', $this->prefillData); // this may either be post data from a previous post, or prefilled from the checkout session
    }

    /**
     * This is basically a workaround function.
     *
     * JTL does not recognize when actually mandatory fields are completely absent from the request
     * and they are not about to fix it anytime soon. So here we go and add all required fields that are missing from the request data.
     *
     * Note: We do not do any checks for validity here, this merely prepares the data such that JTL internal functions work correctly.
     *
     * @param array $request
     * @return array
     */
    private function addMissingRequiredFields(array $request): array {

        $requiredFieldsToAdd  = ['nachname', 'strasse', 'hausnummer', 'plz', 'ort', 'land', 'email']; // these are always required
        // Load settings
        $conf = Shop::getSettings([CONF_KUNDEN, CONF_KUNDENFELD, CONF_GLOBAL]);
        foreach ([
                     'kundenregistrierung_abfragen_anrede' => 'anrede',
                     'kundenregistrierung_pflicht_vorname' => 'vorname',
                     'kundenregistrierung_abfragen_firma' => 'firma',
                     'kundenregistrierung_abfragen_firmazusatz' => 'firmazusatz',
                     'kundenregistrierung_abfragen_titel' => 'titel',
                     'kundenregistrierung_abfragen_adresszusatz' => 'adresszusatz',
                     'kundenregistrierung_abfragen_www' => 'www',
                     'kundenregistrierung_abfragen_bundesland' => 'bundesland',
                     'kundenregistrierung_abfragen_tel' => 'tel',
                     'kundenregistrierung_abfragen_mobil' => 'mobil',
                     'kundenregistrierung_abfragen_fax' => 'fax',
                     'kundenregistrierung_abfragen_ustid' => 'ustid',
                     'kundenregistrierung_abfragen_geburtstag' => 'geburtstag'
                 ] as $confKey => $dataKey) {
            if ($conf['kunden'][$confKey] === 'Y') {
                $requiredFieldsToAdd[] = $dataKey;
            }
        }
        // Add all required but not yet existing request fields as empty string.
        foreach($requiredFieldsToAdd as $req) {
            if(!\array_key_exists($req, $request)) {
                $request[$req] = '';
            }
        }
        return $request;
    }

    private function getEffectiveAccountCreationMode() {
        $createMode = $this->config->getAccountCreation();
        if ($this->context === Button::CONTEXT_LOGIN) {
            // When Login with Amazon is used, account creation is mandatory.
            $createMode = Config::ACCOUNT_CREATION_MODE_ALWAYS;
        }
        if (SessionController::hasDownloadProducts()) {
            // Having a downloadable product in the basket enforces user account creation because guests cannot access downloads after buying them.
            $createMode = Config::ACCOUNT_CREATION_MODE_ALWAYS;
        }
        if(SessionController::get(SessionController::KEY_SUBSCRIPTION_SELECTED_INTERVAL) !== null) {
            // Having selected a recurring order means you NEED to have a customer account. Else managing orders would hardly be possible and/or delivery and billing address information might vanish.
            $createMode = Config::ACCOUNT_CREATION_MODE_ALWAYS;
        }
        return $createMode;
    }

    /**
     * This method is called when the user has logged in via JTL.
     * We can use it to check if that verifies the customer for us. (= Account Merge)
     */
    public function handleRedirectAfterLogin(): void {
        /** @var UserInfo $userInfo */
        $userInfo = SessionController::get(SessionController::KEY_USER_INFO);
        if (null !== $userInfo) {
            // read the associated mapping data for the user info and the customer who just logged in.
            $jtlCustomerId = $_SESSION['Kunde']->kKunde;
            $mappingData = $this->database->findAccountMapping($userInfo->getUserId());
            if (null !== $mappingData && $mappingData->getJtlCustomerId() === $jtlCustomerId && !$mappingData->isIsVerified()) {
                $mappingData->setIsVerified(true);
                $this->database->updateMappingData($mappingData);
                // try to forward user to target location if one exists, else do nothing else and let JTL handle any redirects
                if (SessionController::get(SessionController::KEY_CUSTOMER_TARGET_LOCATION) !== null) {
                    // assume that the login was done after we asked the user to merge accounts, add a success message and redirect him
                    Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, Translation::getInstance()->get(Translation::KEY_ACCOUNT_MERGE_SUCCESSFUL), 'lpaAccountMergeSuccessful', ['dismissable' => true, 'saveInSession' => true]);
                    $this->forwardToTargetLocation();
                } else {
                    // assume the user did not consciously do an account merge, we "silently" let JTL do its things and do not force a redirect or message here. Still we can consider
                    // him being verified, e.g. for later logins
                }
            }
        }
    }

    /**
     * Gets configured custom customer fields.
     *
     * @return array
     */
    private function getCustomCustomerFields(): array {
        $customerFieldsNew = new CustomerFields();
        return $customerFieldsNew->getFields();
    }

    /**
     * Creates pre-filling data from the given BillingAddress
     * @param Address $address
     * @return array
     */
    private function prefillDataFromBillingAddress(Address $address): array {
        $billingAddress = AddressMapper::mapAddressAmazonToJtl($address, AddressMapper::ADDRESS_TYPE_BILLING);
        $prefill = [];
        if(!empty($billingAddress->cVorname)) {
            $prefill['vorname'] = $billingAddress->cVorname;
        }
        if(!empty($billingAddress->cNachname)) {
            $prefill['nachname'] = $billingAddress->cNachname;
        }
        if(!empty($billingAddress->cFirma)) {
            $prefill['firma'] = $billingAddress->cFirma;
        }
        if(!empty($billingAddress->cStrasse)) {
            $prefill['strasse'] = $billingAddress->cStrasse;
        }
        if(!empty($billingAddress->cHausnummer)) {
            $prefill['hausnummer'] = $billingAddress->cHausnummer;
        }
        if(!empty($billingAddress->cPLZ)) {
            $prefill['plz'] = $billingAddress->cPLZ;
        }
        if(!empty($billingAddress->cOrt)) {
            $prefill['ort'] = $billingAddress->cOrt;
        }
        if(!empty($billingAddress->cLand)) {
            $prefill['land'] = $billingAddress->cLand;
        }
        if(!empty($billingAddress->cBundesland)) {
            $prefill['bundesland'] = $billingAddress->cBundesland;
        }
        return $prefill;
    }

}