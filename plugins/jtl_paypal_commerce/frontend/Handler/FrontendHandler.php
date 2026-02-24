<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend\Handler;

use Exception;
use JTL\Cache\JTLCacheInterface;
use JTL\Consent\Item;
use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Router\Router;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\AlertService;
use Plugin\jtl_paypal_commerce\frontend\AccountPage;
use Plugin\jtl_paypal_commerce\frontend\ApplePayDAFController;
use Plugin\jtl_paypal_commerce\frontend\CheckoutPage;
use Plugin\jtl_paypal_commerce\frontend\ExpressCheckout;
use Plugin\jtl_paypal_commerce\frontend\MiniWKPage;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\paymentmethod\PaymentmethodNotFoundException;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class Handler
 * @package Plugin\jtl_paypal_commerce\frontend
 */
final class FrontendHandler
{
    /** @var PluginInterface */
    private PluginInterface $plugin;

    /** @var DbInterface */
    private DbInterface $db;

    /** @var JTLCacheInterface */
    private JTLCacheInterface $cache;

    /** @var Configuration */
    private Configuration $config;

    /** @var AlertServiceInterface */
    private AlertServiceInterface $alertService;

    /**
     * Handler constructor.
     * @param PluginInterface            $plugin
     * @param DbInterface|null           $db
     * @param Configuration|null         $configuration
     * @param AlertServiceInterface|null $alertService
     * @param JTLCacheInterface|null     $cache
     */
    public function __construct(
        PluginInterface $plugin,
        ?DbInterface $db = null,
        ?Configuration $configuration = null,
        ?AlertServiceInterface $alertService = null,
        ?JTLCacheInterface $cache = null
    ) {
        $this->plugin       = $plugin;
        $this->db           = $db ?? Shop::Container()->getDB();
        $this->config       = $configuration ?? PPCHelper::getConfiguration($plugin);
        $this->alertService = $alertService ?? AlertService::getInstance();
        $this->cache        = $cache ?? Shop::Container()->getCache();
    }

    /**
     * @param string $original
     * @return string
     */
    public static function getBackendTranslation(string $original): string
    {
        if (!Shop::isFrontend()) {
            return \__($original);
        }

        $getText   = Shop::Container()->getGetText();
        $oldLocale = $getText->getLanguage();
        $locale    = Helper::sanitizeLocale(Helper::getLocaleFromISO(Helper::sanitizeISOCode(Shop::Lang()->getIso())));
        if ($oldLocale !== $locale) {
            $getText->setLanguage($locale);
            $translate = \__($original);
            $getText->setLanguage($oldLocale);
        } else {
            $translate = \__($original);
        }

        return $translate;
    }

    /**
     * @return void
     */
    public function handleECSOrder(): void
    {
        $linkHelper = Shop::Container()->getLinkService();
        $payMethod  = Helper::getInstance($this->plugin)->getPaymentFromName('PayPalCommerce');
        try {
            if ($payMethod === null) {
                throw new PaymentmethodNotFoundException('Paymentmethod PayPalCommerce not found');
            }
            $expressCheckout = new ExpressCheckout();
            if (!$expressCheckout->ecsCheckout($payMethod, $this->config, Frontend::getCustomer())) {
                $this->alertService->addNotice(
                    $this->plugin->getLocalization()->getTranslation('jtl_paypal_commerce_ecs_missing_data'),
                    'missingPayerData',
                    ['linkText' => $payMethod->getLocalizedPaymentName()]
                );
                Helper::redirectAndExit(
                    $linkHelper->getStaticRoute('bestellvorgang.php')
                    . ((int)Frontend::getCustomer()->kKunde === 0 ? '?unreg_form=1' : '')
                );
                exit();
            }

            Helper::redirectAndExit($linkHelper->getStaticRoute('bestellvorgang.php'));
            exit();
        } catch (Exception $e) {
            $this->alertService->addError(
                self::getBackendTranslation($e->getMessage()),
                'ppcNotFound'
            );

            Helper::redirectAndExit($linkHelper->getStaticRoute('warenkorb.php'));
            exit();
        }
    }

    /**
     * @param array $args
     * @return void
     */
    public function smarty(array $args): void
    {
        /** @var JTLSmarty $smarty */
        $smarty       = $args['smarty'];
        $checkoutPage = CheckoutPage::getInstanceInitialized($this->plugin);
        if ($checkoutPage !== null && $checkoutPage->hasValidStep()) {
            $checkoutPage->render($smarty);

            return;
        }

        $miniWKPage = MiniWKPage::getInstance($this->plugin);
        if ($miniWKPage->hasValidStep()) {
            $miniWKPage->render($smarty);
        }

        $accountPage = AccountPage::getInstanceInitialized($this->plugin);
        if ($accountPage !== null && $accountPage->hasValidStep()) {
            $accountPage->render($smarty);
        }
    }

    /**
     * @param array $args
     */
    public function addConsentItem(array $args): void
    {
        $lastID   = $args['items']->reduce(static function ($result, Item $item) {
                $value = $item->getID();
                return $result === null || $value > $result ? $value : $result;
        }) ?? 0;
        $locale   = $this->plugin->getLocalization();
        $cmActive = Shop::getSettingValue(\CONF_CONSENTMANAGER, 'consent_manager_active') ?? 'N';
        if (
            $cmActive === 'Y' &&
            $this->config->getPrefixedConfigItem(
                Settings::BACKEND_SETTINGS_SECTION_CONSENTMANAGER . '_activate'
            ) === 'Y'
        ) {
            $lastID++;
            $langISO = Shop::getLanguageCode();
            $item    = new Item();
            $item->setName(self::getBackendTranslation('PayPal Express Checkout und Ratenzahlung'));
            $item->setID($lastID);
            $item->setItemID(Configuration::CONSENT_ID);
            $item->setDescription($locale->getTranslation(
                'jtl_paypal_commerce_instalment_banner_consent_description',
                $langISO
            ));
            $item->setPurpose($locale->getTranslation(
                'jtl_paypal_commerce_instalment_banner_consent_purpose',
                $langISO
            ));
            $item->setPrivacyPolicy(
                'https://www.paypal.com/de/webapps/mpp/ua/privacy-full?locale.x=' .
                Helper::sanitizeLocale(Helper::getLocaleFromISO(Helper::sanitizeISOCode(Shop::Lang()->getIso())))
            );
            $item->setCompany('PayPal');
            $args['items']->push($item);
        }
    }

    /**
     * @param array $args
     * @return void
     */
    public function routerPredispatch(array $args): void
    {
        /** @var Router $router */
        $router     = $args['router'];
        $controller = new ApplePayDAFController(
            $this->db,
            $this->cache,
            Shop::getState(),
            [
                'workingMode' => $this->config->getConfigValues()->getWorkingMode(),
                'activated'   => $this->config->getPrefixedConfigItem('ApplePayDisplay_activated', 'N'),
                'version'     => $this->config->getPrefixedConfigItem(
                    'ApplePayDisplay_version',
                    ApplePayDAFController::DAF_VERSION
                ),
            ],
            Shop::Container()->getAlertService()
        );

        $router->addRoute(ApplePayDAFController::DAF_ROUTE, [$controller, 'getResponse'], 'applepayDAFRoute');
        $router->addRoute(
            ApplePayDAFController::DAF_DOWNLOAD_ROUTE,
            [$controller, 'getDownloadResponse'],
            'applepayDAFDownloadRoute'
        );
    }
}
