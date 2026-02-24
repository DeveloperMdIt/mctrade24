<?php declare(strict_types = 1);

use JTL\Shop;
use Plugin\s360_amazonpay_shop5\lib\Utils\JtlLinkHelper;
use Plugin\s360_amazonpay_shop5\lib\Controllers\IpnController;

Shop::set(JtlLinkHelper::PLUGIN_FRONTEND_LINK_TYPE, JtlLinkHelper::FRONTEND_FILE_IPN);

$controller = new IpnController();
$controller->handle();