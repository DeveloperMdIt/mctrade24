<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Renderer;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JTL\Alert\Alert;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\Onboarding\Onboarding;
use Plugin\jtl_paypal_commerce\paymentmethod\Helper;
use Plugin\jtl_paypal_commerce\PPC\APM;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\ConfigValues;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\Endpoint;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\ReferralsRequest;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\ReferralsResponse;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class InfoTabRenderer
 * @package Plugin\jtl_paypal_commerce\adminmenu\Renderer
 */
class InfosRenderer extends AbstractRenderer
{
    private const JTL_LOGO_URL   = 'img/JTL-Shop-Logo-rgb.svg';
    private const PPOB_URL       = 'https://www.paypal.com/bizsignup/partner/entry';
    private const PPOB_SB_URL    = 'https://www.sandbox.paypal.com/bizsignup/partner/entry';
    private const PPOB_SCRIPT    = 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';
    private const PPOB_SB_SCRIPT = 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js';

    /**
     * @throws GuzzleException
     */
    private function getAdditionalMerchantInformations($merchantID, $token): void
    {
        $config      = $this->getConfig();
        $logger      = $this->getLogger();
        $workingMode = $config->getConfigValues()->getWorkingMode();

        if (
            !isset($merchantID, $token)
                || !empty($config->getPrefixedConfigItem('merchantEmail_' . $workingMode))
        ) {
            return;
        }

        $client = new PPCClient(PPCHelper::getEnvironment($config));
        try {
            $merchantInfoResponse = new ReferralsResponse(
                $client->send(new ReferralsRequest(
                    \base64_decode(MerchantCredentials::partnerID($workingMode)),
                    $token,
                    Endpoint::PARTNER_MERCHANTINFO . $merchantID
                ))
            );

            foreach (
                [
                    'PaymentPUI'  => ['PAYMENT_METHODS', 'PAY_UPON_INVOICE'],
                    'PaymentACDC' => ['PPCP_CUSTOM', 'CUSTOM_CARD_PROCESSING'],
                ] as $payment => $productName
            ) {
                $avail          = false;
                $limited        = false;
                $paymentProduct = $merchantInfoResponse->getProductByName($productName[0]);
                if ($paymentProduct !== null && \in_array($productName[1], $paymentProduct->getCapabilities(), true)) {
                    $product = $merchantInfoResponse->getCapabilityByName($productName[1]);
                    $avail   = $product !== null && $product->isActive();
                    $limited = $avail && $product->hasLimits();
                }
                $config->saveConfigItems([
                    $payment . 'Avail' => $avail ? '1' : '0',
                    $payment . 'Limit' => $limited ? '1' : '0',
                ]);
            }

            $config->saveConfigItems([
                'merchantEmail_' . $workingMode => $merchantInfoResponse->getTrackingId(),
            ]);
            $acdcAvail = (int)$config->getPrefixedConfigItem('PaymentACDCAvail', '0');
            if ($acdcAvail > 0) {
                $apm     = new APM($config);
                $enabled = $apm->getEnabled(false);
                $apm->setEnabled(\array_diff($enabled, [APM::CREDIT_CARD]));
            }
        } catch (Exception $e) {
            $logger->write(\LOGLEVEL_ERROR, $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function assignOnboarding(
        string $workingMode,
        array $partnerCredentials,
        string $locale,
        JTLSmarty $smarty
    ): void {
        $plugin            = $this->getPlugin();
        $logger            = $this->getLogger();
        $isSandbox         = $workingMode === ConfigValues::WORKING_MODE_SANDBOX;
        $ppcPartnerLogoUrl = $plugin->getPaths()->getFrontendURL() . self::JTL_LOGO_URL;
        $onboardingUrl     = $isSandbox ? self::PPOB_SB_URL : self::PPOB_URL;
        $scriptUrl         = $isSandbox ? self::PPOB_SB_SCRIPT : self::PPOB_SCRIPT;
        $ppcFeatures       = [
            'PAYMENT',
            'REFUND',
            'ACCESS_MERCHANT_INFORMATION',
            'DELAY_FUNDS_DISBURSEMENT',
            'PARTNER_FEE',
            'ADVANCED_TRANSACTIONS_SEARCH',
            'TRACKING_SHIPMENT_READWRITE',
            'VAULT',
        ];
        $ppcProducts       = ['payment_methods'];
        $ppcCapabilities   = ['PAY_UPON_INVOICE'];
        $onboardingUri     = $onboardingUrl . '?channelId=partner'
            . '&partnerId=' . \base64_decode($partnerCredentials['partnerID'])
            . '&showPermissions=true'
            . '&integrationType=FO'
            . '&features=' . \implode(',', $ppcFeatures)
            . '&product=ppcp'
            . '&secondaryProducts=' . \implode(',', $ppcProducts)
            . '&capabilities=' . \implode(',', $ppcCapabilities)
            . '&partnerClientId=' . \base64_decode($partnerCredentials['partnerClientID'])
            . '&partnerLogoUrl=' . \urlencode($ppcPartnerLogoUrl)
            . '&returnToPartnerUrl=' . \urlencode($partnerCredentials['fetchUrl']
                . '?nonce=' . $partnerCredentials['nonce']
                . '&kPlugin=' . $plugin->getID())
            . '&displayMode=minibrowser'
            . '&sellerNonce=' . $partnerCredentials['nonce']
            . '&country.x=' . (Shop::getCurAdminLangTag() === 'de-DE' ? 'DE' : 'US')
            . '&locale.x=' . $locale;

        $logger->write(\LOGLEVEL_DEBUG, 'Onboard with: ', \str_replace('&', "\n&", $onboardingUri));

        $smarty
            ->assign('partnerCredentials', $partnerCredentials)
            ->assign('cookieSettings', \session_get_cookie_params())
            ->assign('onboardingUri', $onboardingUri)
            ->assign('scriptUrl', $scriptUrl);
    }

    private function assignPayMethods(JTLSmarty $smarty): void
    {
        $plugin     = $this->getPlugin();
        $payMethods = $plugin->getPaymentMethods()->getMethods();
        $ppcMethods = [];
        foreach ($payMethods as $method) {
            $payment = Helper::getInstance($plugin)->getPaymentFromID($method->getMethodID());
            if ($payment !== null) {
                $payment->renderBackendInformation($smarty, $plugin);
                $ppcMethods[] = $payment;
            }
        }

        $smarty->assign('ppc_methods', $ppcMethods);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function render(JTLSmarty $smarty): void
    {
        $config           = $this->getConfig();
        $plugin           = $this->getPlugin();
        $logger           = $this->getLogger();
        $configValues     = $config->getConfigValues();
        $isAuthConfigured = $configValues->isAuthConfigured();
        $workingMode      = $configValues->getWorkingMode();

        try {
            $token = $isAuthConfigured ? Token::getInstance() : null;
        } catch (AuthorizationException) {
            $token = null;
            $this->getAlert()->addWarning(
                \__('Anmeldung bei PayPal fehlgeschlagen.'),
                'authFailed'
            );
        }
        if ($token !== null) {
            try {
                $merchantID = $configValues->getMerchantID($workingMode);
                $this->getAdditionalMerchantInformations($merchantID, $token->getToken());
            } catch (GuzzleException | Exception $e) {
                $logger->write(\LOGLEVEL_ERROR, $e->getMessage());
            }
        }

        $locale             = Helper::sanitizeLocale(Shop::Container()->getGetText()->getLanguage(), true);
        $partnerCredentials = [
            'partnerID'       => MerchantCredentials::partnerID($workingMode),
            'partnerClientID' => MerchantCredentials::partnerClientID($workingMode),
            'nonce'           => '',
            'fetchUrl'        => ''
        ];

        if ($isAuthConfigured === false) {
            $nonce = \bin2hex(\random_bytes(40));
            $configValues->setNonce($nonce, $workingMode);
            $partnerCredentials['nonce']    = $nonce;
            $partnerCredentials['fetchUrl'] = Onboarding::getOnboardingFetchURL($plugin);
            $this->assignOnboarding($workingMode, $partnerCredentials, $locale, $smarty);
        }
        $this->assignPayMethods($smarty);
        $smarty
            ->assign('locale', $locale)
            ->assign('baseUrl', $plugin->getPaths()->getBaseURL())
            ->assign('basePath', $plugin->getPaths()->getBasePath())
            ->assign('isAuthConfigured', $isAuthConfigured)
            ->assign('ppcMerchantID', $merchantID ?? '')
            ->assign('ppcToken', $token !== null ? $token->getToken() : '')
            ->assign('ppc_mode', $workingMode)
            ->assign('paymentAlertList', $this->getAlert()->getAlertlist()->filter(
                static function (Alert $item) {
                    return $item->getShowInAlertListTemplate() === false;
                }
            ));
    }
}
