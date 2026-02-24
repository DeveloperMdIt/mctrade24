<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\PPC;

use Illuminate\Support\Collection;
use JTL\Helpers\Text;
use JTL\Shop;
use Plugin\jtl_paypal_commerce\frontend\ApplePayDAFController;
use Plugin\jtl_paypal_commerce\frontend\CheckoutPage;

/**
 * Class DefaultSettings
 * @package Plugin\jtl_paypal_commerce\PPC
 */
class DefaultSettings
{
    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getMerchantID(string $panel, string $section, int $sort): array
    {
        return [
            'value'       => '',
            'type'        => 'partial_readonly',
            'class'       => 'part-pre-0',
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $sort,
            'label'       => \__('Merchant ID'),
            'description' => \__('Ihre PayPal Merchant ID'),
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getClientID(string $panel, string $section, int $sort): array
    {
        return [
            'value'       => '',
            'type'        => 'partial_readonly',
            'class'       => 'part-pre-10',
            'handler'     => CryptedTagConfigValueHandler::class,
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $sort,
            'label'       => \__('Client ID'),
            'description' => \__('Ihre PayPal Client ID'),
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getClientSecret(string $panel, string $section, int $sort): array
    {
        return [
            'value'       => '',
            'type'        => 'partial_readonly',
            'class'       => 'part-post-10',
            'handler'     => CryptedTagConfigValueHandler::class,
            'sort'        => $sort,
            'panel'       => $panel,
            'section'     => $section,
            'label'       => \__('Client Secret'),
            'description' => \__('Ihr PayPal Client Secret'),
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getSmartPaymentButtonsShape(string $panel, string $section, int $sort): array
    {
        return [
            'value'       => 'rect',
            'type'        => 'selectbox',
            'class'       => '',
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $sort,
            'description' => \__('Hier können Sie die Form der Zahlungsbuttons ändern'),
            'label'       => \ucfirst(\__('Form')),
            'options'     => [
                [
                    'value' => 'pill',
                    'label' => \__('abgerundet'),
                ],
                [
                    'value' => 'rect',
                    'label' => \__('rechteckig (abgerundete Ecken)'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getSmartPaymentButtonsColor(string $panel, string $section, int $sort): array
    {
        return [
            'value'       => 'gold',
            'type'        => 'selectbox',
            'class'       => '',
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $sort,
            'label'       => \__('Farbschema'),
            'loadAfter'   => 'snippets/buttonPreview.tpl',
            'description' => \__('Hier können Sie die Farbe der Zahlungsbuttons ändern'),
            'options'     => [
                [
                    'value' => 'blue',
                    'label' => \__('blau'),
                ],
                [
                    'value' => 'black',
                    'label' => \__('schwarz'),
                ],
                [
                    'value' => 'white',
                    'label' => \__('weiß'),
                ],
                [
                    'value' => 'silver',
                    'label' => \__('silber'),
                ],
                [
                    'value' => 'gold',
                    'label' => \__('gold'),
                ],
            ],
        ];
    }

    public function getExpressBuyVaulting(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'triggerWarn'  => ['N'],
            'type'         => 'selectbox',
            'class'        => '',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => \__('Hier können Sie Vaulting aktivieren'),
            'label'        => \__('Vaulting aktivieren'),
            'loadAfter' => 'snippets/selectToggleScript.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getExpressBuyActivate(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'triggerWarn'  => ['N'],
            'type'         => 'selectbox',
            'class'        => '',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => \__('Hier können Sie Expresskauf aktivieren'),
            'label'        => \__('Expresskauf aktivieren'),
            'loadAfter' => 'snippets/selectToggleScript.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getExpressHandleInvoice(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'E',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle',
            'description'  => \__(
                'Hier legen Sie fest, wie fehlende Rechnungsadressdaten vervollständigt werden sollen'
            ),
            'label'        => \__('Fehlende Adressdaten vervollständigen'),
            'options'      => [
                [
                    'value' => 'E',
                    'label' => \__('Aus Lieferadresse ergänzen und zur Überprüfung anzeigen'),
                ],
                [
                    'value' => 'Y',
                    'label' => \__('Aus Lieferadresse ergänzen und fortfahren'),
                ],
                [
                    'value' => 'O',
                    'label' => \__('Komplette Lieferadresse als Rechnungsadresse verwenden'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Fehlende Daten durch Kunden ergänzen lassen'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getExpressBuyProductDetails(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle singleActivator_' . $section,
            'description'  => \__('Expresskauf in den Artikeldetails anzeigen'),
            'label'        => \__('Expresskauf in den Artikeldetails anzeigen'),
            'loadAfter'    => 'snippets/advancedSettings.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getExpressInCart(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle singleActivator_' . $section,
            'description'  => \__('Expresskauf im Warenkorb anzeigen'),
            'label'        => \__('Expresskauf im Warenkorb anzeigen'),
            'loadAfter'    => 'snippets/advancedSettings.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getExpressInOrderProcess(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle singleActivator_' . $section,
            'description'  => \__('Anzeige im Bestellvorgang'),
            'label'        => \__('Anzeige im Bestellvorgang'),
            'loadAfter'    => 'snippets/advancedSettings.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getExpressInMiniCart(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'vars'         => [
                'section' => $section,
            ],
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle singleActivator_' . $section,
            'description'  => \__('Expresskauf im Mini-Warenkorb anzeigen'),
            'label'        => \__('Expresskauf im Mini-Warenkorb anzeigen'),
            'loadAfter'    => ['snippets/advancedSettings.tpl','snippets/advancedSettingsScript.tpl'],
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getACDC3DSecureActivate(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'class'        => 'singleActivator_' . $section,
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => \__('3D Secure aktivieren (Empfohlen)'),
            'label'        => \__('3D Secure aktivieren'),
            'loadAfter'    => 'snippets/selectToggleScript.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja (Empfohlen)'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getACDC3DSecureMode(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'SCA_WHEN_REQUIRED',
            'type'         => 'selectbox',
            'class'        => 'd-none '
                . $section . '_activate3DSecure_toggle',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => \__('3D Secure SCA Beschreibung'),
            'label'        => \__('3D Secure SCA'),
            'options'      => [
                [
                    'value' => 'SCA_WHEN_REQUIRED',
                    'label' => \__('Wenn benötigt (Empfohlen)'),
                ],
                [
                    'value' => 'SCA_ALWAYS',
                    'label' => \__('Immer'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getApplePayActivate(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'N',
            'type'         => 'selectbox',
            'class'        => 'singleActivator_' . $section,
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => \__('Ihr Shop muss für Apple Pay registriert werden'),
            'label'        => \__('Haben Sie die Registrierung durchgeführt'),
            'loadAfter'    => 'snippets/selectToggleScript.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getApplePayDisplayName(string $panel, string $section, int $sort): array
    {
        $title = \mb_substr(Text::replaceUmlauts(Shop::getSettingValue(\CONF_GLOBAL, 'global_shopname')), 0, 64);

        return [
            'value'        => $title,
            'type'         => 'text',
            'class'        => '',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => \__('Apple Pay benötigt für die Händlerverifizierung die Angabe eines Anzeigenamens'),
            'label'        => \__('Anzeigename für die Händlerverifizierung'),
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getApplePayVersion(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => ApplePayDAFController::DAF_VERSION,
            'type'         => 'hidden',
            'class'        => 'hidden',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => '',
            'label'        => '',
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getConsentManagerActivate(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'N',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => '',
            'description'  => \__('consentManagerActivateDescription'),
            'label'        => \__('Consent-Manager aktivieren'),
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $template
     * @param array  $tplDefaults
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getGeneralTplSupport(
        string $template,
        array $tplDefaults,
        string $panel,
        string $section,
        int $sort
    ): array {
        return [
            'value'        => $template,
            'type'         => 'selectbox',
            'vars'         => [
                'scopes'      => CheckoutPage::PAGE_SCOPES,
                'tplDefaults' => $tplDefaults,
                'sections'    => [
                    Settings::BACKEND_SETTINGS_SECTION_EXPRESSBUYDISPLAY,
                    Settings::BACKEND_SETTINGS_SECTION_INSTALMENTBANNERDISP,
                ],
            ],
            'class'        => '',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => \__('templateSupportDescription'),
            'label'        => \__('Template Unterstützung'),
            'loadAfter'    => 'snippets/templateSupportScript.tpl',
            'options'      => [
                [
                    'value' => 'NOVA',
                    'label' => \__('NOVA'),
                ],
                [
                    'value' => 'custom',
                    'label' => \__('Individuell'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getGeneralPurchaseDesc(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => '',
            'description'  => \__('longPurchaseDescriptionDescription'),
            'label'        => \__('Beschreibung der Kaufdetails'),
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Auflistung der Artikelnamen'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Eigener Text über Sprachvariable'),
                ],
            ],
        ];
    }

    public function getGeneralShipmentTracking(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'N',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => '',
            'description'  => \__('shipmenttrackingActivateDescription'),
            'label'        => \__('Versandinformationen übermitteln'),
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getInstalmentBannerActivate(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'triggerWarn'  => ['N'],
            'type'         => 'selectbox',
            'class'        => '',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'description'  => \__('PayPal Ratenzahlung Banner aktivieren'),
            'label'        => \__('PayPal Ratenzahlung Banner aktivieren'),
            'loadAfter'    => 'snippets/selectToggleScript.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getInstalmentBannerInProductDetails(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle singleActivator_' . $section,
            'description'  => \__('Anzeige auf der Artikeldetailseite'),
            'label'        => \__('Anzeige auf der Artikeldetailseite'),
            'loadAfter'    => 'snippets/advancedSettings.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getInstalmentBannerInCart(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle singleActivator_' . $section,
            'description'  => \__('Anzeige im Warenkorb'),
            'label'        => \__('Anzeige im Warenkorb'),
            'loadAfter'    => 'snippets/advancedSettings.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getInstalmentBannerInOrderProcess(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle singleActivator_' . $section,
            'description'  => \__('Anzeige im Bestellvorgang'),
            'label'        => \__('Anzeige im Bestellvorgang'),
            'loadAfter'    => 'snippets/advancedSettings.tpl',
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getInstalmentBannerInMiniCart(string $panel, string $section, int $sort): array
    {
        return [
            'value'        => 'Y',
            'type'         => 'selectbox',
            'panel'        => $panel,
            'vars'         => [
                'section' => $section,
            ],
            'section'      => $section,
            'sort'         => $sort,
            'class'        => 'd-none '
                . $section . '_activate_toggle singleActivator_' . $section,
            'description'  => \__('Anzeige im Mini-Warenkorb'),
            'label'        => \__('Anzeige im Mini-Warenkorb'),
            'loadAfter'    => ['snippets/advancedSettings.tpl','snippets/advancedSettingsScript.tpl'],
            'options'      => [
                [
                    'value' => 'Y',
                    'label' => \__('Ja'),
                ],
                [
                    'value' => 'N',
                    'label' => \__('Nein'),
                ],
            ],
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getBannerDisplayLayout(array $scope, string $panel, string $section): array
    {
        return [
            'wrapperStart' => '<div class="advancedSettingsWrapper_' . $section . ' card-border mb-5 pt-3">',
            'value'       => $scope['values']['layout'] ?? 'flex',
            'type'        => 'selectbox',
            'class'       => $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('layoutDescription'),
            'label'       => \__('layout'),
            'options'     => [
                [
                    'value' => 'flex',
                    'label' => \__('layout_flex'),
                ],
                [
                    'value' => 'text',
                    'label' => \__('layout_text'),
                ],
            ],
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getBannerDisplayLogotype(array $scope, string $panel, string $section): array
    {
        return [
            'value'       => 'primary',
            'type'        => 'selectbox',
            'class'       => $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('logoTypeDescription'),
            'label'       => \__('logoType'),
            'options'     => [
                [
                    'value' => 'primary',
                    'label' => \__('logoType_primary'),
                ],
                [
                    'value' => 'alternative',
                    'label' => \__('logoType_alternative'),
                ],
                [
                    'value' => 'inline',
                    'label' => \__('logoType_inline'),
                ],
                [
                    'value' => 'none',
                    'label' => \__('logoType_none'),
                ],
            ],
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getBannerDisplayTextsize(array $scope, string $panel, string $section): array
    {
        return [
            'value'       => 12,
            'type'        => 'text',
            'class'       => $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('textSizeDescription'),
            'label'       => \__('textSize'),
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getBannerDisplayTextcolor(array $scope, string $panel, string $section): array
    {
        return [
            'value'       => $scope['values']['textColor'] ?? 'white',
            'type'        => 'selectbox',
            'class'       => $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('textColor'),
            'label'       => \__('textColor'),
            'options'     => [
                [
                    'value' => 'white',
                    'label' => \__('textColor_white'),
                ],
                [
                    'value' => 'black',
                    'label' => \__('textColor_black'),
                ],
            ],
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getBannerDisplayLayoutRatio(array $scope, string $panel, string $section): array
    {
        return [
            'value'       => $scope['values']['layoutRatio'] ?? '8x1',
            'type'        => 'selectbox',
            'class'       => isset($scope['exclude']['layoutRatio']) ? 'd-none' : $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('layoutRatioDescription'),
            'label'       => \__('layoutRatio'),
            'options'     => [
                [
                    'value' => '1x1',
                    'label' => \__('1x1'),
                ],
                [
                    'value' => '1x4',
                    'label' => \__('1x4'),
                ],
                [
                    'value' => '8x1',
                    'label' => \__('8x1'),
                ],
                [
                    'value' => '20x1',
                    'label' => \__('20x1'),
                ],
            ],
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getBannerDisplayLayoutType(array $scope, string $panel, string $section): array
    {
        return [
            'value'       => 'white',
            'type'        => 'selectbox',
            'class'       => isset($scope['exclude']['layoutType']) ? 'd-none' : $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('layoutTypeDescription'),
            'label'       => \__('layoutType'),
            'options'     => [
                [
                    'value' => 'white',
                    'label' => \__('layoutType_white'),
                ],
                [
                    'value' => 'black',
                    'label' => \__('layoutType_black'),
                ],
                [
                    'value' => 'blue',
                    'label' => \__('layoutType_blue'),
                ],
                [
                    'value' => 'grey',
                    'label' => \__('layoutType_grey'),
                ],
            ],
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getBannerDisplayPHPQSelector(array $scope, string $panel, string $section): array
    {
        return [
            'value'       => $scope['selector'],
            'type'        => 'text',
            'class'       => $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('phpqSelectorDescription'),
            'label'       => \__('phpqSelector'),
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getBannerDisplayPHPQMethod(array $scope, string $panel, string $section): array
    {
        return [
            'wrapperEnd'  => '</div>',
            'value'       => $scope['method'],
            'vars'        => [
                'scope' => $scope['name'],
            ],
            'type'        => 'selectbox',
            'class'       => $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('phpqMethodDescription'),
            'label'       => \__('phpqMethod'),
            'loadAfter'   => 'snippets/instalmentBannerPreview.tpl',
            'options'     => [
                [
                    'value' => 'after',
                    'label' => \__('phpq_after'),
                ],
                [
                    'value' => 'append',
                    'label' => \__('phpq_append'),
                ],
                [
                    'value' => 'before',
                    'label' => \__('phpq_before'),
                ],
                [
                    'value' => 'prepend',
                    'label' => \__('phpq_prepend'),
                ],
            ],
        ];
    }

    /**
     * @param int $sort
     * @return array
     */
    public function getBannerDisplayValueProduct(int $sort): array
    {
        return [
            'sort'   => $sort,
            'values' => [
                'textColor' => 'black',
            ],
        ];
    }

    /**
     * @param int $sort
     * @return array
     */
    public function getBannerDisplayValueCart(int $sort): array
    {
        return [
            'sort'   => $sort,
            'values' => [
                'textColor' => 'black',
                'layout'    => 'text',
            ],
        ];
    }

    /**
     * @param int $sort
     * @return array
     */
    public function getBannerDisplayValueMiniCart(int $sort): array
    {
        return [
            'sort'   => $sort,
            'values' => [
                'textColor' => 'black',
                'layout'    => 'text',
            ],
        ];
    }

    /**
     * @param int $sort
     * @return array
     */
    public function getBannerDisplayValueOrderProcess(int $sort): array
    {
        return [
            'sort'   => $sort,
            'values' => [
                'textColor' => 'black',
                'layoutRatio' => '20x1',
            ],
        ];
    }

    /**
     * @return int[]
     */
    public function getECSDisplayValueProduct(int $sort): array
    {
        return [
            'sort'   => $sort,
        ];
    }

    /**
     * @return int[]
     */
    public function getECSDisplayValueCart(int $sort): array
    {
        return [
            'sort'   => $sort,
        ];
    }

    /**
     * @return int[]
     */
    public function getECSDisplayValueMiniCart(int $sort): array
    {
        return [
            'sort'   => $sort,
        ];
    }

    /**
     * @return int[]
     */
    public function getECSDisplayValueOrderProcess(int $sort): array
    {
        return [
            'sort'   => $sort,
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getECSDisplayPHPQSelector(array $scope, string $panel, string $section): array
    {
        return [
            'wrapperStart' => '<div class="advancedSettingsWrapper_' . $section . ' card-border mb-5 pt-3">',
            'value'        => $scope['selector'],
            'type'         => 'text',
            'class'        => $scope['class'],
            'panel'        => $panel,
            'section'      => $section,
            'sort'         => $scope['sort'],
            'description'  => \__('phpqSelectorDescription'),
            'label'        => \__('phpqSelector'),
        ];
    }

    /**
     * @param array  $scope
     * @param string $panel
     * @param string $section
     * @return array
     */
    public function getECSDisplayPHPQMethod(array $scope, string $panel, string $section): array
    {
        return [
            'wrapperEnd'  => '</div>',
            'value'       => $scope['method'],
            'type'        => 'selectbox',
            'class'       => $scope['class'],
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $scope['sort'],
            'description' => \__('phpqMethodDescription'),
            'label'       => \__('phpqMethod'),
            'options'     => [
                [
                    'value' => 'after',
                    'label' => \__('phpq_after'),
                ],
                [
                    'value' => 'append',
                    'label' => \__('phpq_append'),
                ],
                [
                    'value' => 'before',
                    'label' => \__('phpq_before'),
                ],
                [
                    'value' => 'prepend',
                    'label' => \__('phpq_prepend'),
                ],
            ],
        ];
    }

    /**
     * @param Collection $countries
     * @return array
     */
    private function mapCountries(Collection $countries): array
    {
        $locale = static function ($country) {
            return \__($country);
        };

        return $countries->map(function ($countries) use ($locale) {
            return \implode(', ', \array_map($locale, $countries));
        })->toArray();
    }

    /**
     * @param string $panel
     * @param string $section
     * @param int    $sort
     * @return array
     */
    public function getPaymentMethodsActivate(string $panel, string $section, int $sort): array
    {
        return [
            'value'       => APM::APM_ALL,
            'type'        => 'activationList',
            'vars'        => [
                'selectGroups' => [
                    'cards_fundingSource'  => APM::APM_CARDS,
                    'credit_fundingSource' => APM::APM_CREDIT,
                    'bank_fundingSource'   => APM::APM_BANK,
                ],
            ],
            'class'       => '',
            'panel'       => $panel,
            'section'     => $section,
            'sort'        => $sort,
            'label'       => \__('paymentMethodsPanel'),
            'label2'      => \__('paymentMethodsPanelCountries'),
            'description' => \__('Legen Sie hier fest, welche Zahlungsarten generell angezeigt werden sollen.'),
            'options'     => (new Collection(APM::APM_ALL))
                ->map(function ($item) {
                    return [
                        'label'    => \__($item),
                        'value'    => $item,
                        'extended' => $this->mapCountries(new Collection(APM::APM_COUNTRIES)),
                        'action' => \array_fill_keys(APM::APM_ALL, 'APMSettings'),
                    ];
                })->toArray(),
        ];
    }

    /**
     * @return string[][]
     */
    public function getTemplateNovaInstalmentBanner(): array
    {
        return [
            'selector' => [
                CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS => '#add-to-cart.product-buy',
                CheckoutPage::PAGE_SCOPE_CART           => '#cart-checkout-btn',
                CheckoutPage::PAGE_SCOPE_ORDERPROCESS   => '#fieldset-payment .row',
                CheckoutPage::PAGE_SCOPE_MINICART       => '.cart-dropdown-buttons:first',
            ],
            'method'   => [
                CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS => 'after',
                CheckoutPage::PAGE_SCOPE_CART           => 'after',
                CheckoutPage::PAGE_SCOPE_ORDERPROCESS   => 'after',
                CheckoutPage::PAGE_SCOPE_MINICART       => 'after',
            ],
        ];
    }

    /**
     * @return string[][]
     */
    public function getTemplateNovaExpressDisplay(): array
    {
        return [
            'selector' => [
                CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS => '#add-to-cart',
                CheckoutPage::PAGE_SCOPE_CART => '#cart-checkout-btn',
                CheckoutPage::PAGE_SCOPE_ORDERPROCESS => '#order_register_or_login',
                CheckoutPage::PAGE_SCOPE_MINICART => '.cart-dropdown-buttons:first',
            ],
            'method' => [
                CheckoutPage::PAGE_SCOPE_PRODUCTDETAILS => 'append',
                CheckoutPage::PAGE_SCOPE_CART => 'after',
                CheckoutPage::PAGE_SCOPE_ORDERPROCESS => 'after',
                CheckoutPage::PAGE_SCOPE_MINICART => 'append',
            ],
        ];
    }
}
