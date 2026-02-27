{block name='layout-styles'}
    {* TODO: CacheId für jedes file in manifest.json ablegen *}
    {if isset($admorris_pro_templateSettings)}
        {$cacheId = $admorris_pro_templateSettings->cacheId}
    {/if}

    {if empty($cacheId)}
        {$cacheId = $templateVersion}
        
    {/if}

    <link type="text/css" href="{$templateCssPath}styles.css?v={$admPro->getCssFileHash('styles.css')}" rel="stylesheet" fetchpriority="high">

    {if $nSeitenTyp === $smarty.const.PAGE_STARTSEITE}
        <link type="text/css" href="{$templateCssPath}homepage.css?v={$admPro->getCssFileHash('homepage.css')}" rel="stylesheet">

    {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL}
        <link type="text/css" href="{$templateCssPath}productdetails.css?v={$admPro->getCssFileHash('productdetails.css')}" rel="stylesheet">
        {if $Artikel->bHasKonfig}
            <link rel="stylesheet" href="{$templateCssPath}configuration.css?v={$admPro->getCssFileHash('configuration.css')}" media="print" onload="this.media='all'" >
        {/if}

    {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE}
        <link type="text/css" href="{$templateCssPath}productlist.css?v={$admPro->getCssFileHash('productlist.css')}" rel="stylesheet">

    {elseif $nSeitenTyp === $smarty.const.PAGE_MEINKONTO}
        <link type="text/css" href="{$templateCssPath}account.css?v={$admPro->getCssFileHash('account.css')}" rel="stylesheet">

    {elseif $nSeitenTyp === $smarty.const.PAGE_NEWS || $nSeitenTyp === $smarty.const.PAGE_NEWSDETAIL || $nSeitenTyp === $smarty.const.PAGE_NEWSARCHIV || $nSeitenTyp === $smarty.const.PAGE_NEWSMONAT || $nSeitenTyp === $smarty.const.PAGE_NEWSKATEGORIE}
        <link type="text/css" href="{$templateCssPath}blog.css?v={$admPro->getCssFileHash('blog.css')}" rel="stylesheet">

    {elseif $nSeitenTyp === $smarty.const.PAGE_BESTELLVORGANG || $nSeitenTyp === $smarty.const.PAGE_WARENKORB || $nSeitenTyp === $smarty.const.PAGE_GRATISGESCHENK || $nSeitenTyp === $smarty.const.PAGE_BESTELLABSCHLUSS}
        <link type="text/css" href="{$templateCssPath}checkout.css?v={$admPro->getCssFileHash('checkout.css')}" rel="stylesheet">

    {elseif $nSeitenTyp === $smarty.const.PAGE_VERGLEICHSLISTE}
        <link type="text/css" href="{$templateCssPath}comparelist.css?v={$admPro->getCssFileHash('comparelist.css')}" rel="stylesheet">

    {elseif $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE}
        <link type="text/css" href="{$templateCssPath}wishlist.css?v={$admPro->getCssFileHash('wishlist.css')}" rel="stylesheet">
    {/if}

    {if isset($amSlider) && count($amSlider->slide_arr) > 0}
        <link type="text/css" href="{$templateCssPath}slider.css?v={$admPro->getCssFileHash('slider.css')}" rel="stylesheet">
    {/if}

    {* load non-critical styles async *}
    <link rel="stylesheet" href="{$templateCssPath}non-critical.css?v={$admPro->getCssFileHash('non-critical.css')}" media="print" onload="this.media='all'">
{/block}
