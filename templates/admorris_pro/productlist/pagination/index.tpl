{block 'productlist-pagination-index'}
    {if ($admorris_pro_templateSettings->paginationType >= 1)}
        {include file="productlist/pagination/load_more.tpl"}
    
    {else}
        {*only incude standard pagination after *}
        {if $tplScope === "next"}
            {include file="productlist/pagination/productlist_page_nav.tpl" navid='footer' hrTop=true}
        {/if}
    {/if}
{/block}