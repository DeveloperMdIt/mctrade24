<?php declare(strict_types = 1);

/**
 * This page is loaded when the user tries to pay with Amazon Pay.
 */
use \Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use JTL\Shop;
use \Plugin\s360_amazonpay_shop5\lib\Controllers\CheckoutController;
use \Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;

Shop::set(JtlLinkHelper::PLUGIN_FRONTEND_LINK_TYPE, JtlLinkHelper::FRONTEND_FILE_CHECKOUT);

$controller = new CheckoutController(Plugin::getInstance());
$controller->handle();