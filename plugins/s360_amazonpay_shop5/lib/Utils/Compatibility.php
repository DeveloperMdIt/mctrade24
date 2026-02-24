<?php declare(strict_types=1);


namespace Plugin\s360_amazonpay_shop5\lib\Utils;

/**
 * Helper class to allow for shop version checks if they are necessary to call the correct functions or handle things differently.
 */
class Compatibility {
    public static function isShopAtLeast52() {
        return version_compare(\APPLICATION_VERSION, '5.2.0-beta', '>=');
    }

    public static function isShopAtLeast53() {
        return version_compare(\APPLICATION_VERSION, '5.3.0-rc.3', '>=');
    }

    public static function isShopAtLeast54() {
        return version_compare(\APPLICATION_VERSION, '5.4.0-rc.1', '>=');
    }

    public static function isShopAtLeast55() {
        return version_compare(\APPLICATION_VERSION, '5.5.0-rc.1', '>=');
    }
}