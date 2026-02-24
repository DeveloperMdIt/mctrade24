<?php declare(strict_types = 1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use JTL\Plugin\Helper;
use JTL\Plugin\PluginInterface;

/**
 * Class Plugin
 * Just a proxy to ensure that the plugin is used as singleton and not initialized x times during a Request.
 *
 * @package Plugin\s360_amazonpay_shop5\lib\Utils
 */
class Plugin {

    /** @var  PluginInterface */
    private static $instance;

    public static function getInstance() {
        if(null === self::$instance) {
            self::$instance = Helper::getPluginById(Constants::PLUGIN_ID);
        }
        return self::$instance;
    }

    public static function setInstance($plugin): void {
        if(null === self::$instance) {
            self::$instance = $plugin;
        }
    }


}