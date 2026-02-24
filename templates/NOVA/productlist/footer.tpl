{block name='productlist-footer'}
    {assign var=Suchergebnisse value=$NaviFilter->getSearchResults(false)}
    {block name='productlist-footer-include-productlist-page-nav'}
        {include file='snippets/productlist_page_nav.tpl' navid='footer' hrTop=true}
    {/block}

    {* — Hier kommt Dein Langtext aus der Wawi — *}
    {assign var="lt" value=$AktuelleKategorie->getCategoryFunctionAttribute('LangtextKategorie')}
    {* oder, falls es als Objekt mit cWert ankommt: *}
    {* {assign var="lt" value=$AktuelleKategorie->getCategoryFunctionAttribute('LangtextKategorie')->cWert} *}
    {if $lt|@strlen}
        <div class="category-longtext">
            {$lt nofilter}
        </div>
    {/if}
{/block}
