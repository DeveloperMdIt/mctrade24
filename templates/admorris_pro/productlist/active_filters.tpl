{*custom - new partial not existing in Evo*}
{block 'productlist-active-filters'}
{if $NaviFilter->getFilterCount() > 0}
    {* <div class="clearfix top10"></div> *}
    <div class="active-filters{*  card  *}">
        <div class="{* card-body *}">
            {foreach $NaviFilter->getActiveFilters() as $activeFilter}
                {assign var=activeFilterValue value=$activeFilter->getValue()}
                {assign var=activeValues value=$activeFilter->getActiveValues()}
                {if $activeFilterValue !== null}
                    {if $activeValues|is_array}
                        {foreach $activeValues as $filterOption}
                            {strip}
                                <a href="{$activeFilter->getUnsetFilterURL($filterOption->getValue())}" rel="nofollow" title="Filter {lang key='delete'}" class="badge badge-info filter-type-{$activeFilter->getNiceName()}">
                                    {if $Einstellungen.navigationsfilter.merkmal_label_anzeigen === 'Y'
                                    && $activeFilter->getNiceName() === 'Characteristic'}
                                        {$activeFilter->getFilterName()}:&nbsp;
                                    {/if}
                                    {$filterOption->getFrontendName()}&nbsp;{$admIcon->renderIcon('trash', 'icon-content icon-content--default')}
                                </a>
                            {/strip}
                        {/foreach}
                    {else}
                        {strip}
                            <a href="{$activeFilter->getUnsetFilterURL($activeFilter->getValue())}" rel="nofollow" title="Filter {lang key='delete'}" class="badge badge-info filter-type-{$activeFilter->getNiceName()}">
                                {if $Einstellungen.navigationsfilter.merkmal_label_anzeigen === 'Y'
                                && $activeFilter->getNiceName() === 'Characteristic'}
                                    {$activeFilter->getFilterName()}:&nbsp;
                                {/if}
                                {$activeValues->getFrontendName()}&nbsp;{$admIcon->renderIcon('trash', 'icon-content icon-content--default')}
                            </a>
                        {/strip}
                    {/if}
                {/if}
            {/foreach}
            {if $NaviFilter->getURL()->getUnsetAll() !== null}
                {strip}
                    <a href="{$NaviFilter->getURL()->getUnsetAll()}" title="{lang key='removeFilters'}" class="badge badge-warning">
                        {lang key='removeFilters'}
                    </a>
                {/strip}
            {/if}
        </div>
    </div>
{/if}
{/block}