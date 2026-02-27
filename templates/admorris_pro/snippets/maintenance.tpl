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
                {* Fonts Preloading *}
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@300;700&family=Inter:wght@400;700;800&display=swap">
                <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inria+Serif:wght@300;700&family=Inter:wght@400;700;800&display=swap" media="print" onload="this.media='all'">

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
    <style>
        :root {
            --primary-bg: #0f172a;
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --accent-color: #38bdf8;
            --text-main: #f8fafc;
            --text-dim: #94a3b8;
        }

        body {
            background: radial-gradient(circle at top right, #1e293b, var(--primary-bg));
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
        }

        .maintenance-container {
            position: relative;
            z-index: 10;
            padding: 3rem;
            max-width: 600px;
            width: 90%;
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-placeholder {
            margin-bottom: 2rem;
            display: inline-block;
        }

        .logo-placeholder img {
            max-height: 80px;
            filter: drop-shadow(0 0 10px rgba(56, 189, 248, 0.3));
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(to bottom right, #fff, var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -0.025em;
        }

        p {
            font-size: 1.125rem;
            line-height: 1.6;
            color: var(--text-dim);
            margin-bottom: 2.5rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 9999px;
            color: var(--accent-color);
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .status-pulse {
            width: 8px;
            height: 8px;
            background: var(--accent-color);
            border-radius: 50%;
            margin-right: 0.75rem;
            box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(56, 189, 248, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(56, 189, 248, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(56, 189, 248, 0); }
        }

        .footer-info {
            border-top: 1px solid var(--glass-border);
            padding-top: 2rem;
            font-size: 0.875rem;
            color: var(--text-dim);
        }

        .bg-glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.1) 0%, rgba(56, 189, 248, 0) 70%);
            border-radius: 50%;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
        }
    </style>

    <div class="bg-glow"></div>

    <div class="maintenance-container">
        <div class="logo-placeholder">
            {image src=$ShopLogoURL alt=$meta_title webp=true fluid=false height=80}
        </div>

        <div class="status-badge">
            <span class="status-pulse"></span>
            Wartungsarbeiten aktiv
        </div>

        <h1>Wir sind bald wieder da</h1>
        <p>
            Wir führen gerade wichtige Performance-Optimierungen und Wartungsarbeiten durch, um Ihr Einkaufserlebnis noch schneller und besser zu gestalten. 
            Vielen Dank für Ihre Geduld!
        </p>

        <div class="footer-info">
            {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_IMPRESSUM])}
                <strong>{$oSpezialseiten_arr[$smarty.const.LINKTYP_IMPRESSUM]->getTitle()}</strong><br>
                {$oSpezialseiten_arr[$smarty.const.LINKTYP_IMPRESSUM]->getContent()|strip_tags|truncate:200}
            {else}
                &copy; {$smarty.now|date_format:"%Y"} {$meta_title}
            {/if}
        </div>
    </div>
    {/block}

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
