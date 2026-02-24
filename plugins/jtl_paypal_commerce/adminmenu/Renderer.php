<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu;

use Exception;
use JTL\Plugin\PluginInterface;
use JTL\Smarty\JTLSmarty;
use Plugin\jtl_paypal_commerce\adminmenu\Renderer\InfosRenderer;
use Plugin\jtl_paypal_commerce\adminmenu\Renderer\RendererInterface;

/**
 * Class Renderer
 * @package Plugin\jtl_paypal_commerce\adminmenu
 */
final class Renderer
{
    public const TAB_MAPPINGS = [
        'Infos'                 => 'infos',
        'Zugangsdaten'          => 'credentials',
        'Einstellungen'         => 'settings',
        'Versandinformationen'  => 'shipmentState',
        'Webhook'               => 'webhook',
        'Offene Bestellungen'   => 'pendingOrders'
    ];

    /** @var PluginInterface */
    private PluginInterface $plugin;

    /** @var int */
    private int $menuID;

    /** @var JTLSmarty */
    private JTLSmarty $smarty;

    /**
     * Renderer constructor.
     * @param PluginInterface $plugin
     * @param int             $menuID
     * @param JTLSmarty       $smarty
     */
    public function __construct(PluginInterface $plugin, int $menuID, JTLSmarty $smarty)
    {
        $this->plugin = $plugin;
        $this->menuID = $menuID;
        $this->smarty = $smarty;
    }

    /**
     * @throws TabNotAvailException
     * @uses InfosRenderer
     * @uses CredentialsRenderer
     * @uses WebhookRenderer
     * @uses SettingsRenderer
     * @uses PendingOrdersRenderer
     * @uses ShipmentStateRenderer
     */
    public function render(string $tabName): string
    {
        $className = 'Plugin\\jtl_paypal_commerce\\adminmenu\Renderer\\' . \ucfirst($tabName) . 'Renderer';
        try {
            if (\class_exists($className) && is_a($className, RendererInterface::class, true)) {
                $renderer = new $className($this->plugin);
                $renderer->render($this->smarty);
            }

            return $this->smarty
                ->assign('kPlugin', $this->plugin->getID())
                ->assign('kPluginAdminMenu', $this->menuID)
                ->fetch($this->plugin->getPaths()->getAdminPath() . '/template/' . $tabName . '.tpl');
        } catch (TabNotAvailException $e) {
            throw $e;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
