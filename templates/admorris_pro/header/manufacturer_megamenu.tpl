{*custom*}
{block name='megamenu-manufacturers'}
    {if ($Einstellungen.global.global_sichtbarkeit != 3
            || isset($smarty.session.Kunde->kKunde)
            && $smarty.session.Kunde->kKunde != 0)}
        {get_manufacturers assign='manufacturers'}
        {if !empty($manufacturers)}
            {if $layoutType === 'desktopLayout'}
                <li class="header__manufacturer-dropdown nav-scrollbar-item nav-item has-dropdown megamenu-fw{if $NaviFilter->hasManufacturer() || $nSeitenTyp == PAGE_HERSTELLER} active{/if}">
                    {assign var='linkKeyHersteller' value=\JTL\Shop::Container()->getLinkService()->getSpecialPageID(LINKTYP_HERSTELLER, false)|default:0}
                    {assign var='linkSEOHersteller' value=\JTL\Shop::Container()->getLinkService()->getLinkByID($linkKeyHersteller)|default:null}
                    {assign var=manufacturerOverview value=null}
                    {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_HERSTELLER])}
                        {$manufacturerOverview=$oSpezialseiten_arr[$smarty.const.LINKTYP_HERSTELLER]}
                    {/if}
                    <a href="{if $manufacturerOverview !== null}{$manufacturerOverview->getURL()}{else}#{/if}" class="dropdown-toggle nav-link" data-toggle="dropdown" aria-expanded="false" aria-controls="manufacturers-dropdown">
                        <span class="text-truncate icon-text--center">
                            {if $manufacturerOverview !== null && !empty($manufacturerOverview->getName())}
                                {$manufacturerOverview->getName()}
                            {else}
                                {lang key='manufacturers'}
                            {/if}
                        </span>
                        {$admIcon->renderIcon('chevronDown', 'icon-content icon-content--center icon-content--toggle')}
                    </a>
                    <div id="manufacturers-dropdown" class="dropdown-menu dropdown-menu--megamenu{if $admorris_pro_themeVars->headerDropdownMenuWidth === 'full-width'} dropdown-menu--full-width {/if}">
                        {$columns = $admPro->subcategories_columns_count($manufacturers, false)}
                        
                        <div class="megamenu-content{if $columns > 1} columns columns-{$columns}{/if}{if $admorris_pro_themeVars->headerDropdownMenuWidth === 'full-width'} admPro-container {$admPro->header_container_size()}{/if}">
                            <nav aria-label="{lang key='manufacturers'}" class="megamenu-content__row">
                                {if $manufacturerOverview !== null}
                                    <a class="keyboard-focus-link h3 megamenu-content__category-title" href="{$manufacturerOverview->getURL()}">
                                        {if !empty($manufacturerOverview->getName())}
                                            {$manufacturerOverview->getName()}
                                        {else}
                                            {lang key='manufacturers'}
                                        {/if}
                                    </a>
                                {/if}
                                {foreach $manufacturers as $mft}
                                    <div class="megamenu-content__item">
                                        {link href=$mft->getURL() title=$mft->getName()|escape:'html' class='submenu-headline submenu-headline-toplevel'}
                                            <span>{$mft->getName()}</span>
                                        {/link}
                                    </div>
                                {/foreach}
                            </nav>
                        </div>{* /megamenu-content *}
                    </div>
                </li>
            {else}
                {* mobile version without dropdown *}

                <li class="header__manufacturer-dropdown dropdown{if $NaviFilter->hasManufacturer() || $nSeitenTyp == PAGE_HERSTELLER} active{/if}">
                    {assign var='linkKeyHersteller' value=\JTL\Shop::Container()->getLinkService()->getSpecialPageID(LINKTYP_HERSTELLER)|default:0}
                    {assign var='linkSEOHersteller' value=\JTL\Shop::Container()->getLinkService()->getLinkByID($linkKeyHersteller)|default:null}
                    {if !empty($linkKeyHersteller)}{assign var="linkSEOHersteller" value=JTL\Shop::Container()->getLinkService()->getPageLinkLanguage($linkKeyHersteller)}{/if}
                    {if $linkSEOHersteller !== null && !empty($linkSEOHersteller->getName())}
                        <a href="{$linkSEOHersteller->getURL()}" class="dropdown-toggle" data-toggle="dropdown">
                            {$linkSEOHersteller->getName()}
                                {$admIcon->renderIcon('chevronDown', 'icon-content icon-content--center icon-content--toggle')}
                        </a>
                    {else}
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            {lang key="manufacturers" section="global"}
                                {$admIcon->renderIcon('chevronDown', 'icon-content icon-content--center icon-content--toggle')}
                        </a>
                    {/if}
                    <ul class="dropdown-menu">                 
                        {foreach name=hersteller from=$manufacturers item=hst} 
                            <li><a href="{$hst->getSeo()}"><span>{$hst->getName()}</span></a></li>
                        {/foreach}
                    </ul>
                </li>
            {/if}
        {/if}
    {/if}
{/block}{* megamenu-manufacturers *}