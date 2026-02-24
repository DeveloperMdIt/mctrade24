<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers\Admin;

use JTL\Filter\Metadata;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Utils\Crypto;
use Plugin\s360_amazonpay_shop5\lib\Utils\Currency;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;

/**
 * Class AdminConfigController
 *
 * Configuration controller.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
class AdminConfigController extends AdminController {


    private const CONTEXT_ACCOUNT = 'account';
    private const CONTEXT_CONFIG = 'config';
    private const CONTEXT_SUBSCRIPTION = 'subscription';

    private const DEFAULT_SP_COUNTRY_ISO = 'DE';
    private const DEFAULT_SP_LOCALE = 'de_DE';

    /**
     * Defines if we are in config or account context
     * @var string $context
     */
    private $context;

    /**
     * Handles the display of the Admin Account Configuration Tab.
     * @throws \Exception
     */
    public function handleAccount() {
        $this->context = self::CONTEXT_ACCOUNT;
        return $this->handle();
    }

    /**
     * Handles the display of the Admin Configuration Tab.
     * @throws \Exception
     */
    public function handleConfig() {
        $this->context = self::CONTEXT_CONFIG;
        return $this->handle();
    }

    /**
     * Handles the display of the Admin Subscription Configuration Tab.
     * @throws \Exception
     */
    public function handleSubscription() {
        $this->context = self::CONTEXT_SUBSCRIPTION;
        return $this->handle();
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function handle(): string {
        // handle any requests made
        $this->handleRequest();

        // prepare display
        $this->prepareSmartyVariables();

        if ($this->context === self::CONTEXT_ACCOUNT) {
            return $this->finalize($this->plugin->getPaths()->getAdminPath() . 'template/account.tpl');
        }
        if ($this->context === self::CONTEXT_SUBSCRIPTION) {
            return $this->finalize($this->plugin->getPaths()->getAdminPath() . 'template/subscriptions_config.tpl');
        }

        return $this->finalize($this->plugin->getPaths()->getAdminPath() . 'template/config.tpl');
    }

    private function handleRequest(): void {

        // identify request
        if ($this->context === self::CONTEXT_CONFIG) {
            if (isset($this->request['saveConfig'])) {
                $this->config->setEnvironment($this->request['environment']);
                $this->config->setHiddenButtonMode(isset($this->request['hiddenButtonMode']) && $this->request['hiddenButtonMode'] === 'on');
                $this->config->setCaptureMode($this->request['captureMode'] ?? '');
                // Discontinued: $this->config->setTemplateMode($this->request['templateMode'] ?? '');


                $this->config->setButtonLoginActive(isset($this->request['buttonLoginActive']) && $this->request['buttonLoginActive'] === 'on');
                $this->config->setButtonLoginColor($this->request['buttonLoginColor'] ?? '');
                $this->config->setButtonLoginHeight($this->request['buttonLoginHeight'] ?? '');
                $this->config->setButtonLoginPqSelector($this->request['buttonLoginPqSelector'] ?? '');
                $this->config->setButtonLoginPqMethod($this->request['buttonLoginPqMethod'] ?? '');
                $this->config->setButtonLoginCssColumns($this->request['buttonLoginCssColumns'] ?? '');


                // Button is always on. $this->config->setButtonPayActive(isset($this->request['buttonPayActive']) && $this->request['buttonPayActive'] === 'on');
                $this->config->setButtonPayHeight($this->request['buttonPayHeight'] ?? '');
                $this->config->setButtonPayColor($this->request['buttonPayColor'] ?? '');
                $this->config->setButtonPayPqSelector($this->request['buttonPayPqSelector'] ?? '');
                $this->config->setButtonPayPqMethod($this->request['buttonPayPqMethod'] ?? '');
                $this->config->setButtonPayCssColumns($this->request['buttonPayCssColumns'] ?? '');

                $this->config->setButtonPayDetailActive(isset($this->request['buttonPayDetailActive']) && $this->request['buttonPayDetailActive'] === 'on');
                $this->config->setButtonPayDetailHeight($this->request['buttonPayDetailHeight'] ?? '');
                $this->config->setButtonPayDetailColor($this->request['buttonPayDetailColor'] ?? '');
                $this->config->setButtonPayDetailPqSelector($this->request['buttonPayDetailPqSelector'] ?? '');
                $this->config->setButtonPayDetailPqMethod($this->request['buttonPayDetailPqMethod'] ?? '');
                $this->config->setButtonPayDetailCssColumns($this->request['buttonPayDetailCssColumns'] ?? '');

                $this->config->setButtonPayCategoryActive(isset($this->request['buttonPayCategoryActive']) && $this->request['buttonPayCategoryActive'] === 'on');
                $this->config->setButtonPayCategoryHeight($this->request['buttonPayCategoryHeight'] ?? '');
                $this->config->setButtonPayCategoryColor($this->request['buttonPayCategoryColor'] ?? '');
                $this->config->setButtonPayCategoryPqSelector($this->request['buttonPayCategoryPqSelector'] ?? '');
                $this->config->setButtonPayCategoryPqMethod($this->request['buttonPayCategoryPqMethod'] ?? '');
                $this->config->setButtonPayCategoryCssColumns($this->request['buttonPayCategoryCssColumns'] ?? '');

                $this->config->setAllowPackstation(isset($this->request['allowPackstation']) && $this->request['allowPackstation'] === 'on');
                $this->config->setAllowPoBox(isset($this->request['allowPoBox']) && $this->request['allowPoBox'] === 'on');
                $this->config->setAccountCreation($this->request['accountCreation'] ?? '');
                $this->config->setPasswordCreation($this->request['passwordCreation'] ?? null);
                $this->config->setAuthorizationMode($this->request['authorizationMode'] ?? '');
                $this->config->setUseAmazonPayBillingAddress(isset($this->request['useAmazonPayBillingAddress']) && $this->request['useAmazonPayBillingAddress'] === 'on');
                $this->config->setCheckAccountMerge(isset($this->request['checkAccountMerge']) && $this->request['checkAccountMerge'] === 'on');
                $this->config->setUseBehavioralOverlay(isset($this->request['useBehavioralOverlay']) && $this->request['useBehavioralOverlay'] === 'on');
                $this->config->setCronMode($this->request['cronMode'] ?? '');
                $this->config->setHidePaymentMethod(!isset($this->request['showPaymentMethod']) || $this->request['showPaymentMethod'] !== 'on'); // Semantics are turned around due to the legacy setting being about hiding the payment method
                $this->config->setAddIncomingPayments(isset($this->request['addIncomingPayments']) && $this->request['addIncomingPayments'] === 'on');
                $this->config->setDeliveryNotificationsEnabled(isset($this->request['deliveryNotificationsEnabled']) && $this->request['deliveryNotificationsEnabled'] === 'on');
                $this->config->setMultiCurrencyEnabled(isset($this->request['multiCurrencyEnabled']) && $this->request['multiCurrencyEnabled'] === 'on');
                $this->config->setShowCommentField(isset($this->request['showCommentField']) && $this->request['showCommentField'] === 'on');
                $this->config->setAlwaysAddReferenceToComment(isset($this->request['alwaysAddReferenceToComment']) && $this->request['alwaysAddReferenceToComment'] === 'on');
                $this->config->setLoginRequiredFieldsOnly(isset($this->request['loginRequiredFieldsOnly']) && $this->request['loginRequiredFieldsOnly'] === 'on');
                // TODO LATER: For now we only allow enabling/disabling of the multicurrency feature, not excluding of certain currencies.
                $this->config->setExcludedCurrencies($this->request['excludedCurrencies'] ?? []);
            }
        }

        if ($this->context === self::CONTEXT_ACCOUNT) {
            if (isset($this->request['saveSimplePathJson'])) {
                if (empty($this->request['simplePathJson'])) {
                    $this->addError(__('lpaConfigErrorJsonMissing'));
                    return;
                }
                $jsonData = json_decode(trim($this->request['simplePathJson']), true);
                if (\json_last_error() !== JSON_ERROR_NONE) {
                    $this->addError(__('lpaConfigErrorJsonInvalid'));
                    return;
                }
                // jsonData is now an assoc array with the config. Save these values to the database.
                // we use filterXSS because this information in parts might end up in the frontend
                !empty($jsonData['client_id']) ? $this->config->setClientId(Text::filterXSS($jsonData['client_id'])) : null;
                !empty($jsonData['client_Id']) ? $this->config->setClientId(Text::filterXSS($jsonData['client_Id'])) : null; // older JSON variant had this in upper case
                !empty($jsonData['merchant_id']) ? $this->config->setMerchantId(Text::filterXSS($jsonData['merchant_id'])) : null;

                $this->addSuccess(__('lpaConfigSuccessGeneric'));
                return;
            }
            if (isset($this->request['saveAccountData'])) {
                // account data is being saved manually - in this case the inputs are directly in the request
                $this->config->setRegion(!empty($this->request['region']) ? Text::filterXSS(trim($this->request['region'])) : '');
                $this->config->setClientId(!empty($this->request['clientId']) ? Text::filterXSS(trim($this->request['clientId'])) : '');
                $this->config->setMerchantId(!empty($this->request['merchantId']) ? Text::filterXSS(trim($this->request['merchantId'])) : '');

                $this->addSuccess(__('lpaConfigSuccessGeneric'));
                return;
            }
            if (isset($this->request['saveManualKeys'])) {
                // User tries to save the private key manually
                if (empty($this->request['privateKey']) || mb_strpos($this->request['privateKey'], 'PRIVATE') === false) {
                    $this->addError(__('lpaConfigErrorMissingOrInvalidPrivateKey'));
                } else {
                    $this->config->setPrivateKey(Text::filterXSS(trim($this->request['privateKey'])));
                    $this->config->setPublicKey(''); // we must be sure to override any previously saved public key
                    $this->addSuccess(__('lpaConfigSuccessKeys'));
                }
            }
            if (isset($this->request['savePublicKeyId'])) {
                // User tries to save the public key ID received from Amazon Pay
                $this->config->setPublicKeyId(!empty($this->request['publicKeyId']) ? Text::filterXSS(trim($this->request['publicKeyId'])) : '');
                $this->addSuccess(__('lpaConfigSuccessPublicKeyId'));
            }
        }

        if ($this->context === self::CONTEXT_SUBSCRIPTION) {
            if (isset($this->request['saveSubscriptionsConfig'])) {
                $this->config->setSubscriptionMode(Text::filterXSS($this->request['subscriptionMode']));
                $this->config->setSubscriptionDisplayDetail(isset($this->request['subscriptionDisplayDetail']) && $this->request['subscriptionDisplayDetail'] === 'on');
                $this->config->setSubscriptionDisplayCart(isset($this->request['subscriptionDisplayCart']) && $this->request['subscriptionDisplayCart'] === 'on');
                $this->config->setSubscriptionGlobalActive(isset($this->request['subscriptionGlobalActive']) && $this->request['subscriptionGlobalActive'] === 'on');
                $this->config->setSubscriptionGlobalInterval(Text::filterXSS($this->request['subscriptionGlobalInterval']));
                $this->config->setSubscriptionFunctionalAttributeInterval(Text::filterXSS($this->request['subscriptionFunctionalAttributeInterval']));
                $this->config->setSubscriptionOrderAttributeFlag(Text::filterXSS($this->request['subscriptionOrderAttributeFlag']));
                $this->config->setSubscriptionOrderAttributeInterval(Text::filterXSS($this->request['subscriptionOrderAttributeInterval']));
                $this->config->setSubscriptionReminderMailLeadTimeDays((int) $this->request['subscriptionReminderMailLeadTimeDays']);
                $this->config->setSubscriptionNormalizeOrderTime(isset($this->request['subscriptionNormalizeOrderTime']) && $this->request['subscriptionNormalizeOrderTime'] === 'on');
                $this->config->setSubscriptionNormalizeOrderTimeTo(Text::filterXSS($this->request['subscriptionNormalizeOrderTimeTo']));
                $this->config->setSubscriptionNotificationMailAddress(trim(Text::filterXSS($this->request['subscriptionNotificationMailAddress'])));
                $this->config->setSubscriptionCustomerAccountPqSelector(trim(Text::filterXSS($this->request['subscriptionCustomerAccountPqSelector'])));
                $this->config->setSubscriptionCustomerAccountPqMethod(trim(Text::filterXSS($this->request['subscriptionCustomerAccountPqMethod'])));
                $this->config->setSubscriptionDiscountMode(Text::filterXSS($this->request['subscriptionDiscountMode']));
                $this->config->setSubscriptionDiscountGlobal($this->request['subscriptionDiscountGlobal']);
                $this->config->setSubscriptionDiscountAttribute(!empty($this->request['subscriptionDiscountAttribute']) ? trim(Text::filterXSS($this->request['subscriptionDiscountAttribute'])) : '');
            }
        }
    }

    protected function prepareSmartyVariables(): void {

        $vars = [];

        $vars['currentConfig'] = [];
        $vars['currentConfig']['region'] = $this->config->getRegion();
        $vars['currentConfig']['environment'] = $this->config->getEnvironment();
        $vars['currentConfig']['clientId'] = $this->config->getClientId();
        $vars['currentConfig']['merchantId'] = $this->config->getMerchantId();

        $vars['currentConfig']['hiddenButtonMode'] = $this->config->isHiddenButtonMode();
        $vars['currentConfig']['captureMode'] = $this->config->getCaptureMode();
        // Discontinued: $vars['currentConfig']['templateMode'] = $this->config->getTemplateMode();


        $vars['currentConfig']['buttonLoginActive'] = $this->config->isButtonLoginActive();
        $vars['currentConfig']['buttonLoginColor'] = $this->config->getButtonLoginColor();
        $vars['currentConfig']['buttonLoginHeight'] = $this->config->getButtonLoginHeight();
        $vars['currentConfig']['buttonLoginPqSelector'] = $this->config->getButtonLoginPqSelector();
        $vars['currentConfig']['buttonLoginPqMethod'] = $this->config->getButtonLoginPqMethod();
        $vars['currentConfig']['buttonLoginCssColumns'] = $this->config->getButtonLoginCssColumns();

        $vars['currentConfig']['buttonPayActive'] = $this->config->isButtonPayActive();
        $vars['currentConfig']['buttonPayHeight'] = $this->config->getButtonPayHeight();
        $vars['currentConfig']['buttonPayColor'] = $this->config->getButtonPayColor();
        $vars['currentConfig']['buttonPayPqSelector'] = $this->config->getButtonPayPqSelector();
        $vars['currentConfig']['buttonPayPqMethod'] = $this->config->getButtonPayPqMethod();
        $vars['currentConfig']['buttonPayCssColumns'] = $this->config->getButtonPayCssColumns();

        $vars['currentConfig']['buttonPayDetailActive'] = $this->config->isButtonPayDetailActive();
        $vars['currentConfig']['buttonPayDetailHeight'] = $this->config->getButtonPayDetailHeight();
        $vars['currentConfig']['buttonPayDetailColor'] = $this->config->getButtonPayDetailColor();
        $vars['currentConfig']['buttonPayDetailPqSelector'] = $this->config->getButtonPayDetailPqSelector();
        $vars['currentConfig']['buttonPayDetailPqMethod'] = $this->config->getButtonPayDetailPqMethod();
        $vars['currentConfig']['buttonPayDetailCssColumns'] = $this->config->getButtonPayDetailCssColumns();

        $vars['currentConfig']['buttonPayCategoryActive'] = $this->config->isButtonPayCategoryActive();
        $vars['currentConfig']['buttonPayCategoryHeight'] = $this->config->getButtonPayCategoryHeight();
        $vars['currentConfig']['buttonPayCategoryColor'] = $this->config->getButtonPayCategoryColor();
        $vars['currentConfig']['buttonPayCategoryPqSelector'] = $this->config->getButtonPayCategoryPqSelector();
        $vars['currentConfig']['buttonPayCategoryPqMethod'] = $this->config->getButtonPayCategoryPqMethod();
        $vars['currentConfig']['buttonPayCategoryCssColumns'] = $this->config->getButtonPayCategoryCssColumns();

        $vars['currentConfig']['allowPackstation'] = $this->config->isAllowPackstation();
        $vars['currentConfig']['allowPoBox'] = $this->config->isAllowPoBox();
        $vars['currentConfig']['accountCreation'] = $this->config->getAccountCreation();
        $vars['currentConfig']['passwordCreation'] = $this->config->getPasswordCreation();
        $vars['currentConfig']['authorizationMode'] = $this->config->getAuthorizationMode();
        $vars['currentConfig']['useAmazonPayBillingAddress'] = $this->config->isUseAmazonPayBillingAddress();
        $vars['currentConfig']['checkAccountMerge'] = $this->config->isCheckAccountMerge();
        $vars['currentConfig']['useBehavioralOverlay'] = $this->config->isUseBehavioralOverlay();
        $vars['currentConfig']['cronMode'] = $this->config->getCronMode();
        $vars['currentConfig']['hidePaymentMethod'] = $this->config->isHidePaymentMethod();
        $vars['currentConfig']['addIncomingPayments'] = $this->config->isAddIncomingPayments();
        $vars['currentConfig']['deliveryNotificationsEnabled'] = $this->config->isDeliveryNotificationsEnabled();
        $vars['currentConfig']['multiCurrencyEnabled'] = $this->config->isMultiCurrencyEnabled();
        $vars['currentConfig']['showCommentField'] = $this->config->isShowCommentField();

        $vars['currentConfig']['alwaysAddReferenceToComment'] = $this->config->isAlwaysAddReferenceToComment();
        $vars['currentConfig']['loginRequiredFieldsOnly'] = $this->config->isLoginRequiredFieldsOnly();
        $vars['currentConfig']['excludedCurrencies'] = $this->config->getExcludedCurrencies();

        // subscription settings
        $vars['currentConfig']['subscriptionMode'] = $this->config->getSubscriptionMode();
        $vars['currentConfig']['subscriptionDisplayDetail'] = $this->config->isSubscriptionDisplayDetail();
        $vars['currentConfig']['subscriptionDisplayCart'] = $this->config->isSubscriptionDisplayCart();
        $vars['currentConfig']['subscriptionGlobalActive'] = $this->config->isSubscriptionGlobalActive();
        $vars['currentConfig']['subscriptionGlobalInterval'] = $this->config->getSubscriptionGlobalInterval();
        $vars['currentConfig']['subscriptionFunctionalAttributeInterval'] = $this->config->getSubscriptionFunctionalAttributeInterval();
        $vars['currentConfig']['subscriptionOrderAttributeFlag'] = $this->config->getSubscriptionOrderAttributeFlag();
        $vars['currentConfig']['subscriptionOrderAttributeInterval'] = $this->config->getSubscriptionOrderAttributeInterval();
        $vars['currentConfig']['subscriptionReminderMailLeadTimeDays'] = $this->config->getSubscriptionReminderMailLeadTimeDays();
        $vars['currentConfig']['subscriptionNormalizeOrderTime'] = $this->config->isSubscriptionNormalizeOrderTime();
        $vars['currentConfig']['subscriptionNormalizeOrderTimeTo'] = $this->config->getSubscriptionNormalizeOrderTimeTo();
        $vars['currentConfig']['subscriptionNotificationMailAddress'] = $this->config->getSubscriptionNotificationMailAddress();
        $vars['currentConfig']['subscriptionCustomerAccountPqSelector'] = $this->config->getSubscriptionCustomerAccountPqSelector();
        $vars['currentConfig']['subscriptionCustomerAccountPqMethod'] = $this->config->getSubscriptionCustomerAccountPqMethod();
        $vars['currentConfig']['subscriptionDiscountMode'] = $this->config->getSubscriptionDiscountMode();
        $vars['currentConfig']['subscriptionDiscountGlobal'] = $this->config->getSubscriptionDiscountGlobal();
        $vars['currentConfig']['subscriptionDiscountAttribute'] = $this->config->getSubscriptionDiscountAttribute();
        // Feature flag
        $vars['currentConfig']['subscriptionDiscountFeatureEnabled'] = $this->config->isSubscriptionDiscountFeatureEnabled();


        $vars['configuredCurrencies'] = Currency::getInstance()->getShopCurrencyCodes();

        // additionally load defaults, to enable the frontend to reset the values if necessary
        $vars['defaultConfig'] = $this->config->getDefaultValues();

        $vars['checkoutEndpointUrl'] = $this->config->getCheckoutEndpointUrl();
        $vars['publicKeyId'] = $this->config->getPublicKeyId();

        $defaultLanguageId = LanguageHelper::getDefaultLanguage(true)->kSprache;
        if ($this->context === self::CONTEXT_ACCOUNT) {

            $publicKey = $this->config->getPublicKey();
            $privateKey = $this->config->getPrivateKey();

            $vars['keysGenerated'] = false;
            if (empty($publicKey)) {
                // No public key set
                if (empty($privateKey)) {
                    // No private key set, either - generate both.
                    if (Crypto::getInstance()->createKeyPair()) {
                        $publicKey = $this->config->getPublicKey();
                        $privateKey = $this->config->getPrivateKey();
                        $vars['publicKeyId'] = ''; // creating new keys automatically invalidates the publicKeyId, so we reset it for the template as well.
                        $vars['keysGenerated'] = true;
                    }
                }
            }

            // fallback for missing key exchange token
            if($this->config->getKeyExchangeToken() === null) {
                Crypto::getInstance()->createKeyExchangeToken();
            }

            $vars['formTargetUrl'] = JtlLinkHelper::getInstance()->getFullUrlForAdminTab(JtlLinkHelper::ADMIN_TAB_ACCOUNT);
            $vars['ipnUrl'] = JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_IPN);
            $vars['allowedJsOrigin'] = JtlLinkHelper::getInstance()->getShopDomain();
            $vars['spId'] = $this->config->getPlatformId();
            $vars['spUniqueId'] = 'LPA-SP-' . preg_replace('/[^A-Za-z0-9]/', '', Shop::getURL(true));
            $vars['spLocale'] = self::DEFAULT_SP_LOCALE;
            $vars['privateKeyExists'] = !empty($privateKey);
            $vars['publicKey'] = $publicKey;
            $vars['spPublicKey'] = $this->formatPublicKey($publicKey);
            $vars['keyShareURL'] = JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_AUTO_KEY_EXCHANGE, $defaultLanguageId) . '?auth=' . $this->config->getKeyExchangeToken();
            $vars['spSoftwareVersion'] = \APPLICATION_VERSION;
            $vars['spAmazonPluginVersion'] = $this->plugin->getCurrentVersion()->getOriginalVersion();
            $vars['merchantStoreDescription'] = '';
            $metadata = Metadata::getGlobalMetaData();
            if (!empty($metadata) && array_key_exists($defaultLanguageId, $metadata) && !empty($metadata[$defaultLanguageId]->Title)) {
                $vars['merchantStoreDescription'] = $metadata[$defaultLanguageId]->Title;
            }
            $firma = Shop::Container()->getDB()->select('tfirma', [], []);
            $vars['merchantCountry'] = isset($firma->cLand) ? $this->mapCountryNameToIso($firma->cLand) : self::DEFAULT_SP_COUNTRY_ISO;
            $vars['merchantPrivacyNoticeURL'] = JtlLinkHelper::getInstance()->getPrivacyNoticeUrl($defaultLanguageId);
            $vars['allowedReturnUrl'] = JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_RETURN, $defaultLanguageId);

            Shop::Smarty()->assign('lpaAccount', $vars);
        } elseif($this->context === self::CONTEXT_SUBSCRIPTION) {
            $vars['formTargetUrl'] = JtlLinkHelper::getInstance()->getFullUrlForAdminTab(JtlLinkHelper::ADMIN_TAB_SUBSCRIPTION_CONFIG);
            Shop::Smarty()->assign('lpaSubscription', $vars);
        } else {
            $vars['formTargetUrl'] = JtlLinkHelper::getInstance()->getFullUrlForAdminTab(JtlLinkHelper::ADMIN_TAB_CONFIG);
            Shop::Smarty()->assign('lpaConfig', $vars);
        }
    }

    private function mapCountryNameToIso($cLand): string {
        $result = Shop::Container()->getDB()->select('tland', 'cDeutsch', $cLand);
        if (empty($result)) {
            $result = Shop::Container()->getDB()->select('tland', 'cEnglisch', $cLand);
        }
        return $result->cISO ?? self::DEFAULT_SP_COUNTRY_ISO;
    }

    private function formatPublicKey($publicKey) {
        if (empty($publicKey)) {
            return '';
        }
        $result = str_replace(
            [
                '-----BEGIN PUBLIC KEY-----',
                '-----BEGIN RSA PUBLIC KEY-----',
                '-----END PUBLIC KEY-----',
                '-----END RSA PUBLIC KEY-----',
                "\n"
            ],
            ['', '', '', '', ''],
            $publicKey
        );
        // Remove binary characters
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $result);
    }
}