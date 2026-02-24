{*custom*}
{block name='layout-header-shop-nav-wish'}
    {if !empty($wishlists) && $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
        {if !isset($headerLayout)}
            {$headerLayout = $admPro->initHeaderLayout()}
        {/if}
        {if !isset($layoutType)}
            {$layoutType = 'desktopLayout'}
        {/if}
        {$labelSetting = $headerLayout->getItemSetting('wishlist', 'label', $layoutType)}
        {if !$labelSetting}
            {$labelSetting = 'icon'}
        {/if}
        {$showIcon = ($labelSetting|in_array:['icon', 'icon_text'])?true:false}
        {$showLabel = ($labelSetting|in_array:['text', 'icon_text'])?true:false}
        {$iconSpacing = ($labelSetting === 'icon_text')?' ':''}

        {$wlCount = 0}
        {if \JTL\Session\Frontend::getWishlist()->getID() > 0}
            {$wlCount = \JTL\Session\Frontend::getWishlist()->getItems()|count}
        {/if}

        {function name='wishlistButton'}
            {if $layoutType === 'desktopLayout'}
                <button class="btn nav-link dropdown-toggle shopnav__link" data-toggle="dropdown" data-display="static" title="{lang key='wishlist'}" aria-expanded="false">
                    {$content}
                </button>
            {else}
                <a href="{get_static_route id='wunschliste.php'}" title="{lang key="goToWishlist" section="global"}" class="nav-link shopnav__link link_to_wishlist">
                    {$content}
                </a>
            {/if}
        {/function}

        {capture 'wishlistButtonContent'}
            {if $showIcon}
                {$admIcon->renderIcon('heart', 'icon-content icon-content--default icon-content--center shopnav__icon')}
            {/if}
            {$iconSpacing}
            <span class="shopnav__label icon-text--center{if !$showLabel} sr-only{/if}">{lang key="wishlist" section="global"}</span>
            <span class="shopnav__badge badge badge-pill{if $wlCount === 0} d-none{/if}">
                {$wlCount}
            </span>
        {/capture}

        {strip}
            <ul class="header-shop-nav nav horizontal">
                <li class="shop-nav-wish nav-item dropdown{if $nSeitenTyp === $smarty.const.PAGE_WUNSCHLISTE} active{/if}">
                    {block name='layout-header-shop-nav-wish-link'}
                        {wishlistButton content=$smarty.capture.wishlistButtonContent}
                    {/block}
                    {if $layoutType === 'desktopLayout'}
                        {block name='layout-header-shop-nav-wish-dropdown'}
                            <div id="nav-wishlist-collapse" class="dropdown-menu dropdown-menu-right lg-min-w-lg">
                                <div id="wishlist-dropdown-container">
                                    {block name='layout-header-shop-nav-wish-include-wishlist-dropdown'}
                                        {include file='snippets/wishlist_dropdown.tpl'}
                                    {/block}
                                </div>
                            </div>
                        {/block}
                    {/if}
                </li>
            </ul>
        {/strip}
    {/if}
{/block}