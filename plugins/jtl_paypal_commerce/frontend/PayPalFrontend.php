<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use Exception;
use JTL\Cart\Cart;
use JTL\Customer\Customer;
use JTL\Helpers\Text;
use JTL\Link\LinkInterface;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\PayPalPaymentInterface;
use Plugin\jtl_paypal_commerce\PPC\APM;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\ClientToken;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\Order\AppleTransactionInfo;
use Plugin\jtl_paypal_commerce\PPC\Order\GoogleTransactionInfo;
use Plugin\jtl_paypal_commerce\PPC\Order\Order;
use Plugin\jtl_paypal_commerce\PPC\Order\OrderStatus;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Settings;
use Plugin\jtl_paypal_commerce\PPC\VaultingHelper;
use SmartyException;
use stdClass;

use function Functional\first;

/**
 * Class PayPalFrontend
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class PayPalFrontend
{
    /** @var PluginInterface */
    private PluginInterface $plugin;

    /** @var Configuration */
    private Configuration $config;

    /** @var JTLSmarty */
    private JTLSmarty $smarty;

    /**
     * PayPalFrontend constructor
     * @param PluginInterface $plugin
     * @param Configuration   $config
     * @param JTLSmarty       $smarty
     */
    public function __construct(PluginInterface $plugin, Configuration $config, JTLSmarty $smarty)
    {
        $this->plugin = $plugin;
        $this->config = $config;
        $this->smarty = $smarty;
    }

    /**
     * @param string $page
     * @return bool
     */
    public function preloadECSJS(string $page): bool
    {
        $config = $this->config->mapFrontendSettings(Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY);

        if (
            !$this->config->checkComponentVisibility($config, $page)
            && !$this->config->checkComponentVisibility($config, CheckoutPage::PAGE_SCOPE_MINICART)
        ) {
            return false;
        }

        try {
            $path = $this->plugin->getPaths()->getFrontendURL() . 'template/ecs/';
            \pq('body')->prepend(
                '<script src="' . $path . 'jsTemplates/standaloneButtonTemplate.js?v=1.1.0"></script>
                 <script src="' . $path . 'jsTemplates/activeButtonLabelTemplate.js?v=1.1.0"></script>
                 <script src="' . $path . 'init.js?v=1.1.0"></script>'
            );
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @param string $page
     * @return bool
     */
    public function preloadInstalmentBannerJS(string $page): bool
    {
        $config = $this->config->mapFrontendSettings(Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP);

        if (
            !$this->config->checkComponentVisibility($config, $page)
            && !$this->config->checkComponentVisibility($config, CheckoutPage::PAGE_SCOPE_MINICART)
        ) {
            return false;
        }
        try {
            $path = $this->plugin->getPaths()->getFrontendURL() . 'template/instalmentBanner/jsTemplates/';
            \pq('body')->prepend(
                '<script src="' . $path . 'instalmentBannerPlaceholder.js?v=1.1.0"></script>'
            );
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @param string $page
     * @return bool
     */
    public function renderInstalmentBanner(string $page): bool
    {
        $mappedConfigs = $this->config->mapFrontendSettings(null, null, [
            Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP,
            Settings::BACKEND_SETTINGS_SECTION_GENERAL,
        ]);
        $config        = $mappedConfigs[Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP];
        try {
            $amount = $page === CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS ?
                \pq('meta[itemprop="price"]')->attr('content') :
                Frontend::getCart()->gibGesamtsummeWaren(!Frontend::getCustomerGroup()->isMerchant());
        } catch (Exception) {
            return false;
        }

        if ($amount < 1 || !$this->config->checkComponentVisibility($config, $page)) {
            return false;
        }

        $method          = $config[$page . '_phpqMethod'];
        $selector        = $config[$page . '_phpqSelector'];
        $ppcFrontendPath = $this->plugin->getPaths()->getFrontendPath();
        $ppcStyle        = [
            'placement' => 'product',
            'amount' => $amount,
            'style' => [
                'layout' => $config[$page . '_layout'],
                'logo' => [
                    'type' => $config[$page . '_logoType'],
                ],
                'text' => [
                    'size' => $config[$page . '_textSize'],
                    'color' => $config[$page . '_textColor'],
                ],
                'color' => $config[$page . '_layoutType'],
                'ratio' => $config[$page . '_layoutRatio'],

            ],
        ];
        $ppcActiveTemplate = $mappedConfigs[Settings::BACKEND_SETTINGS_SECTION_GENERAL]['templateSupport'];
        try {
            \pq($selector)->{$method}($this->smarty
                ->assign('ppcStyle', $ppcStyle)
                ->assign('ppcFrontendUrl', $this->plugin->getPaths()->getFrontendURL())
                ->assign('ppcFrontendPath', $ppcFrontendPath)
                ->assign('ppcConsentPlaceholder', $this->plugin->getLocalization()->getTranslation(
                    'jtl_paypal_commerce_instalment_banner_consent_placeholder'
                ))
                ->assign('ppcComponentName', $page)
                ->assign('ppcActiveTemplate', $ppcActiveTemplate)
                ->fetch($ppcFrontendPath . 'template/instalmentBanner/' . $page . '/banner.tpl'));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    public function renderPayPalJsSDK(array $components, array $componentArgs, ?string $bnCode = null): void
    {
        $logger     = Shop::Container()->getLogService();
        $components = \array_values(\array_unique($components));
        if (empty($components)) {
            return;
        }

        $ppcCommit    = (bool)($componentArgs['ppcCommit'] ?? true);
        $isECS        = (bool)($componentArgs['isECS'] ?? false);
        $countryCode  = (string)($componentArgs['countryCode'] ?? '');
        $buyerCountry = '';
        $vaultToken   = $componentArgs['vaultToken'] ?? null;

        try {
            $locale = Helper::sanitizeLocale(
                Helper::getLocaleFromISO(Text::convertISO2ISO639(Frontend::getInstance()->getLanguage()->cISOSprache)),
                true
            );
        } catch (Exception) {
            $locale = 'en_GB';
        }
        $cmActive          = Shop::getSettingValue(\CONF_CONSENTMANAGER, 'consent_manager_active') ?? 'N';
        $ppcConsentActive  = $cmActive === 'Y' && $this->config->getPrefixedConfigItem(
            Settings::BACKEND_SETTINGS_SECTION_CONSENTMANAGER . '_activate'
        ) === 'Y';
        $ppcConsentGiven   = Shop::Container()->getConsentManager()->hasConsent(Configuration::CONSENT_ID);
        $config            = $this->config->mapFrontendSettings(Settings::BACKEND_SETTINGS_SECTION_GENERAL);
        $ppcActiveTemplate = $config['templateSupport'];
        $APMs              = new APM($this->config);

        try {
            $clientToken = \in_array(Settings::COMPONENT_HOSTED_FIELDS, $components, true)
                ? ClientToken::getInstance()->getToken()
                : null;
        } catch (AuthorizationException $e) {
            $logger->error('fetch clientToken failed: ' . $e->getMessage());
            $clientToken = null;
        }

        if (\defined('PPC_DEBUG') && \PPC_DEBUG && PPCHelper::getEnvironment()->isSandbox()) {
            $buyerCountry = ($countryCode !== ''
                ? $countryCode
                : (Frontend::getDeliveryAddress()->cLand ?? $countryCode));
        }

        try {
            \pq('body')
                ->append($this->smarty
                    ->assign('ppcComponents', $components)
                    ->assign('ppcFrontendUrl', $this->plugin->getPaths()->getFrontendURL())
                    ->assign('ppcCommit', $ppcCommit ? 'true' : 'false')
                    ->assign('ppcFundingDisabled', $APMs->getDisabled($isECS))
                    ->assign('ppcOrderLocale', $locale)
                    ->assign('ppcClientID', $this->config->getConfigValues()->getClientID())
                    ->assign('ppcClientToken', $clientToken)
                    ->assign('ppcVaultToken', $vaultToken)
                    ->assign('ppcConsentID', Configuration::CONSENT_ID)
                    ->assign('ppcConsentActive', $ppcConsentActive ? 'true' : 'false')
                    ->assign('ppcCurrency', Frontend::getCurrency()->getCode())
                    ->assign('ppcConsentGiven', $ppcConsentGiven ? 'true' : 'false')
                    ->assign('ppcActiveTemplate', $ppcActiveTemplate)
                    ->assign('ppcBNCode', $bnCode ?? MerchantCredentials::BNCODE_CHECKOUT)
                    ->assign('ppcBuyerCountry', $buyerCountry)
                    ->assign('ppcCSRFToken', $_SESSION['jtl_token'])
                    ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/paypalJsSDK.tpl'));
        } catch (Exception) {
            $logger->error('phpquery rendering failed: renderPayPalJsSDK()');

            return;
        }
    }

    /**
     * @return void
     */
    private function ecsDefaultSmartyAssignments(): void
    {
        /** @var LinkInterface $ecsLink */
        $ecsLink           = $this->plugin->getLinks()->getLinks()->first(static function (LinkInterface $link) {
            return $link->getTemplate() === 'expresscheckout.tpl';
        });
        $templateConfig    = $this->config->mapFrontendSettings(Settings::BACKEND_SETTINGS_SECTION_GENERAL);
        $ppcActiveTemplate = $templateConfig['templateSupport'];

        $this->smarty
            ->assign('ppcFrontendUrl', $this->plugin->getPaths()->getFrontendURL())
            ->assign('ppcClientID', $this->config->getConfigValues()->getClientID())
            ->assign('ppcActiveTemplate', $ppcActiveTemplate)
            ->assign('ppcECSUrl', $ecsLink !== null ? $ecsLink->getURL() : '')
            ->assign(
                'ppcPreloadButtonLabelInactive',
                $this->plugin->getLocalization()->getTranslation(
                    'jtl_paypal_commerce_ecs_preload_button_label_inactive'
                )
            )
            ->assign(
                'ppcLoadingPlaceholder',
                $this->plugin->getLocalization()->getTranslation(
                    'jtl_paypal_commerce_ecs_loading_placeholder'
                )
            )
            ->assign(
                'ppcPreloadButtonLabelActive',
                $this->plugin->getLocalization()->getTranslation(
                    'jtl_paypal_commerce_ecs_preload_button_label_active'
                )
            );
    }

    /**
     * @param bool $isVaulting
     * @return bool
     */
    private function renderMiniCartButtons(bool $isVaulting): bool
    {
        $frontendConf = FrontendConfig::getInstance($this->config, CheckoutPage::PAGE_SCOPE_MINICART);
        if (!$frontendConf->checkComponentVisibility()) {
            return false;
        }

        $tplPath = 'template/ecs/miniCart.tpl';
        try {
            $this->ecsDefaultSmartyAssignments();
            \pq($frontendConf->getSelector())->{$frontendConf->getMethod()}($this->smarty
                ->assign('ppcNamespace', CheckoutPage::PAGE_SCOPE_MINICART)
                ->assign('ppcConfig', $frontendConf->getConfig(
                    Settings::BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS
                ))
                ->assign('ppcPrice', Frontend::getCart()->gibGesamtsummeWaren(
                    !Frontend::getCustomerGroup()->isMerchant()
                ))
                ->assign('ppcVaultingActive', $isVaulting)
                ->fetch($this->plugin->getPaths()->getFrontendPath() . $tplPath));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @param PayPalPaymentInterface $payment
     * @param array                  $components
     * @param Customer               $customer
     * @param Cart                   $cart
     * @param bool                   $isVaulting
     * @return array
     */
    public function renderMiniCartComponents(
        PayPalPaymentInterface $payment,
        array $components,
        Customer $customer,
        Cart $cart,
        bool $isVaulting
    ): array {
        if ($payment->isValidExpressPayment($customer, $cart) && $this->renderMiniCartButtons($isVaulting)) {
            $components[] = Settings::COMPONENT_BUTTONS;
            $components[] = Settings::COMPONENT_FUNDING_ELIGIBILITY;
        }
        if (
            $payment->isValidBannerPayment($customer, $cart)
            && $this->renderInstalmentBanner(CheckoutPage::PAGE_SCOPE_MINICART)
        ) {
            $components[] = Settings::COMPONENT_MESSAGES;
        }

        return $components;
    }

    /**
     * @param bool $isVaulting
     * @return bool
     */
    public function renderProductDetailsButtons(bool $isVaulting): bool
    {
        $frontendConf = FrontendConfig::getInstance($this->config, CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS);
        if (!$frontendConf->checkComponentVisibility()) {
            return false;
        }

        try {
            $this->ecsDefaultSmartyAssignments();
            $localization = $this->plugin->getLocalization();
            \pq($frontendConf->getSelector())->{$frontendConf->getMethod()}($this->smarty
                ->assign('ppcNamespace', CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS)
                ->assign('ppcConfig', $frontendConf->getConfig(
                    Settings::BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS
                ))
                ->assign('ecs_wk_error_desc', $localization->getTranslation(
                    'jtl_paypal_commerce_ecs_wk_error_desc'
                ))
                ->assign('ecs_wk_error_title', $localization->getTranslation(
                    'jtl_paypal_commerce_ecs_wk_error_title'
                ))
                ->assign('ppcVaultingActive', $isVaulting)
                ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/ecs/productDetails.tpl'));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @param bool $isVaulting
     * @return bool
     */
    public function renderCartButtons(bool $isVaulting): bool
    {
        $frontendConf = FrontendConfig::getInstance($this->config, CheckoutPage::PAGE_SCOPE_CART);
        if (!$frontendConf->checkComponentVisibility()) {
            return false;
        }

        try {
            $this->ecsDefaultSmartyAssignments();
            \pq($frontendConf->getSelector())->{$frontendConf->getMethod()}($this->smarty
                ->assign('ppcNamespace', CheckoutPage::PAGE_SCOPE_CART)
                ->assign('ppcConfig', $frontendConf->getConfig(
                    Settings::BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS
                ))
                ->assign('ppcVaultingActive', $isVaulting)
                ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/ecs/cart.tpl'));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function renderOrderProcessButtons(): bool
    {
        $frontendConf = FrontendConfig::getInstance($this->config, CheckoutPage::PAGE_SCOPE_ORDERPROCESS);
        if (!$frontendConf->checkComponentVisibility()) {
            return false;
        }

        try {
            $this->ecsDefaultSmartyAssignments();
            \pq($frontendConf->getSelector())->{$frontendConf->getMethod()}($this->smarty
                ->assign('ppcNamespace', CheckoutPage::PAGE_SCOPE_ORDERPROCESS)
                ->assign('ppcConfig', $frontendConf->getConfig(
                    Settings::BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS
                ))
                ->assign('ppcPrice', Frontend::getCart()->gibGesamtsummeWaren(
                    !Frontend::getCustomerGroup()->isMerchant()
                ))
                ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/ecs/orderProcess.tpl'));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @param PayPalPaymentInterface $payment
     * @param string                 $ppcOrderId
     * @param bool                   $payAgainProcess
     * @return bool
     */
    public function renderOrderConfirmationButtons(
        PayPalPaymentInterface $payment,
        string $ppcOrderId,
        bool $payAgainProcess = false
    ): bool {
        if (!$payAgainProcess) {
            $curMethod = Frontend::get('Zahlungsart');
            if ($curMethod === null || (int)$curMethod->kZahlungsart !== $payment->getMethod()->getMethodID()) {
                return false;
            }
        }

        $ppOrder = $payment->getPPOrder($ppcOrderId);
        if ($ppOrder === null) {
            Helper::redirectAndExit($payment->getPaymentCancelURL($ppOrder));
            exit();
        }

        if (
            ($payment->isAutoCapture() && !$payAgainProcess)
                || $ppOrder->getStatus() === OrderStatus::STATUS_APPROVED
        ) {
            // Order is approved (from express checkout?) -  no call to paypal necessary
            try {
                \pq('#complete-order-button')
                    ->after($this->smarty
                        ->assign('ppcStateURL', $payment->getPaymentStateURL($ppOrder) ?? '')
                        ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/orderConfirmationSimple.tpl'));
            } catch (Exception) {
                // template error - how to handle?
            }

            return true;
        }

        $templateConfig    = $this->config->mapFrontendSettings(Settings::BACKEND_SETTINGS_SECTION_GENERAL);
        $ppcActiveTemplate = $templateConfig['templateSupport'];

        try {
            $path = $this->plugin->getPaths()->getFrontendURL() . 'template/ecs/';
            \pq('body')->prepend(
                '<script src="' . $path . 'init.js?v=1.1.0"></script>'
            );
            $this->ecsDefaultSmartyAssignments();
            /** @noinspection JsonEncodingApiUsageInspection */
            \pq('#complete-order-button')
                ->after($this->smarty
                    ->assign('ppcConfig', \json_encode($this->config->mapFrontendSettings()))
                    ->assign('ppcOrderId', $ppcOrderId)
                    ->assign('ppcActiveTemplate', $ppcActiveTemplate)
                    ->assign('ppcStateURL', $payment->getPaymentStateURL($ppOrder) ?? '#')
                    ->assign('ppcCancelURL', $payment->getPaymentCancelURL($ppOrder))
                    ->assign('ppcFundingSource', $payment->getFundingSource())
                    ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/orderConfirmation.tpl'));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @param stdClass $method
     * @return string
     * @throws SmartyException
     */
    private function renderPaymentOptionsTemplate(stdClass $method): string
    {
        $this->smarty->assign('Zahlungsarten', [
            (object)[
                'cModulId'        => '${ paymentOptionId }',
                'kZahlungsart'    => '-${ paymentOptionId }_input',
                'cBild'           => '#',
                'angezeigterName' => '${ fundingSourceTitle }',
                'fAufpreis'       => $method->fAufpreis,
                'cGebuehrname'    => $method->cGebuehrname,
                'cPreisLocalized' => $method->cPreisLocalized,
                'cHinweisText'    => '#',
            ],
        ]);

        return $this->smarty->fetch('checkout/inc_payment_methods.tpl');
    }

    /**
     * @param array $fundingMethods
     * @return array
     * @throws SmartyException
     */
    private function assignMappedFieldVariables(array $fundingMethods): array
    {
        foreach ($fundingMethods as $fundingMethod) {
            if (is_object($fundingMethod->fields)) {
                foreach ($fundingMethod->fields as $field => $value) {
                    $fundingMethod->fields->$field = $this->smarty->fetch('string:' . $value);
                }
            }
        }

        return $fundingMethods;
    }

    /**
     * @param PayPalPaymentInterface $payment
     * @return bool
     * @noinspection JsonEncodingApiUsageInspection
     */
    public function renderPaymentButtons(PayPalPaymentInterface $payment): bool
    {
        $langCode = Shop::getLanguageCode();
        $methods  = $this->smarty->getTemplateVars('Zahlungsarten');
        $methodID = $payment->getMethod()->getMethodID();
        $method   = first($methods, static function (stdClass $method) use ($methodID) {
            return $method->kZahlungsart === $methodID;
        });
        $ppOrder  = $payment->getPPOrder();

        if ($method === null) {
            return false;
        }

        $ppcFundingMethodsMapping = Helper::getInstance($this->plugin)->getFundingMethodsMapping($langCode)->toArray();
        $methodPicture            = $method->cBild ?? '';
        $methodHint               = $method->cHinweisText[$langCode] ?? '';
        $defaultFundingSource     = $payment->getDefaultFundingSource();
        $vaultingHelper           = new VaultingHelper($this->config);
        $customer                 = Frontend::getCustomer();

        $ppcFundingMethodsMapping[$defaultFundingSource] = (object)[
            'title'   => $method->angezeigterName[$langCode] ?? $method->cName,
            'picture' => $methodPicture,
            'note'    => $methodHint,
            'fields'  => null,
            'sort'    => '0',
        ];
        if (
            $vaultingHelper->isVaultingEnabled($defaultFundingSource, $customer->kKunde ?? 0)
            && !$vaultingHelper->isVaultingActive($customer->kKunde ?? 0, $payment)
        ) {
            try {
                $ppcFundingMethodsMapping[$defaultFundingSource]->fields = $this->smarty
                    ->assign('fundingSource', $defaultFundingSource)
                    ->assign('vaulting_enabled', $payment->getCache('ppc_vaulting_enable') === 'Y')
                    ->assign('label_vaulting_enable', $this->plugin->getLocalization()->getTranslation(
                        'jtl_paypal_commerce_vaulting_enable_description'
                    ))
                    ->assign('label_vaulting_tooltip', $this->plugin->getLocalization()->getTranslation(
                        'jtl_paypal_commerce_vaulting_enable_tooltip'
                    ))
                    ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/paymentCheckboxVaulting.tpl');
            } catch (Exception) {
                $ppcFundingMethodsMapping[$defaultFundingSource]->fields = null;
            }
        }

        try {
            //Hide all preloaded payment methods before SmartPaymentButtons are rendered
            \pq('.checkout-payment-method')->addClass('d-none');
            \pq('.checkout-shipping-form')->append(
                '<input id="ppc-funding-source_input" type="hidden" name="ppc-funding-source" '
                . 'value="' . $payment->getFundingSource() . '">'
            );
            \pq('#fieldset-payment')->append('<div class="jtl-spinner"><i class="fa fa-spinner fa-pulse"></i></div>');
            \pq('#result-wrapper')->append(
                $this->smarty
                    ->assign('zahlungsart', $method)
                    ->assign('ppcFundingMethodsMapping', \json_encode(
                        $this->assignMappedFieldVariables($ppcFundingMethodsMapping)
                    ))
                    ->assign('ppcModuleID', $payment->getMethod()->getModuleID())
                    ->assign('ppcMethodName', $payment->getMethod()->getName())
                    ->assign('ppcFrontendUrl', $this->plugin->getPaths()->getFrontendURL())
                    ->assign('ppcPaymentMethodID', $payment->getMethod()->getMethodID())
                    ->assign('ppcFundingSource', $payment->getFundingSource())
                    ->assign('ppcSingleFunding', $ppOrder !== null
                        && $ppOrder->getStatus() === OrderStatus::STATUS_APPROVED)
                    ->assign(
                        'ppcActiveTemplate',
                        Shop::Container()->getTemplateService()->getActiveTemplate()->getName()
                    )
                    ->assign('ppcOptionsTemplate', $this->renderPaymentOptionsTemplate($method))
                    ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/paymentButtons.tpl')
            );
        } catch (Exception) {
            // template error - how to handle?
        }

        return true;
    }

    /**
     * @param PayPalPaymentInterface $payment
     * @param string                 $ppcOrderId
     * @param GoogleTransactionInfo  $googleTransactionInfo
     * @return bool
     */
    public function renderGooglePay(
        PayPalPaymentInterface $payment,
        string $ppcOrderId,
        GoogleTransactionInfo $googleTransactionInfo
    ): bool {
        $ppOrder = $payment->getPPOrder($ppcOrderId);
        if ($ppOrder === null) {
            Helper::redirectAndExit($payment->getPaymentCancelURL($ppOrder));
            exit();
        }

        $environment  = PPCHelper::getEnvironment($this->config);
        $localization = $this->plugin->getLocalization();
        $locale       = Helper::twoDigitLocale(
            Helper::getLocaleFromISO(Helper::sanitizeISOCode(Shop::Lang()->getIso()))
        );

        try {
            \pq('#complete-order-button')
                ->after($this->smarty
                    ->assign('ppcLocale', $locale)
                    ->assign('ppcPaymentName', $payment->getLocalizedPaymentName())
                    ->assign('ppcFrontendUrl', $this->plugin->getPaths()->getFrontendURL())
                    ->assign('ppcFundingSource', $payment->getFundingSource())
                    ->assign('ppcOrderId', $ppcOrderId)
                    ->assign('ppcStateURL', $payment->getPaymentStateURL($ppOrder) ?? '#')
                    ->assign('ppcCancelURL', $payment->getPaymentCancelURL($ppOrder))
                    ->assign('ppcTransactionInfo', $googleTransactionInfo->stringify() ?? '{}')
                    ->assign('ppcSandbox', $environment->isSandbox())
                    ->assign('ppcLogLevel', $payment->getLogger()->getLogLevel())
                    ->assign('ppcGPayNotAvailable', \sprintf(
                        $localization->getTranslation('jtl_paypal_commerce_payment_notavailable') ?? '',
                        $payment->getLocalizedPaymentName()
                    ))
                    ->assign('ppcPayerActionError', $localization->getTranslation('acdc_3dserror_occured') ?? '')
                    ->assign(
                        'ppcProcessPaymentError',
                        $localization->getTranslation('jtl_paypal_commerce_payment_error') ?? ''
                    )
                    ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/googlepay.tpl'));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @param PayPalPaymentInterface $payment
     * @param string                 $ppcOrderId
     * @param AppleTransactionInfo   $appleTransactionInfo
     * @return bool
     */
    public function renderApplePay(
        PayPalPaymentInterface $payment,
        string $ppcOrderId,
        AppleTransactionInfo $appleTransactionInfo
    ): bool {
        $ppOrder = $payment->getPPOrder($ppcOrderId);
        if ($ppOrder === null) {
            Helper::redirectAndExit($payment->getPaymentCancelURL($ppOrder));
            exit();
        }

        $environment    = PPCHelper::getEnvironment($this->config);
        $localization   = $this->plugin->getLocalization();
        $billingContact = $appleTransactionInfo->getBillingContact();
        $locale         = Helper::twoDigitLocale(
            Helper::getLocaleFromISO(Helper::sanitizeISOCode(Shop::Lang()->getIso()))
        );

        try {
            \pq('#complete-order-button')
                ->after($this->smarty
                    ->assign('ppcShopName', $this->config->getPrefixedConfigItem(
                        Settings::BACKEND_SETTINGS_SECTION_APPLEPAYDISPLAY . '_displayname',
                        \mb_substr(Text::replaceUmlauts($payment->getShopTitle()), 0, 64)
                    ))
                    ->assign('ppcLocale', $locale)
                    ->assign('ppcPaymentName', $payment->getLocalizedPaymentName())
                    ->assign('ppcFrontendUrl', $this->plugin->getPaths()->getFrontendURL())
                    ->assign('ppcFundingSource', $payment->getFundingSource())
                    ->assign('ppcOrderId', $ppcOrderId)
                    ->assign('ppcStateURL', $payment->getPaymentStateURL($ppOrder) ?? '#')
                    ->assign('ppcCancelURL', $payment->getPaymentCancelURL($ppOrder))
                    ->assign('ppcTransactionInfo', $appleTransactionInfo->setBillingContact()->stringify() ?? '{}')
                    ->assign('ppcBillingContact', $billingContact->stringify() ?? 'null')
                    ->assign('ppcSandbox', $environment->isSandbox())
                    ->assign('ppcLogLevel', $payment->getLogger()->getLogLevel())
                    ->assign('ppcApplePayNotAvailable', \sprintf(
                        $localization->getTranslation('jtl_paypal_commerce_payment_notavailable') ?? '',
                        $payment->getLocalizedPaymentName()
                    ))
                    ->assign(
                        'ppcApplePayCanceled',
                        $localization->getTranslation('jtl_paypal_commerce_payment_error') ?? ''
                    )
                    ->fetch($this->plugin->getPaths()->getFrontendPath() . 'template/applepay.tpl'));
        } catch (Exception) {
            return false;
        }

        return true;
    }

    /**
     * @param string $fundingSource
     * @param string $shopOrderId
     * @param Order  $ppOrder
     * @return bool
     * @noinspection JsonEncodingApiUsageInspection
     */
    public function renderPayAgainPage(string $fundingSource, string $shopOrderId, Order $ppOrder): bool
    {
        $localization             = $this->plugin->getLocalization();
        $ppcFundingMethodsMapping = Helper::getInstance($this->plugin)
                                          ->getFundingMethodsMapping(Shop::getLanguageCode(), $fundingSource)
                                          ->toArray();
        try {
            $this->smarty->assign('abschlussseite')
                         ->assign('shopOrderId', $shopOrderId)
                         ->assign('ppcpOrderId', $ppOrder->getId())
                         ->assign('ppcFundingMethodsMapping', \json_encode(
                             $this->assignMappedFieldVariables($ppcFundingMethodsMapping)
                         ))
                         ->assign('ppcpFundingsource', $fundingSource)
                         ->assign(
                             'payment_pi_auto_complete_header',
                             $localization->getTranslation(
                                 'jtl_paypal_commerce_payment_pi_auto_complete_header'
                             ) ?? ''
                         )
                         ->assign(
                             'payment_pi_auto_complete_description',
                             $localization->getTranslation(
                                 'jtl_paypal_commerce_payment_pi_auto_complete_description'
                             ) ?? ''
                         );
        } catch (Exception) {
            // template error - how to handle?
        }

        return true;
    }

    /**
     * @return bool
     */
    public function renderFinishPage(): bool
    {
        \pq('.order-completed a.btn')->addClass('d-none');

        return true;
    }
}
