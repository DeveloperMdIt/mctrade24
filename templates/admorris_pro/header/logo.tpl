{*custom*}
{* INFO: Logos werden verkehrt herum gespeichert *}
{block 'header-logo'}
    {$HeaderShopLogoInverted = $admorris_pro_templateSettings->logo}
    {$HeaderShopLogo = $admorris_pro_templateSettings->logoInverted}
    {$svgLogoClass = (strpos($HeaderShopLogo, '.svg')) ? ' header__logo--svg':''}
    {if $layoutType === 'desktopLayout'}
        {$logoWidth = $admorris_pro_themeVars->logoWidthDesktop}
    {else}
        {$logoWidth = $admorris_pro_themeVars->logoWidthMobile}
    {/if}

    {$minWidth = intval($logoWidth) / 3}
    {if $minWidth < 40}
        {$minWidth = 40}
    {/if}

    {$logoAltAttribute = "{$Einstellungen.global.global_shopname} {lang key='startpage' section='breadcrumb'}"}

    <div class="header__logo{if !empty($HeaderShopLogoInverted)} header__logo--invert{/if}{$svgLogoClass}" id="logo-{$layoutType}" style="min-width: {$minWidth}px;">
        {block name="logo"}
        <a href="{$ShopHomeURL}" aria-label="{$logoAltAttribute|escape:'html'}">
            {if !empty($HeaderShopLogo && empty($svgLogoClass))}
                {responsiveImage
                    src="{$HeaderShopLogo}"
                    alt="$logoAltAttribute"
                    lazy=false
                    class="header__logo-img"
                    sizes="{$logoWidth}"
                    fetchpriority="high"
                    progressiveLoading=false
                }
                {if !empty($HeaderShopLogoInverted)}
                    {responsiveImage 
                        src="{$HeaderShopLogoInverted}" 
                        alt="$logoAltAttribute"
                        lazy=false
                        class="header__inverted-logo-img header__logo-img"
                        sizes="{$logoWidth}"
                    }
                {/if}
            {elseif !empty($HeaderShopLogo) && !empty($svgLogoClass)}
                {* SVG Logo *}
                <img src="{$ShopURL}{$HeaderShopLogo}"
                    alt="$logoAltAttribute"
                    class="header__logo-img img-fluid"
                    fetchpriority="high"
                />
                {if !empty($HeaderShopLogoInverted)}
                <img src="{$ShopURL}{$HeaderShopLogoInverted}" 
                    alt="$logoAltAttribute"
                    class="header__inverted-logo-img header__logo-img img-fluid"
                />
                {/if}
            {elseif isset($ShopLogoURL)}
                {image src=$ShopLogoURL
                   alt=$meta_title
                   id="shop-logo"
                   width=$Einstellungen.template.header.logo_width
                   height=$Einstellungen.template.header.logo_height
                    alt=$Einstellungen.global.global_shopname
                    sizes="auto"
                    fetchpriority="high"
                }
            {else}
                <span class="h1">{$Einstellungen.global.global_shopname}</span>
            {/if}
        </a>
        {/block}
    </div>
    {/block}