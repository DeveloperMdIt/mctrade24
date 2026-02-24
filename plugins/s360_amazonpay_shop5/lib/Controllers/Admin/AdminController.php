<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Controllers\Admin;

use JTL\License\Manager;
use JTL\License\Mapper;
use JTL\License\Struct\ExsLicense;
use JTL\Plugin\PluginInterface;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Adapter\ApiAdapter;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\CheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\Error;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Objects\WebCheckoutDetails;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\CreateCheckoutSession;
use Plugin\s360_amazonpay_shop5\lib\AmazonPay\Operations\GetChargePermission;
use Plugin\s360_amazonpay_shop5\lib\Utils\Config;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Currency;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLoggerTrait;
use Plugin\s360_amazonpay_shop5\paymentmethod\AmazonPay;

/**
 * Class AdminController.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Controllers
 */
abstract class AdminController {
    use JtlLoggerTrait;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * @var array $request 
     */
    protected $request;

    /**
     * @var PluginInterface $plugin
     */
    protected $plugin;

    /**
     * Errors to show to the user.
     * @var string[] $errors 
     */
    protected $errors;

    /**
     * Warnings to show to the user.
     * @var string[] $warnings
     */
    protected $warnings;

    /**
     * Normal messages to show to the user.
     * @var string[] $messages 
     */
    protected $messages;

    /**
     * Success messages to show to the user.
     * @var string[] $successes 
     */
    protected $successes;

    protected const CHECK_STATUS_UNKNOWN = 'unknown';
    protected const CHECK_STATUS_SUCCESS = 'success';
    protected const CHECK_STATUS_WARNING = 'warning';
    protected const CHECK_STATUS_INFO = 'info';
    protected const CHECK_STATUS_DANGER = 'danger';

    protected $selfCheckResults = [
        'ssl' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckStatusUnknown'],
        'update' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckStatusUnknown'],
        'sandbox' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckStatusUnknown'],
        'buttons' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckStatusUnknown'],
        'paymethod' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckStatusUnknown'],
        'deliverymethods' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckStatusUnknown'],
        'currencies' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckStatusUnknown'],
        'accountconfig' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckStatusUnknown'],
        'accountstatus' => ['status' => self::CHECK_STATUS_UNKNOWN, 'message' => 'lpaCheckAccountStatusUnknown']
    ];

    /**
     * AdminController constructor.
     * @param PluginInterface $plugin
     */
    public function __construct(PluginInterface $plugin) {
        $this->plugin = $plugin;
        $this->config = Config::getInstance();
        $this->request = $_REQUEST;
        $this->errors = [];
        $this->warnings = [];
        $this->messages = [];
        $this->successes = [];
        $this->prepareGlobalVariables();
    }


    /**
     * Prepares variables needed for every tab.
     */
    protected function prepareGlobalVariables(): void {
        $vars = [];
        $vars['adminTemplatePath'] = $this->plugin->getPaths()->getAdminPath() . 'template/';
        $vars['adminTemplateUrl'] = $this->plugin->getPaths()->getAdminURL() . 'template/';
        $vars['adminUrl'] = JtlLinkHelper::getInstance()->getFullAdminUrl(); // notice that this returns the actual URL that displays the plugin admin area, not an url to its own folder
        $vars['widgetEndpointUrl'] = $this->config->getWidgetEndpointUrl();
        $vars['clientId'] = $this->config->getClientId();
        $vars['isSandbox'] = $this->config->getEnvironment() === Config::ENVIRONMENT_SANDBOX ? 'true' : 'false';
        $vars['pluginVersion'] = (string) $this->plugin->getCurrentVersion();
        Shop::Smarty()->assign('lpaAdminGlobal', $vars);
    }

    /**
     * Finalizes the process by assigning the messages acquired while handling and rendering the template file.
     * @param string $templateFilePath
     * @return string
     * @throws \Exception
     */
    protected function finalize(string $templateFilePath): string {
        Shop::Smarty()->assign('lpaErrors', $this->errors);
        Shop::Smarty()->assign('lpaWarnings', $this->warnings);
        Shop::Smarty()->assign('lpaMessages', $this->messages);
        Shop::Smarty()->assign('lpaSuccesses', $this->successes);
        return Shop::Smarty()->fetch($templateFilePath);
    }

    /**
     * Adds an error to display to the user.
     * @param string $error
     * @param string $postfix
     */
    protected function addError(string $error, string $postfix = ''): void {
        $this->errors[] = __($error) . $postfix;
    }

    /**
     * Adds an error to display to the user.
     * @param string $warning
     */
    protected function addWarning(string $warning): void {
        if (!\in_array($warning, $this->warnings, true)) {
            $this->warnings[] = __($warning);
        }
    }

    /**
     * Adds a message to display to the user.
     * @param string $message
     */
    protected function addMessage(string $message): void {
        if (!\in_array($message, $this->messages, true)) {
            $this->messages[] = __($message);
        }
    }

    /**
     * Adds a message to display to the user.
     * @param string $success
     */
    protected function addSuccess(string $success): void {
        if (!\in_array($success, $this->successes, true)) {
            $this->successes[] = __($success);
        }
    }

    /**
     * Expected to fill Smarty Variables and return the rendered template.
     */
    abstract public function handle(): string;

    /**
     * @return Config
     */
    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @param Config $config
     * @return AdminController
     */
    public function setConfig(Config $config): AdminController {
        $this->config = $config;
        return $this;
    }

    /**
     * @return array
     */
    public function getRequest(): array {
        return $this->request;
    }

    /**
     * @param array $request
     * @return AdminController
     */
    public function setRequest(array $request): AdminController {
        $this->request = $request;
        return $this;
    }

    /**
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface {
        return $this->plugin;
    }

    /**
     * @param PluginInterface $plugin
     * @return AdminController
     */
    public function setPlugin(PluginInterface $plugin): AdminController {
        $this->plugin = $plugin;
        return $this;
    }

    /**
     * Performs a self check on the plugin.
     */
    protected function performSelfCheck(): string {
        $this->checkSSL();
        $this->checkForUpdate();
        $this->checkSandbox();
        $this->checkButtons();
        $this->checkPaymethodConfiguration();
        $this->checkDeliveryMethods();
        $this->checkCurrencies();
        $this->checkFrontendLinks();
        $this->checkVersionSpecific(\APPLICATION_VERSION);
        $this->checkAccountConfiguration();
        $this->checkAccountStatus();
        $this->checkCronConfiguration();
        Shop::Smarty()->assign('lpaSelfCheck', $this->selfCheckResults);
        return Shop::Smarty()->fetch($this->plugin->getPaths()->getAdminPath() . 'template/snippets/selfcheck.tpl');
    }

    private function checkSSL() {
        if (stripos(Shop::getURL(true), 'https') !== 0) {
            $this->selfCheckResults['ssl'] = [
                'status' => self::CHECK_STATUS_DANGER,
                'message' => 'lpaCheckSSLNotActive'
            ];
        } else {
            $this->selfCheckResults['ssl'] = [
                'status' => self::CHECK_STATUS_SUCCESS,
                'message' => 'lpaCheckSSLActive'
            ];
        }
    }

    private function checkSandbox() {
        if ($this->config->getEnvironment() !== Config::ENVIRONMENT_SANDBOX) {
            $this->selfCheckResults['sandbox'] = [
                'status' => self::CHECK_STATUS_SUCCESS,
                'message' => 'lpaCheckSandboxNotActive'
            ];
        } else {
            $this->selfCheckResults['sandbox'] = [
                'status' => self::CHECK_STATUS_WARNING,
                'message' => 'lpaCheckSandboxActive'
            ];
        }
    }

    private function checkForUpdate() {
        $db = Shop::Container()->getDB();
        $manager = new Manager($db, Shop::Container()->getCache());
        $mapper = new Mapper($manager);
        $collection = $mapper->getCollection();
        $updates = $collection->getUpdateableItems();
        $updates = $updates->filter(function (ExsLicense $update) {
            return $update->getReferencedItem() !== null && $update->getReferencedItem()->getID() === Constants::PLUGIN_ID && version_compare($this->plugin->getCurrentVersion()->getOriginalVersion(), $update->getReferencedItem()->getMaxInstallableVersion()->getOriginalVersion(), '<');
        })->toArray();
        if (!empty($updates)) {
            $this->selfCheckResults['update'] = [
                'status' => self::CHECK_STATUS_INFO,
                'message' => 'lpaCheckUpdateAvailable'
            ];
        } else {
            $this->selfCheckResults['update'] = [
                'status' => self::CHECK_STATUS_SUCCESS,
                'message' => 'lpaCheckUpdateNotAvailable'
            ];
        }
    }

    private function checkButtons() {
        if (!$this->config->isButtonLoginActive()
            && !$this->config->isButtonPayActive()
            && !$this->config->isButtonPayCategoryActive()
            && !$this->config->isButtonPayDetailActive()
        ) {
            $this->addMessage('lpaCheckNoButtonsActive');
        }
        if (!$this->config->isHiddenButtonMode()) {
            $this->selfCheckResults['buttons'] = [
                'status' => self::CHECK_STATUS_SUCCESS,
                'message' => 'lpaCheckHiddenButtonsInactive'
            ];
        } else {
            $this->selfCheckResults['buttons'] = [
                'status' => self::CHECK_STATUS_WARNING,
                'message' => 'lpaCheckHiddenButtonsActive'
            ];
        }
    }

    private function checkPaymethodConfiguration() {
        try {
            $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
            if ($paymentMethodModule->duringCheckout) {
                $this->selfCheckResults['paymethod'] = [
                    'status' => self::CHECK_STATUS_SUCCESS,
                    'message' => 'lpaCheckPaymentMethodDuringCheckout'
                ];
            } else {
                $this->selfCheckResults['paymethod'] = [
                    'status' => self::CHECK_STATUS_DANGER,
                    'message' => 'lpaCheckPaymentMethodNotDuringCheckout'
                ];
            }

        } catch(\Throwable $t) {
            // This might occur if something went wrong during JTL plugin updates
            $this->addError('Exception: ' . $t->getMessage() . ', ' . $t->getTraceAsString());
        }
    }

    private function checkDeliveryMethods() {
        $paymentMethodModule = new AmazonPay(AmazonPay::getModuleId($this->plugin));
        if (!empty($paymentMethodModule->getConfiguredShippingMethodIds())) {
            $this->selfCheckResults['deliverymethods'] = [
                'status' => self::CHECK_STATUS_SUCCESS,
                'message' => 'lpaCheckShippingMethodAssigned'
            ];
        } else {
            $this->selfCheckResults['deliverymethods'] = [
                'status' => self::CHECK_STATUS_DANGER,
                'message' => 'lpaCheckNoShippingMethodAssigned'
            ];
        }
    }

    private function checkCurrencies() {
        if (null !== Currency::getInstance()->getFallbackCurrency()) {
            $this->selfCheckResults['currencies'] = [
                'status' => self::CHECK_STATUS_SUCCESS,
                'message' => 'lpaCheckValidCurrency'
            ];
        } else {
            $this->selfCheckResults['currencies'] = [
                'status' => self::CHECK_STATUS_DANGER,
                'message' => 'lpaCheckNoValidCurrency'
            ];
        }
    }

    private function checkFrontendLinks() {
        foreach (JtlLinkHelper::getInstance()->getAllFrontendUrls() as $key => $url) {
            if (empty($url)) {
                $this->addError('lpaCheckNoFrontendLinkForKey', ' ' . $key);
            }
        }
    }

    private function checkVersionSpecific($applicationVersion) {
        // No checks yet - this method is intended to check for Shop-Version specific warnings/quirks
        if (!version_compare($applicationVersion, '5.0.0', '>=')) {
            $this->addMessage('lpaCheckShopVersionTooLow');
        }
    }

    private function checkAccountConfiguration() {
        $messages = [];
        if (empty($this->config->getRegion())) {
            $messages[] = 'lpaCheckMissingRegion';
        }
        // Note that a missing public key is not a problem but may, for example, arise from manual key entry
        if (empty($this->config->getPrivateKey())) {
            $messages[] = 'lpaCheckMissingPrivateKey';
        }
        if (empty($this->config->getPublicKeyId())) {
            $messages[] = 'lpaCheckMissingPublicKeyId';
        }
        if (empty($this->config->getMerchantId())) {
            $messages[] = 'lpaCheckMissingMerchantId';
        }
        if (empty($this->config->getClientId())) {
            $messages[] = 'lpaCheckMissingClientId';
        }
        if (!empty($messages)) {
            $messages[] = 'lpaCheckAccountSettingsHint';
            $messages = array_map(function ($msg) {
                return __($msg);
            }, $messages);
            $this->addWarning('<br/>' . implode('<br/>', $messages));
        }
    }


    private function checkAccountStatus() {
        // We can only check this if the access keys were configured.
        if (!empty($this->config->getPublicKeyId())
            && !empty($this->config->getPrivateKey()
                && !empty($this->config->getRegion()))
        ) {
            /*
             * We do two things:
             * - First, we try to load an unknown Reference ID - if we get a 404, the credentials are correct.
             * - Then we try to initiate a checkout session *in production mode* to check if the account is validated and free to operate
             */
            $adapter = new ApiAdapter();
            $request = new GetChargePermission(Constants::TEST_REFERENCE_ID);
            try {
                $skipAccountStatus = false;
                $testResult = $adapter->execute($request);
                if ($testResult instanceof Error) {
                    switch ($testResult->getHttpErrorCode()) {
                        case 404:
                            // Success! The api did not find the test id, which is expected
                            $this->selfCheckResults['accountconfig'] = [
                                'status' => self::CHECK_STATUS_SUCCESS,
                                'message' => 'lpaCheckAccountCredentialsValid'
                            ];
                            break;
                        case 403:
                            $this->debugLog("Account Check resulted in code 403 with reason code: '{$testResult->getReasonCode()}'.");
                            if ($testResult->getReasonCode() === Error::REASON_CODE_INVALID_ACCOUNT_STATUS) {
                                // the account is not allowed to do this (it might be suspended or not yet completely configured)
                                $this->selfCheckResults['accountconfig'] = [
                                    'status' => self::CHECK_STATUS_SUCCESS,
                                    'message' => 'lpaCheckAccountCredentialsValid'
                                ];
                                $this->selfCheckResults['accountstatus'] = [
                                    'status' => self::CHECK_STATUS_DANGER,
                                    'message' => 'lpaCheckAccountStatusInvalid'
                                ];
                                $skipAccountStatus = true;
                            } else {
                                // the access key is wrong
                                $this->selfCheckResults['accountconfig'] = [
                                    'status' => self::CHECK_STATUS_DANGER,
                                    'message' => 'lpaCheckAccountCredentialsInvalid'
                                ];
                                $skipAccountStatus = true;
                            }
                            break;
                        case 400:
                            $this->debugLog("Account Check resulted in code 400 with reason code: '{$testResult->getReasonCode()}'.");
                            if ($testResult->getReasonCode() === Error::REASON_CODE_INVALID_HEADER_VALUE) {
                                // the public key is probably wrong
                                $this->selfCheckResults['accountconfig'] = [
                                    'status' => self::CHECK_STATUS_DANGER,
                                    'message' => 'lpaCheckAccountCredentialsInvalid'
                                ];
                                $skipAccountStatus = true;
                            } else {
                                // the access key is wrong
                                $skipAccountStatus = true;
                                $this->addWarning('lpaCheckAccountGenericError');
                            }
                            break;
                        default:
                            $this->debugLog("Account Check resulted in unexpected http code {$testResult->getHttpErrorCode()} with reason code: '{$testResult->getReasonCode()}'.");
                            $this->addWarning('lpaCheckAccountGenericError');
                            $skipAccountStatus = true;
                            break;
                    }
                } else {
                    $this->debugLog("Account Check resulted in unexpected reply type: " . get_class($testResult));
                    $this->addWarning('lpaCheckAccountGenericError');
                    $skipAccountStatus = true;
                }

                if(!$skipAccountStatus) {
                    $webCheckoutDetails = new WebCheckoutDetails();
                    $webCheckoutDetails->setCheckoutReviewReturnUrl(JtlLinkHelper::getInstance()->getFullUrlForFrontendFile(JtlLinkHelper::FRONTEND_FILE_RETURN));
                    $request = new CreateCheckoutSession($webCheckoutDetails);
                    $adapter->setSandbox(false); // we always try this in production mode
                    $response = $adapter->execute($request);
                    $adapter->setSandbox($this->config->isSandbox()); // reset to current config
                    if ($response instanceof CheckoutSession) {
                        $this->selfCheckResults['accountstatus'] = [
                            'status' => self::CHECK_STATUS_SUCCESS,
                            'message' => 'lpaCheckAccountStatusValid'
                        ];
                    } else {
                        $this->selfCheckResults['accountstatus'] = [
                            'status' => self::CHECK_STATUS_DANGER,
                            'message' => 'lpaCheckAccountStatusInvalid'
                        ];
                    }
                }

            } catch (\Throwable $e) {
                $this->debugLog('Exception when trying to check account credentials and status: ' . $e->getMessage() . ', ' . $e->getTraceAsString());
                $this->addWarning('lpaCheckAccountGenericError');
            }
        } else {
            $this->selfCheckResults['accountconfig'] = [
                'status' => self::CHECK_STATUS_INFO,
                'message' => 'lpaCheckAccountSettingsIncomplete'
            ];
        }
    }

    private function checkCronConfiguration(): void
    {
        if ($this->config->getSubscriptionMode() !== Config::SUBSCRIPTION_MODE_INACTIVE) {
            if($this->config->getCronMode() === Config::CRON_MODE_OFF) {
                $this->selfCheckResults['cronconfiguration'] = [
                    'status' => self::CHECK_STATUS_DANGER,
                    'message' => 'lpaCheckNoCronForSubscription'
                ];
            }
        }
    }

}