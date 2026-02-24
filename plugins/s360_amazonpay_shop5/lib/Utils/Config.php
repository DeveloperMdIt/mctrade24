<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use JTL\Plugin\PluginInterface;
use JTL\Shop;

/**
 * Class Config
 *
 * Covers all plugin specific configuration needs.
 * It contains static configurations, plugin default settings and plugin custom settings.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
class Config {

    use JtlLoggerTrait;

    private const PLATFORM_ID = 'A3T9U9SJWH1V2O';
    private const APPLICATION_NAME = 'Solution360 AmazonPay APIv2 Plugin for JTL-Shop 5';
    private const CUSTOM_INFORMATION_PREFIX = 'created by Solution 360 GmbH, JTL-Shop ';

    public const REGION_DE = 'de';
    public const REGION_UK = 'uk';
    public const REGION_EU = 'eu';
    public const REGION_NA = 'na';
    public const REGION_US = 'us';
    public const REGION_JP = 'jp';

    public const ENVIRONMENT_SANDBOX = 'sandbox';
    public const ENVIRONMENT_PRODUCTION = 'production';

    public const PASSWORD_CREATION_MODE_GENERATE = 'generate';
    public const PASSWORD_CREATION_MODE_INPUT = 'input';

    public const ACCOUNT_CREATION_MODE_ALWAYS = 'always';
    public const ACCOUNT_CREATION_MODE_NEVER = 'never';
    public const ACCOUNT_CREATION_MODE_OPTIONAL = 'optional';

    public const CAPTURE_MODE_IMMEDIATE = 'immediate';
    public const CAPTURE_MODE_ON_SHIPPING_PARTIAL = 'onShippingPartial';
    public const CAPTURE_MODE_ON_SHIPPING_COMPLETE = 'onShippingComplete';
    public const CAPTURE_MODE_MANUAL = 'manual';

    public const AUTHORIZATION_MODE_OMNI = 'omni';
    public const AUTHORIZATION_MODE_SYNC = 'sync';

    public const CRON_MODE_OFF = 'off';
    public const CRON_MODE_SYNC = 'sync';
    public const CRON_MODE_TASK = 'task';
    public const BUTTON_MIN_HEIGHT = 45;
    public const BUTTON_MAX_HEIGHT = 190;

    // These values are used to write boolean configs into the database. (They MUST properly cast to bool!)
    private const FALSE = '0';
    private const TRUE = '1';

    private const ENDPOINT_TYPE_MWS = 'MWS';
    private const ENDPOINT_TYPE_WIDGET = 'Widget';
    private const ENDPOINT_TYPE_LOGIN = 'Login';
    private const ENDPOINT_TYPE_PROFILE = 'Profile';
    private const ENDPOINT_TYPE_CHECKOUT = 'Checkout';

    private const CONFIG_SECTION_ACCOUNT = 'account';
    private const CONFIG_SECTION_GENERAL = 'general';
    private const CONFIG_SECTION_BUTTONS = 'buttons';
    private const CONFIG_SECTION_CHECKOUT = 'checkout';
    private const CONFIG_SECTION_ADVANCED = 'advanced';

    public const SUBSCRIPTION_MODE_ACTIVE = 'active';
    public const SUBSCRIPTION_MODE_EXISTING_ONLY = 'existingOnly';
    public const SUBSCRIPTION_MODE_INACTIVE = 'inactive';
    public const SUBSCRIPTION_DISCOUNT_MODE_INACTIVE = 'inactive';
    public const SUBSCRIPTION_DISCOUNT_MODE_GLOBAL = 'global';
    public const SUBSCRIPTION_DISCOUNT_MODE_ATTRIBUTE = 'attribute';

    /**
     * This is called "locale" in Amazon Pay terminology, while it actually is a currency code - EUR / USD / GBP
     */
    private const DEFAULT_LOCALE = 'EUR';

    /**
     * All configuration default values. Can be used to reset parts of or the whole configuration.
     */
    private const DEFAULT_VALUES = [
        // account settings
        'environment' => 'sandbox',
        'region' => 'de',
        // general settings
        'hiddenButtonMode' => true,
        'captureMode' => self::CAPTURE_MODE_IMMEDIATE,
        // template compatibility - Cannot be modified in admin anymore because EVO was discontinued.
        'templateMode' => 'nova',
        // login button
        'buttonLoginActive' => true,
        'buttonLoginColor' => 'Gold',
        'buttonLoginCssColumns' => 'col-12',
        'buttonLoginHeight' => 45,
        'buttonLoginPqSelector' => '#quick-login, #login_form, fieldset.quick_login',
        'buttonLoginPqMethod' => 'append',
        // pay button global / basket
        'buttonPayActive' => true,
        'buttonPayColor' => 'Gold',
        'buttonPayCssColumns' => 'col-12',
        'buttonPayHeight' => 60,
        'buttonPayPqSelector' => '.cart-summary .card > .card-body, .cart-dropdown-buttons',
        'buttonPayPqMethod' => 'append',
        // pay button detail express
        'buttonPayDetailActive' => true,
        'buttonPayDetailColor' => 'Gold',
        'buttonPayDetailCssColumns' => 'col-12 offset-md-6 col-md-6',
        'buttonPayDetailHeight' => 60,
        'buttonPayDetailPqSelector' => '#add-to-cart',
        'buttonPayDetailPqMethod' => 'append',
        // pay button category express
        'buttonPayCategoryActive' => true,
        'buttonPayCategoryColor' => 'Gold',
        'buttonPayCategoryCssColumns' => 'col-12',
        'buttonPayCategoryHeight' => 45,
        'buttonPayCategoryPqSelector' => '#buy_form_#kArtikel# .productbox-actions',
        'buttonPayCategoryPqMethod' => 'append',
        // checkout and advanced settings
        'allowPackstation' => false,
        'allowPoBox' => false,
        'accountCreation' => self::ACCOUNT_CREATION_MODE_OPTIONAL,
        'passwordCreation' => self::PASSWORD_CREATION_MODE_INPUT,
        'authorizationMode' => self::AUTHORIZATION_MODE_SYNC,
        'useAmazonPayBillingAddress' => false,
        'checkAccountMerge' => true,
        'useBehavioralOverlay' => false,
        'cronMode' => self::CRON_MODE_SYNC,
        'hidePaymentMethod' => false,
        'addIncomingPayments' => true,
        'deliveryNotificationsEnabled' => true,
        'multiCurrencyEnabled' => false,
        'showCommentField' => false,
        'alwaysAddReferenceToComment' => false,
        'loginRequiredFieldsOnly' => true,
        // subscription settings
        'subscriptionMode' => self::SUBSCRIPTION_MODE_INACTIVE,
        'subscriptionDisplayDetail' => true,
        'subscriptionDisplayCart' => true,
        'subscriptionGlobalActive' => false,
        'subscriptionGlobalInterval' => '1w,2w,1m,3m,1y',
        'subscriptionFunctionalAttributeInterval' => 'amazonpay_abo_intervall',
        'subscriptionOrderAttributeFlag' => 'amazonpay_abo_bestellung',
        'subscriptionOrderAttributeInterval' => 'amazonpay_abo_intervall',
        'subscriptionReminderMailLeadTimeDays' => 0,
        'subscriptionNormalizeOrderTime' => false,
        'subscriptionNormalizeOrderTimeTo' => '08:00',
        'subscriptionNotificationMailAddress' => '',
        'subscriptionCustomerAccountPqSelector' => '#account',
        'subscriptionCustomerAccountPqMethod' => 'append',
        'subscriptionDiscountMode' => self::SUBSCRIPTION_DISCOUNT_MODE_INACTIVE,
        'subscriptionDiscountGlobal' => 5,
        'subscriptionDiscountAttribute' => 'amazonpay_abo_rabatt',
    ];

    /**
     * Endpoint-URLs according to integration guide:
     *
     * https://images-na.ssl-images-amazon.com/images/G/02/mwsportal/doc/en_US/offamazonpayments/LoginAndPayWithAmazonIntegrationGuide._V326922526_.pdf
     *
     * The structure is Type => Region => Environment (=> Subtype [for Type Profile]) => URL
     */
    private const ENDPOINTS = array(
        self::ENDPOINT_TYPE_MWS => array(
            self::REGION_DE => array(
                self::ENVIRONMENT_SANDBOX => 'https://mws-eu.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01',
                self::ENVIRONMENT_PRODUCTION => 'https://mws-eu.amazonservices.com/OffAmazonPayments/2013-01-01'
            ),
            self::REGION_EU => array(
                self::ENVIRONMENT_SANDBOX => 'https://mws-eu.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01',
                self::ENVIRONMENT_PRODUCTION => 'https://mws-eu.amazonservices.com/OffAmazonPayments/2013-01-01'
            ),
            self::REGION_UK => array(
                self::ENVIRONMENT_SANDBOX => 'https://mws-eu.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01',
                self::ENVIRONMENT_PRODUCTION => 'https://mws-eu.amazonservices.com/OffAmazonPayments/2013-01-01'
            ),
            self::REGION_NA => array(
                self::ENVIRONMENT_SANDBOX => 'https://mws.amazonservices.com/OffAmazonPayments_Sandbox/2013-01-01',
                self::ENVIRONMENT_PRODUCTION => 'https://mws.amazonservices.com/OffAmazonPayments/2013-01-01'
            )
        ),
        self::ENDPOINT_TYPE_WIDGET => array(
            self::REGION_DE => array(
                self::ENVIRONMENT_SANDBOX => 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/sandbox/lpa/js/Widgets.js',
                self::ENVIRONMENT_PRODUCTION => 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/lpa/js/Widgets.js'
            ),
            self::REGION_UK => array(
                self::ENVIRONMENT_SANDBOX => 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/sandbox/lpa/js/Widgets.js',
                self::ENVIRONMENT_PRODUCTION => 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/lpa/js/Widgets.js'
            ),
            self::REGION_US => array(
                self::ENVIRONMENT_SANDBOX => 'https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js',
                self::ENVIRONMENT_PRODUCTION => 'https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js'
            )
        ),
        self::ENDPOINT_TYPE_CHECKOUT => array(
            self::REGION_DE => array(
                self::ENVIRONMENT_SANDBOX => 'https://static-eu.payments-amazon.com/checkout.js',
                self::ENVIRONMENT_PRODUCTION => 'https://static-eu.payments-amazon.com/checkout.js'
            ),
            self::REGION_UK => array(
                self::ENVIRONMENT_SANDBOX => 'https://static-eu.payments-amazon.com/checkout.js',
                self::ENVIRONMENT_PRODUCTION => 'https://static-eu.payments-amazon.com/checkout.js'
            ),
            self::REGION_EU => array(
                self::ENVIRONMENT_SANDBOX => 'https://static-eu.payments-amazon.com/checkout.js',
                self::ENVIRONMENT_PRODUCTION => 'https://static-eu.payments-amazon.com/checkout.js'
            ),
            self::REGION_US => array(
                self::ENVIRONMENT_SANDBOX => 'https://static-na.payments-amazon.com/checkout.js',
                self::ENVIRONMENT_PRODUCTION => 'https://static-na.payments-amazon.com/checkout.js'
            ),
            self::REGION_NA => array(
                self::ENVIRONMENT_SANDBOX => 'https://static-na.payments-amazon.com/checkout.js',
                self::ENVIRONMENT_PRODUCTION => 'https://static-na.payments-amazon.com/checkout.js'
            )
        ),
        self::ENDPOINT_TYPE_LOGIN => array(
            self::REGION_DE => array(
                self::ENVIRONMENT_SANDBOX => 'https://assets.loginwithamazon.com/sdk/eu/login1.js',
                self::ENVIRONMENT_PRODUCTION => 'https://assets.loginwithamazon.com/sdk/eu/login1.js'
            ),
            self::REGION_UK => array(
                self::ENVIRONMENT_SANDBOX => 'https://assets.loginwithamazon.com/sdk/eu/login1.js',
                self::ENVIRONMENT_PRODUCTION => 'https://assets.loginwithamazon.com/sdk/eu/login1.js'
            ),
            self::REGION_EU => array(
                self::ENVIRONMENT_SANDBOX => 'https://assets.loginwithamazon.com/sdk/eu/login1.js',
                self::ENVIRONMENT_PRODUCTION => 'https://assets.loginwithamazon.com/sdk/eu/login1.js'
            ),
            self::REGION_US => array(
                self::ENVIRONMENT_SANDBOX => 'https://assets.loginwithamazon.com/sdk/na/login1.js',
                self::ENVIRONMENT_PRODUCTION => 'https://assets.loginwithamazon.com/sdk/na/login1.js'
            ),
            self::REGION_NA => array(
                self::ENVIRONMENT_SANDBOX => 'https://assets.loginwithamazon.com/sdk/na/login1.js',
                self::ENVIRONMENT_PRODUCTION => 'https://assets.loginwithamazon.com/sdk/na/login1.js'
            )
        ),
        self::ENDPOINT_TYPE_PROFILE => array(
            self::REGION_DE => array(
                self::ENVIRONMENT_SANDBOX => array(
                    'info' => 'https://api.sandbox.amazon.de/auth/o2/tokeninfo?access_token=',
                    'base' => 'https://api.sandbox.amazon.de/user/profile'
                ),
                self::ENVIRONMENT_PRODUCTION => array(
                    'info' => 'https://api.amazon.de/auth/o2/tokeninfo?access_token=',
                    'base' => 'https://api.amazon.de/user/profile'
                )
            ),
            self::REGION_UK => array(
                self::ENVIRONMENT_SANDBOX => array(
                    'info' => 'https://api.sandbox.amazon.co.uk/auth/o2/tokeninfo?access_token=',
                    'base' => 'https://api.sandbox.amazon.co.uk/user/profile'
                ),
                self::ENVIRONMENT_PRODUCTION => array(
                    'info' => 'https://api.amazon.co.uk/auth/o2/tokeninfo?access_token=',
                    'base' => 'https://api.amazon.co.uk/user/profile'
                )
            ),
            self::REGION_US => array(
                self::ENVIRONMENT_SANDBOX => array(
                    'info' => 'https://api.sandbox.amazon.com/auth/o2/tokeninfo?access_token=',
                    'base' => 'https://api.sandbox.amazon.com/user/profile'
                ),
                self::ENVIRONMENT_PRODUCTION => array(
                    'info' => 'https://api.amazon.com/auth/o2/tokeninfo?access_token=',
                    'base' => 'https://api.amazon.com/user/profile'
                )
            )
        )
    );

    public const CACHING_GROUP_ID = 'lpaConfigGroup';

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var PluginInterface $plugin
     */
    private $plugin;

    private static $instance;

    /**
     * Gets the config instance.
     * @return Config
     */
    public static function getInstance(): Config {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->database = Database::getInstance();
        $this->plugin = Plugin::getInstance();
    }

    public function setHiddenButtonMode(bool $param): void {
        $this->setConfigValue('hiddenButtonMode', $param ? self::TRUE : self::FALSE);
    }

    public function setCaptureMode($param): void {
        $this->setConfigValue('captureMode', $param);
    }

    public function setTemplateMode($param): void {
        $this->setConfigValue('templateMode', $param);
    }

    public function setButtonLoginActive(bool $param): void {
        $this->setConfigValue('buttonLoginActive', $param ? self::TRUE : self::FALSE);
    }

    public function setButtonLoginColor($param): void {
        $this->setConfigValue('buttonLoginColor', $param);
    }

    public function setButtonLoginHeight($param): void {
        $param = $this->limitButtonHeight($param);
        $this->setConfigValue('buttonLoginHeight', $param);
    }

    public function setButtonLoginPqSelector($param): void {
        $this->setConfigValue('buttonLoginPqSelector', $param);
    }

    public function setButtonLoginPqMethod($param): void {
        $this->setConfigValue('buttonLoginPqMethod', $param);
    }

    public function setButtonPayActive(bool $param): void {
        $this->setConfigValue('buttonPayActive', $param ? self::TRUE : self::FALSE);
    }

    public function setButtonPayColor($param): void {
        $this->setConfigValue('buttonPayColor', $param);
    }

    public function setButtonPayHeight($param): void {
        $param = $this->limitButtonHeight($param);
        $this->setConfigValue('buttonPayHeight', $param);
    }

    public function setButtonPayPqSelector($param): void {
        $this->setConfigValue('buttonPayPqSelector', $param);
    }

    public function setButtonPayPqMethod($param): void {
        $this->setConfigValue('buttonPayPqMethod', $param);
    }

    public function setButtonPayDetailActive(bool $param): void {
        $this->setConfigValue('buttonPayDetailActive', $param ? self::TRUE : self::FALSE);
    }

    public function setButtonPayDetailColor($param): void {
        $this->setConfigValue('buttonPayDetailColor', $param);
    }

    public function setButtonPayDetailHeight($param): void {
        $param = $this->limitButtonHeight($param);
        $this->setConfigValue('buttonPayDetailHeight', $param);
    }

    public function setButtonPayDetailPqSelector($param): void {
        $this->setConfigValue('buttonPayDetailPqSelector', $param);
    }

    public function setButtonPayDetailPqMethod($param): void {
        $this->setConfigValue('buttonPayDetailPqMethod', $param);
    }

    public function setButtonPayCategoryActive(bool $param): void {
        $this->setConfigValue('buttonPayCategoryActive', $param ? self::TRUE : self::FALSE);
    }

    public function setButtonPayCategoryColor($param): void {
        $this->setConfigValue('buttonPayCategoryColor', $param);
    }

    public function setButtonPayCategoryHeight($param): void {
        $param = $this->limitButtonHeight($param);
        $this->setConfigValue('buttonPayCategoryHeight', $param);
    }

    public function setButtonPayCategoryPqSelector($param): void {
        $this->setConfigValue('buttonPayCategoryPqSelector', $param);
    }

    public function setButtonPayCategoryPqMethod($param): void {
        $this->setConfigValue('buttonPayCategoryPqMethod', $param);
    }

    public function setAllowPackstation(bool $param): void {
        $this->setConfigValue('allowPackstation', $param ? self::TRUE : self::FALSE);
    }

    public function setAllowPoBox(bool $param): void {
        $this->setConfigValue('allowPoBox', $param ? self::TRUE : self::FALSE);
    }

    public function setAccountCreation($param): void {
        $this->setConfigValue('accountCreation', $param);
    }

    public function setPasswordCreation($param): void {
        $this->setConfigValue('passwordCreation', $param);
    }

    public function setAuthorizationMode($param): void {
        $this->setConfigValue('authorizationMode', $param);
    }

    public function setUseAmazonPayBillingAddress(bool $param): void {
        $this->setConfigValue('useAmazonPayBillingAddress', $param ? self::TRUE : self::FALSE);
    }

    public function setCheckAccountMerge(bool $param): void {
        $this->setConfigValue('checkAccountMerge', $param ? self::TRUE : self::FALSE);
    }

    public function setUseBehavioralOverlay(bool $param): void {
        $this->setConfigValue('useBehavioralOverlay', $param ? self::TRUE : self::FALSE);
    }

    public function setExcludedCurrencies(array $param): void {
        $this->setConfigValue('excludedCurrencies', empty($param) ? '' : implode(',', $param));
    }

    public function setCronMode($param): void {
        $previousMode = $this->getCronMode();
        $this->setConfigValue('cronMode', $param);

        // Additionally, we have to manipulate the tcron table IF we are to run the cron as a regular cron task within the shop.
        if ($previousMode === self::CRON_MODE_TASK && $param !== self::CRON_MODE_TASK) {
            $this->disableCronjob();
        }
        if ($previousMode !== self::CRON_MODE_TASK && $param === self::CRON_MODE_TASK) {
            $this->enableCronjob();
        }
    }

    public function setHidePaymentMethod(bool $param): void {
        $this->setConfigValue('hidePaymentMethod', $param ? self::TRUE : self::FALSE);
    }

    public function setAddIncomingPayments(bool $param): void {
        $this->setConfigValue('addIncomingPayments', $param ? self::TRUE : self::FALSE);
    }

    public function setDeliveryNotificationsEnabled(bool $param): void {
        $this->setConfigValue('deliveryNotificationsEnabled', $param ? self::TRUE : self::FALSE);
    }

    public function setMultiCurrencyEnabled($param) {
        $this->setConfigValue('multiCurrencyEnabled', $param ? self::TRUE : self::FALSE);
    }

    public function setShowCommentField($param) {
        $this->setConfigValue('showCommentField', $param ? self::TRUE : self::FALSE);
    }

    public function setAlwaysAddReferenceToComment($param) {
        $this->setConfigValue('alwaysAddReferenceToComment', $param ? self::TRUE : self::FALSE);
    }

    public function setLoginRequiredFieldsOnly($param) {
        $this->setConfigValue('loginRequiredFieldsOnly', $param ? self::TRUE : self::FALSE);
    }

    // this is not an actual configuration value that can be set from outside
    public function setLastCronRunTimestamp($param): void {
        $this->setConfigValue('lastCronRunTimestamp', (string)$param);
    }


    /**
     * @return PluginInterface
     */
    private function getPlugin(): PluginInterface {
        return $this->plugin;
    }


    /**
     * The platform ID.
     * @return string
     */
    public function getPlatformId(): string {
        return self::PLATFORM_ID;
    }

    /**
     * The application name.
     * @return string
     */
    public function getApplicationName(): string {
        return self::APPLICATION_NAME;
    }

    /**
     * The version as set in the info.xml
     * @return string
     */
    public function getPluginVersion(): string {
        return $this->getPlugin()->getMeta()->getVersion();
    }

    /**
     * Custom information as defined by Amazon Pay.
     * @return string
     */
    public function getCustomInformation(): string {
        return self::CUSTOM_INFORMATION_PREFIX . \APPLICATION_VERSION . ', ' . $this->getPluginVersion();
    }

    public function getRegion(): ?string {
        return $this->getConfigValue('region', self::REGION_DE);
    }

    /**
     * Don't use a global currency code, just return null.
     * @return null|string
     */
    public function getCurrencyCode(): ?string {
        return null;
    }

    public function getEnvironment(): ?string {
        return $this->getConfigValue('environment', self::ENVIRONMENT_SANDBOX);
    }

    public function isSandbox(): bool {
        return ($this->getEnvironment() === self::ENVIRONMENT_SANDBOX);
    }

    public function isHiddenButtonMode(): bool {
        return ((bool)$this->getConfigValue('hiddenButtonMode', self::DEFAULT_VALUES['hiddenButtonMode']));
    }

    public function getClientId(): ?string {
        return $this->getConfigValue('clientId');
    }

    public function getCaptureMode() {
        return $this->getConfigValue('captureMode', self::DEFAULT_VALUES['captureMode']);
    }

    public function getTemplateMode() {
        return $this->getConfigValue('templateMode', self::DEFAULT_VALUES['templateMode']);
    }

    public function isButtonLoginActive(): bool {
        return (bool)$this->getConfigValue('buttonLoginActive', self::DEFAULT_VALUES['buttonLoginActive']);
    }

    public function getButtonLoginColor() {
        return $this->getConfigValue('buttonLoginColor', self::DEFAULT_VALUES['buttonLoginColor']);
    }

    public function getButtonLoginHeight() {
        return $this->getConfigValue('buttonLoginHeight', self::DEFAULT_VALUES['buttonLoginHeight']);
    }

    public function getButtonLoginPqSelector() {
        return $this->getConfigValue('buttonLoginPqSelector', self::DEFAULT_VALUES['buttonLoginPqSelector']);
    }

    public function getButtonLoginPqMethod() {
        return $this->getConfigValue('buttonLoginPqMethod', self::DEFAULT_VALUES['buttonLoginPqMethod']);
    }

    public function isButtonPayActive(): bool {
        return true;
        // Pay button is always active; return (bool)$this->getConfigValue('buttonPayActive', self::DEFAULT_VALUES['buttonPayActive']);
    }

    public function getButtonPayColor() {
        return $this->getConfigValue('buttonPayColor', self::DEFAULT_VALUES['buttonPayColor']);
    }

    public function getButtonPayHeight() {
        return $this->getConfigValue('buttonPayHeight', self::DEFAULT_VALUES['buttonPayHeight']);
    }

    public function getButtonPayPqSelector() {
        return $this->getConfigValue('buttonPayPqSelector', self::DEFAULT_VALUES['buttonPayPqSelector']);
    }

    public function getButtonPayPqMethod() {
        return $this->getConfigValue('buttonPayPqMethod', self::DEFAULT_VALUES['buttonPayPqMethod']);
    }

    public function isButtonPayDetailActive(): bool {
        return (bool)$this->getConfigValue('buttonPayDetailActive', self::DEFAULT_VALUES['buttonPayDetailActive']);
    }

    public function getButtonPayDetailColor() {
        return $this->getConfigValue('buttonPayDetailColor', self::DEFAULT_VALUES['buttonPayDetailColor']);
    }

    public function getButtonPayDetailHeight() {
        return $this->getConfigValue('buttonPayDetailHeight', self::DEFAULT_VALUES['buttonPayDetailHeight']);
    }

    public function getButtonPayDetailPqSelector() {
        return $this->getConfigValue('buttonPayDetailPqSelector', self::DEFAULT_VALUES['buttonPayDetailPqSelector']);
    }

    public function getButtonPayDetailPqMethod() {
        return $this->getConfigValue('buttonPayDetailPqMethod', self::DEFAULT_VALUES['buttonPayDetailPqMethod']);
    }

    public function isButtonPayCategoryActive(): bool {
        return (bool)$this->getConfigValue('buttonPayCategoryActive', self::DEFAULT_VALUES['buttonPayCategoryActive']);
    }

    public function getButtonPayCategoryColor() {
        return $this->getConfigValue('buttonPayCategoryColor', self::DEFAULT_VALUES['buttonPayCategoryColor']);
    }

    public function getButtonPayCategoryHeight() {
        return $this->getConfigValue('buttonPayCategoryHeight', self::DEFAULT_VALUES['buttonPayCategoryHeight']);
    }

    public function getButtonPayCategoryPqSelector() {
        return $this->getConfigValue('buttonPayCategoryPqSelector', self::DEFAULT_VALUES['buttonPayCategoryPqSelector']);
    }

    public function getButtonPayCategoryPqMethod() {
        return $this->getConfigValue('buttonPayCategoryPqMethod', self::DEFAULT_VALUES['buttonPayCategoryPqMethod']);
    }

    public function isAllowPackstation(): bool {
        return (bool)$this->getConfigValue('allowPackstation', self::DEFAULT_VALUES['allowPackstation']);
    }

    public function isAllowPoBox(): bool {
        return (bool)$this->getConfigValue('allowPoBox', self::DEFAULT_VALUES['allowPoBox']);
    }

    public function getAccountCreation() {
        return $this->getConfigValue('accountCreation', self::DEFAULT_VALUES['accountCreation']);
    }

    public function getPasswordCreation() {
        return $this->getConfigValue('passwordCreation', self::DEFAULT_VALUES['passwordCreation']);
    }

    public function getAuthorizationMode() {
        return $this->getConfigValue('authorizationMode', self::DEFAULT_VALUES['authorizationMode']);
    }

    public function isUseAmazonPayBillingAddress(): bool {
        return (bool)$this->getConfigValue('useAmazonPayBillingAddress', self::DEFAULT_VALUES['useAmazonPayBillingAddress']);
    }

    public function isCheckAccountMerge(): bool {
        return (bool)$this->getConfigValue('checkAccountMerge', self::DEFAULT_VALUES['checkAccountMerge']);
    }

    public function isUseBehavioralOverlay(): bool {
        return (bool)$this->getConfigValue('useBehavioralOverlay', self::DEFAULT_VALUES['useBehavioralOverlay']);
    }

    public function getExcludedCurrencies(): array {
        return explode(',', $this->getConfigValue('excludedCurrencies', ''));
    }

    public function getCronMode() {
        return $this->getConfigValue('cronMode', self::DEFAULT_VALUES['cronMode']);
    }

    public function isHidePaymentMethod(): bool {
        return (bool)$this->getConfigValue('hidePaymentMethod', self::DEFAULT_VALUES['hidePaymentMethod']);
    }

    public function isAddIncomingPayments(): bool {
        return (bool)$this->getConfigValue('addIncomingPayments', self::DEFAULT_VALUES['addIncomingPayments']);
    }

    public function isDeliveryNotificationsEnabled(): bool {
        return (bool)$this->getConfigValue('deliveryNotificationsEnabled', self::DEFAULT_VALUES['deliveryNotificationsEnabled']);
    }

    public function isMultiCurrencyEnabled() {
        return (bool)$this->getConfigValue('multiCurrencyEnabled', self::DEFAULT_VALUES['multiCurrencyEnabled']);
    }

    public function isShowCommentField() {
        return (bool)$this->getConfigValue('showCommentField', self::DEFAULT_VALUES['showCommentField']);
    }

    public function isAlwaysAddReferenceToComment() {
        return (bool)$this->getConfigValue('alwaysAddReferenceToComment', self::DEFAULT_VALUES['alwaysAddReferenceToComment']);
    }

    public function isLoginRequiredFieldsOnly() {
        return (bool)$this->getConfigValue('loginRequiredFieldsOnly', self::DEFAULT_VALUES['loginRequiredFieldsOnly']);
    }

    // This is not an actual configuration but we use the config mechanics to avoid having a special table just for the cron run timestamp
    public function getLastCronRunTimestamp(): ?int {
        $lastTimestamp = $this->getConfigValue('lastCronRunTimestamp');
        return $lastTimestamp === null ? null : (int)$lastTimestamp;
    }

    public function getLastSubscriptionPrenotificationTimestamp(): ?int {
        $lastTimestamp = $this->getConfigValue('lastSubscriptionPrenotificationTimestamp');
        return $lastTimestamp === null ? null : (int)$lastTimestamp;
    }


    public function setLastSubscriptionPrenotificationTimestamp($param): void {
       $this->setConfigValue('lastSubscriptionPrenotificationTimestamp', (string) $param);
    }

    /**
     * Make Amazon Pay by default handle throttling itself.
     * @return bool
     */
    public function isHandleThrottle(): bool {
        return true;
    }

    /**
     * Returns the URL for the Widget Endpoint depending on the current configuration.
     * @return string|null
     */
    public function getWidgetEndpointUrl(): ?string {
        return self::ENDPOINTS[self::ENDPOINT_TYPE_WIDGET][$this->getRegion()][$this->getEnvironment()];
    }

    /**
     * Returns the URL for the checkout js depending on the current configurations
     * @return null|string
     */
    public function getCheckoutEndpointUrl(): ?string {
        return self::ENDPOINTS[self::ENDPOINT_TYPE_CHECKOUT][$this->getRegion()][$this->getEnvironment()];
    }
    /**
     * Returns the URL for the login js depending on the current configurations
     * @return null|string
     */
    public function getLoginEndpointUrl(): ?string {
        return self::ENDPOINTS[self::ENDPOINT_TYPE_LOGIN][$this->getRegion()][$this->getEnvironment()];
    }


    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getAll(): array {
        return $this->database->getConfig();
    }

    public function getDefaultLocale(): string {
        return self::DEFAULT_LOCALE;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId): void {
        $this->setConfigValue('clientId', $clientId);
    }

    public function setMerchantId($merchantId): void {
        $this->setConfigValue('merchantId', $merchantId);
    }

    public function getMerchantId(): ?string {
        return $this->getConfigValue('merchantId');
    }

    /**
     * @param $environment
     */
    public function setEnvironment($environment): void {
        $this->setConfigValue('environment', $environment);
    }

    /**
     * @param $region
     */
    public function setRegion($region): void {
        $this->setConfigValue('region', $region);
    }

    /*
     *  Subscription settings.
     */

    public function setSubscriptionMode($param) {
        $this->setConfigValue('subscriptionMode', $param);
    }
    public function getSubscriptionMode() {
        return $this->getConfigValue('subscriptionMode', self::DEFAULT_VALUES['subscriptionMode']);
    }
    public function setSubscriptionDisplayDetail($param) {
        $this->setConfigValue('subscriptionDisplayDetail', $param ? self::TRUE : self::FALSE);
    }
    public function isSubscriptionDisplayDetail() {
        return (bool)$this->getConfigValue('subscriptionDisplayDetail', self::DEFAULT_VALUES['subscriptionDisplayDetail']);
    }
    public function setSubscriptionDisplayCart($param) {
        $this->setConfigValue('subscriptionDisplayCart', $param ? self::TRUE : self::FALSE);
    }
    public function isSubscriptionDisplayCart() {
        return (bool)$this->getConfigValue('subscriptionDisplayCart', self::DEFAULT_VALUES['subscriptionDisplayCart']);
    }
    public function setSubscriptionGlobalActive($param) {
        $this->setConfigValue('subscriptionGlobalActive', $param ? self::TRUE : self::FALSE);
    }
    public function isSubscriptionGlobalActive() {
        return (bool)$this->getConfigValue('subscriptionGlobalActive', self::DEFAULT_VALUES['subscriptionGlobalActive']);
    }
    public function setSubscriptionGlobalInterval($param) {
        $this->setConfigValue('subscriptionGlobalInterval', $param);
    }
    public function getSubscriptionGlobalInterval() {
        return $this->getConfigValue('subscriptionGlobalInterval', self::DEFAULT_VALUES['subscriptionGlobalInterval']);
    }
    public function setSubscriptionFunctionalAttributeInterval($param) {
        $this->setConfigValue('subscriptionFunctionalAttributeInterval', $param);
    }
    public function getSubscriptionFunctionalAttributeInterval() {
        return $this->getConfigValue('subscriptionFunctionalAttributeInterval', self::DEFAULT_VALUES['subscriptionFunctionalAttributeInterval']);
    }
    public function setSubscriptionOrderAttributeFlag($param) {
        $this->setConfigValue('subscriptionOrderAttributeFlag', $param);
    }
    public function getSubscriptionOrderAttributeFlag() {
        return $this->getConfigValue('subscriptionOrderAttributeFlag', self::DEFAULT_VALUES['subscriptionOrderAttributeFlag']);
    }
    public function setSubscriptionOrderAttributeInterval($param) {
        $this->setConfigValue('subscriptionOrderAttributeInterval', $param);
    }
    public function getSubscriptionOrderAttributeInterval() {
        return $this->getConfigValue('subscriptionOrderAttributeInterval', self::DEFAULT_VALUES['subscriptionOrderAttributeInterval']);
    }
    public function setSubscriptionReminderMailLeadTimeDays($param) {
        $this->setConfigValue('subscriptionReminderMailLeadTimeDays', (string) $param);
    }
    public function getSubscriptionReminderMailLeadTimeDays() {
        return (int) $this->getConfigValue('subscriptionReminderMailLeadTimeDays', self::DEFAULT_VALUES['subscriptionReminderMailLeadTimeDays']);
    }
    public function setSubscriptionNormalizeOrderTime($param) {
        $this->setConfigValue('subscriptionNormalizeOrderTime', $param ? self::TRUE : self::FALSE);
    }
    public function isSubscriptionNormalizeOrderTime() {
        return (bool)$this->getConfigValue('subscriptionNormalizeOrderTime', self::DEFAULT_VALUES['subscriptionNormalizeOrderTime']);
    }
    public function setSubscriptionNormalizeOrderTimeTo($param) {
        $this->setConfigValue('subscriptionNormalizeOrderTimeTo', $param);
    }
    public function getSubscriptionNormalizeOrderTimeTo() {
        return $this->getConfigValue('subscriptionNormalizeOrderTimeTo', self::DEFAULT_VALUES['subscriptionNormalizeOrderTimeTo']);
    }
    public function setSubscriptionNotificationMailAddress($param) {
        $this->setConfigValue('subscriptionNotificationMailAddress', $param);
    }
    public function getSubscriptionNotificationMailAddress() {
        return $this->getConfigValue('subscriptionNotificationMailAddress', self::DEFAULT_VALUES['subscriptionNotificationMailAddress']);
    }

    public function setSubscriptionCustomerAccountPqSelector($param) {
        $this->setConfigValue('subscriptionCustomerAccountPqSelector', $param);
    }

    public function getSubscriptionCustomerAccountPqSelector() {
        return $this->getConfigValue('subscriptionCustomerAccountPqSelector', self::DEFAULT_VALUES['subscriptionCustomerAccountPqSelector']);
    }

    public function setSubscriptionCustomerAccountPqMethod($param) {
        $this->setConfigValue('subscriptionCustomerAccountPqMethod', $param);
    }

    public function getSubscriptionCustomerAccountPqMethod() {
        return $this->getConfigValue('subscriptionCustomerAccountPqMethod', self::DEFAULT_VALUES['subscriptionCustomerAccountPqMethod']);
    }

    /**
     * Does the actual database access and clears the cache afterwards.
     * @param string $key
     * @param string $value
     */
    protected function setConfigValue(string $key, string $value): void {
        $this->database->upsertConfig($key, $value);
        $this->clearCache();
    }

    /**
     * Does the actual database access and handles reading from cache.
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    protected function getConfigValue(string $key, $default = null) {
        $cachedResult = Shop::Container()->getCache()->get('lpaConfig_' . $key);
        if ($cachedResult) {
            return $cachedResult;
        }
        // no hit in the cache
        $res = $this->database->getConfigSetting($key);
        if ($res === null && $default !== null) {
            $res = $default;
        }
        Shop::Container()->getCache()->set('lpaConfig_' . $key, $res, [CACHING_GROUP_PLUGIN, self::CACHING_GROUP_ID]);
        return $res;
    }

    public function clearCache(): void {
        Shop::Container()->getCache()->flush(null, [self::CACHING_GROUP_ID]);
    }

    public function getDefaultValues(): array {
        return self::DEFAULT_VALUES;
    }

    /**
     * Enables the cronjob as a default JTL task which is run when cron_inc.php is called.
     */
    public function enableCronjob(): void {
        $job = new \stdClass();
        $job->name = 'AmazonPay Sync Cron';
        $job->jobType = Constants::CRON_JOB_TYPE_SYNC;
        $job->frequency = 1; // Run every 1 hour
        $job->startDate = 'NOW()';
        $job->startTime = '00:00:00';

        // Clear job from the queue
        Shop::Container()->getDB()->delete('tjobqueue', 'jobType', Constants::CRON_JOB_TYPE_SYNC);

        // Update or insert the object
        if(!empty(Shop::Container()->getDB()->select('tcron', 'jobType', $job->jobType))) {
            Shop::Container()->getDB()->update('tcron', 'jobType', $job->jobType, $job);
        } else {
            Shop::Container()->getDB()->insert('tcron', $job);
        }
    }

    /**
     * Disables the cronjob as a default JTL task.
     */
    public function disableCronjob(): void {
        Shop::Container()->getDB()->delete('tcron', 'jobType', Constants::CRON_JOB_TYPE_SYNC);
        Shop::Container()->getDB()->delete('tjobqueue', 'jobType', Constants::CRON_JOB_TYPE_SYNC);
    }

    public function getPrivateKey(): ?string {
        $privateKeyEncrypted = $this->getConfigValue('privateKey');
        if (null !== $privateKeyEncrypted && \is_string($privateKeyEncrypted)) {
            return Shop::Container()->getCryptoService()->decryptXTEA($privateKeyEncrypted);
        }
        return null;
    }
    public function setPrivateKey(string $privateKey): void {
        // The private key needs encryption
        $this->setConfigValue('privateKey', Shop::Container()->getCryptoService()->encryptXTEA($privateKey));
    }


    public function getPublicKeyId() {
        return $this->getConfigValue('publicKeyId');
    }


    public function setPublicKeyId(string $publicKeyId): void {
        $this->setConfigValue('publicKeyId', $publicKeyId);
    }

    public function getPublicKey() {
        return $this->getConfigValue('publicKey');
    }

    public function setPublicKey($publicKey): void {
        $this->setConfigValue('publicKey', $publicKey);
    }


    public function getKeyExchangeToken() {
        return $this->getConfigValue('keyExchangeToken');
    }

    public function setKeyExchangeToken($keyExchangeToken) {
        $this->setConfigValue('keyExchangeToken', $keyExchangeToken);
    }

    protected function limitButtonHeight($param): string {
        $value = (int) $param;
        return (string) min(self::BUTTON_MAX_HEIGHT, max($value, self::BUTTON_MIN_HEIGHT));
    }

    public function getButtonLoginCssColumns() {
        return $this->getConfigValue('buttonLoginCssColumns', self::DEFAULT_VALUES['buttonLoginCssColumns']);
    }

    public function getButtonPayCssColumns() {
        return $this->getConfigValue('buttonPayCssColumns', self::DEFAULT_VALUES['buttonPayCssColumns']);
    }

    public function getButtonPayDetailCssColumns() {
        return $this->getConfigValue('buttonPayDetailCssColumns', self::DEFAULT_VALUES['buttonPayDetailCssColumns']);
    }

    public function getButtonPayCategoryCssColumns() {
        return $this->getConfigValue('buttonPayCategoryCssColumns', self::DEFAULT_VALUES['buttonPayCategoryCssColumns']);
    }

    public function setButtonLoginCssColumns($param) {
        $this->setConfigValue('buttonLoginCssColumns', $param);
    }

    public function setButtonPayCssColumns($param) {
        $this->setConfigValue('buttonPayCssColumns', $param);
    }

    public function setButtonPayDetailCssColumns($param) {
        $this->setConfigValue('buttonPayDetailCssColumns', $param);
    }

    public function setButtonPayCategoryCssColumns($param) {
        $this->setConfigValue('buttonPayCategoryCssColumns', $param);
    }

    public function setSubscriptionDiscountMode($param) {
        $this->setConfigValue('subscriptionDiscountMode', $param);
    }

    public function getSubscriptionDiscountMode() {
        return $this->getConfigValue('subscriptionDiscountMode', self::DEFAULT_VALUES['subscriptionDiscountMode']);
    }

    public function setSubscriptionDiscountGlobal($param) {
        $this->setConfigValue('subscriptionDiscountGlobal', $param);
    }

    public function getSubscriptionDiscountGlobal(): int {
        return (int) $this->getConfigValue('subscriptionDiscountGlobal', self::DEFAULT_VALUES['subscriptionDiscountGlobal']);
    }

    public function setSubscriptionDiscountAttribute($param) {
        $this->setConfigValue('subscriptionDiscountAttribute', $param);
    }

    public function getSubscriptionDiscountAttribute() {
        return $this->getConfigValue('subscriptionDiscountAttribute', self::DEFAULT_VALUES['subscriptionDiscountAttribute']);
    }

    public function isSubscriptionDiscountFeatureEnabled() {
        return defined(Constants::SUBSCRIPTION_DISCOUNT_FLAG) && constant(Constants::SUBSCRIPTION_DISCOUNT_FLAG) === true;
    }

}