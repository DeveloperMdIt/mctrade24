<?php declare(strict_types=1);


/**
 * This page is loaded on confirming the order IN THE REGULAR JTL CHECKOUT
 */
use \Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Controllers\AdditionalPaymentButtonRedirectController;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;

Shop::set(JtlLinkHelper::PLUGIN_FRONTEND_LINK_TYPE, JtlLinkHelper::FRONTEND_FILE_APB_REDIRECT);

$controller = new AdditionalPaymentButtonRedirectController(Plugin::getInstance());
$controller->handle();