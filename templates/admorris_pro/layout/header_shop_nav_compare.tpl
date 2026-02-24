{*custom*}
{block name='layout-header-shop-nav-compare'}
    {if !isset($Einstellungen.vergleichsliste.vergleichsliste_anzeigen) || $Einstellungen.vergleichsliste.vergleichsliste_anzeigen === 'Y'}
        {if !isset($headerLayout)}
            {* when the cart dropdown is refreshed and rendered via AJAX *}
            {$headerLayout = $admPro->initHeaderLayout()}
        {/if}
        {if !isset($layoutType)}
            {$layoutType = 'desktopLayout'}
        {/if}
        {$labelSetting = $headerLayout->getItemSetting('comparelist', 'label', $layoutType)}
        {if !$labelSetting}
            {$labelSetting = 'icon'}
        {/if}
        {$showIcon = ($labelSetting|in_array:['icon', 'icon_text'])?true:false}
        {$showLabel = ($labelSetting|in_array:['text', 'icon_text'])?true:false}
        {$iconSpacing = ($labelSetting === 'icon_text')?' ':''}

        {$productCount = count(JTL\Session\Frontend::getCompareList()->oArtikel_arr)}

        {function name='comparelistButton'}
            {if $layoutType === 'desktopLayout'}
                <button class="btn nav-link dropdown-toggle shopnav__link" data-toggle="dropdown" data-display="static" title="{lang key='compare'}" aria-expanded="false">
                    {$content}
                </button>
            {else}
                <a href="{get_static_route id='vergleichsliste.php'}" title="{lang key="goToCompareList" section="comparelist"}" class="nav-link shopnav__link link_to_comparelist">
                    {$content}
                </a>
            {/if}
        {/function}

        {capture 'comparelistButtonContent'}
            {if $showIcon}
                {$admIcon->renderIcon('tasks', 'icon-content icon-content--default icon-content--center shopnav__icon')}
            {/if}
            {$iconSpacing}
            <span class="shopnav__label icon-text--center{if !$showLabel} sr-only{/if}">{lang key="compare" section="global"}</span>
            <span class="shopnav__badge badge badge-pill{if $productCount === 0} d-none{/if}">
                {$productCount}
            </span>
        {/capture}

        {strip}
            <ul class="header-shop-nav nav horizontal">
                <li class="shop-nav-compare nav-item dropdown{if $nSeitenTyp === $smarty.const.PAGE_VERGLEICHSLISTE} active{/if}{if $productCount === 0} d-none{/if}">
                    {block name='layout-header-shop-nav-compare-link'}
                        {comparelistButton content=$smarty.capture.comparelistButtonContent}
                    {/block}
                    {block name='layout-header-shop-nav-compare-dropdown'}
                        <div id="comparelist-dropdown-container" class="dropdown-menu dropdown-menu-right lg-min-w-lg">
                            <div id='comparelist-dropdown-content'>
                                {block name='layout-header-shop-nav-compare-include-comparelist-dropdown'}
                                    {include file='snippets/comparelist_dropdown.tpl'}
                                {/block}
                            </div>
                        </div>
                    {/block}
                </li>
            </ul>
        {/strip}
    {/if}
{/block}