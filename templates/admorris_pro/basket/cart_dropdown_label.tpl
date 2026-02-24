{*custom*}
{block name='basket-cart-dropdown-label'}
    {if !isset($headerLayout)} 
        {* when the cart dropdown is refreshed and rendered via AJAX *}
        {$headerLayout = $admPro->initHeaderLayout()}
    {/if}
    {if !isset($layoutType)}
        {$layoutType = 'desktopLayout'}
    {/if}
        
    {$labelSetting = $headerLayout->getItemSetting('cart', 'label', $layoutType)}
    {if !$labelSetting}
        {$labelSetting = 'icon'}
    {/if}
    {$showIcon = ($labelSetting|in_array:['icon', 'icon_text'])?true:false}
    {$showLabel = ($labelSetting|in_array:['text', 'icon_text'])?true:false}
    {$iconSpacing = ($labelSetting === 'icon_text')?' ':''}



    {function name='cartButton'}
        <button id="cart-dropdown-button" class="btn nav-link dropdown-toggle shopnav__link" data-toggle="dropdown" data-display="static" title="{lang key='basket'}" aria-expanded="false">
            {$content}
        </button>
    {/function}

    {capture 'cartButtonContent'}
        {if $showIcon}
            {$admIcon->renderIcon('shoppingCart', 'icon-content icon-content--default icon-content--center shopnav__icon')}
        {/if}
        {$iconSpacing}
        <span class="shopnav__label icon-text--center{if !$showLabel} sr-only{/if}">{lang key='basket'}</span>
        {if $WarenkorbArtikelAnzahl >= 1}
            <span class="shopnav__badge badge badge-pill icon-text--center">
                <span class="sr-only">{lang key="quantity" section="checkout"}: </span>{$WarenkorbArtikelAnzahl}
            </span>
        {/if}
        {if $WarensummeLocalized[$NettoPreise]|substr:0:1 != '0'}
            <span class="shopping-cart-label d-none d-lg-block icon-text--center"> {$WarensummeLocalized[$NettoPreise]}</span>
        {/if}
    {/capture}

    {strip}
        {block name='basket-cart-dropdown-label-button'}
            {cartButton content=$smarty.capture.cartButtonContent}
        {/block}
        {block name='basket-cart-dropdown-label-include-cart-dropdown'}
            <div id="cart-dropdown-container" class="cart-dropdown dropdown-menu dropdown-menu-right lg-min-w-lg p-0 dropdown-menu--animated" aria-labelledby="cart-dropdown-button">
                {include file='basket/cart_dropdown.tpl'}
            </div>
        {/block}
    {/strip}
{/block}