<?php

namespace Template\admorris_pro;

use JTL\Shop;
use Monolog\Logger;

class TemplateUtils
{
    protected Logger $log;

    public function initLogger()
    {
      $this->log = Shop::Container()->getLogService();
    }

    public function __construct()
    {
        $this->initLogger();
    }
    
    public static function initHeaderLayout()
    {
        return new HeaderLayout();
    }

    // calculate columns
    public function subcategories_columns_count($subcategories, $show_subcategories)
    {
        if (!is_array($subcategories)) {
            return 0;
        }
        $divisor = 10;
        $sub_category_count = count($subcategories);
        $subsub_category_count = 0;
        if ($show_subcategories) {
            $subsub_category_count = array_reduce(
                $subcategories,
                function ($carry, $item) {
                    return $carry + count($item->getChildren());
                },
                0,
            );
        }
        $count = ceil(($sub_category_count + $subsub_category_count) / $divisor);
        $maxColumns = min(count($subcategories), 4);
        return min($count, $maxColumns);
    }

    public function categoryIcon(\JTL\Catalog\Category\MenuItem $category)
    {
        $functionalAttributes = $category->getFunctionalAttributes();
        if (empty($functionalAttributes['category_navbar_icon'])) {
            return;
        }

        $iconFileNames = explode(PHP_EOL, $functionalAttributes['category_navbar_icon']);
        $switchModifier = '';
        $invertedDataAttribute = '';

        $iconPath = Shop::getURL() . '/bilder/kategorien/icons/';
        if (count($iconFileNames) > 1) {
            $switchModifier = ' megamenu__category-icon--switch';
        }
        if (isset($iconFileNames[1])) {
            $invertedDataAttribute = " data-inverted-src='{$iconPath}{$iconFileNames[1]}'";
        }

        /* leaving the alt attribute empty, because the icons are always accompanied by text that explains what category they are for */
        $icon = "<img class='megamenu__category-icon{$switchModifier} icon-content' src='{$iconPath}{$iconFileNames[0]}'{$invertedDataAttribute} alt=''>";
        if (isset($iconFileNames[1])) {
            $icon =
                $icon .
                "<img class='megamenu__category-icon megamenu__category-icon--inverted icon-content' src='{$iconPath}{$iconFileNames[1]}' alt=''>";
        }

        return $icon;
    }

    /**
     * Load the template Settings
     *
     * Used in cart dropdown because $Einstellungen smarty var not working when refreshed via AJAX
     * Returns value from index or NULL if not found, whole template configuration if $index is empty.
     * Also looks to return a single value (non array) if $search_for_Array is set to false.
     */
    public function get_template_settings($index = '', $search_for_Array = true)
    {
        $template_settings = Shop::getSettings([CONF_TEMPLATE])['template'];

        if (!$index) {
            return $template_settings;
        }

        if ($search_for_Array) {
            return $template_settings[$index];
        }

        $single_val = null;
        array_walk_recursive(
            $template_settings,
            function ($template_setting, $key, $index) use (&$single_val) {
                if ($key === $index) {
                    $single_val = $template_setting;
                    return;
                }
            },
            $index,
        );

        return $single_val;
    }

    public function make_url_absolute($url)
    {
        if (substr($url, 0, 4) === 'http') {
            return $url;
        } elseif (substr($url, 0, 1) === '/') {
            return Shop::getURL() . $url;
        } else {
            return Shop::getURL() . '/' . $url;
        }
    }

    public function header_container_size()
    {
        // Default Sizes
        $default_container_sizes = ['xs', 's', 'm', 'l', 'xl', 'fullwidth'];

        // If something with database value wrong such as null, not exists or etc..., then default container size is container--xl
        $default_size_for_error_handler = 'container--xl';

        // Get Template Settings
        $templateSettings = Shop::get('admorrisProTemplateSettings');

        // default value to be return
        $container = $default_size_for_error_handler;

        // check if database has record for header container
        if (
            isset($templateSettings->header_container) &&
            in_array($templateSettings->header_container, $default_container_sizes)
        ) {
            // if has, assign value to $container
            $container = "container--$templateSettings->header_container";
        }
        else if (isset($templateSettings->header_container) && $templateSettings->header_container === 'Global') {
            $container = "container--$templateSettings->global_container";
        }

        // before render, check layout if mobile or desktop
        return $this->checkIfMobileScreen() == 0 ? $container : '';
    }

    /**
     * TODO: Replace with package used by JTL
     *
     * @return bool
     */
    public function checkIfMobileScreen()
    {
        $isMobile = preg_match(
            '/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i',
            $_SERVER['HTTP_USER_AGENT'],
        );

        return $isMobile;
    }

    public function container_size()
    {
        $templateSettings = Shop::get('admorrisProTemplateSettings');
        $smarty = Shop::Smarty();
        $pageType = Shop::getPageType();
        $containerSize = $templateSettings->global_container;
        $admorrisProSettings = Shop::get('admorrisPro-settings');

        $containerSize = match ($pageType) {
            \PAGE_ARTIKEL => $templateSettings->detail_container !== 'Global' ? $templateSettings->detail_container : $containerSize,
            \PAGE_ARTIKELLISTE => $templateSettings->productlist_container !== 'Global' ? $templateSettings->productlist_container : $containerSize,
            \PAGE_NEWSDETAIL => $admorrisProSettings['blog_container_width_details'] !== 'Global' ? $admorrisProSettings['blog_container_width_details'] : $containerSize,
            \PAGE_NEWS => $admorrisProSettings['blog_container_width_overview'] !== 'Global' ? $admorrisProSettings['blog_container_width_overview'] : $containerSize,
            \PAGE_LOGIN, \PAGE_PASSWORTVERGESSEN, \PAGE_REGISTRIERUNG, \PAGE_NEWSLETTER => 's',
            \PAGE_VERSAND, \PAGE_AGB, \PAGE_DATENSCHUTZ, \PAGE_WRB => 'm',
            \PAGE_WARENKORB => 'm',
            \PAGE_BESTELLVORGANG => 'l',
            default => $containerSize,
        };

        return $containerSize;

        // var_dump($globalContainer);
        // $smarty->getTemplateVars('ShopURL')
    }

    public function container_size_footer() {
        $templateSettings = Shop::get('admorrisProTemplateSettings');
        $containerSize = $templateSettings->footerContainerSize !== 'Global' ? $templateSettings->footerContainerSize : $templateSettings->global_container;
        return $containerSize;
    }

    public function container_size_px()
    {
        switch ($this->container_size()) {
            case 'xs':
                return '650';
            case 's':
                return '900';
            case 'm':
                return '1260';
            case 'l':
                return '1480';
            case 'xl':
                return '1680';
        }
    }

    public function product_gallery_sizes($thumbsColWidth)
    {
        $containerWidth = $this->container_size_px();
        $minWidth = $containerWidth + 100;
        $width = $containerWidth / 2 - $thumbsColWidth - 10; /* -10px half gutter size */
        $sizes = "(min-width: {$minWidth}px) {$width}px, ";
        /**
         * 67
         * -100px is the padding of 50px on both sides
         *  -20px gutter
         * =============
         * -120px
         */
        if ($containerWidth > 1200) {
            if ($thumbsColWidth !== 0) {
                $sizes .= "(min-width: 1200px) calc((100vw - 120px) / 2 - {$thumbsColWidth}px), ";
            } else {
                $sizes .= '(min-width: 1200px) calc((100vw - 120px) / 2), ';
            }
        }

        /**
         * -40px is the padding of 20px on both sides
         */
        if ($containerWidth > 991 && $thumbsColWidth !== 0) {
            $sizes .= "(min-width: 991px) calc((100vw - 40px) / 2 - {$thumbsColWidth}px), ";
        }
        if ($containerWidth > 768) {
            $sizes .= '(min-width: 768px) calc(100vw / 2 - 40px), ';
        }
        $sizes .= 'calc(100vw - 40px)';

        return $sizes;
    }

    public function is_small_container()
    {
        return in_array($this->container_size(), ['xs', 's']);
    }

    public function getCustomerOrders()
    {
        $kKunde = $_SESSION['Kunde']->kKunde;
        static $orders = null;
        if (!empty($kKunde)) {
            if (is_null($orders)) {
                $orders = Shop::Container()
                    ->getDB()
                    ->query('SELECT * FROM tbestellung WHERE kKunde = ' . (int) $kKunde, 2);
            }
            return $orders;
        }
    }

    public function getCategoryBannerImage()
    {
        $shopURL = Shop::getURL();
        $AktuelleKategorie = Shop::Smarty()->getTemplateVars('AktuelleKategorie');
        $imgName = $AktuelleKategorie->getCategoryFunctionAttribute('category_banner_image');
        return "$shopURL/bilder/kategorien/banner/$imgName";
    }

    public function createTemplateSettingsArray()
    {
        $smarty = Shop::Smarty();
        $amTemplateDirFull = $smarty->getTemplateVars('amTemplateDirFull');
        $nTemplateVersion = $smarty->getTemplateVars('templateVersion');
        $admorris_pro_templateSettings = Shop::get('admorris-custom-template-settings');
        $iconsUsed = Shop::get('admIconsUsed');
        $categoryFunctionAttributes = $smarty->getTemplateVars('AktuelleKategorie')->getCategoryFunctionAttributes();
        $headerLayout = Shop::get('admProHeaderLayout');
        $cartDropdownSetting = $admorris_pro_templateSettings->miniCartDisplay;
        $proSlider = $smarty->getTemplateVars('amSlider');

        // Create the admorris_pro_template_settings object
        // set as global var admorris_pro_template_settings
        $admorris_pro_template_settings = [
            'templateDir' => $amTemplateDirFull,
            'paginationType' => (int) $admorris_pro_templateSettings->paginationType,
            'templateVersion' => $nTemplateVersion,
            'sliderItems' => (int) $admorris_pro_templateSettings->sliderItems ?? null,
            'loadParallaxScript' => !empty($categoryFunctionAttributes['category_banner_image']),
            'scrollToTopButton' => $admorris_pro_templateSettings->scroll_to_top_active ?? false,
            'fixedAddToBasketButton' => $admorris_pro_templateSettings->fixedAddToBasket ?? null,
            'hoverSecondImage' => $admorris_pro_templateSettings->hover_second_image,
            'easyzoom' => (bool) $this->handleEasyZoom($admorris_pro_templateSettings->productGalleryEasyZoom),
            'hoverProductlist' => Shop::getSettingValue(\CONF_TEMPLATE, 'productlist')['hover_productlist'] === 'Y',
            'productSliderPurchaseFunctions' => $admorris_pro_templateSettings->productSliderPurchaseFunctions,
            'iconFamily' => [
                'chevronRight' => $iconsUsed['chevronRight']->iconFamily,
                'chevronLeft' => $iconsUsed['chevronLeft']->iconFamily,
            ],
            'langVars' => [
                'close' => Shop::Lang()->get('close', 'consent'),
            ],
            'cartDropdown' => $cartDropdownSetting,
            'proSliderActive' => !empty($proSlider) && count($proSlider->slide_arr) > 0,
        ];

        return $admorris_pro_template_settings;
    }

    protected function handleEasyZoom($setting)
    {
        $smarty = Shop::Smarty();
        $useEasyZoom = filter_var($smarty->getTemplateVars('loadEasyzoom'), FILTER_VALIDATE_BOOLEAN);

        if ($useEasyZoom) {
            return $useEasyZoom;
        }

        return filter_var($setting, FILTER_VALIDATE_BOOLEAN);
    }

    public function getImageSize(string $path): object|null
    {
        $container = Shop::Container();
        $plugins = new Plugins($container->getDB(), $container->getCache(), $this);
        return $plugins->getImageSize($path);
    }

    /**
     * Takes the $Artikel->cVariationKombi string and converts it to an array
     *
     * The string looks like this: 104_252;105_255
     *
     * @param string $string
     * @return array
     */
    public function varikombiStringToArray($string): array
    {
        $variations = explode(';', $string);
        $variations = array_map(function ($variation) {
            return explode('_', $variation);
        }, $variations);
        return $variations;
    }

    public function getMinValueArticle($Artikel)
    {
        if ($Artikel->fAbnahmeintervall > 0) {
            if ($Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall) {
                $min = $Artikel->fMindestbestellmenge;
            } else {
                $min = $Artikel->fAbnahmeintervall;
            }
        } else {
            if ($Artikel->fMindestbestellmenge > 0) {
                $min = $Artikel->fMindestbestellmenge;
            } else {
                $min = 0;
            }
        }

        return $min;
    }

    /**
     * Takes a category list and replaces the category paths with the seo url if available
     *
     * @param array $categoryTree
     * @return void
     */
    public function changeCategoriePaths($categoryTree): void
    {
        foreach ($categoryTree as $category) {
            $seoUrlAttr = $category->getAttribute('category_seo_url');
            if ($seoUrlAttr != null) {
                $category->setURL($seoUrlAttr->cWert);
            }

            if ($category->hasChildren()) {
                $this->changeCategoriePaths($category->getChildren());
            }
        }
    }

    public function getSocialmedia()
    {
        $smarty = Shop::Smarty();
        $settings = $smarty->getTemplateVars('Einstellungen');
        $footerSettings = $settings['template']['footer'];
        $config = [
            [
                'name' => 'facebook',
                'title' => 'Facebook',
            ],
            [
                'name' => 'twitter',
                'title' => 'Twitter',
            ],
            [
                'name' => 'googleplus',
                'title' => 'Google Plus',
            ],
            [
                'name' => 'youtube',
                'title' => 'YouTube',
            ],
            [
                'name' => 'vimeo',
                'title' => 'Vimeo',
            ],
            [
                'name' => 'pinterest',
                'title' => 'Pinterest',
            ],
            [
                'name' => 'instagram',
                'title' => 'Instagram',
            ],
            [
                'name' => 'skype',
                'title' => 'Skype',
            ],
            [
                'name' => 'xing',
                'title' => 'Xing',
            ],
            [
                'name' => 'linkedin',
                'title' => 'Linkedin',
            ],
            [
                'name' => 'tiktok',
                'title' => 'TikTok',
            ],
            [
                'name' => 'twitch',
                'title' => 'Twitch',
            ],
        ];

        $socialList =  array_map(function ($item) use ($footerSettings) {
            if (isset($footerSettings[$item['name']])) {
                $item['link'] = $this->addHttpToLink($footerSettings[$item['name']]);
            }
            return $item;
        }, $config);

        $socialList = array_filter($socialList, function ($item) {
            if (!empty($item['link'])) {
                return $item;
            }
        });

        return $socialList;
    }

    private function addHttpToLink($url) {
    if (!empty($url)) {
        return strpos($url, 'http') === 0 ? $url : 'https://'.$url;
    }
}

    public function removeHiddenCategories($categories)
    {
        $shownCategories = $categories;

        foreach ($categories as $key => $category) {
            $fnAttr = $category->getFunctionalAttributes();

            if (!empty($fnAttr["category_hide"])) {
                unset($shownCategories[$key]);
            }
        }

        return $shownCategories;
    }

    public function webpBrowserSupport()
    {
        return isset($_SERVER['HTTP_ACCEPT']) && \strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }

    public function getWebpImage($imageUrl)
    {
        if (\JTL\Media\Image::hasWebPSupport() && $this->webpBrowserSupport()) {
            $imageUrl = \preg_replace('/\.(?i)(jpg|jpeg|png)/', '.webp', $imageUrl);
        }
        return $imageUrl;
    }

    /* CSS Minify
     * https://stackoverflow.com/questions/15195750/minify-compress-css-with-regex
     * https://stackoverflow.com/a/44350195/3637049
     */

    public function minify_css($string = '')
    {
        return \Plugin\admorris_pro\minifyCss($string);
    }

    public function getCssFileHash($fileName)
    {
        $file = @file_get_contents(\PFAD_ROOT . 'templates/admorris_pro/styles/admorris/manifest.json');

        if ($file === false) {
            // return template version if file not found
            return Shop::Smarty()->getTemplateVars('templateVersion');
        }
        // read the manifest.json file
        $manifest = json_decode($file, true);
        if (!isset($manifest[$fileName])) {
            $this->log->warning("File not found in manifest: $fileName");
            return Shop::Smarty()->getTemplateVars('templateVersion');
        }
        return $manifest[$fileName];
    }

    public function getJsFilenameFromManifest($fileName)
    {

        $manifestFilePath = \PFAD_ROOT . 'templates/admorris_pro/js/admorris/manifest.json';
        $file = @file_get_contents($manifestFilePath);

        if ($file === false) {
            // return template version if file not found
            throw new \Exception($manifestFilePath . ' not found');
        }
        // read the manifest.json file
        $manifest = json_decode($file, true);
        if (!isset($manifest[$fileName])) {
            throw new \Exception("File not found in manifest: $fileName");
        }
        return $manifest[$fileName];
    }

    public function setProductSliderDisplayAmount()
    {
        $amount = Shop::get('admorris-custom-template-settings')->sliderItems;
        $breakpoints = [
            'xs' => 2,
            'sm' => 3,
            'md' => 4,
            'lg' => 5,
            'xl' => 6,
        ];

        foreach ($breakpoints as $breakpoint => $value) {
            if ($amount < $value) {
                $breakpoints[$breakpoint] = $amount;
            }
        }

        return $breakpoints;
    }


    public function hasCategoryBanner() {
        $AktuelleKategorie = Shop::Smarty()->getTemplateVars('AktuelleKategorie');
        $hasBannerImage = !empty($AktuelleKategorie->getCategoryFunctionAttribute('category_banner_image'));
        $isArtikelListe = Shop::getPageType() === PAGE_ARTIKELLISTE;
        // $parallaxActive = Shop::Smarty()->getTemplateVars('Einstellungen')['template']['productlist']['banner_parallax'] === 'Y';
    
        return $isArtikelListe && $hasBannerImage;
    
    }

    public function getArticleVariations($Artikel) {
        $options = new \stdClass();
        $options->nVariationen = 1;
        $Artikel->fuelleArtikel($Artikel->kArtikel, $options);
    }

    public function sortFilterOptionsByCount($filter) {
        $copy = $filter;
        $options = $copy->getOptions();

        // sort filter options by count desc, then name asc
        usort($options, function($a, $b) {
            if ($a->getCount() === $b->getCount()) {
                return strcasecmp($a->getName(), $b->getName());
            }
            return $b->getCount() <=> $a->getCount();
        });

        $filter->setOptions($options);
        return $filter;
    }

    public function checkIfAmSliderHasVideo ($amSlides, $videoPlatform) {
        $sliderIds = [];
        $hasVideo = false;
        foreach($amSlides as $slide) {
            if($slide->videoType === $videoPlatform && !empty($slide->video)) {
                $hasVideo = true;
                break;
            }
        }
        return json_encode($hasVideo);
    }


    public function handleSlideLink ($link) {
        $finalLink = Shop::getURL() . $link;
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            $finalLink = str_replace(Shop::getURL(), "", $link);
        }
        return $finalLink;
    }

    public function openSlideLinkInNewTab ($link) {
        return !str_contains($link, Shop::getURL());
    }

    public function uniqid($prefix = '') {
        return uniqid($prefix, false);
    }

    public function getClass($class) {
        return get_class($class);
    }

    public function filterAlertsByShowInTemplate($alerts)
    {
        if (!($alerts instanceof \Illuminate\Support\Collection)) {
            $alerts = collect($alerts);
        }

        return $alerts->filter(function ($alert) {
            return $alert->getShowInAlertListTemplate();
        });
    }
}
