<?php
/**
* Eigene Smarty-Funktionen mit Vererbung aus dem Vatertemplate
*
* @global \JTL\Smarty\JTLSmarty $smarty
*/

// namespace Template\admorris_pro;


use JTL\Shop;


// require_once PFAD_ROOT. 'plugins/admorris_pro/helper_functions.php';


// /** AdmorrisPro Settings */

// function getAdmorrisProSettings() {
//     Shop::Smarty()->assign('admorrisProSettings', Shop::get('admorrisPro-settings'));
// }





// $templateSettings = Shop::Container()->getDB()->select('xplugin_admorris_pro_template_settings', 'id', 1);
// Shop::set('admorrisProTemplateSettings', $templateSettings);

// $smarty->registerPlugin('function', 'subcategories_columns_count', 'subcategories_columns_count');
// $smarty->registerPlugin('function', 'category_icon', 'category_icon');
// $smarty->registerPlugin('function', 'get_template_settings', 'get_template_settings');

// $smarty->registerPlugin('function', 'make_url_absolute', 'make_url_absolute');
// $smarty->registerPlugin('function', 'container_size', 'container_size');
// $smarty->registerPlugin('function', 'is_small_container', 'is_small_container');
// /* Block function for wrapping scripts in loadjs.ready for loading jQuery async */
// $smarty->registerPlugin("block","jqueryReady", "jqueryReady");



// function adm_triggerDeprecationError($functionName, $moved = true) {
//     $msg = "{$functionName} is deprecated and should not be used anymore.";
//     if ($moved) {
//         $msg .= " Switch to using \$admPro->{$functionName} instead.";
//     }
//     trigger_error($msg, E_USER_DEPRECATED);
// }

// // calculate columns
// function subcategories_columns_count ($subcategories, $show_subcategories) {
//     adm_triggerDeprecationError(__FUNCTION__);
//     if (!is_array($subcategories)) {
//         return 0;
//     }
//     $divisor = 10;
//     $sub_category_count = count($subcategories);
//     $subsub_category_count = 0;
//     if($show_subcategories) {
//         $subsub_category_count = array_reduce($subcategories, function($carry, $item) {
//             return $carry + count($item->getChildren());
//         }, 0);
        

//     }
//     $count = ceil(($sub_category_count + $subsub_category_count)/$divisor);
//     $maxColumns = min(count($subcategories), 4);
//     return min($count, $maxColumns);
// }

// function category_icon ($params, $smarty) {
//     adm_triggerDeprecationError(__FUNCTION__);
//     if (empty($params['category'])) {
//         trigger_error("assign: missing 'category' parameter");
//         return;
//     }


//     if (!empty($params['category']->getFunctionalAttributes()['category_navbar_icon'])) {
//         $iconFileNames = explode(PHP_EOL, $params['category']->getFunctionalAttributes()['category_navbar_icon']);
//         $switchModifier = '';
//         $invertedDataAttribute = '';

//         $iconPath = $smarty->getTemplateVars('ShopURL') . '/bilder/kategorien/icons/';
//         if (count($iconFileNames) > 1) {
//             $switchModifier = ' megamenu__category-icon--switch';
//         }
//         if (isset($iconFileNames[1])) {
//             $invertedDataAttribute = " data-inverted-src='{$iconPath}{$iconFileNames[1]}'"; 
//         }
        
//         /* leaving the alt attribute empty, because the icons are always accompanied by text that explains what category they are for */
//         $icon = "<img class='megamenu__category-icon{$switchModifier} icon-content' src='{$iconPath}{$iconFileNames[0]}'{$invertedDataAttribute} alt=''>";
//         if (isset($iconFileNames[1])) {
//             $icon = $icon ."<img class='megamenu__category-icon megamenu__category-icon--inverted icon-content' src='{$iconPath}{$iconFileNames[1]}' alt=''>";
            
//         }
        
//         return $icon;
        

//     }
// }

// // used in cart dropdown because $Einstellungen smarty var not working when refreshed via AJAX
// /**
//  * Returns value from index or NULL if not found, whole template configuration if $index is empty.
//  * 
//  * Also looks to return a single value (non array) if $search_for_Array is set to false.
//  */
// function get_template_settings( $index = '', $search_for_Array = true ) {
//     adm_triggerDeprecationError(__FUNCTION__);

//     $template_settings = Shop::getSettings([CONF_TEMPLATE])['template'];

//     if( !$index )
//         return $template_settings;

//     if ($search_for_Array)
//         return $template_settings[$index];

//     $single_val;
//     array_walk_recursive($template_settings,
//         function($template_setting, $key, $index) use (&$single_val) {
//             if($key === $index){
//                 $single_val = $template_setting;
//                 return;
//             }
//         },
//         $index);

//     return $single_val;
// }

// /**
//  * Additional Smarty Variables
//  */




// function make_url_absolute ($url) {
//     adm_triggerDeprecationError(__FUNCTION__);
//     if (substr( $url, 0, 4 ) === 'http') {
//         return $url;
//     } elseif (substr( $url, 0, 1 ) === '/') {
//         return Shop::getURL() . $url;
//     } else {
//         return Shop::getURL() . '/' . $url;
//     }
// }

// function header_container_size()
// {
//     adm_triggerDeprecationError(__FUNCTION__);
//     // Default Sizes
//     $default_container_sizes = ['xs', 's', 'm', 'l', 'xl'];

//     // If something with database value wrong such as null, not exists or etc..., then default container size is container--xl
//     $default_size_for_error_handler = 'container--xl';

//     // Get Template Settings
//     $templateSettings = Shop::get('admorrisProTemplateSettings');

//     // default value to be return
//     $container = $default_size_for_error_handler;

//     // check if database has record for header container
//     if (isset($templateSettings->header_container) && in_array($templateSettings->header_container, $default_container_sizes))
//     {
//         // if has, assign value to $container
//         $container = "container--$templateSettings->header_container";
//     }

//     // before render, check layout if mobile or desktop
//     return checkIfMobileScreen() == 0 ? $container : '';
// }

// function container_size() {
//     adm_triggerDeprecationError(__FUNCTION__);
//     $templateSettings = Shop::get('admorrisProTemplateSettings');
//     $smarty = Shop::Smarty();
//     $pageType = Shop::getPageType();
//     $containerSize = $templateSettings->global_container;
//     $admorrisProSettings = Shop::get('admorrisPro-settings');




//     if ($pageType === PAGE_ARTIKEL) { // Artikeldetails
//         $detailContainer = $templateSettings->detail_container;
//         if ($detailContainer !== 'global') {
//             $containerSize = $detailContainer;
//         }
//     } elseif ($pageType === PAGE_ARTIKELLISTE) { // Artikelliste
//         $productlistContainer = $templateSettings->productlist_container;
//         if ($productlistContainer !== 'global') {
//             $containerSize = $productlistContainer;
//         }

//     } elseif ($pageType === PAGE_NEWSDETAIL) {
//         $containerSize = $admorrisProSettings['blog_container_width_details'];
//     } elseif ($pageType === PAGE_NEWS) {
//         $containerSize = $admorrisProSettings['blog_container_width_overview'];
//     } elseif (in_array($pageType, [9, 14, 10, 8 ])) { // Login, Passwort vergessen, Registrierung, Newsletter
//         $containerSize = 's';
//     } elseif (in_array($pageType, [PAGE_VERSAND, PAGE_AGB, PAGE_DATENSCHUTZ, PAGE_WRB])) { 
//         $containerSize = 'm';
//     }

//     return $containerSize;

//     // var_dump($globalContainer);
//     // $smarty->getTemplateVars('ShopURL')
// }

// function container_size_px() {
//     adm_triggerDeprecationError(__FUNCTION__);
//     switch (container_size()) {
//         case 'xs':
//             return '650';
//         case 's':
//             return '900';
//         case 'm':
//             return '1260';
//         case 'l':
//             return '1480';
//         case 'xl':
//             return '1680';
//     }
// }

// function product_gallery_sizes($thumbsColWidth) {
//     adm_triggerDeprecationError(__FUNCTION__);
//     $minWidth = container_size_px() + 100;
//     $width = container_size_px() / 2 - $thumbsColWidth - 10; /* -10px half gutter size */
//     $sizes = "(min-width: {$minWidth}px) {$width}px, ";
//     /** 
//      * -100px is the padding of 50px on both sides 
//      *  -20px gutter
//      * =============
//      * -120px
//      * */
//     if (container_size_px() > 1200) {
//         if ($thumbsColWidth !== 0) {
//             $sizes .= "(min-width: 1200px) calc((100vw - 120px) / 2 - {$thumbsColWidth}px), ";
//         } else {
//             $sizes .= "(min-width: 1200px) calc((100vw - 120px) / 2), ";
//         }
//     }
//     /** 
//      * -40px is the padding of 20px on both sides 
//      * */
//     if (container_size_px() > 991 && $thumbsColWidth !== 0) {
//         $sizes .= "(min-width: 991px) calc((100vw - 40px) / 2 - {$thumbsColWidth}px), ";
//     }
//     if (container_size_px() > 768) {
//         $sizes .= "(min-width: 768px) calc(100vw / 2 - 40px), ";
//     }
//     $sizes .= "calc(100vw - 40px)";

//     return $sizes;
    
// }


// function is_small_container() {

//     adm_triggerDeprecationError(__FUNCTION__);
    
//     return in_array(container_size(), ['xs', 's']);
// }


// /* deprecated */
// function mediafileImageSize($image) {
//     adm_triggerDeprecationError(__FUNCTION__);
//     $localDir = "mediafiles/";


//     return imageSize($localDir . $image);
// }


// function imageWidth($imagePath) {
//     adm_triggerDeprecationError(__FUNCTION__);
//     list($width, $height) = getimagesize(PFAD_ROOT. $imagePath);
//     return $width;
// }

// // function imageSizeFromSrcset($srcsetAttr) {
// //     $sizeSets = explode(',', $srcsetAttr);
// //     $sizeSets = array_reverse($sizeSets); // start with the largest image
// //     // dump($sizeSets);
    
// //     foreach ($sizeSets as $item) {
// //         // split the width off
// //         $imgUrl = (explode(' ', trim($item)))[0];
// //         $size = imageSize($imgUrl);
// //         if (!empty($size)) {
// //             return $size;
// //         }
// //     }
// // }


// function getCustomerOrders() {
//   adm_triggerDeprecationError(__FUNCTION__);
//   $kKunde = $_SESSION['Kunde']->kKunde;
//   static $orders = null;
//   if (!empty($kKunde)) {
//     if(is_null($orders)) {
//       $orders = Shop::Container()->getDB()->query('SELECT * FROM tbestellung WHERE kKunde = '. (int)$kKunde, 2);
//     }
//     return $orders;
//   }
// }


// function get_cms_link($name, $menu = 'megamenu')
// {
//     adm_triggerDeprecationError(__FUNCTION__, false);
//     $linkGroups = Shop::Container()->getLinkService()->getVisibleLinkGroups();
//     $linkGroup  = $linkGroups->getLinkgroupByTemplate($menu)->getLinks();

//     if ($linkGroup && !empty($linkGroups)) {
//         foreach ($linkGroup as $link) {
//             if ($link->getName() === $name) {
//                 return $link;
//             }
//         }
//     }

// }


// function setPaginationItemsPerPage($oPagination, $optionArray = [9, 18, 24]) {
//     adm_triggerDeprecationError(__FUNCTION__, false);
//     $oPagination->setItemsPerPageOptions($optionArray);


// }

// function getCategoryBannerImage() {
//     adm_triggerDeprecationError(__FUNCTION__);
//     $shopURL = Shop::getURL();
//     $AktuelleKategorie = Shop::Smarty()->getTemplateVars('AktuelleKategorie');
//     $imgName = $AktuelleKategorie->getCategoryFunctionAttribute('category_banner_image');
//     return "$shopURL/bilder/kategorien/banner/$imgName";
// }




// /**
//  * @deprecated 2.4.14
//  */
// function jqueryReady($params, $content, $smarty, &$repeat) {
//     if(/* !repeat && */ isset($content)) {
//         return $content;
//     }
// }

// // $smarty->registerPlugin("block","adm_loadScript", "adm_loadScript");

// /**
//  * @deprecated
//  * 
//  * Use type="module" on the script tag to defer the script execution
//  *
//  */
// function adm_loadScript($params, $content, $smarty, &$repeat) {
//     if(/* !repeat && */ isset($content)) {
//          return "loadjs.ready('template-scripts', function() {{$content}});";
//     }
// }


// function isHtml($string){
//     return $string != strip_tags($string) ? true:false;
// }

// // $smarty->registerPlugin('modifier', 'nl2br_notHtml', 'nl2br_notHtml');

// function nl2br_notHtml($string) {
//     return isHtml($string) ? $string : nl2br($string);
// }


// // $smarty->registerPlugin('function', 'getSocialmedia', 'getSocialmedia');

// function getSocialmedia() {
//     $smarty = Shop::Smarty();
//     $settings = $smarty->getTemplateVars('Einstellungen');
//     $footerSettings = $settings['template']['footer'];
//     $config = [
//         [
//             'name' => 'facebook',
//             'title' => 'Facebook',
//         ],
//         [
//             'name' => 'twitter',
//             'title' => 'Twitter',
//         ],
//         [
//             'name' => 'googleplus',
//             'title' => 'Google Plus',
//         ],
//         [
//             'name' => 'youtube',
//             'title' => 'YouTube',
//         ],
//         [
//             'name' => 'vimeo',
//             'title' => 'Vimeo',
//         ],
//         [
//             'name' => 'pinterest',
//             'title' => 'Pinterest',
//         ],
//         [
//             'name' => 'instagram',
//             'title' => 'Instagram',
//         ],
//         [
//             'name' => 'skype',
//             'title' => 'Skype',
//         ],
//         [
//             'name' => 'xing',
//             'title' => 'Xing',
//         ],
//         [
//             'name' => 'linkedin',
//             'title' => 'Linkedin',
//         ],
//         [
//             'name' => 'tiktok',
//             'title' => 'TikTok',
//         ], 
//         [
//             'name' => 'twitch',
//             'title' => 'Twitch',
//         ],     
//     ];

//     $socialList =  array_map(function($item) use ($footerSettings) {
//         if (isset($footerSettings[$item['name']])) {
//             $item['link'] = addHttpToLink($footerSettings[$item['name']]);
//         }
//         return $item;
//     }, $config);

//     $socialList = array_filter($socialList, function($item) {
//         if (!empty($item['link'])) {
//             return $item;
//         }
//     });

//     return $socialList;
// }


// function addHttpToLink($url) {
//     if (!empty($url)) {
//         return strpos($url, 'http') === 0 ? $url : 'https://'.$url;
//     }
// }



// // $smarty->registerPlugin('modifier', 'template_exists', 'template_exists');


// function template_exists($string)
// {
//     global $smarty;
//     return $smarty->template_exists($string);
// }





// function createBreadcrumbItemref() {
//     $pageType = Shop::getPageType();
//     if (in_array($pageType, [PAGE_ARTIKEL, PAGE_ARTIKELLISTE, PAGE_NEWS ])) {
//         return true;
//     }

// }


// function buildIconsObj($value) {
//     if ($value->pathOrSvg[0] == "/") {
//         $value = file_get_contents(PFAD_ROOT . $value->pathOrSvg);
//     }
//     $obj = new StdClass();
//     $obj->svg = $value->pathOrSvg;
//     $obj->iconFamily = $value->iconFamily;
//     return $obj;
// }

// function filterIcons($key) {
//     return in_array($key, ['info', 'chevronLeft', 'chevronRight', 'chevronUp', 'chevronDown', 'warning', 'search', 'cross']);
// }

// function getIcons($admIconsUsed) {
//     $icon = null;
//     $iconsUsed = array_filter($admIconsUsed, 'filterIcons', ARRAY_FILTER_USE_KEY);
//     $iconsUsed = array_map('buildIconsObj', $iconsUsed);
    
//     return json_encode($iconsUsed);
// }

// // $smarty->registerPlugin("block","obfuscate", "obfuscateEmail");

// function obfuscateEmail($params, $content, $smarty, &$repeat) {
//     if(isset($content)) {
//         $encoded = json_encode(str_rot13($content));
//         $id = 'A' . base64_encode(random_bytes(10));
//         $script = '<span id="'.$id.'"><script>document.getElementById("'.$id.'").parentNode.innerHTML='.$encoded.'.replace(/[a-zA-Z]/g,function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);});</script></span>';
//         return $script;
//     }
// }




// function checkIfAmSliderHasVideo ($amSlides, $videoPlatform) {
//     $sliderIds = [];
//     $hasVideo = false;
//     foreach($amSlides as $slide) {
//         if($slide->videoType === $videoPlatform && !empty($slide->video)) {
//             $hasVideo = true;
//             break;
//         }
//     }
//     return json_encode($hasVideo);
// } 


// function checkIfMobileScreen () {
//     $isMobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);

//     return $isMobile;
// }

// function handleSlideLink ($link) {
//     $finalLink = Shop::getURL() . $link;
//     if (filter_var($link, FILTER_VALIDATE_URL)) {
//         $finalLink = str_replace(Shop::getURL(), "", $link);
//     }
//     return $finalLink;
// }

// function openSlideLinkInNewTab ($link) {
//     return !str_contains($link, Shop::getURL());
// }

// function sortFilterOptionsByCount($filter) {
//     $copy = $filter;
//     $options = $copy->getOptions();

//     // sort filter options by count desc, then name asc
//     usort($options, function($a, $b) {
//         if ($a->getCount() === $b->getCount()) {
//             return strcasecmp($a->getName(), $b->getName());
//         }
//         return $b->getCount() <=> $a->getCount();
//     });

//     $filter->setOptions($options);
//     return $filter;
// }

// function handleEasyZoom($setting) {
//     global $smarty;
//     $useEasyZoom = filter_var($smarty->getTemplateVars('loadEasyzoom'), FILTER_VALIDATE_BOOLEAN);

//     if($useEasyZoom) {
//         return json_encode($useEasyZoom);
//     }

//     return json_encode(filter_var($setting, FILTER_VALIDATE_BOOLEAN));
// }

// /**
//  * Takes the $Artikel->cVariationKombi string and converts it to an array
//  * 
//  * The string looks like this: 104_252;105_255
//  *
//  * @param string $string
//  * @return array
//  */
// function varikombiStringToArray($string): array 
// {
//     $variations = explode(';', $string);
//     $variations = array_map(function($variation) {
//         return explode('_', $variation);
//     }, $variations);
//     return $variations;
// }

// function getMinValueArticle($Artikel) {
//     if ($Artikel->fAbnahmeintervall > 0) {
//         if ($Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall) {
//             $min = $Artikel->fMindestbestellmenge;
//         } else {
//             $min = $Artikel->fAbnahmeintervall;
//         }
//     } else {
//         if ($Artikel->fMindestbestellmenge > 0) {
//             $min = $Artikel->fMindestbestellmenge;
//         } else {
//             $min = 0;
//         }
//     }

//     return $min;
// }


// /**
//  * Takes a category list and replaces the category paths with the seo url if available
//  *
//  * @param array $categoryTree
//  * @return void
//  */
// function changeCategoriePaths($categoryTree): void {
//     foreach ($categoryTree as $category) {
//         $seoUrlAttr = $category->getAttribute('category_seo_url');
//         if ($seoUrlAttr != null) {
//             $category->setURL($seoUrlAttr->cWert);
//         }

//         if ($category->hasChildren()) {
//             changeCategoriePaths($category->getChildren());
//         }
//     }
// }


// /**
//  * Fills the article object with variations
//  *
//  * @param Artikel $Artikel
//  * @return void
//  */
// function getArticleVariations($Artikel) {
//     $options = new stdClass();
//     $options->nVariationen = 1;
//     $Artikel->fuelleArtikel($Artikel->kArtikel, $options);
// }