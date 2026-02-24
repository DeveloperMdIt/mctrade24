{*custom*}
{block name='productlist-footer'}
{assign var=Suchergebnisse value=$NaviFilter->getSearchResults(false)}
{if $Suchergebnisse->getProducts()|@count > 0}
    {if $Einstellungen.navigationsfilter.suchtrefferfilter_nutzen === 'Y'
        && $Suchergebnisse->getSearchFilterOptions()|@count > 0
        && $Suchergebnisse->getSearchFilterJSON()
        && !$NaviFilter->hasSearchFilter()}
        <hr>
        <div class="card  tags search-terms">
            <div class="card-header">{lang key='productsSearchTerm' section='productOverview'}</div>
            <div class="card-body">
                {foreach $Suchergebnisse->getSearchFilterOptions() as $oSuchFilter}
                    <a href="{$oSuchFilter->getURL()}" class="badge badge-primary tag{$oSuchFilter->getClass()}">{$oSuchFilter->getName()}</a>
                {/foreach}
            </div>
        </div>
    {/if}
{/if}
{if $Suchergebnisse->getPages()->getMaxPage() > 1}
    {opcMountPoint id='opc_before_footer'}

    {include file="productlist/pagination/index.tpl" tplScope="next"}

{/if}
{/block}