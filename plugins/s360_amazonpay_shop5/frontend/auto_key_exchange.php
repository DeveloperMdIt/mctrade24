<?php declare(strict_types=1);


use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Controllers\AutoKeyExchangeController;

Shop::set(JtlLinkHelper::PLUGIN_FRONTEND_LINK_TYPE, JtlLinkHelper::FRONTEND_FILE_AUTO_KEY_EXCHANGE);

$controller = new AutoKeyExchangeController();
$controller->handle();