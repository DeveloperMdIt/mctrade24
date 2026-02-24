<ul class="filter_search nav nav-list">
    {foreach $NaviFilter->searchFilterCompat->getOptions() as $searchFilter}
        <li>
            <a rel="nofollow" href="{$searchFilter->getURL()}" class="{if $searchFilter->isActive()}active{/if}">
                <span class="badge badge-pill float-right">{$searchFilter->getCount()}<span class="sr-only"> {lang key='productsFound'}</span></span>
                <span class="value">
                    {if $searchFilter->isActive()}
                        {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default text-muted')}
                    {else}
                        {$admIcon->renderIcon('squareO', 'icon-content icon-content--default text-muted')}
                    {/if} 
                    {$searchFilter->getName()}
                </span>
            </a>
        </li>
    {/foreach}
</ul>
