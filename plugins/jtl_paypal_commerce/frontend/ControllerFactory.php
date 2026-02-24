<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\frontend;

use JTL\Router\Controller\CheckoutController;
use JTL\Router\Controller\OrderCompleteController;
use JTL\Shop;
use JTL\Shopsetting;
use Plugin\jtl_paypal_commerce\AlertService;

/**
 * Class FrontendHelper
 * @package Plugin\jtl_paypal_commerce\frontend
 */
class ControllerFactory
{
    private static ?CheckoutController $checkoutController = null;

    private static ?OrderCompleteController $orderCompleteController = null;

    public static function getCheckoutController(): CheckoutController
    {
        if (self::$checkoutController === null) {
            self::$checkoutController = new CheckoutController(
                Shop::Container()->getDB(),
                Shop::Container()->getCache(),
                Shop::getRouter()->getState(),
                Shopsetting::getInstance()->getAll(),
                AlertService::getInstance()
            );
        }
        self::$checkoutController->init();

        return self::$checkoutController;
    }

    public static function getOrderCompleteController(): OrderCompleteController
    {
        if (self::$orderCompleteController === null) {
            self::$orderCompleteController = new OrderCompleteController(
                Shop::Container()->getDB(),
                Shop::Container()->getCache(),
                Shop::getRouter()->getState(),
                Shopsetting::getInstance()->getAll(),
                AlertService::getInstance()
            );
        }
        self::$orderCompleteController->init();

        return self::$orderCompleteController;
    }
}
