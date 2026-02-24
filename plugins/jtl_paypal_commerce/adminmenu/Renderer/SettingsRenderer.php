<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu\Renderer;

use Exception;
use JTL\Helpers\Request;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\adminmenu\TabNotAvailException;
use Plugin\jtl_paypal_commerce\PPC\Settings;

/**
 * Class SettingsRenderer
 * @package Plugin\jtl_paypal_commerce\adminmenu\Renderer
 */
class SettingsRenderer extends AbstractRenderer
{
    /**
     * @inheritDoc
     */
    public function render(JTLSmarty $smarty): void
    {
        $this->checkRendering();

        $config = $this->getConfig();
        try {
            $smarty
                ->assign(
                    'settingsPanels',
                    $config->mapBackendSettings(null, [Settings::BACKEND_SETTINGS_SECTION_CREDENTIALS])
                )
                ->assign('clientID', $config->getConfigValues()->getClientID())
                ->assign('panelActive', Request::getInt('panelActive'))
                ->assign('configuration', $config);
        } catch (Exception $e) {
            throw new TabNotAvailException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
