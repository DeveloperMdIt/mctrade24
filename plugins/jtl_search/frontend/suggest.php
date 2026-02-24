<?php

declare(strict_types=1);

use JTL\Helpers\Form;
use JTL\Helpers\Text;
use JTL\Plugin\Helper;
use JTL\Session\Frontend;
use JTL\Shop;
use Plugin\jtl_search\JtlSearch;
use Plugin\jtl_search\QueryValidator;

$query = trim($_POST['k'] ?? '');
if (mb_strlen($query) < 3) {
    exit;
}
require_once __DIR__ . '/../../../includes/globalinclude.php';
require_once PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
if (!Form::validateToken()) {
    exit;
}
$plugin    = Helper::getPluginById('jtl_search');
$validator = new QueryValidator($plugin);
if ($validator->validate($query) === false) {
    exit;
}
if (
    $plugin !== null
    && $plugin->getID() > 0
    && strlen($query) >= 3
    && strlen($plugin->getConfig()->getValue('cProjectId')) > 0
    && strlen($plugin->getConfig()->getValue('cAuthHash')) > 0
    && strlen($plugin->getConfig()->getValue('cServerUrl')) > 0
) {
    require_once $plugin->getPaths()->getBasePath() . 'includes/defines_inc.php';

    $templatePath     = $plugin->getPaths()->getFrontendPath() . PFAD_PLUGIN_TEMPLATE;
    $templateSettings = Shop::getSettings([CONF_TEMPLATE])['template'];
    $response         = null;
    $queries          = JtlSearch::doSuggest(
        Frontend::getCustomerGroup()->getID(),
        Shop::getLanguageCode(),
        Frontend::getCurrency()->getCode(),
        $query,
        $plugin->getConfig()->getValue('cProjectId'),
        $plugin->getConfig()->getValue('cAuthHash'),
        urldecode($plugin->getConfig()->getValue('cServerUrl')),
        $response
    );
    if (is_array($queries) && count($queries) > 0) {
        Shop::Smarty()->assign('cSearch', Text::filterXSS($query))
            ->assign('queries', $queries)
            ->assign('localization', $plugin->getLocalization())
            ->assign('cTemplatePath', $templatePath)
            ->assign('oSearchResponse', $response)
            ->assign('bBranding', $plugin->getConfig()->getValue('jtlsearch_branding') !== '0')
            ->assign('isNova', ($templateSettings['general']['is_nova'] ?? 'N') === 'Y')
            ->assign('noImagePath', Shop::getImageBaseURL() . BILD_KEIN_ARTIKELBILD_VORHANDEN)
            ->display($templatePath . 'results.tpl');
    }
}
