<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Controller;

use DateInterval;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use JTL\Backend\AdminAccount;
use JTL\Backend\Permissions;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\TwoFA\BackendTwoFA;
use JTL\TwoFA\BackendUserData;
use Plugin\jtl_paypal_commerce\adminmenu\AdminMailer;
use Plugin\jtl_paypal_commerce\adminmenu\InvalidMerchantException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\AuthorizationException;
use Plugin\jtl_paypal_commerce\PPC\Authorization\CredentialCode;
use Plugin\jtl_paypal_commerce\PPC\Authorization\MerchantCredentials;
use Plugin\jtl_paypal_commerce\PPC\Authorization\Token;
use Plugin\jtl_paypal_commerce\PPC\Configuration;
use Plugin\jtl_paypal_commerce\PPC\ConfigValues;
use Plugin\jtl_paypal_commerce\PPC\HttpClient\PPCClient;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\MerchantIntegrationRequest;
use Plugin\jtl_paypal_commerce\PPC\Onboarding\MerchantIntegrationResponse;
use Plugin\jtl_paypal_commerce\PPC\PPCHelper;
use Plugin\jtl_paypal_commerce\PPC\Request\PPCRequestException;
use Plugin\jtl_paypal_commerce\PPC\Request\UnexpectedResponseException;
use Plugin\jtl_paypal_commerce\PPC\Webhook\Webhook;

/**
 * Class ResetCredentialsController
 * @package Plugin\jtl_paypal_commerce\adminmenu\Controller
 */
class ResetCredentialsController extends AbstractController
{
    /**
     * @return void
     * @throws AuthorizationException
     * @uses self::sendResetMail()
     * @uses self::doResetCredentials()
     */
    public function run(): void
    {
        $this->runSubTask(Request::postVar('subTask') ?? '');
        $this->redirectSelf();
    }

    /**
     * @throws AuthorizationException
     */
    public function runSubTask(string $subTask): void
    {
        $adminAccount = Shop::Container()->getAdminAccount();
        $permission   = $adminAccount->logged() &&
            ($adminAccount->permission(Permissions::PLUGIN_DETAIL_VIEW_ALL)
                || $adminAccount->permission(Permissions::PLUGIN_DETAIL_VIEW_ID . $this->getPlugin()->getID())
            );
        if (!$permission) {
            throw new AuthorizationException(__('Trennen des Paypal-Kontos nicht möglich.'));
        }

        if (method_exists($this, $subTask)) {
            $config      = $this->getConfig();
            $workingMode = $config->getConfigValues()->getWorkingMode();
            $this->$subTask($adminAccount, $config, $workingMode);
        }
    }

    /**
     * @throws AuthorizationException
     */
    private function doResetCredentials(AdminAccount $adminAccount, Configuration $config, string $workingMode): void
    {
        $resetCode   = $this->getCodeFromPost();
        $configItem  = 'resetCredentialsSingleCode_' . $workingMode;
        $resetSerial = $config->getPrefixedConfigItem($configItem);
        if ($resetSerial !== null) {
            $config->saveConfigItems([$configItem => '']);
            $codeIsValid = $this->validateResetCode(new CredentialCode($resetSerial), $resetCode);
        } else {
            $codeIsValid = $this->validate2FACode($adminAccount, $resetCode);
        }
        if ($codeIsValid) {
            $this->resetCredentials($config, $config->getConfigValues());
            $backendUser = BackendUserData::getByID($adminAccount->getID(), $this->getDB());
            $adminMailer = new AdminMailer($this->getDB());
            $shopMailer  = Shop::Container()->getMailer();
            $infoMail    = $adminMailer->prepareForAdmin(
                __('Der PayPal-Account wurde getrennt.'),
                __('Von %s wurde der PayPal-Account erfolgreich vom Shop getrennt.', $backendUser->getName())
            );
            $shopMailer->send($infoMail);
            $this->getAlertService()->addSuccess(
                __('Das PayPal-Konto wurde erfolgreich getrennt.'),
                'resetCredentialsSuccess'
            );

            return;
        }

        throw new AuthorizationException(__('Der angegebene Rücksetz-Code konnte nicht verifiziert werden!'));
    }

    /**
     * @throws InvalidMerchantException
     */
    private function sendResetMail(AdminAccount $adminAccount, Configuration $config, string $workingMode): void
    {
        try {
            $merchantEmail = $this->getMerchantEmail($config->getConfigValues());
        } catch (GuzzleException | Exception) {
            throw new InvalidMerchantException(
                __('Die primäre E-Mail-Adresse des verbundenen PayPal-Kontos kann nicht ermittelt werden.'),
            );
        }

        $backendUser    = BackendUserData::getByID($adminAccount->getID(), $this->getDB());
        $credentialCode = CredentialCode::create((new DateTime())->add(new DateInterval('PT30M')), $resetCode);
        $config->saveConfigItems(
            ['resetCredentialsSingleCode_' . $workingMode => (string)$credentialCode]
        );
        $adminMailer = new AdminMailer($this->getDB());
        $shopMailer  = Shop::Container()->getMailer();
        $resetMail   = $adminMailer->prepare(
            $merchantEmail,
            __('Ihr Rücksetz-Code'),
            __('Ihr Rücksetz-Code lautet: %s', $resetCode),
            defined('PPC_DEBUG') && \PPC_DEBUG === true && PPCHelper::getEnvironment()->isSandbox()
        );
        $shopMailer->send($resetMail);
        $infoMail = $adminMailer->prepareForAdmin(
            __('Es wurde ein Rücksetz-Code angefordert.'),
            __('Von %s wurde ein Rücksetz-Code für den PayPal-Account angefordert.', $backendUser->getName())
        );
        $shopMailer->send($infoMail);
        $this->getAlertService()->addInfo(
            __('Eine EMail mit dem Rücksetz-Code wurde an %s versendet.', Text::htmlentitiesOnce($merchantEmail)),
            'resetMailSent'
        );
    }

    private function getCodeFromPost(): string
    {
        $resetCode = '';
        $codeArr   = Request::postVar('resetCode');
        if (!is_array($codeArr)) {
            return $resetCode;
        }

        foreach ($codeArr as $codePart) {
            $resetCode .= (int)$codePart;
        }

        return $resetCode;
    }

    /**
     * @throws AuthorizationException
     */
    private function validateResetCode(CredentialCode $credentialCode, string $resetCode): bool
    {
        if ($credentialCode->isExpired()) {
            throw new AuthorizationException(__('Der Rücksetz-Code ist abgelaufen oder ungültig.'));
        }

        return $credentialCode->verifyCode($resetCode);
    }

    private function validate2FACode(AdminAccount $adminAccount, string $code): bool
    {
        $db       = $this->getDB();
        $userData = BackendUserData::getByID($adminAccount->getID(), $db);

        return (new BackendTwoFA($db, $userData))->isCodeValid($code);
    }

    /**
     * @param ConfigValues $configValues
     * @return MerchantIntegrationResponse
     * @throws AuthorizationException
     * @throws GuzzleException
     * @throws PPCRequestException
     */
    private function getMerchantData(ConfigValues $configValues): MerchantIntegrationResponse
    {
        $workingMode = $configValues->getWorkingMode();
        $client      = new PPCClient(PPCHelper::getEnvironment($this->getConfig()));
        $merchantID  = $configValues->getMerchantID($workingMode);
        $partnerID   = \base64_decode(MerchantCredentials::partnerID($workingMode));

        return new MerchantIntegrationResponse($client->send(new MerchantIntegrationRequest(
            Token::getInstance()->getToken(),
            $partnerID,
            $merchantID
        )));
    }

    /**
     * @throws UnexpectedResponseException
     * @throws GuzzleException
     * @throws AuthorizationException
     * @throws PPCRequestException
     */
    private function getMerchantEmail(ConfigValues $configValues): string
    {
        $merchantData = $this->getMerchantData($configValues);
        $merchantMail = $merchantData->isPrimaryEmailConfirmed() ? $merchantData->getPrimaryEmail() : '';
        if ($merchantMail === '') {
            throw new InvalidArgumentException('Merchant email is empty or not confirmed');
        }
        $merchantName = $merchantData->getLegalName();

        return ($merchantName === '' ? $merchantMail : $merchantName) . ' <' . $merchantMail . '>';
    }

    private function resetCredentials(Configuration $config, ConfigValues $configValues): void
    {
        $workingMode = $configValues->getWorkingMode();
        $webhook     = new Webhook($this->getPlugin(), $config);
        try {
            $webhookId = $webhook->loadWebhook()->getId();
            if ($webhookId !== null) {
                $webhook->deleteWebhook($webhookId);
            }
        } catch (Exception) {
            // webhook not found - is allways deleted and can be ignored
        }
        $configValues->setClientID('', $workingMode);
        $configValues->setClientSecret('', $workingMode);
        $config->saveConfigItems(
            [
                'merchantEmail_' . $workingMode => '',
                'merchantID_' . $workingMode    => '',
                'PaymentPUIAvail'  => 0,
                'PaymentACDCAvail' => 0,
            ]
        );
        unset($_POST['kPluginAdminMenu']);

        $this->getLogger()->write(\LOGLEVEL_DEBUG, 'PayPal connection reset! (credentials removed)');
    }
}
