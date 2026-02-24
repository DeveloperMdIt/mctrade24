<?php

// Load Shop Basis and setup autoloader
require_once __DIR__ . '/../../../includes/globalinclude.php';

use JTL\Plugin\Helper;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\s360_clerk_shop5\src\Controllers\FeedController;
use Plugin\s360_clerk_shop5\src\Utils\Config;
use Plugin\s360_clerk_shop5\src\Utils\Logger;

try {
    Frontend::getInstance();
    $controller = new FeedController(
        Helper::getPluginById(Config::PLUGIN_ID),
        Shop::Smarty(),
        Shop::Container()->getAlertService()
    );

    $controller->handle();
} catch (Throwable $err) {
    Logger::error(
        'Error in feed.php: ' . $err->getMessage() . ' on line ' . $err->getLine() . ' in file ' . $err->getFile()
    );
}
