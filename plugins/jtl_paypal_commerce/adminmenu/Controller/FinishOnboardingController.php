<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use GuzzleHttp\Exception\GuzzleException;
use JTL\Helpers\Request;
use PHPUnit\Framework\Exception;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\Endpoint;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\OAuthRequest;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\OAuthResponse;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\ReferralsRequest;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\ReferralsResponse;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Webhook\Webhook;
use Plugin\jtl_paypal_commerce\PPC\Webhook\WebhookException;

/**
 * Class FinishOnboardingController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class FinishOnboardingController extends AbstractController
{
    /**
     * @throws GuzzleException
     * @throws AuthorizationException
     * @throws PPCRequestException
     */
    private function retrieveClientCredentials($nonce, $sharedID, $authCode): void
    {
        $config       = $this->getConfig();
        $configValues = $config->getConfigValues();
        $workingMode  = $configValues->getWorkingMode();
        $logger       = $this->getLogger();

        if (!isset($nonce, $sharedID, $authCode)) {
            $logger->write(\LOGLEVEL_ERROR, 'Onboarding Request failed, missing parameters.');
            $this->redirectSelf();
        }

        $client = new PPCClient(PPCHelper::getEnvironment($config));
        $logger->write(\LOGLEVEL_DEBUG, 'Onboarding data retrieved.');

        try {
            $oAuthResponse     = new OAuthResponse(
                $client->send(new OAuthRequest($sharedID, $nonce, $authCode))
            );
            $referralsResponse = new ReferralsResponse(
                $client->send(new ReferralsRequest(
                    \base64_decode(MerchantCredentials::partnerID($workingMode)),
                    $oAuthResponse->getToken(),
                    Endpoint::PARTNER_CREDENTIALS
                ))
            );
            $clientId          = $referralsResponse->getClientId();
            $clientSecret      = $referralsResponse->getClientSecret();
            $merchantId        = $referralsResponse->getPayerId();

            if (!empty($clientId) && !empty($clientSecret)) {
                $configValues->setClientID($clientId, $workingMode);
                $configValues->setClientSecret($clientSecret, $workingMode);
                $config->saveConfigItems([
                    'merchantID_' . $workingMode => $merchantId,
                ]);

                $logger->write(\LOGLEVEL_DEBUG, 'Onboarding was successfull.');
            }
        } catch (Exception $e) {
            $logger->write(\LOGLEVEL_ERROR, $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     * @throws GuzzleException
     */
    public function run(): void
    {
        $config             = $this->getConfig();
        $configValues       = $config->getConfigValues();
        $logger             = $this->getLogger();
        $plugin             = $this->getPlugin();
        $nonce              = Request::getVar('nonce');
        $sharedID           = Request::getVar('sharedID');
        $authCode           = Request::getVar('authCode');
        $merchantIdInPayPal = Request::getVar('merchantIdInPayPal');
        $workingMode        = $configValues->getWorkingMode();
        $storedNonce        = $configValues->getNonce($workingMode);

        if ($storedNonce !== $nonce || empty($storedNonce)) {
            $logger->write(\LOGLEVEL_ERROR, 'Onboarding Request failed, wrong nonce.');
            $this->redirectSelf();
        }
        if (isset($sharedID, $authCode)) {
            $this->retrieveClientCredentials($nonce, $sharedID, $authCode);
        }
        if (!isset($merchantIdInPayPal)) {
            $this->redirectSelf();
        }

        $config->saveConfigItems([
            'merchantID_' . $workingMode => $merchantIdInPayPal,
        ]);

        try {
            $webhook = (new Webhook($plugin, $config))->createWebhook();
            if (empty($webhook)) {
                throw new WebhookException('Webhook::create returned no data!');
            }
            $config->setWebhookId($webhook->id);
            $config->setWebhookUrl($webhook->url);
        } catch (WebhookException $e) {
            $logger->write(\LOGLEVEL_ERROR, 'Webhook not created during onboarding: ' . $e);
        }

        $this->redirect([
            'kPluginAdminMenu=' . $config->getAdminmenuSettingsId('Zugangsdaten'),
            'task=welcome',
            'nonce=' . $storedNonce,
        ], 'plugin/' . $plugin->getID());
    }
}
