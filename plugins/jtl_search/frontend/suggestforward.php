<?php

declare(strict_types=1);

use JTL\Plugin\Helper;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\jtl_search\JtlSearch;

require_once __DIR__ . '/../../../includes/globalinclude.php';

Frontend::getInstance();

$plugin = Helper::getPluginById('jtl_search');
$query  = trim($_POST['query']);

if (
    $plugin !== null
    && $plugin->getID() > 0
    && strlen($query) > 0
    && strlen($plugin->getConfig()->getValue('cProjectId')) > 0
    && strlen($plugin->getConfig()->getValue('cAuthHash')) > 0
    && strlen($plugin->getConfig()->getValue('cServerUrl')) > 0
) {
    require_once $plugin->getPaths()->getBasePath() . 'includes/defines_inc.php';

    echo JtlSearch::doSuggestForward(
        $query,
        Shop::getLanguageCode(),
        $plugin->getConfig()->getValue('cProjectId'),
        $plugin->getConfig()->getValue('cAuthHash'),
        urldecode($plugin->getConfig()->getValue('cServerUrl'))
    );
}
