{*custom*}
{block name='snippets-linkgroup-list'}
    {if isset($linkgroupIdentifier)}
    {strip}
        {assign var=checkLinkParents value=false}
        {if isset($Link) && $Link->getID() > 0}
            {assign var=activeId value=$Link->getID()}
        {elseif \JTL\Shop::$kLink > 0}
            {assign var=activeId value=\JTL\Shop::$kLink}
            {assign var=Link value=\JTL\Shop::Container()->getLinkService()->getLinkByID($activeId)}
        {/if}
        {if !isset($activeParents) && (isset($Link))}
            {assign var=activeParents value=\JTL\Shop::Container()->getLinkService()->getParentIDs($activeId)}
            {assign var=checkLinkParents value=true}
        {/if}
        {get_navigation linkgroupIdentifier=$linkgroupIdentifier assign='links'}
        {if !empty($links)}
            {foreach $links as $li}
                {$isActive = ''} 
                {if $li->getIsActive() || ($checkLinkParents === true 
                    && isset($activeParents) 
                    && in_array($li->getID(), $activeParents))}
                    {$isActive = ' active'} 
                {/if}
                {$hoverClass = ''}
                {if $tplscope === 'megamenu' && $li->getChildLinks()->count() > 0}
                    {$hoverClass = ' bs-hover-enabled'}{/if}
                {$dropdownClasses = ''}
                {$isDropdown = false}
                {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)}
                    {$dropdownClasses = 'has-dropdown dropdown-multi'}
                    {$isDropdown = true}
                {/if}
            <li class="nav-item {if $tplscope === 'megamenu'}nav-scrollbar-item{/if} {$dropdownClasses}{$isActive}{$hoverClass}">
                    {if isset($li->getName())}
                        <a href="{$li->getURL()}"{if $li->getNoFollow()} rel="nofollow"{/if}{if !empty($li->getTitle())} title="{$li->getTitle()}"{/if} class="nav-link{if $isDropdown} dropdown-toggle{/if}" {if $isDropdown} data-toggle="dropdown" aria-expanded="false" data-display="static"{/if}>
                            <span class="icon-text--center">{$li->getName()}</span>
                            {if $li->getChildLinks()->count() > 0 && isset($dropdownSupport)} {$caret }{* {$admIcon->renderIcon('chevronDown', 'icon-content icon-content--default icon-content--center icon-content--toggle')} *}{/if}
                        </a>
                        {if $li->getChildLinks()->count() > 0}
                            {if $tplscope !== 'megamenu'}
                                <ul class="{if isset($dropdownSupport)}{if $tplscope !== 'megamenu' && $tplscope !== 'offcanvas'}inline {/if}dropdown-menu keepopen{else}submenu list-unstyled{/if}">
                                    {foreach $li->getChildLinks() as $subli}
                                        {if !empty($subli->getName())}
                                            <li class="{if $subli->getIsActive() || ($checkLinkParents === true && isset($activeParents) && in_array($subli->getID(), $activeParents))} active{/if}">
                                                <a class="nav-link" href="{$subli->getURL()}"{if $subli->getNoFollow()} rel="nofollow"{/if}{if !empty($subli->getTitle())} title="{$subli->getTitle()}"{/if}>
                                                    {$subli->getName()}
                                                </a>
                                            </li>
                                        {/if}
                                    {/foreach}
                                </ul>
                            {else}
                                {$columns = $admPro->subcategories_columns_count($li->getChildLinks(), false)}
                                <div class="{if isset($dropdownSupport)}dropdown-menu {if $tplscope === 'megamenu'} dropdown-menu--megamenu{/if} keepopen{/if}{if isset($dropdownAnimation)} dropdown-menu--animated{/if}{if $admorris_pro_themeVars->headerDropdownMenuWidth === 'full-width'} dropdown-menu--full-width{/if}">
                                    <div class="megamenu-content{if $columns > 1} columns columns-{$columns}{/if}{if $admorris_pro_themeVars->headerDropdownMenuWidth === 'full-width'} admPro-container {$admPro->header_container_size()}{/if}">
                                        <div class="megamenu-content__row">
                                            <a href="{$li->getURL()}"{if $li->getNoFollow()} rel="nofollow"{/if} class="keyboard-focus-link h3 megamenu-content__category-title" >
                                                {$li->getName()}
                                            </a>
                                            {foreach $li->getChildLinks() as $subli}
                                                {if !empty($subli->getName())}
                                                    <div class="megamenu-content__item">
                                                        <a href="{$subli->getURL()}"{if $subli->getNoFollow()} rel="nofollow"{/if}{if !empty($subli->getTitle())} title="{$subli->getTitle()}"{/if}>
                                                            {$subli->getName()}
                                                        </a>
                                                    </div>
                                                {/if}
                                            {/foreach}
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        {/if}
                    {/if}
                </li>
            {/foreach}
        {/if}
    {/strip}
    {/if}
{/block}