{block name='snippets-maintenance'}
    {block name='snippets-maintenance-doctype'}
     <!DOCTYPE html>
    {/block}
    <html {block name='snippets-maintenance-html-attributes'}lang="{$meta_language}" itemscope {if $nSeitenTyp === $smarty.const.URLART_ARTIKEL}itemtype="http://schema.org/ItemPage"
          {elseif $nSeitenTyp === $smarty.const.URLART_KATEGORIE}itemtype="http://schema.org/CollectionPage"
          {else}itemtype="http://schema.org/WebPage"{/if}{/block}>
    {block name='snippets-maintenance-head'}
        <head>
            {block name='snippets-maintenance-head-meta'}
                <meta http-equiv="content-type" content="text/html; charset={$smarty.const.JTL_CHARSET}">
                <meta name="description" itemprop="description" content={block name='snippets-maintenance-head-meta-description'}"{$meta_description|truncate:1000:'':true}{/block}">
                {if !empty($meta_keywords)}
                    <meta name="keywords" itemprop="keywords" content="{block name='snippets-maintenance-head-meta-keywords'}{$meta_keywords|truncate:255:'':true}{/block}">
                {/if}
                <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="robots" content="{if $robotsContent}{$robotsContent}{elseif $bNoIndex === true  || (isset($Link) && $Link->getNoFollow() === true)}noindex{else}index, follow{/if}">

                <meta itemprop="url" content="{$cCanonicalURL}"/>
                <meta property="og:type" content="website" />
                <meta property="og:site_name" content="{$meta_title}" />
                <meta property="og:title" content="{$meta_title}" />
                <meta property="og:description" content="{$meta_description|truncate:1000:'':true}" />
                <meta property="og:url" content="{$cCanonicalURL}"/>

                {if $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && !empty($Artikel->Bilder)}
                    <meta itemprop="image" content="{$Artikel->Bilder[0]->cURLGross}" />
                    <meta property="og:image" content="{$Artikel->Bilder[0]->cURLGross}">
                {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE
                && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild.gif'
                && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild_kl.gif'
                }
                    <meta itemprop="image" content="{$imageBaseURL}{$oNavigationsinfo->getImageURL()}" />
                    <meta property="og:image" content="{$imageBaseURL}{$oNavigationsinfo->getImageURL()}" />
                {elseif $nSeitenTyp === $smarty.const.PAGE_NEWSDETAIL && !empty($oNewsArchiv->getPreviewImage())}
                    <meta itemprop="image" content="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}" />
                    <meta property="og:image" content="{$imageBaseURL}{$oNewsArchiv->getPreviewImage()}" />
                {else}
                    <meta itemprop="image" content="{$ShopLogoURL}" />
                    <meta property="og:image" content="{$ShopLogoURL}" />
                {/if}
            {/block}

            <title itemprop="name">{block name='snippets-maintenance-head-title'}{$meta_title}{/block}</title>

            {if !empty($cCanonicalURL)}
                <link rel="canonical" href="{$cCanonicalURL}">
            {/if}

            {block name='snippets-maintenance-head-base'}{/block}

            {block name='snippets-maintenance-head-icons'}
                {$FaviconHelper->getLinkTag()}
            {/block}

            {block name='snippets-maintenance-head-resources'}
                {* css *}

                {include 'layout/styles.tpl'}
                
                {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                    {foreach $cCSS_arr as $cCSS}
                        {* theme.css is the first item in $cCSS - 
                           needs custom cache busting strategy because of theme compiler 
                         *}
                        {if isset($cacheId) && $cCSS@index === 0}
                            <link type="text/css" href="{$ShopURL}/{$cCSS}?v={$cacheId}" rel="stylesheet">
                        {else}
                            <link type="text/css" href="{$ShopURL}/{$cCSS}?v={$templateVersion}" rel="stylesheet">
                        {/if}
                    {/foreach}
        
                    {if isset($cPluginCss_arr)}
                        {foreach $cPluginCss_arr as $cCSS}
                            <link type="text/css" href="{$ShopURL}/{$cCSS}?v={$templateVersion}" rel="stylesheet">
                        {/foreach}
                    {/if}
                {else}
                    {if isset($cacheId)}
                        <link type="text/css" href="{$ShopURL}/asset/{$Einstellungen.template.theme.theme_default}.css{if isset($cPluginCss_arr) && $cPluginCss_arr|@count > 0},plugin_css{/if}?v={$cacheId}" rel="stylesheet">
                    {else}
                        <link type="text/css" href="{$ShopURL}/asset/{$Einstellungen.template.theme.theme_default}.css{if isset($cPluginCss_arr) && $cPluginCss_arr|@count > 0},plugin_css{/if}?v={$templateVersion}" rel="stylesheet">
                    {/if}
                {/if}
        
        
                {if $opc->isEditMode() === false && $opc->isPreviewMode() === false && \JTL\Shop::isAdmin(true)}
                    <link type="text/css" href="{$ShopURL}/admin/opc/css/startmenu.css" rel="stylesheet">
                {/if}
                {foreach $opcPageService->getCurPage()->getCssList($opc->isEditMode()) as $cssFile => $cssTrue}
                    <link rel="stylesheet" href="{$cssFile}">
                {/foreach}
                
                {* Languages *}
                {if !empty($smarty.session.Sprachen) && count($smarty.session.Sprachen) > 1}
                    {foreach item=oSprache from=$smarty.session.Sprachen}
                        <link rel="alternate" hreflang="{$oSprache->cISO639}" href="{if $nSeitenTyp === $smarty.const.PAGE_STARTSEITE && $oSprache->cStandard === 'Y'}{$cCanonicalURL}{else}{$oSprache->cURLFull}{/if}">
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


            {block name='snippets-maintenance-head-resources-jquery'}
                <script src="{$ShopURL}/{if empty($parentTemplateDir)}{$currentTemplateDir}{else}{$parentTemplateDir}{/if}js/jquery-3.7.1.min.js"></script>
            {/block}
            {include file='layout/header_inline_js.tpl'}
            {$dbgBarHead}
        </head>
    {/block}

    {block name='snippets-maintenance-body-tag'}
    <body data-page="{$nSeitenTyp}" class="body-offcanvas"{if isset($Link) && !empty($Link->getIdentifier())} id="{$Link->getIdentifier()}"{/if}>
    {/block}

    {block name='snippets-maintenance-content'}
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <div id="maintenance-notice" class="card panel-info">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                <h3 class="">{* {$admIcon->renderIcon('wrench', 'icon-content icon-content--default')}  *}{lang key="maintainance" section="global"}</h3>
                            </div>
                            <div class="col-6">
                                <ul class="list-inline user-settings float-right">
                                    {block name='snippets-maintenance-top-bar-user-settings-language'}
                                        {if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
                                            <li class="language-dropdown center-block dropdown">
                                                <a href="#" class="dropdown-toggle btn btn-secondary btn-sm" data-toggle="dropdown" itemprop="inLanguage" itemscope itemtype="http://schema.org/Language" title="{lang key='selectLang'}">
                                                {* {$admIcon->renderIcon('languageSelection', 'icon-content icon-content--default')} *}
                                                    {foreach $smarty.session.Sprachen as $Sprache}
                                                        {if $Sprache->kSprache == $smarty.session.kSprache}
                                                            <span class="lang-{$lang}" itemprop="name"> {$Sprache->displayLanguage}</span>
                                                        {/if}
                                                    {/foreach}
                                                    <span class="caret"></span>
                                                </a>
                                                <ul id="language-dropdown" class="dropdown-menu dropdown-menu-right">
                                                    {foreach $smarty.session.Sprachen as $oSprache}
                                                        {if $oSprache->kSprache != $smarty.session.kSprache}
                                                            <li>
                                                                <a href="{$oSprache->url}" class="link_lang {$oSprache->iso}" rel="nofollow">{$oSprache->displayLanguage}</a>
                                                            </li>
                                                        {/if}
                                                    {/foreach}
                                                </ul>
                                            </li>
                                        {/if}
                                    {/block}
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        {lang key="maintenanceModeActive" section="global"}
                    </div>
                </div>
            </div>
            {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_IMPRESSUM])}
            <div class="col-lg-6 offset-lg-3 text-center">
                <h2 class="mt-2">{$oSpezialseiten_arr[$smarty.const.LINKTYP_IMPRESSUM]->getTitle()}</h2>
                <p>{$oSpezialseiten_arr[$smarty.const.LINKTYP_IMPRESSUM]->getContent()}</p>
            </div>
            {/if}
        </div>
    </div>
    {* JavaScripts *}
    {block name='snippets-maintenance-footer-js'}
        {assign var='isFluidContent' value=isset($Einstellungen.template.theme.pagelayout) && $Einstellungen.template.theme.pagelayout === 'fluid' && isset($Link) && $Link->getIsFluid()}

  
        {$dbgBarBody}
        <script>
            jtl.load({strip}[
                {* evo js *}
                {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {if isset($cPluginJsHead_arr)}
                {foreach $cPluginJsHead_arr as $cJS}
                "{$ShopURL}/{$cJS}?v={$templateVersion}",
                {/foreach}
                {/if}
                {else}
                {if isset($cPluginJsHead_arr) && $cPluginJsHead_arr|@count > 0}
                "{$ShopURL}/asset/plugin_js_head?v={$templateVersion}",
                {/if}
                {/if}
                {if !isset($Einstellungen.template.general.use_minify) || $Einstellungen.template.general.use_minify === 'N'}
                {foreach $cJS_arr as $cJS}
                "{$ShopURL}/{$cJS}?v={$templateVersion}",
                {/foreach}
                {if isset($cPluginJsBody_arr)}
                {foreach $cPluginJsBody_arr as $cJS}
                "{$ShopURL}/{$cJS}?v={$templateVersion}",
                {/foreach}
                {/if}
                {else}
                "{$ShopURL}/asset/jtl3.js?v={$templateVersion}",
                {if isset($cPluginJsBody_arr) && $cPluginJsBody_arr|@count > 0}
                "{$ShopURL}/asset/plugin_js_body?v={$templateVersion}",
                {/if}
                {/if}

                {assign var='customJSPath' value=$currentTemplateDir|cat:'/js/custom.js'}
                {if file_exists($customJSPath)}
                "{$ShopURL}/{$customJSPath}?v={$templateVersion}",
                {/if}
            ]{/strip});
        </script>
        {captchaMarkup getBody=false}
    {/block}

    {block name='snippets-maintenance-layout-footer-js'}{/block}
    {block name='snippets-maintenance-layout-footer-io-path'}
        <div id="jtl-io-path" data-path="{$ShopURL}" class="d-none"></div>
    {/block}
    </body>
    </html>
    {/block}
{/block}
