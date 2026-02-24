{*custom*}
{block name='productlist-index'}
    {block name='header'}
        {if !isset($bAjaxRequest) || !$bAjaxRequest}
            {include file='layout/header.tpl'}
        {/if}
    {/block}

    {block name='content'}
        <div id="result-wrapper" data-wrapper="true">
            {block name='productlist-header'}
            {include file='productlist/header.tpl'}
            {/block}

            {assign var='style' value='gallery'}
            {assign var='grid' value='col-6 col-xl-4'}

            {* {assign var='grid' value='col-6 col-md-4 col-lg-3 col-xl-2'} *}

            {$grid_columns = $admorris_pro_templateSettings->productlist_item_count}

                {if $grid_columns === '1'}
                    {$grid = 'col-12'}
                {elseif $grid_columns === '2'}
                    {$grid = 'col-6'}
                {elseif $grid_columns === '3'}
                    {$grid = 'col-6 col-md-4'}
                {/if}
            {if $admorris_pro_templateSettings->floating_filter_sidebar}
                {if $grid_columns === '4'}
                    {$grid = 'col-6 col-md-4 col-lg-3'}
                    
                {elseif $grid_columns === '5'}
                    {$grid = 'col-6 col-md-4 col-lg-3 col-xl-1/5'}
                {elseif $grid_columns === '6'}
                    {$grid = 'col-6 col-md-4 col-lg-1/5 col-xl-2'}
                {/if}
            {else}
                {if $grid_columns === '4'}
                    {$grid = 'col-6 col-md-4 col-lg-6 col-xl-4 col-xxl-3'}
                {elseif $grid_columns === '5'}
                    {$grid = 'col-6 col-md-4  col-xl-1/5'}
                {elseif $grid_columns === '6'}
                    {$grid = 'col-sm-6 col-md-4 col-xxl-2'}
                {/if}
                
            {/if}

            {*Prio: -> Funktionsattribut -> Benutzereingabe -> Standarddarstellung*}
            {if (!empty($AktuelleKategorie->getCategoryFunctionAttribute('darstellung'))
                && $AktuelleKategorie->getCategoryFunctionAttribute('darstellung') == 1)
                || (empty($AktuelleKategorie->getCategoryFunctionAttribute('darstellung'))
                    && ((!empty($oErweiterteDarstellung->nDarstellung) && $oErweiterteDarstellung->nDarstellung == 1)
                        || (empty($oErweiterteDarstellung->nDarstellung)
                            && isset($Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung_stdansicht)
                            && $Einstellungen.artikeluebersicht.artikeluebersicht_erw_darstellung_stdansicht == 1))
            )}
                {assign var='style' value='list'}
                {assign var='grid' value=''}
                {* {assign var='grid' value='col-12'} *}
            {/if}
            {if !empty($Suchergebnisse->getError())}
                <p class="alert alert-danger">{$Suchergebnisse->getError()}</p>
            {/if}

            {if isset($oBestseller_arr) && $oBestseller_arr|@count > 0}
                {block name='productlist-bestseller'}
                    {opcMountPoint id='opc_before_bestseller'}
                        {lang key='bestseller' section='global' assign='slidertitle'}
                        {include file='snippets/product_slider.tpl' id='slider-top-products'
                            productlist=$oBestseller_arr title=$slidertitle}
                {/block}
            {/if}

            {if !empty($Suchergebnisse->getProducts())}
                {has_boxes position='left' assign='hasLeftBox'}
                {$sidebarGrid = !$bExclusive && !$admorris_pro_templateSettings->floating_filter_sidebar && $hasLeftBox && isset($boxes) && !empty($boxes.left)}

                <div class="productlist__row d-flex">
                    {block name=imagesLoadingAnimation}
                        {$loadingAnimation = $admorris_pro_templateSettings->image_preload_animation && !empty($Suchergebnisse->getProducts())}
                        
                        {if $loadingAnimation}
                            <div class="productlist__loader">
                                {include 'components/loading_animation.tpl'}
                            </div>
                        {/if}
                    {/block}


                    <div class="productlist__results-wrapper{if $loadingAnimation} is-loading{/if}">
                        {if $Suchergebnisse->getPages()->getMaxPage() > 1 && $filterPagination->getPrev()->getPageNumber() > 0}
                            {include file="productlist/pagination/index.tpl" tplScope="prev"}
                        {/if}
                    {block name='productlist-results'}
                        {if $Suchergebnisse->getProducts()|@count > 0}
                            {opcMountPoint id='opc_before_products'}
                            <div class="{if $style !== 'list'}row{else}stack  {/if} {$style} productlist--{$style}" id="product-list">
                                {$wrapperHoverClass = ($Einstellungen.template.productlist.hover_productlist === 'Y')?' product-wrapper--hover-enabled':' product-wrapper--hover-disabled'}
                    
                                {foreach $Suchergebnisse->getProducts() as $Artikel}
                                    <div class="product-wrapper {$grid}{$wrapperHoverClass}">
                                        {if $style === 'list'}
                                            {include file='productlist/item_list.tpl' tplscope=$style}
                                        {else}
                                            {include file='productlist/item_box.tpl' tplscope=$style class='product-cell--gallery'}
                                        {/if}
                                    </div>
                                {/foreach}
                            </div>
                        {/if}
                    {/block}

                    {block name='productlist-include-footer'}
                        {include file='productlist/footer.tpl'}
                    {/block}
                    </div>
                {if $sidebarGrid}
                {* fixed filter sidebar *}
                    <div class="left-sidebar-boxes productlist__sidebar productlist__sidebar--fixed d-none d-lg-block" id="productlist-sidebar">
                        {block name="aside"}
                            {block name="footer-sidepanel-left"}
                            <aside id="sidepanel_left" class="left-sidebar-boxes productlist__sidepanel{if !$admorris_pro_templateSettings->floating_filter_sidebar} productlist__sidepanel--fixed{/if} d-print-none">
                                {block name="footer-sidepanel-left-content"}{$boxes.left}{/block}
                            </aside>
                            {/block}
                        {/block}
                    </div>
                    {* Moving the sidebar boxes depending on the current screen width *}
                    {if !isset($bAjaxRequest) || !$bAjaxRequest}
                        <script type="module">
                            $(function() {
                                var sidebarBoxes = $('#sidepanel_left > *'),
                                    sidebar = $('#sidepanel_left'),
                                    offcanvasSidebar = $('#sidepanel_left_offcanvas'),
                                    initial = true;

                                var mediaQueryList = window.matchMedia("(max-width: 991px)");

                                function handleMovingSidebarBoxes(evt) {
                                    if (evt.matches) {
                                        initial = false;
                                        offcanvasSidebar.append(sidebarBoxes);
                                    } else if(!initial) {
                                        sidebar.append(sidebarBoxes);
                                    }
                                }

                                handleMovingSidebarBoxes(mediaQueryList);
                                mediaQueryList.addListener(handleMovingSidebarBoxes);
                            });
                        </script>
                    {/if}
                {/if}
                </div> {* //.product-list__row *}
            {/if}
        </div>
    {/block}

    {block name='footer'}
        {if !isset($bAjaxRequest) || !$bAjaxRequest}
            {include file='layout/footer.tpl'}
        {/if}
    {/block}
{/block}