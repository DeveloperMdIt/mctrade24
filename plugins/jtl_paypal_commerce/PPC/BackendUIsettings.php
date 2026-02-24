<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use Exception;
use Illuminate\Support\Collection;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\frontend\CheckoutPage;

class BackendUIsettings
{
    /** @var BackendUIsettings|null */
    private static self|null $instance = null;

    /** @var DefaultSettings */
    private DefaultSettings $defaults;

    /**
     * BackendUIsettings constructor
     */
    private function __construct()
    {
        $this->defaults = new DefaultSettings();
        self::$instance = $this;
    }

    /**
     * @return array
     */
    private function getCredentialSettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_CREDENTIALS;
        $section = Settings::BACKEND_SETTINGS_SECTION_CREDENTIALS;

        return [
            'merchantID'   => $this->defaults->getMerchantID($panel, $section, 1),
            'clientID'     => $this->defaults->getClientID($panel, $section, 2),
            'clientSecret' => $this->defaults->getClientSecret($panel, $section, 3),
        ];
    }

    /**
     * @return array
     */
    private function getSmartPaymentSettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_DISPLAY;
        $section = Settings::BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS;

        return [
            $section . '_shape' => $this->defaults->getSmartPaymentButtonsShape($panel, $section, 1),
            $section . '_color' => $this->defaults->getSmartPaymentButtonsColor($panel, $section, 2),
        ];
    }

    private function getVaultingSettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_EXPRESSBUY;
        $section = Settings::BACKEND_SETTINGS_SECTION_VAULTING;

        return [
            $section . '_activateVaulting'     => $this->defaults->getExpressBuyVaulting($panel, $section, 10),
        ];
    }

    /**
     * @return array
     */
    private function getExpressPaymentSettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_EXPRESSBUY;
        $section = Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY;

        return [
            $section . '_activate'             => $this->defaults->getExpressBuyActivate($panel, $section, 10),
            $section . '_handleInvoiceAddress' => $this->defaults->getExpressHandleInvoice($panel, $section, 15),
            $section . '_showInProductDetails' => $this->defaults->getExpressBuyProductDetails($panel, $section, 20),
            $section . '_showInCart'           => $this->defaults->getExpressInCart($panel, $section, 30),
            $section . '_showInOrderProcess'   => $this->defaults->getExpressInOrderProcess($panel, $section, 40),
            $section . '_showInMiniCart'       => $this->defaults->getExpressInMiniCart($panel, $section, 50),
        ];
    }

    /**
     * @return array
     */
    private function getACDCSettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_ACDC;
        $section = Settings::BACKEND_SETTINGS_SECTION_ACDCDISPLAY;

        return [
            $section . '_activate3DSecure' => $this->defaults->getACDC3DSecureActivate($panel, $section, 10),
            $section . '_mode3DSecure'     => $this->defaults->getACDC3DSecureMode($panel, $section, 20),
        ];
    }

    /**
     * @return array
     */
    private function getApplePaySettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_APPLEPAY;
        $section = Settings::BACKEND_SETTINGS_SECTION_APPLEPAYDISPLAY;

        return [
            $section . '_activated'   => $this->defaults->getApplePayActivate($panel, $section, 10),
            $section . '_displayname' => $this->defaults->getApplePayDisplayName($panel, $section, 12),
            $section . '_version'     => $this->defaults->getApplePayVersion($panel, $section, 15),
        ];
    }

    /**
     * @return array
     */
    private function getInstallmentBannerSettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_INSTALMENTBANNER;
        $section = Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP;

        return [
            $section . '_activate'             => $this->defaults->getInstalmentBannerActivate($panel, $section, 10),
            $section . '_showInProductDetails' => $this->defaults->getInstalmentBannerInProductDetails(
                $panel,
                $section,
                20
            ),
            $section . '_showInCart'           => $this->defaults->getInstalmentBannerInCart($panel, $section, 30),
            $section . '_showInOrderProcess'   => $this->defaults->getInstalmentBannerInOrderProcess(
                $panel,
                $section,
                40
            ),
            $section . '_showInMiniCart'       => $this->defaults->getInstalmentBannerInMiniCart($panel, $section, 50),
        ];
    }

    /**
     * @param string $template
     * @return array
     */
    private function getGeneralSettings(string $template): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_GENERAL;
        $section = Settings::BACKEND_SETTINGS_SECTION_GENERAL;

        return [
            $section . '_templateSupport'     => $this->defaults->getGeneralTplSupport(
                $template,
                $this->getTemplateDefaults(),
                $panel,
                $section,
                10
            ),
            $section . '_purchaseDescription' => $this->defaults->getGeneralPurchaseDesc($panel, $section, 20),
            $section . '_shipmenttracking' => $this->defaults->getGeneralShipmentTracking($panel, $section, 30),
        ];
    }

    /**
     * @return array
     */
    private function getConsentManagerSettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_GENERAL;
        $section = Settings::BACKEND_SETTINGS_SECTION_CONSENTMANAGER;

        return [
            $section . '_activate' => $this->defaults->getConsentManagerActivate($panel, $section, 30),
        ];
    }

    /**
     * @return array
     */
    private function getPaymentMethodSettings(): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_PAYMENTMETHODSPANEL;
        $section = Settings::BACKEND_SETTINGS_SECTION_PAYMENTMETHODS;

        return [
            $section . '_enabled' => $this->defaults->getPaymentMethodsActivate($panel, $section, 70),
        ];
    }

    /**
     * @param mixed $scope
     * @param array $settings
     * @return array
     */
    private function getBannerDisplayScopeSettings(array $scope, array $settings): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_INSTALMENTBANNER;
        $section = Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP;
        $name    = $section . '_' . $scope['name'];

        $settings[$name . '_layout'] = $this->defaults->getBannerDisplayLayout($scope, $panel, $section);
        $scope['sort']++;
        $settings[$name . '_logoType'] = $this->defaults->getBannerDisplayLogotype($scope, $panel, $section);
        $scope['sort']++;
        $settings[$name . '_textSize'] = $this->defaults->getBannerDisplayTextsize($scope, $panel, $section);
        $scope['sort']++;
        $settings[$name . '_textColor'] = $this->defaults->getBannerDisplayTextcolor($scope, $panel, $section);
        $scope['sort']++;
        $settings[$name . '_layoutRatio'] = $this->defaults->getBannerDisplayLayoutRatio($scope, $panel, $section);
        $scope['sort']++;
        $settings[$name . '_layoutType'] = $this->defaults->getBannerDisplayLayoutType($scope, $panel, $section);
        $scope['sort']++;
        $settings[$name . '_phpqSelector'] = $this->defaults->getBannerDisplayPHPQSelector($scope, $panel, $section);
        $scope['sort']++;
        $settings[$name . '_phpqMethod'] = $this->defaults->getBannerDisplayPHPQMethod($scope, $panel, $section);

        return $settings;
    }

    /**
     * @param array $scope
     * @param array $settings
     * @return array
     */
    private function getECSDisplayScopeSettings(array $scope, array $settings): array
    {
        $panel   = Settings::BACKEND_SETTINGS_PANEL_EXPRESSBUY;
        $section = Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY;
        $name    = $section . '_' . $scope['name'];

        $settings[$name . '_phpqSelector'] = $this->defaults->getECSDisplayPHPQSelector($scope, $panel, $section);
        $scope['sort']++;
        $settings[$name . '_phpqMethod'] = $this->defaults->getECSDisplayPHPQMethod($scope, $panel, $section);

        return $settings;
    }

    /**
     * @param string $scope
     * @param array  $setting
     * @return array
     */
    private function getBannerDisplayScopeSettingValues(string $scope, array $setting): array
    {
        switch ($scope) {
            case CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS:
                $setting = \array_merge($setting, $this->defaults->getBannerDisplayValueProduct(21));
                break;
            case CheckoutPage::PAGE_SCOPE_CART:
                $setting = \array_merge($setting, $this->defaults->getBannerDisplayValueCart(31));
                break;
            case CheckoutPage::PAGE_SCOPE_ORDERPROCESS:
                $setting = \array_merge($setting, $this->defaults->getBannerDisplayValueOrderProcess(41));
                break;
            case CheckoutPage::PAGE_SCOPE_MINICART:
                $setting = \array_merge($setting, $this->defaults->getBannerDisplayValueMiniCart(51));
                break;
            default:
        }

        return $setting;
    }

    /**
     * @param string $scope
     * @param array  $setting
     * @return array
     */
    private function getECSDisplayScopeSettingValues(string $scope, array $setting): array
    {
        switch ($scope) {
            case CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS:
                $setting = \array_merge($setting, $this->defaults->getECSDisplayValueProduct(21));
                break;
            case CheckoutPage::PAGE_SCOPE_CART:
                $setting = \array_merge($setting, $this->defaults->getECSDisplayValueCart(31));
                break;
            case CheckoutPage::PAGE_SCOPE_ORDERPROCESS:
                $setting = \array_merge($setting, $this->defaults->getECSDisplayValueOrderProcess(41));
                break;
            case CheckoutPage::PAGE_SCOPE_MINICART:
                $setting = \array_merge($setting, $this->defaults->getECSDisplayValueMiniCart(51));
                break;
            default:
        }

        return $setting;
    }

    /**
     * @param array    $tplDefaults
     * @param callable $scopeValuesCallback
     * @param callable $scopeSettingsCallback
     * @return array
     */
    private function getGenericDisplaySettings(
        array $tplDefaults,
        callable $scopeValuesCallback,
        callable $scopeSettingsCallback
    ): array {
        $scopes   = new Collection(CheckoutPage::PAGE_SCOPES);
        $settings = [];

        $scopes = $scopes->map(function ($item) use ($tplDefaults, $scopeValuesCallback) {
            return $scopeValuesCallback($item, [
                'name'     => $item,
                'selector' => $tplDefaults['selector'][$item],
                'method'   => $tplDefaults['method'][$item],
                'class'    => $item . '_advancedSetting',
            ]);
        })->toArray();

        foreach ($scopes as $scope) {
            $settings = $scopeSettingsCallback($scope, $settings);
            $scope['sort']++;
        }

        return $settings;
    }

    /**
     * @param array $tplDefaults
     * @return array
     */
    private function getBannerDisplaySettings(array $tplDefaults): array
    {
        return $this->getGenericDisplaySettings(
            $tplDefaults[Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP],
            [$this, 'getBannerDisplayScopeSettingValues'],
            [$this, 'getBannerDisplayScopeSettings']
        );
    }

    /**
     * @param array $tplDefaults
     * @return array
     */
    private function getECSDisplaySettings(array $tplDefaults): array
    {
        return $this->getGenericDisplaySettings(
            $tplDefaults[Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY],
            [$this, 'getECSDisplayScopeSettingValues'],
            [$this, 'getECSDisplayScopeSettings']
        );
    }

    /**
     * @param string|null $tpl
     * @return array
     */
    private function getTemplateDefaults(?string $tpl = null): array
    {
        $defaults = [
            'NOVA' => [
                Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP
                    => $this->defaults->getTemplateNovaInstalmentBanner(),
                Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY
                    => $this->defaults->getTemplateNovaExpressDisplay(),
            ],
        ];

        if ($tpl === null) {
            return $defaults;
        }

        return $defaults[$tpl] ?? $defaults['NOVA'];
    }

    /**
     * @return Collection
     * @throws Exception
     */
    public static function getDefaultSettings(): Collection
    {
        $instance    = self::$instance ?? new self();
        $template    = Shop::Container()->getTemplateService()->getActiveTemplate()->getName() ?? 'custom';
        $tplDefaults = $instance->getTemplateDefaults($template);

        return (new Collection($instance->getCredentialSettings()))
                 ->merge($instance->getSmartPaymentSettings())
                 ->merge($instance->getVaultingSettings())
                 ->merge($instance->getExpressPaymentSettings())
                 ->merge($instance->getACDCSettings())
                 ->merge($instance->getApplePaySettings())
                 ->merge($instance->getInstallmentBannerSettings())
                 ->merge($instance->getGeneralSettings($template))
                 ->merge($instance->getConsentManagerSettings())
                 ->merge($instance->getPaymentMethodSettings())
                 ->merge($instance->getBannerDisplaySettings($tplDefaults))
                 ->merge($instance->getECSDisplaySettings($tplDefaults));
    }
}
