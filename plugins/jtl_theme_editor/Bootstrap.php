<?php declare(strict_types=1);

namespace Plugin\jtl_theme_editor;

use JTL\Events\Dispatcher;
use JTL\Plugin\Bootstrapper;
use JTL\Shop;

/**
 * Class Bootstrap
 * @package Plugin\jtl_theme_editor
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @inheritdoc
     */
    public function boot(Dispatcher $dispatcher): void
    {
        parent::boot($dispatcher);
        $config              = Shop::getSettings([\CONF_TEMPLATE])['template'];
        $config['activated'] = ($config['demo']['demo_mode'] ?? 'N') === 'Y';
        $config['curtheme']  = $config['theme']['theme_default'] ?? null;
        if ($config['activated'] !== true || !Shop::isAdmin()) {
            return;
        }
        $dispatcher->listen('shop.hook.' . \HOOK_INDEX_NAVI_HEAD_POSTGET, function () {
            if (!isset($_POST['tpl_config_save_export'])) {
                return;
            }
            $adminHelper = new Helper($this->getPlugin());
            $result      = $adminHelper->handlePost($_POST);
            \header('Content-Type: application/json');
            die(\json_encode($result));
        });
        if ($config['curtheme'] !== null) {
            $dispatcher->listen('shop.hook.' . \HOOK_SMARTY_OUTPUTFILTER, function () use ($config) {
                $this->addHtml($config);
            });
        }
        $dispatcher->listen('shop.hook.' . \HOOK_LETZTERINCLUDE_CSS_JS, static function (array $args) {
            if (!isset($args['cCSS_arr'])) {
                return;
            }
            // in evo the copmiled css file is always called "bootstrap.css" - remove it
            foreach ($args['cCSS_arr'] as $_idx => $_file) {
                if (\strpos($_file, 'bootstrap.css') !== false) {
                    unset($args['cCSS_arr'][$_idx]);
                    break;
                }
            }
            Shop::Smarty()->assign('tpl_editor_active', true);
        });
    }

    /**
     * @param array $config
     * @throws \SmartyException
     */
    private function addHtml(array $config): void
    {
        $plugin   = $this->getPlugin();
        $smarty   = Shop::Smarty();
        $helper   = new Helper($plugin);
        $tplDir   = $smarty->getTemplateVars('currentTemplateDir');
        $lessFile = $tplDir . 'themes/' . $config['curtheme'] . '/less/theme.less';

        if ($smarty::$isChildTemplate === true && !\file_exists(PFAD_ROOT . $lessFile)) {
            $parentTemplateDir = $smarty->getTemplateVars('parentTemplateDir');
            if ($parentTemplateDir !== null) {
                $lessFile = $parentTemplateDir . 'themes/' . $config['curtheme'] . '/less/theme.less';
                $tplDir   = $parentTemplateDir;
            }
        }
        if (!\file_exists($lessFile)) {
            return;
        }
        $html = $smarty->assign('less_vars', $helper->getOptions($config['curtheme'], $tplDir))
            ->assign('shop_url', Shop::getURL())
            ->assign('is_admin', true)
            ->assign('template_dir', PFAD_ROOT . $tplDir)
            ->assign('theme', $config['curtheme'])
            ->assign('tpl_config_lang_vars', $plugin->getLocalization()->getTranslations())
            ->fetch($plugin->getPaths()->getFrontendPath() . 'template/tpl_configurator_frontend.tpl');

        \pq('body')->append('<link type="text/css" rel="stylesheet" href="' .
            $plugin->getPaths()->getFrontendURL() . 'css/tpl_configurator.css" />' . "\n" .
            '<link type="text/css" rel="stylesheet" href="' .
            $plugin->getPaths()->getFrontendURL() . 'css/bootstrap-colorpicker.min.css" />' . "\n" .
            '<link rel="stylesheet/less" type="text/css" href="' . Shop::getURL() . '/' . $lessFile . '" />' . "\n" .
            '<script type="text/javascript">
                    var less = {
                        env: "development",
                        async: true,
                        fileAsync: true,
                        dumpLineNumbers: "comments",
                        relativeUrls: true
                    };
                    jtl.load("' . $plugin->getPaths()->getFrontendURL() . 'js/bootstrap-colorpicker.min.js", "' .
            $plugin->getPaths()->getFrontendURL() . 'js/tpl_configurator.js", "' .
            $plugin->getPaths()->getFrontendURL() . 'js/less.min.js");
                </script>' . $html);
    }
}
