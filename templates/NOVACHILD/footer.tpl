{extends file="{$parent_template_path}/productlist/footer.tpl"}

{* Alles, was hier im Block productlist-footer angehängt wird, landet ganz zuletzt auf der Seite *}
{block name='productlist-footer' append}
    {assign var="lt" value=$AktuelleKategorie->getCategoryFunctionAttribute('LangtextKategorie')}
    {if $lt && $lt|@strlen}
        <div class="category-longtext">
            {$lt nofilter}
        </div>
    {/if}
{/block}
