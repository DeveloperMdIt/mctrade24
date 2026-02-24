<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use JTL\Helpers\Request;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;

/**
 * Class SaveCredentialsManuallyController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class SaveCredentialsManuallyController extends AbstractController
{
    public function run(): void
    {
        $merchantID   = Request::postVar('ppcManualCredentials', [])['merchantID'] ?? null;
        $clientID     = Request::postVar('ppcManualCredentials', [])['clientID'] ?? null;
        $clientSecret = Request::postVar('ppcManualCredentials', [])['clientSecret'] ?? null;
        $config       = $this->getConfig();
        $configValues = $config->getConfigValues();
        $workingMode  = $configValues->getWorkingMode();

        if (!empty($clientID) && !empty($clientSecret)) {
            $config->saveConfigItems(['merchantID_' . $workingMode => $merchantID]);
            $configValues->setClientID($clientID, $workingMode);
            $configValues->setClientSecret($clientSecret, $workingMode);
            try {
                Token::inValidate();
                $environment = PPCHelper::getEnvironment($config);
                $environment->reInit($clientID, $clientSecret);
                Token::getInstance()->refresh();
            } catch (AuthorizationException $e) {
                $this->getAlertService()->addError(
                    \__('Anmeldung nicht möglich') . ': ' . \__($e->getMessage()),
                    'authFailed'
                );
                (new ResetCredentialsController(
                    $this->getPlugin(),
                    $this->getDB(),
                    $this->getConfig(),
                    $this->getLogger(),
                    $this->getAlertService()
                ))->run();
            }
        }

        $this->redirectSelf();
    }
}
