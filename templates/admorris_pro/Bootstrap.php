<?php

namespace Template\admorris_pro;

use JTL\Events\Dispatcher;
use JTL\License\Struct\ExsLicense;
use JTL\Shop;
use JTL\Template\Bootstrapper;
use scc\Renderer;
use JTL\Template\XMLReader;
use Smarty;
use JTL\Plugin\Helper;
use JTL\Plugin\State;
use Plugin\admorris_pro\TemplateSettings;
use Plugin\admorris_pro\Utils\Icon;
use scc\ComponentRegistratorInterface;
use scc\RendererInterface;
use Template\admorris_pro\components\CustomComponentRegistrator;
use Template\admorris_pro\Utils\Html;


/**
 * Class Bootstrap
 * @package Template\NOVA
 */
class Bootstrap extends Bootstrapper
{
    /**
     * @var ComponentRegistratorInterface&CustomComponentRegistrator|null
     */
    protected ?ComponentRegistratorInterface $scc = null;

    /**
     * @var RendererInterface|null
     */
    protected ?RendererInterface $renderer = null;

    protected TemplateUtils $utils;

    /**
     * @inheritdoc
     */
    public function boot(): void
    {
        parent::boot();

        /**
         * Check if Admorris Pro Plugin is enabled
         * But don't show the error message if the shop is in maintenance mode, because safe mode might be enabled for
         * shop updates.
         */
        if (!$this->pluginEnabled() && (Shop::getSettings([CONF_GLOBAL])['global']['wartungsmodus_aktiviert'] === 'N' || Shop::isAdmin())) {
            $this->outputNoPluginError();
        }
        $this->utils = new TemplateUtils();
        $this->registerComponents();

        $dispatcher = Dispatcher::getInstance();
        $dispatcher->hookInto(\HOOK_SMARTY_INC, function () {
            $this->registerPlugins();
            $this->initVariables();
        });
        $dispatcher->hookInto(\HOOK_SMARTY_OUTPUTFILTER, function () {
            $this->insertIcons();
            $this->addFirstAndLastHeaderColClasses();
        }, 9);
    }

    protected function registerPlugins(): void
    {
        $smarty = $this->getSmarty();
        if ($smarty === null) {
            // this will never happen but it calms the IDE down
            return;
        }
        $plugins = new CustomPlugins($this->getDB(), $this->getCache(), $this->utils);

        if (isset($_GET['scc-demo']) && Shop::isAdmin()) {
            $smarty->display('demo.tpl');
            die();
        }

        $smarty->assign('templateCssPath', Shop::getURL().'/templates/admorris_pro/styles/admorris/');

        $func = Smarty::PLUGIN_FUNCTION;
        $mod  = Smarty::PLUGIN_MODIFIER;

        //NOVA Function Plugins
        $smarty->registerPlugin($func, 'gibPreisStringLocalizedSmarty', $plugins->getLocalizedPrice(...))
            ->registerPlugin($func, 'getBoxesByPosition', $plugins->getBoxesByPosition(...))
            ->registerPlugin($func, 'has_boxes', $plugins->hasBoxes(...))
            ->registerPlugin($func, 'imageTag', $plugins->getImgTag(...))
            ->registerPlugin($func, 'getCheckBoxForLocation', $plugins->getCheckBoxForLocation(...))
            ->registerPlugin($func, 'hasCheckBoxForLocation', $plugins->hasCheckBoxForLocation(...))
            ->registerPlugin($func, 'aaURLEncode', $plugins->aaURLEncode(...))
            ->registerPlugin($func, 'get_navigation', $plugins->getNavigation(...))
            ->registerPlugin($func, 'get_category_array', $plugins->getCategoryArray(...))
            ->registerPlugin($func, 'get_category_parents', $plugins->getCategoryParents(...))
            ->registerPlugin($func, 'prepare_image_details', $plugins->prepareImageDetails(...))
            ->registerPlugin($func, 'get_manufacturers', $plugins->getManufacturers(...))
            ->registerPlugin($func, 'get_cms_content', $plugins->getCMSContent(...))
            ->registerPlugin($func, 'get_static_route', $plugins->getStaticRoute(...))
            ->registerPlugin($func, 'hasOnlyListableVariations', $plugins->hasOnlyListableVariations(...))
            ->registerPlugin($func, 'get_product_list', $plugins->getProductList(...))
            ->registerPlugin($func, 'captchaMarkup', $plugins->captchaMarkup(...))
            ->registerPlugin($func, 'getStates', $plugins->getStates(...))
            ->registerPlugin($func, 'getDecimalLength', $plugins->getDecimalLength(...))
            ->registerPlugin($func, 'getUploaderLang', $plugins->getUploaderLang(...))
            ->registerPlugin($func, 'getCountry', $plugins->getCountry(...))
            ->registerPlugin($func, 'sanitizeTitle', $plugins->sanitizeTitle(...))
            ->registerPlugin($mod, 'seofy', $plugins->seofy(...))
            ->registerPlugin($mod, 'has_trans', $plugins->hasTranslation(...))
            ->registerPlugin($mod, 'trans', $plugins->getTranslation(...))
            ->registerPlugin($mod, 'transByISO', $plugins->getTranslationByISO(...))
            ->registerPlugin($mod, 'transById', $plugins->getTranslationById(...))
            ->registerPlugin($mod, 'formatForMicrodata', $plugins->formatForMicrodata(...));




        // add deprecated modifiers that were removed from smarty 4
        $phpFunctionPlugins = [
            'trim',
            'http_build_query',
            'strpos',
            'is_array',
            'strip_tags',
            'sprintf',
            'array_reverse',
            'intval',
            'max',
            'substr',
            'json_encode',
            'in_array',
            'htmlentities',
            'version_compare',
            'filter_var',
            'array_values',
            'implode'
        ];

        // check if the plugin is already registered because JTL Shop 5.4 also registers them
        foreach ($phpFunctionPlugins as $function) {
            if (!isset($smarty->registered_plugins[Smarty::PLUGIN_MODIFIER][$function])) {
                try {
                    $smarty->registerPlugin(Smarty::PLUGIN_MODIFIER, $function, $function);
                } catch (\Throwable $th) {
                    //throw $th;
                }
            }
        }

        $smarty->registerClass('admProInputType', \JTL\Filter\InputType::class);
        $smarty->registerClass('admProCoupon', \JTL\Checkout\Kupon::class);
        $smarty->registerClass('admProForm', \JTL\Helpers\Form::class);

        //Custom Admorris Function Plugins
        // $smarty
        //     ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'subcategories_columns_count', [
        //         $plugins,
        //         'subcategories_columns_count',
        //     ])
        //     ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'category_icon', [$plugins, 'category_icon'])
        //     ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'get_template_settings', [$plugins, 'get_template_settings'])
        //     ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'make_url_absolute', [$plugins, 'make_url_absolute'])
        //     ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'container_size', [$plugins, 'container_size'])
        //     ->registerPlugin(Smarty::PLUGIN_FUNCTION, 'is_small_container', [$plugins, 'is_small_container']);

        //Custom Admorris Block Plugins
        $smarty->registerPlugin(Smarty::PLUGIN_BLOCK, 'obfuscate', [$plugins, 'obfuscateEmail']);

        //Custom Admorris Modifier Plugins
        $smarty
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'nl2br_notHtml', [$plugins, 'nl2br_notHtml'])
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'template_exists', [$plugins, 'template_exists'])
            ->registerPlugin(Smarty::PLUGIN_MODIFIER, 'adm_trim_html', Html::trim(...));
    }

    protected function registerComponents()
    {
        // $this->renderer = new Renderer($smarty);
        $this->renderer = new Renderer($this->getSmarty());
        $this->scc = new CustomComponentRegistrator($this->renderer);
        $this->scc->setDefaultComponents();
        $this->scc->registerComponents();
    }

    protected function initVariables()
    {
        /**
         * Cachebusting with Child-Templates
         * Adding the parent template version to the version string of child templates used for cachebusting,
         * because child template versions normally aren't updated when an admorris pro update was installed.
         * */
        $version = Shop::Container()
            ->getTemplateService()
            ->getActiveTemplate()
            ->getVersion();

        $smarty = $this->getSmarty();

        $parentTemplateDir = $smarty->getTemplateVars('parentTemplateDir');
        $currentTemplateDir = 'templates/admorris_pro/';

        if (empty($parentTemplateDir)) {
            $smarty->assign('templateVersion', $version);
        } else {
            /* child template active */
            $reader = new XMLReader();
            $xml = $reader->getXML('templates/admorris_pro');
            $smarty->assign('templateVersion', "{$version}_{$xml->Version}");
        }

        /** TemplateDir */

        $shopURL = Shop::getURL();


        if (empty($parentTemplateDir)) {
            $smarty->assign('amTemplateDir', $currentTemplateDir);
            $smarty->assign('amTemplateDirFull', $shopURL . '/' . $currentTemplateDir);
        } else {
            $smarty->assign('amTemplateDir', $parentTemplateDir);
            $smarty->assign('amTemplateDirFull', $shopURL . '/' . $parentTemplateDir);
        }
        
        $templateSettings = Shop::get('admorris-custom-template-settings');
        if (empty($templateSettings)) {
            $templateSettings = Shop::Container()
                ->getDB()
                ->select('xplugin_admorris_pro_template_settings', 'id', 1);
        }
        Shop::set('admorrisProTemplateSettings', $templateSettings);

        /**
         * Favicon Helper
         */
        $faviconHelper = new Utils\Favicon($smarty);
        $smarty->assign('FaviconHelper', $faviconHelper);

        $smarty->assign('admPro', $this->utils);

        $headerLayout = new HeaderLayout();
        $smarty->assign('headerLayout', $headerLayout);
        Shop::set('admProHeaderLayout', $headerLayout);

    }


    /**
     * @inheritDoc
     */
    public function licenseExpired(ExsLicense $license): void {}

    /**
     * @inheritdoc
     */
    public function installed(): void
    {
        parent::installed();
    }

    /**
     * @inheritDoc
     */
    public function enabled(): void
    {
        parent::enabled();
    }

    /**
     * @inheritDoc
     */
    public function disabled(): void
    {
        parent::enabled();
    }

    /**
     * @inheritdoc
     */
    public function updated($oldVersion, $newVersion): void {}

    /**
     * @inheritdoc
     */
    public function uninstalled(bool $deleteData = true): void
    {
        parent::uninstalled($deleteData);
    }

    public function pluginEnabled()
    {
        $admPlugin = Helper::getPluginById('admorris_pro');
        if ($admPlugin && $admPlugin->getState() === State::ACTIVATED) {
            return true;
        }
        return false;
    }

    public function outputNoPluginError()
    {
        echo <<<HTML
            <style>
                .alert {
                    padding: 20px;
                    background-color: #f44336;
                    color: white;
                }
            </style>

            <div class="alert">
                <strong>Fehler: Das Admorris Pro Plugin ist nicht aktiv! Das Plugin wird f&uuml;r die Funktion des Templates ben&ouml;tigt</strong>
            </div>
        HTML;
    }

    private function insertIcons() {
        $plugin = Shop::get('oplugin_admorris_pro');
        $iconHelper = new Icon($plugin);
        pq('[data-toggle="collapse"]:not(.navbar-toggler, .no-caret, label)')->append($iconHelper->renderIcon('chevronDown', 'icon-content icon-content--center icon-content--toggle'));
    }

    private function addFirstAndLastHeaderColClasses()
    {
        // use phpQuery to add first and last classes to header columns
        // selecting the .header-row elements, going through each and finding the first and last .header-row__col
        pq('.header-row')->each(function($el) {
            $firstCol = pq($el)->find('.header-row__col:first');
            $lastCol = pq($el)->find('.header-row__col:last');

            $firstCol->addClass('header-row__col--first');
            $lastCol->addClass('header-row__col--last');
        });
    }
}
