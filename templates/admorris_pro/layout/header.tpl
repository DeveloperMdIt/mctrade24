{*custom*}
{block name='layout-header'}
{$categoryFunctionAttributes = $AktuelleKategorie->getCategoryFunctionAttributes()}



{block name='layout-header-doctype'}<!DOCTYPE html>{/block}
<html {block name="html-attributes"}lang="{$meta_language}"{/block}>
{block name='layout-header-head'}
<head>
    {block name='layout-header-head-meta'}
        <meta http-equiv="content-type" content="text/html; charset={$smarty.const.JTL_CHARSET}">
        <meta http-equiv="Accept" content="image/webp,image/apng,image/*,*/*" />
        <meta name="description" content="{block name='layout-header-head-meta-description'}{$meta_description|truncate:1000:'':true}{/block}">
        {if !empty($meta_keywords)}
            <meta name="keywords" content="{block name='layout-header-head-meta-keywords'}{$meta_keywords|truncate:255:'':true}{/block}">
        {/if}
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">

        {$noindex = $bNoIndex === true  || (isset($Link) && $Link->getNoFollow() === true) || (!empty($AktuelleKategorie->getCategoryAttributeValue('category_seo_url')) && $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE)}
        <meta name="robots" content="{if $robotsContent}{$robotsContent}{elseif $noindex}noindex{else}index, follow{/if}">
        {$logoImage = $admorris_pro_templateSettings->logoInverted}
        {if empty($logoImage)}
            {$logoImage = "{$ShopURL}/{$ShopLogoURL}"}
        {/if}

        <meta property="og:type" content="website" />
        <meta property="og:site_name" content="{$meta_title}" />
        <meta property="og:title" content="{block name="head-title"}{$meta_title}{/block}" />
        <meta property="og:description" content="{block name="head-meta-description"}{$meta_description|truncate:1000:"":true}{/block}" />
        {if $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && !empty($Artikel->Bilder)}
            <meta property="og:image" content="{$Artikel->Bilder[0]->cURLGross}">
        {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
            && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild.gif'
            && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild_kl.gif'
        }
            <meta property="og:image" content="{$imageBaseURL}{$oNavigationsinfo->getImageURL()}" />
        {elseif $nSeitenTyp === $smarty.const.PAGE_NEWSDETAIL && isset($oNewsArchiv) && !empty($oNewsArchiv->getPreviewImage())}
            <meta property="og:image" content="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}" />
        {else}
            <meta property="og:image" content="{$ShopLogoURL}" />
        {/if}
        <meta property="og:url" content="{$cCanonicalURL}"/>
    {/block}

    <title>{block name='layout-header-head-title'}{$meta_title}{/block}</title>

    {if !empty($cCanonicalURL) && !$noindex}
        <link rel="canonical" href="{$cCanonicalURL}">
    {/if}


    {block name='layout-header-head-preload'}

        {* Fontawesome preloading *}
        {if isset($Einstellungen["template"]["general"]["use_font_awesome_4"]) && $Einstellungen["template"]["general"]["use_font_awesome_4"] === "Y" || \JTL\Shop::isAdmin(true)}
            <link rel="preload" href="{$amTemplateDirFull}fonts/fontawesome-webfont.woff2?v=4.7.0" as="font" type="font/woff2" crossorigin>
        {/if}
        {if isset($Einstellungen["template"]["general"]["use_font_awesome_5"]) && $Einstellungen["template"]["general"]["use_font_awesome_5"] === "Y" || \JTL\Shop::isAdmin(true)}
            <link rel="preload" href="{$amTemplateDirFull}fonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
            <link rel="preload" href="{$amTemplateDirFull}fonts/fa-brands-400.woff2" as="font" type="font/woff2" crossorigin>
        {/if}
        <link rel="preload" href="{$templateCssPath}styles.css?v={$admPro->getCssFileHash('styles.css')}" as="style">

        {if isset($admorris_pro_templateSettings) && $admorris_pro_templateSettings->fontFamilyBase === 'Inria Serif'}
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@300;700&display=swap">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@300;700&display=swap" media="print" onload="this.media='all'">
        {/if}

        {include file='slider/am_slider_header.tpl'}

        {* Preload first slider image if on homepage *}
        {if $nSeitenTyp === 1 && isset($amSlider) && count($amSlider->slide_arr) > 0}
            {$firstSlide = $amSlider->slide_arr[0]}
            <link rel="preload" as="image" href="{$firstSlide->cBildPfadFull}" fetchpriority="high">
        {/if}
        
        {* preload first image on productpage *}
        {if $nSeitenTyp === PAGE_ARTIKEL && !empty($Artikel->Bilder[0])}
            {$image = $Artikel->Bilder[0]}
            {$thumbsColWidth = (count($Artikel->Bilder) > 1) ? ($Einstellungen.bilder.bilder_artikel_mini_breite + 20) : 0}
            {$imgsrcset = "{$image->cURLKlein} {$image->imageSizes->sm->size->width}w, {$image->cURLNormal} {$image->imageSizes->md->size->width}w, {$image->cURLGross} {$image->imageSizes->lg->size->width}w"}
            {if \JTL\Media\Image::hasWebPSupport()}
                {$imgsrcset = $imgsrcset|regex_replace:"/\.(?i)(jpg|png)/":".webp"}
            {/if}
            <link rel="preload" as="image"  imagesrcset="{$imgsrcset}" imagesizes="{$admPro->product_gallery_sizes($thumbsColWidth)}" fetchpriority="high">
        {/if}

        {if $Einstellungen["template"]["general"]["progressive_loading"] === "Y" }
            <link rel="preload" as="script" href="{$amTemplateDirFull}js/lazysizes-blur.min.js?v=5.3.0" >
        {/if}
        
        <link rel="preload" as="script" href="{$amTemplateDirFull}js/lazysizes.min.js?v=5.3.0" >

        {if $nSeitenTyp === $smarty.const.PAGE_ARTIKEL}
            <link rel="modulepreload" href="{$amTemplateDirFull}js/admorris/{$admPro->getJsFilenameFromManifest('detailsGallery.js')}" as="script">
        {/if}

        <script data-name="admorris-script">{strip}
            {fetch file="{$amTemplateDir}js/loadjs.min.js"}
            {* deprecated *}
            function adm_loadScript(fn) {
                return fn();
            }
            function admorrispro_ready(fn) {
                if (document.readyState != 'loading'){
                    fn();
                } else {
                    document.addEventListener('DOMContentLoaded', fn);
                }
            }{/strip}
        </script>
            
        {* Category Banner -> load parallax.js *}
        {if $admPro->hasCategoryBanner() || isset($admBlogSettings) && $admBlogSettings->getBannerParallax()}
            {* <script async src="{$amTemplateDirFull}js/parallax.min.js"></script> *}
            {if !empty($categoryFunctionAttributes.category_banner_image)}
                {$bannerImage = $admPro->getCategoryBannerImage()}
            {elseif isset($admBlogSettings) && $admBlogSettings->getBannerParallax()}
                {$bannerImage = $admBlogSettings->currentBannerImage}
            {/if}
            <link rel="preload" href="{$bannerImage|trim}" as="image">
            <script type="module">
                loadjs(['{$amTemplateDirFull}js/parallax.min.js'], 'parallax');
            </script>
            
        {/if}
        
    {/block}

    {block name='head-icons'}
        {$FaviconHelper->getLinkTag()}
    {/block}

    {block name="head-resources"}


        <script>
            window.lazySizesConfig = window.lazySizesConfig || {};
            window.lazySizesConfig.lazyClass = 'lazy';
        </script>
        {if $Einstellungen["template"]["general"]["progressive_loading"] === "Y" }
            <script src="{$amTemplateDirFull}js/lazysizes-blur.min.js?v=5.3.0" async></script>
        {/if}
        <script src="{$amTemplateDirFull}js/ls.unveilhooks.min.js?v=5.3.0" async></script>
        
        <script src="{$amTemplateDirFull}js/lazysizes.min.js?v=5.3.0" async></script>

        {* script for inlining the svg icon sprites *}
        <script>
            window.svgLocalStorageConfig = [{
                name: 'icons',
                path:'{$amTemplateDirFull}icons.svg',
                revision: '{$admorris_pro_templateSettings->iconSpriteHash|default:1}'
            },
            {
                name: 'payment-icons',
                path: '{$amTemplateDirFull}payment-icons.svg',
                revision: '{$admorris_pro_templateSettings->paymentIconsSpriteHash|default:1}'
            }];
        </script>

        <script src="{$amTemplateDirFull}js/svgLocalStorage.min.js?v={$templateVersion}" async></script>

        {* fontAweSome 4 css *}
        {if isset($Einstellungen["template"]["general"]["use_font_awesome_4"]) && $Einstellungen["template"]["general"]["use_font_awesome_4"] === "Y" ||  \JTL\Shop::isAdmin(true)}
            <link rel="stylesheet" href="{$amTemplateDirFull}styles/font-awesome.min.css" media="print" onload="this.media='all'">
        {/if}

        {* fontAweSome 5 css *}
        {if isset($Einstellungen["template"]["general"]["use_font_awesome_5"]) && $Einstellungen["template"]["general"]["use_font_awesome_5"] === "Y" ||  \JTL\Shop::isAdmin(true)}
            <link rel="stylesheet" href="{$amTemplateDirFull}styles/font-awesome-5.min.css" media="print" onload="this.media='all'">
            <link rel="stylesheet" href="{$amTemplateDirFull}styles/font-awesome-5-brands.min.css" media="print" onload="this.media='all'">
            <link rel="stylesheet" href="{$amTemplateDirFull}styles/font-awesome-5-solid.min.css" media="print" onload="this.media='all'">
        {/if}

        {* css *}
        {if isset($admorris_pro_templateSettings)}

            {block 'webfont-loading'}
            {* load webfonts *}
            {fetch file="{$amTemplateDir}fonts/{$admorris_pro_templateSettings->fontFamilyBase|lower}.css" assign=mainFont}{*  *}
            
            {$ShopUrlWithSlash = $ShopURL|cat:'/'}
            <style>
                {str_replace('./', $ShopUrlWithSlash, $admPro->minify_css($mainFont))}
            </style>
            
            {if $admorris_pro_templateSettings->fontFamilyBase !== $admorris_pro_templateSettings->headingsFontFamily}
                {fetch file="{$amTemplateDir}fonts/{$admorris_pro_templateSettings->headingsFontFamily|lower}.css" assign=secondaryFont}           
                <style>
                    {str_replace('./', $ShopUrlWithSlash, $admPro->minify_css($secondaryFont))}
                </style>
            {/if}     
            {/block}
        {else}       
            <link type="text/css" rel="stylesheet" href="{$amTemplateDir}fonts/roboto.css">    
        {/if}
        


        {include 'layout/styles.tpl'}

        {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
            {foreach $cCSS_arr as $cCSS}
                {* theme.css is the first item in $cCSS - 
                   needs custom cache busting strategy because of theme compiler 
                 *}
                {* {if isset($cacheId) && $cCSS@index === 0}
                    <link type="text/css" href="{$ShopURL}/{$cCSS}?v={$cacheId}" rel="stylesheet">
                {else} *}
                    <link type="text/css" href="{$ShopURL}/{$cCSS}?v={$templateVersion}" rel="stylesheet">
                {* {/if} *}
            {/foreach}

            {if isset($cPluginCss_arr)}
                {foreach $cPluginCss_arr as $cCSS}
                    <link type="text/css" href="{$ShopURL}/{$cCSS}?v={$templateVersion}" rel="stylesheet">
                {/foreach}
            {/if}
        {else}
            {if $Einstellungen.template.general.use_minify !== 'static'}
                {if isset($cacheId)}
                    {$cachebustingId = $cacheId}
                {else}
                    {$cachebustingId = $templateVersion}
                {/if}
            {/if}
            <link type="text/css" href="{$ShopURL}/{$combinedCSS}{if isset($cachebustingId)}&id={$cachebustingId}{/if}" rel="stylesheet">
        {/if}


        {if $opc->isEditMode() === false && $opc->isPreviewMode() === false && \JTL\Shop::isAdmin(true)}
            <link type="text/css" href="{$ShopURL}/admin/opc/css/startmenu.css" rel="stylesheet">
        {/if}
        {foreach $opcPageService->getCurPage()->getCssList($opc->isEditMode()) as $cssFile => $cssTrue}
            <link rel="stylesheet" href="{$cssFile}">
        {/foreach}


        {** 
          * async css loading
          *}

        <link rel="stylesheet" href="{$amTemplateDirFull}styles/consent.min.css?v={$templateVersion}" media="print" onload="this.media='all'">
        {* loading animation *}
        {$loadingAnimation = $admorris_pro_templateSettings->image_preload_animation && !empty($Suchergebnisse->getProducts())}
        {if $loadingAnimation}
            <link rel="stylesheet" href="{$amTemplateDirFull}styles/loader/ball-spin-clockwise.min.css" media="print" onload="this.media='all'">
        {/if}
        <link rel="stylesheet" href="{$amTemplateDirFull}styles/animate.css" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{$amTemplateDirFull}styles/slick-lightbox.css" media="print" onload="this.media='all'">

        {* RSS *}
        {if isset($Einstellungen.rss.rss_nutzen) && $Einstellungen.rss.rss_nutzen === 'Y'}
            <link rel="alternate" type="application/rss+xml" title="Newsfeed {$Einstellungen.global.global_shopname}" href="{$ShopURL}/rss.xml">
        {/if}
        {* Languages *}
        {$languages = JTL\Session\Frontend::getLanguages()}
        {if $languages|count > 1}
            {foreach $languages as $language}
                <link rel="alternate"
                        hreflang="{$language->getIso639()}"
                        href="{if $language->getShopDefault() === 'Y' && isset($Link) && $Link->getLinkType() === $smarty.const.LINKTYP_STARTSEITE}{$ShopURL}/{else}{$language->getUrl()}{/if}">
                {if $language->getShopDefault() === 'Y'}
                <link rel="alternate"
                    hreflang="x-default"
                    href="{if isset($Link) && $Link->getLinkType() === $smarty.const.LINKTYP_STARTSEITE}{$ShopURL}/{else}{$language->getUrl()}{/if}">
                {/if}
            {/foreach}
        {/if}

    {/block}

    {* Pagination *}
    {if isset($Suchergebnisse) && $Suchergebnisse->getPages()->getMaxPage() > 1}
        {if $Suchergebnisse->getPages()->getCurrentPage() > 1}
            <link rel="prev" href="{$filterPagination->getPrev()->getURL()}">
        {/if}
        {if $Suchergebnisse->getPages()->getCurrentPage() < $Suchergebnisse->getPages()->getMaxPage()}
            <link rel="next" href="{$filterPagination->getNext()->getURL()}">
        {/if}
    {/if}



    {block name='head-resources-jquery'}
        <script defer src="{$ShopURL}/{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/jquery-3.7.1.min.js"></script>
    {/block}

    {block name="head-asset-loading"}{/block}

    {include 'layout/css_variables.tpl'}
    <script data-name="admorris-script">
        var templateSettings = {$Einstellungen.template.theme|json_encode};
        {if isset($amSlider) && count($amSlider->slide_arr) > 0}
            templateSettings.am_slider_active = true; 
        {/if}
    </script>

        {* {dump($admPro->createTemplateSettingsArray())} *}

    {* Template Settings for js *}
    <script data-name="admorris-settings">
        var admorris_pro_template_settings = {$admPro->createTemplateSettingsArray()|json_encode};
        
        
    </script>


    <script defer src="{$amTemplateDirFull}js/admorris/{$admPro->getJsFilenameFromManifest('app.js')}"></script>

    {if $Einstellungen.template.general.use_minify === 'N'}
        {if isset($cPluginJsHead_arr)}
            {foreach $cPluginJsHead_arr as $cJS}
                <script defer src="{$ShopURL}/{$cJS}?v={$templateVersion}"></script>
            {/foreach}
        {/if}
        {foreach $cJS_arr as $cJS}
            <script defer src="{$ShopURL}/{$cJS}?v={$templateVersion}"></script>
        {/foreach}
        {foreach $cPluginJsBody_arr as $cJS}
            <script defer src="{$ShopURL}/{$cJS}?v={$templateVersion}"></script>
        {/foreach}
    {else}
        {foreach $minifiedJS as $item}
            <script defer src="{$ShopURL}/{$item}"></script>
        {/foreach}
    {/if}
    
    {if version_compare($smarty.const.APPLICATION_VERSION, '5.3.0', '>=')}
        {foreach $opcPageService->getCurPage()->getJsList() as $jsFile => $jsTrue}
            <script defer src="{$jsFile}"></script>
        {/foreach}
    {/if}



    {assign var="customJSPath" value=$currentTemplateDir|cat:'js/custom.js'}
    {if file_exists($customJSPath)} 
        <script src="{$ShopURL}/{$customJSPath}?v={$templateVersion}" defer></script>
    {/if}
    <script type="module">
        loadjs.done('template-scripts');
    </script>

    {* https://github.com/muicss/loadjs/issues/31#issuecomment-302603127 *}
    <script>{strip}
    {literal}
        var loadjsDefer={success:function(){},error:function(e){},before:function(e,r){if(r.tagName=='SCRIPT'){r.setAttribute("defer",""),r.removeAttribute("async")}}};
    {/literal}
    {/strip}</script>
    {* <link rel="stylesheet" href="{$amTemplateDirFull}css/bootstrap-select.min.css" media="print" onload="this.media='all'"> *}

    {if isset($oSlider) && count($oSlider->getSlides()) > 0}
        <link rel="stylesheet" href="{$amTemplateDirFull}styles/jtl-slider/nivo-slider-min.css" media="print" onload="this.media='all'">
        <script src="{$amTemplateDirFull}js/jquery.nivo.slider.pack.js" defer></script>
    {/if}


    {if !empty($oUploadSchema_arr)}
        {getUploaderLang iso=$smarty.session.currentLanguage->getIso639()|default:'' assign='uploaderLang'}
        <script defer src="{$amTemplateDirFull}js/fileinput/fileinput.min.js"></script>
        {* <script defer src="{$amTemplateDirFull}js/fileinput/themes/fas/theme.min.js"></script> *}
        <script defer src="{$amTemplateDirFull}js/fileinput/locales/{$uploaderLang}.js"></script>
        <link href="{$amTemplateDirFull}js/fileinput/css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
    {/if}



    {include file='layout/header_inline_js.tpl'}

    {block name='layout-header-head-resources-datatables'}
        {if $nSeitenTyp === $smarty.const.PAGE_MEINKONTO || $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG}
            <script defer src="{$amTemplateDirFull}vendor/DataTables/datatables.min.js"></script>
            <link rel="stylesheet" href="{$amTemplateDirFull}vendor/DataTables/datatables.min.css?v={$templateVersion}" media="print" onload="this.media='all'">
        {/if}
    {/block}

    {* Logo structured data *}
    {$HeaderShopLogo = $admorris_pro_templateSettings->logoInverted}
    {block 'logo-structured-data'}
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "url": "{$ShopURL}",
            "logo": "{if !empty($HeaderShopLogo)}{$HeaderShopLogo}{else}{$ShopLogoURL}{/if}"
        }
    </script>
    {/block}

    {if $nSeitenTyp === $smarty.const.PAGE_ARTIKEL}
        {block 'header-productdetails-structured-data'}
            {include file='productdetails/structured_data.tpl'}
        {/block}
    {/if}

    {$dbgBarHead}
</head>
{/block}

{assign var="isFluidContent" value=false}
{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' }{*&& isset($Link) && $Link->bIsFluid*}
    {assign var="isFluidContent" value=true}
{/if}

{block name="body-tag"}
    <body data-page="{$nSeitenTyp}" class="no-js body-offcanvas{block 'body-class-attr'}{/block}{if $isMobile} is-mobile{/if}{if $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG} is-checkout{/if}"{if isset($Link) && !empty($Link->getIdentifier())} id="{$Link->getIdentifier()}"{/if}>
{/block}

{* OPC Menu *}
{if !$isMobile}
    {include file=$opcDir|cat:'tpl/startmenu.tpl'}
{/if}

<script data-name="admorris-script">
    document.querySelector('body').classList.remove('no-js');
</script>

{has_boxes position='left' assign='hasLeftBox'}


{include 'productlist/offcanvas_filter.tpl'}



{if !$bExclusive}
    {block name="layout-header-skip-to-links"}
        {link id="skip-navigation-link" href="#main-wrapper" class="btn-skip-to"}
            {lang key='skipToContent'}
        {/link}
        {* {if $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG} *}
        {* TODO: fix search skip link. It has to work with the mobile header too *}
            {* {link id="skip-navigation-link-search" href="#search-header" class="btn-skip-to"}
                {lang key='skipToSearch'}
            {/link} *}
            {link id="skip-navigation-link-nav" href="#jtl-nav-wrapper" class="btn-skip-to"}
                {lang key='skipToNav'}
            {/link}
        {* {/if} *}
    {/block}

    {block name='consent-manager'}
        {if $Einstellungen.consentmanager.consent_manager_active === 'Y' && !$isAjax && $consentItems->isNotEmpty()}
            <input id="consent-manager-show-banner" type="hidden" value="{$Einstellungen.consentmanager.consent_manager_show_banner}">
            {include file='snippets/consent_manager.tpl'}
            <script type="module">
                setTimeout(function() {
                    $('#consent-manager, #consent-settings-btn').removeClass('d-none');
                }, 100)
                document.addEventListener('consent.updated', function(e) {
                    $.post('{$ShopURLSSL}/_updateconsent', {
                            'action': 'updateconsent',
                            'jtl_token': '{$smarty.session.jtl_token}',
                            'data': e.detail
                        }
                    );
                });
                {if !isset($smarty.session.consents)}
                    document.addEventListener('consent.ready', function(e) {
                        document.dispatchEvent(new CustomEvent('consent.updated', { detail: e.detail }));
                    });
                {/if}

                window.CM = new ConsentManager({
                    version: {$smarty.session.consentVersion|default:1}
                });
                var trigger = document.querySelectorAll('.trigger');
                var triggerCall = function (e) {
                    e.preventDefault();
                    let type = e.target.dataset.consent;
                    if (CM.getSettings(type) === false) {
                        CM.openConfirmationModal(type, function () {
                            let data = CM._getLocalData();
                            if (data === null) {
                                data = { settings: {} };
                            }
                            data.settings[type] = true;
                            document.dispatchEvent(new CustomEvent('consent.updated', { detail: data.settings }));
                        });
                    }
                }
                for (let i = 0; i < trigger.length; ++i) {
                    trigger[i].addEventListener('click', triggerCall)
                }
            </script>
        {/if}
    {/block}
    {if isset($bAdminWartungsmodus) && $bAdminWartungsmodus}
        <div id="maintenance-mode">
            <span data-toggle="tooltip" data-placement="left" title="{lang key="adminMaintenanceMode" section="global"}">
                {$admIcon->renderIcon('cogs', 'icon-content icon-content--center')}
            </span>
        </div>

        <style>
            #maintenance-mode {
                position: fixed;
                top: calc(var(--adm-header-height) + 10px);
                right: 10px;
                z-index: 1050;
            }
            #maintenance-mode > span {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 48px;
                height: 48px;
                border-radius: 50px;
                color: #fff;
                font-size: 20px;
                background-color: orange;
            }
            #maintenance-mode .tooltip {
                top: 0px !important;
            }
            #maintenance-mode .tooltip-inner {
                max-width: 300px;
                width: 300px;
            }
            #maintenance-mode .tooltip-arrow {
                top: 24px !important;
            }
        </style>
    {/if}

    {if $smarty.const.SAFE_MODE === true}
        <div id="safe-mode" class="navbar navbar-inverse">
            <div class="container">
                <div class="navbar-text text-center">
                    {lang key='safeModeActive' section='global'}
                </div>
            </div>
         </div>
    {/if}


    {block name="header"}
        {include 'header/header_nav.tpl'}
    {/block}
{/if}

{opcMountPoint id='opc_after_header_wrapper' inContainer=false title='Nach .header-wrapper und Admorris Pro Slider'}

{block name='layout-header-main-wrapper-starttag'}
    <main id="main-wrapper" tabindex="-1" class="main-wrapper{if $bExclusive} exclusive{/if}{if isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'boxed'} boxed{else} fluid{/if}{if $hasLeftBox && $Einstellungen.template.theme.left_sidebar === 'Y'} aside-active{/if}">
{/block}


{block name="header-fluid-banner"}
{assign var="isFluidBanner" value=isset($Einstellungen.template.theme.banner_full_width, $Einstellungen.template.theme.pagelayout, $oImageMap) && $Einstellungen.template.theme.banner_full_width === 'Y' && $Einstellungen.template.theme.pagelayout === 'fluid'}
{if $isFluidBanner}
    {include file="snippets/banner.tpl"}
{/if}

    
{assign var='isFluidSlider' value=isset($Einstellungen.template.theme.slider_full_width, $Einstellungen.template.theme.pagelayout, $oSlider) && $Einstellungen.template.theme.slider_full_width === 'Y' &&  $Einstellungen.template.theme.pagelayout === 'fluid' && count($oSlider->getSlides()) > 0}
{if $isFluidSlider}
    {include file="snippets/slider.tpl"}
{/if}

{/block}

{* Category Banner *}
{if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE && !empty($categoryFunctionAttributes.category_banner_image)}
    {include 'productlist/category_heading.tpl' banner=true}
{* News Banner *}
{elseif isset($admBlogSettings) && $admBlogSettings->initBanner(true)}
    {include 'blog/image_banner.tpl'}
{/if}

{opcMountPoint id='opc_before_main_content_wrapper' title="Nach JTL-Slider & Banner, vor .main-content-wrapper" inContainer=false}

{block name="content-all-starttags"}
    {block name="content-wrapper-starttag"}
        <div id="content-wrapper" class="main-content-wrapper">
    {/block}

    {$showSidebarBoxes = !$bExclusive && !empty($boxes.left) && !empty($boxes.left|strip_tags|trim)
        &&  $nSeitenTyp !== $smarty.const.PAGE_ARTIKELLISTE}

    {block name='content-container-starttag'}
        {* Container width *}
        {$containerClass = ($isFluidContent)?'admPro-container':'container'}

        <div {if !$bExclusive}class="{$containerClass} container--{$admPro->container_size()} sidebar-layout sidebar-layout--reverse"{/if} style="--sidebar-width: 250px; --sidebar-gap: 2rem calc(3vw + 2rem);">
    {/block}

    {* {block name='content-row-starttag'}
        <div class="row">
    {/block} *}

    {block name='layout-header-content-starttag'}
        <div id="content" class="main-content stack stack--collapse-margins sidebar-layout__main">
    {/block}

    {* admorris Pro custom: breadcrumb loading moved to productlist/header.tpl and result_options.tpl for variing position *}
    {block name="header-breadcrumb"}{/block}

    {block name='layout-header-alert'}
        {include file='snippets/alert_list.tpl'}
    {/block}
{/block}{* /content-all-starttags *}
{/block}
