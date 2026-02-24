<?php declare(strict_types = 1);

use Plugin\s360_amazonpay_shop5\lib\Controllers\LoginController;
use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Utils\Plugin;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;

Shop::set(JtlLinkHelper::PLUGIN_FRONTEND_LINK_TYPE, JtlLinkHelper::FRONTEND_FILE_LOGIN);

$controller = new LoginController(Plugin::getInstance());
$controller->handle();