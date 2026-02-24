<?php

namespace Plugin\s360_amazonpay_shop5\lib\Utils;

use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use Smarty;
use SmartyException;

class SmartyHelper
{
    public static function registerSmartyFunctions($smarty = null): void {
        if (\version_compare(Smarty::SMARTY_VERSION, '4.5', '<')) {
            return;
        }
        $functions = ['date', 'stripcslashes', 'mb_strtolower', 'mb_stripos', 'html_entity_decode'];
        /** @var JTLSmarty $smarty */
        $smarty = $smarty ?? Shop::Smarty();
        foreach($functions as $function) {
            try {
                // try to register it - but try/catch since it may be registered by another plugin
                $smarty->registerPlugin(
                    Smarty::PLUGIN_MODIFIER,
                    $function,
                    '\\' . $function
                );
            } catch (SmartyException $ex) {
                // probably already registered
            }
        }
    }
}