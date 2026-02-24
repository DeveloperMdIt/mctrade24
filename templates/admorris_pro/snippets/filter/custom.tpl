<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach $filter->getOptions() as $filterOption}
        <li>
            <a rel="nofollow" href="{$filterOption->getURL()}">
                <span class="value">
                    {if $NaviFilter->getFilterValue($filter->getClassName()) == $filterOption->getValue()}
                    {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default text-muted')}
                    {else}
                    {$admIcon->renderIcon('squareO', 'icon-content icon-content--default text-muted')}
                    {/if} 
                    {$filterOption->getName()|escape:'html':'UTF-8':FALSE}<span class="badge badge-pill float-right">{$filterOption->getCount()}<span class="sr-only"> {lang key='productsFound'}</span></span>
                </span>
            </a>
        </li>
    {/foreach}
</ul>

{*FilterOption variant:*}


{*<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">*}
    {*{foreach $filter->filterOptions as $filterOption}*}
        {*<li>*}
            {*<a rel="nofollow" href="{$filterOption->getURL()}">*}
                {*<span class="value">*}
                    {*<i class="fa {if $NaviFilter->getFilterValue($filter->cClassname) == $filterOption->getValue()}fa-check-square-o{else}fa-square-o{/if} text-muted"></i>*}
                    {*{$filterOption->getName()|escape:'html':'UTF-8':FALSE}*}
                    {*<span class="badge badge-pill float-right">{$filterOption->getCount()}</span>*}
                {*</span>*}
            {*</a>*}
        {*</li>*}
    {*{/foreach}*}
{*</ul>*}
