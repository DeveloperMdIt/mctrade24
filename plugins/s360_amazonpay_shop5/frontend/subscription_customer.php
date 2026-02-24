<?php declare(strict_types=1);


/**
 * This page is shown when a customer looks at their own subscriptions
 */
use JTL\Shop;
use \Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use Plugin\s360_amazonpay_shop5\lib\Controllers\SubscriptionCustomerController;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;

Shop::set(JtlLinkHelper::PLUGIN_FRONTEND_LINK_TYPE, JtlLinkHelper::FRONTEND_FILE_SUBSCRIPTION_CUSTOMER);

$controller = new SubscriptionCustomerController(Plugin::getInstance());
$controller->handle();