<?php declare(strict_types=1);


/**
 * This page is loaded on return paying on Amazon Pay
 */
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Controllers\CheckoutResultController;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;

Shop::set(JtlLinkHelper::PLUGIN_FRONTEND_LINK_TYPE, JtlLinkHelper::FRONTEND_FILE_CALLBACK_RESULT);

$controller = new CheckoutResultController();
$controller->handle();