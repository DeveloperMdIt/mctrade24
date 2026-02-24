{*custom*}
{block 'header-shopnav-cart'}
    {$labelSetting = $headerLayout->getItemSetting('cart', 'label', $layoutType)}

    {$showIcon = ($labelSetting|in_array:['icon', 'icon_text'])?true:false}
    {$showLabel = ($labelSetting|in_array:['text', 'icon_text'])?true:false}
    {$iconSpacing = ($labelSetting === 'icon_text')?' ':''}



    {if $layoutType !== 'desktopLayout'}
        {strip}
        <a href="{get_static_route id='warenkorb.php'}" title="{lang key='basket'}" class="nav-link shopnav__link">
            {if $showIcon}
                {$admIcon->renderIcon('shoppingCart', 'icon-content icon-content--default icon-content--center shopnav__icon')}
            {/if}
            {$iconSpacing}
            <span class="shopnav__label icon-text--center{if !$showLabel} sr-only{/if}">{lang key='basket'}</span>
            {if $WarenkorbArtikelAnzahl >= 1}
                <span class="sr-only">{lang key="quantity" section="checkout"}: </span>
                <span class="shopnav__badge badge badge-pill icon-text--center">
                    {$WarenkorbArtikelAnzahl}
                </span>
            {/if}
            {*
            <span class="shopping-cart-label">{$WarensummeLocalized[$NettoPreise]}</span>
            *}
        </a>
        {/strip}
    {else}

        {strip}
        <ul class="header-shop-nav nav horizontal">
            <li class="nav-item cart-menu dropdown{* {if $WarenkorbArtikelanzahl >= 1} items{/if} *}{if $nSeitenTyp == 3} current{/if}{if $admorris_pro_templateSettings->miniCartDisplay === "sidebar"} cart-menu--sidebar{else} cart-menu--dropdown{/if}" data-toggle="basket-items">
                {include file='basket/cart_dropdown_label.tpl'}
            </li>
        </ul>
        {/strip}

    {/if}
{/block}