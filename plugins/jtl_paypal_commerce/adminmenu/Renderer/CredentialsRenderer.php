<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Renderer;

use Exception;
use JTL\Alert\Alert;
use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\adminmenu\TabNotAvailException;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class CredentialsRenderer
 * @package Plugin\jtl_paypal_commerce\adminmenu\Renderer
 */
class CredentialsRenderer extends AbstractRenderer
{
    /**
     * @inheritDoc
     */
    public function render(JTLSmarty $smarty): void
    {
        $this->checkRendering();

        $config       = $this->getConfig();
        $configValues = $config->getConfigValues();
        try {
            $backendSettings = $config->mapBackendSettings(Settings::BACKEND_SETTINGS_SECTION_CREDENTIALS);
        } catch (Exception $e) {
            throw new TabNotAvailException($e->getMessage(), $e->getCode(), $e);
        }

        $workingMode = $configValues->getWorkingMode();
        if (
            Request::getVar('task') === 'welcome'
            && $configValues->isAuthConfigured()
            && Request::getVar('nonce') === $configValues->getNonce($workingMode)
        ) {
            $configValues->setNonce('', $workingMode);
            $this->getAlert()->addSuccess(
                __(
                    'Das Onboarding war erfolgreich.',
                    $configValues->getClientID($workingMode),
                    $configValues->getClientSecret($workingMode)
                ),
                'welcome',
                [
                    'linkText' => __('Onboarding erfolgreich'),
                    'fadeOut' => Alert::FADE_NEVER
                ]
            );
        }

        $backendSettings[Settings::BACKEND_SETTINGS_SECTION_CREDENTIALS]['heading'] = \__('PayPal-Account') . ' : '
            . $config->getPrefixedConfigItem('merchantEmail_' . $workingMode);

        $smarty
            ->assign('settingSections', $backendSettings);
    }
}
