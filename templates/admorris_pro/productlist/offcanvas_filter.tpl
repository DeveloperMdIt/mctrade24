{* Offcanvas Filter Sidebar *}

{block 'productlist-offcanvas-filter'}
    
    
    {* The offcanvas sidebar is also used as mobile filter sidebar when the floating filter sidebar is deactivated *}
    {$offcanvasSidebar = !$bExclusive && $nSeitenTyp === 2 && $hasLeftBox && isset($boxes) && !empty($boxes.left) && count($Suchergebnisse->getProducts()) > 0}
    {* $admorris_pro_templateSettings->floating_filter_sidebar === 'Y' *}

    {if $offcanvasSidebar}
        {block 'productlist-offcanvas-filter-sidebar'}
            <div class="productlist__sidebar js-filter-sidebar-offcanvas offcanvas-nav offcanvas-nav--filter modal" id="productlist-sidebar-offcanvas" tabindex="-1" aria-modal="true" role="dialog" aria-label="filter sidebar">
            <div class="modal-dialog">
                {block name="productlist-offcanvas-filter-aside"}
                    {block name="footer-sidepanel-left"}
                    <aside id="sidepanel_left_offcanvas" class="modal-content navbar-offcanvas left-sidebar-boxes productlist__sidepanel d-print-none rounded-0">
                        <div class="text-right">
                            <button class="navbar-toggler collapsed" type="button" data-toggle="modal" data-target="#productlist-sidebar-offcanvas" aria-controls="productlist-sidebar-offcanvas" aria-label="Toggle filters">
                                {block 'productlist-offcanvas-filter-close-button-icon'}
                                    {$admIcon->renderIcon('cross', 'icon-content icon-content--default')}
                                {/block}
                            </button>
                        </div>
                        {block name="footer-sidepanel-left-content"}
                        {if $admorris_pro_templateSettings->floating_filter_sidebar}
                            {$boxes.left}
                        {else}
                        {* when offcanvas is off the boxes need to be duplicated and the id and data-target need to be changed to make the button work *}
                        <div id="offcanvas-filter"></div>
                        {/if}
                        {/block}
                    </aside>
                    {/block}
                
                {/block}
            </div>
            </div>
        {/block}
    {* </div> *}
    {/if}

{/block}
