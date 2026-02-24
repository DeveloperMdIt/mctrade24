{*custom*}
{block name='productlist-result-options'}
    {assign var=contentFilters value=$NaviFilter->getAvailableContentFilters()}
    {assign var=show_filters value=$Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab == 0
        || $NaviFilter->getSearchResults()->getProductCount() >= $Einstellungen.artikeluebersicht.suchfilter_anzeigen_ab
        || $NaviFilter->getFilterCount() > 0}
<div id="result-options" class="result-options {if !$show_filters}d-none d-sm-block{/if}">
    <div class="result-options__row">

        {* offcanvas filter button *}
        {has_boxes position='left' assign='hasLeftBox'}
        <div class="result-options__nav">
            {if !$bExclusive && $nSeitenTyp === 2 && $hasLeftBox && isset($boxes) && !empty($boxes.left)}
                {lang key="productlistFilterButton" section="custom" assign='filterButtonText'}
            
                <button 
                    class="filter-sidebar-button btn btn-link offcanvas-toggle{if !$admorris_pro_templateSettings->floating_filter_sidebar} d-lg-none filter-sidebar-button--mobile-only{/if}" 
                    type="button" 
                    data-toggle="modal" 
                    data-target="#productlist-sidebar-offcanvas" 
                    aria-controls="productlist-sidebar-offcanvas"
                    aria-expanded="false"
                >
                    {$admIcon->renderIcon('filter', 'icon-content icon-content--default')} {$filterButtonText}
                </button>
            {/if}
            
            {include "layout/breadcrumb.tpl" hideOnMobile=true}
        </div>
            
            {if $NaviFilter->getSearchResults()->getProductCount() > 0}
                <div class="list-pageinfo">
                    <div class="page-total">
                        <span>{* {lang key="products" section="global"}  *}{$NaviFilter->getSearchResults()->getOffsetStart()} - {$NaviFilter->getSearchResults()->getOffsetEnd()} / {* {lang key="of" section="productOverview"}  *}{$NaviFilter->getSearchResults()->getProductCount()}</span>
                    </div>
                </div>
            {/if}

        {block name='productlist-result-options-sort'}
            <div class="{* form-group  *}dropdown filter-type-FilterItemSort">
                <button type="button" class="filter-dropdown__button | btn {* btn-default  *}dropdown-toggle form-control" data-toggle="dropdown" aria-expanded="false">
                    {lang key='sorting' section='productOverview'} {$admIcon->renderIcon('caretDown', 'icon-content icon-content--default caret-custom')}
                </button>
                <ul class="dropdown-menu">
                    {foreach $Suchergebnisse->getSortingOptions() as $option}
                    <li class="filter-item{if $option->isActive()} active{/if}">
                        <a class="dropdown-item" rel="nofollow" href="{$option->getURL()}">{$option->getName()}</a>
                    </li>
                    {/foreach}
                </ul>
            </div>
            <div class="{* form-group  *}dropdown filter-type-FilterItemLimits">
                <button type="button" class="filter-dropdown__button | btn {* btn-default  *}dropdown-toggle form-control" data-toggle="dropdown" aria-expanded="false">
                    {lang key='productsPerPage' section='productOverview'} {$admIcon->renderIcon('caretDown', 'icon-content icon-content--default caret-custom')}
                </button>
                <ul class="dropdown-menu">
                    {foreach $Suchergebnisse->getLimitOptions() as $option}
                        <li class="filter-item{if $option->isActive()} active{/if}">
                            <a class="dropdown-item" rel="nofollow" href="{$option->getURL()}">{$option->getName()}</a>
                        </li>
                    {/foreach}
                </ul>
            </div>
            {if $show_filters && $contentFilters && count($contentFilters) > 0}

                    {include "productlist/filter.tpl"}
            {/if}
            
            {* <div class="form-group">
                <select name="af" onchange="$('#improve_search').submit();" class="form-control form-small">
                    <option value="0"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 0} selected="selected"{/if}>{lang key="productsPerPage" section="productOverview"}</option>
                    <option value="9"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 9} selected="selected"{/if}>9 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="18"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 18} selected="selected"{/if}>18 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="30"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 30} selected="selected"{/if}>30 {lang key="productsPerPage" section="productOverview"}</option>
                    <option value="90"{if isset($smarty.session.ArtikelProSeite) && $smarty.session.ArtikelProSeite == 90} selected="selected"{/if}>90 {lang key="productsPerPage" section="productOverview"}</option>
                </select>
            </div> *}
            {if isset($oErweiterteDarstellung->nDarstellung) && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung === 'Y' && empty($AktuelleKategorie->getCategoryFunctionAttribute('darstellung'))}
                <div class="btn-group">
                    <a href="{$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE]}"
                       id="ed_list"
                       class="{* custom button class *}list-view-options__button btn{*  btn-default *} btn-option ed list{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_LISTE} active{/if}"
                       role="button" title="{lang key='list' section='productOverview'}" 
                       aria-label="{lang key='list' section='productOverview'}">
                        {$admIcon->renderIcon('list', 'icon-content icon-content--default icon-content--center')}
                    </a>
                    <a href="{$oErweiterteDarstellung->cURL_arr[$smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE]}"
                       id="ed_gallery"
                       class="{* custom button class *}list-view-options__button btn {* btn-default *} btn-option ed gallery{if $oErweiterteDarstellung->nDarstellung === $smarty.const.ERWDARSTELLUNG_ANSICHT_GALERIE} active{/if}"
                       role="button"
                       title="{lang key='gallery' section='productOverview'}" 
                       aria-label="{lang key='gallery' section='productOverview'}">
                        {$admIcon->renderIcon('grid', 'icon-content icon-content--default icon-content--center')}
                    </a>
                </div>
                {elseif ($AktuelleKategorie->getCategoryFunctionAttribute('darstellung'))}
                {if ($AktuelleKategorie->getCategoryFunctionAttribute('darstellung') == 1)}{$listType = "list"}{elseif ($AktuelleKategorie->getCategoryFunctionAttribute('darstellung') == 2)}{$listType = "gallery"}{elseif ($AktuelleKategorie->getCategoryFunctionAttribute('darstellung') == 3)}{$listType = "mosaic"}{/if}
                <input type="hidden" id="product-list-type" value="{$listType}">
            {/if}
        {/block}
            
        {* </div> *}
        {* {if $show_filters}
            <div class="filter-collapsible-control">
                <a class="btn btn-default" data-toggle="collapse" href="#filter-collapsible" aria-expanded="false" aria-controls="filter-collapsible">
                    <span class="fa fa-filter"></span> {lang key='filterBy' section='global'}
                    <span class="caret"></span>
                </a>
            </div>
        {/if} *}
    </div>{* /row *}
    {include 'productlist/active_filters.tpl'}
    
</div>
{/block}