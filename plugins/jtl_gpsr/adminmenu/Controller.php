<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr\adminmenu;

use JTL\DB\DbInterface;
use JTL\Helpers\Request;
use JTL\Plugin\Helper;
use JTL\Plugin\PluginInterface;
use JTL\Router\Router;
use JTL\Shop;

/**
 * Class Controller
 * @package Plugin\jtl_gpsr\adminmenu
 */
class Controller
{
    private PluginInterface $plugin;

    private DBInterface $db;

    private static self $instance;

    /**
     * Controller constructor
     * @param PluginInterface $plugin
     * @param DbInterface     $db
     */
    private function __construct(PluginInterface $plugin, DbInterface $db)
    {
        $this->plugin   = $plugin;
        $this->db       = $db;
        self::$instance = $this;
    }

    public static function getInstance(PluginInterface $plugin, ?DbInterface $db = null): self
    {
        return self::$instance ?? new self($plugin, $db ?? Shop::Container()->getDB());
    }

    private function redirect(array $params = [], string $file = 'plugin'): void
    {
        \header(
            'Location: ' . Shop::getURL() . '/' . \PFAD_ADMIN . $file . '?' . \implode('&', $params),
            true,
            302
        );

        exit();
    }

    private function redirectSelf(): void
    {
        $file   = \class_exists(Router::class) ? 'plugin/' : 'plugin.php?kPlugin=';
        $params = [
            'kPluginAdminMenu=' . Request::postInt('kPluginAdminMenu'),
        ];

        $this->redirect($params, $file . $this->plugin->getID());
    }

    private function saveConfig(array $data): self
    {
        $config = new Config($this->plugin, $this->db);
        foreach ($data as $key => $value) {
            $config->storeValue('gpsr_template_' . $key, $value);
        }

        return $this;
    }

    private function resetPresentationConfig(): self
    {
        $gpsrPresentation = [
            'manufacturer'      => '',
            'responsibleperson' => '',
        ];

        foreach ($gpsrPresentation as $key => $presentation) {
            $gpsrPresentation[$key] = \file_get_contents(
                $this->plugin->getPaths()->getAdminPath() . 'template/gpsr_template_' . $key . '.tpl'
            );
        }
        $this->saveConfig($gpsrPresentation);

        return $this;
    }

    private function resetAllConfig(): self
    {
        $config   = new Config($this->plugin, $this->db);
        $defaults = [
            'gpsr_productdetails_show'             => 'on',
            'gpsr_templatefile_productdetails'     => 'productdetails/attributes.tpl',
            'gpsr_templateblock_productdetails'    => 'productdetails-attributes',
            'gpsr_templateposition_productdetails' => 'prepend',
            'gpsr_productlist_show'                => '',
            'gpsr_templatefile_productlist'        => 'productlist/item_list.tpl',
            'gpsr_templateblock_productlist'       => 'productlist-item-list-buy-form',
            'gpsr_templateposition_productlist'    => 'prepend',
        ];
        foreach ($defaults as $key => $value) {
            $config->storeValue($key, $value);
        }
        $this->resetPresentationConfig();

        return $this;
    }

    private function resetCache(): self
    {
        $pluginID     = $this->plugin->getID();
        $loader       = Helper::getLoaderByPluginID($pluginID, $this->db);
        $this->plugin = $loader->init($pluginID, true);

        return $this;
    }

    public function run(string $task): void
    {
        if ($task === 'savePresentation') {
            $this->saveConfig([
                'manufacturer'      => Request::postVar('template_manufacturer'),
                'responsibleperson' => Request::postVar('template_responsibleperson'),
            ])->resetCache()
              ->redirectSelf();
        }

        if ($task === 'resetPresentation') {
            $this->resetPresentationConfig()
                 ->resetCache()
                 ->redirectSelf();
        }

        if ($task === 'resetAllSettings') {
            $this->resetAllConfig()
                 ->resetCache();
            (new Handler($this->plugin))->generateTemplateFiles(['plugin' => $this->plugin]);
            $this->redirectSelf();
        }

        if ($task === 'resetAllSettingsInstall') {
            $this->resetAllConfig()
                 ->resetCache();
            (new Handler($this->plugin))->generateTemplateFiles(['plugin' => $this->plugin]);
        }
    }
}
