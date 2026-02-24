<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

/**
 * Class Settings
 * @package Plugin\jtl_paypal_commerce\PPC
 */
class Settings
{
    public const BACKEND_SETTINGS_PANEL_INSTALMENTBANNER       = 'instalmentBanner';
    public const BACKEND_SETTINGS_PANEL_DISPLAY                = 'display';
    public const BACKEND_SETTINGS_SECTION_ACDCDISPLAY          = 'ACDCDisplay';
    public const COMPONENT_APPLE_PAY                           = 'applepay';
    public const BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS     = 'smartPaymentButtons';
    public const BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP = 'instalmentBannerDisplay';
    public const BACKEND_SETTINGS_PANEL_PAYMENTMETHODSPANEL    = 'paymentMethodsPanel';
    public const COMPONENT_MARKS                               = 'marks';
    public const BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY    = 'expressBuyDisplay';
    public const BACKEND_SETTINGS_SECTION_GENERAL              = 'general';
    public const COMPONENT_FUNDING_ELIGIBILITY                 = 'funding-eligibility';
    public const BACKEND_SETTINGS_SECTION_CREDENTIALS          = 'credentials';
    public const BACKEND_SETTINGS_SECTION_CONSENTMANAGER       = 'consentManager';
    public const COMPONENT_HOSTED_FIELDS                       = 'hosted-fields';
    public const BACKEND_SETTINGS_PANEL_EXPRESSBUY             = 'expressBuy';
    public const BACKEND_SETTINGS_PANEL_GENERAL                = 'general';
    public const BACKEND_SETTINGS_SECTION_VAULTING             = 'vaultingDisplay';
    public const BACKEND_SETTINGS_PANEL_APPLEPAY               = 'ApplePay';
    public const BACKEND_SETTINGS_SECTION_APPLEPAYDISPLAY      = 'ApplePayDisplay';
    public const BACKEND_SETTINGS_SECTION_PAYMENTMETHODS       = 'paymentMethods';
    public const COMPONENT_PAYMENT_FIELDS                      = 'payment-fields';
    public const BACKEND_SETTINGS_PANEL_ACDC                   = 'ACDC';
    public const COMPONENT_BUTTONS                             = 'buttons';
    public const COMPONENT_GOOGLE_PAY                          = 'googlepay';
    public const BACKEND_SETTINGS_PANEL_CREDENTIALS            = 'credentials';
    public const COMPONENT_MESSAGES                            = 'messages';

    public const BACKEND_SETTINGS_PANELS   = [
        Settings::BACKEND_SETTINGS_PANEL_CREDENTIALS,
        Settings::BACKEND_SETTINGS_PANEL_DISPLAY,
        Settings::BACKEND_SETTINGS_PANEL_EXPRESSBUY,
        Settings::BACKEND_SETTINGS_PANEL_ACDC,
        Settings::BACKEND_SETTINGS_PANEL_APPLEPAY,
        Settings::BACKEND_SETTINGS_PANEL_INSTALMENTBANNER,
        Settings::BACKEND_SETTINGS_PANEL_GENERAL,
        Settings::BACKEND_SETTINGS_PANEL_PAYMENTMETHODSPANEL
    ];
    public const BACKEND_SETTINGS_SECTIONS = [
        Settings::BACKEND_SETTINGS_SECTION_CREDENTIALS,
        Settings::BACKEND_SETTINGS_SECTION_SMARTPAYMENTBTNS,
        Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY,
        Settings::BACKEND_SETTINGS_SECTION_VAULTING,
        Settings::BACKEND_SETTINGS_SECTION_ACDCDISPLAY,
        Settings::BACKEND_SETTINGS_SECTION_APPLEPAYDISPLAY,
        Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP,
        Settings::BACKEND_SETTINGS_SECTION_CONSENTMANAGER,
        Settings::BACKEND_SETTINGS_SECTION_GENERAL,
        Settings::BACKEND_SETTINGS_SECTION_PAYMENTMETHODS,
    ];
}
