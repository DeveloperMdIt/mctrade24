{*custom - new partial not existing in Evo*}
{block 'productlist-filter'}
    <div class="result-options__dropdowns">
        {strip}
        <div id="filter-collapsible" class="result-options__filter">
            <div id="navbar-filter" class="filter-navbar d-flex flex-wrap">
                {foreach $contentFilters as $filter}
                    {if count($filter->getFilterCollection()) > 0}
                        {block name='productlist-result-options-'|cat:$filter->getNiceName()}
                            {foreach $filter->getOptions() as $subFilter}
                                {if $subFilter->getVisibility() !== \JTL\Filter\Visibility::SHOW_BOX}
                                    <div class="dropdown filter-type-{$filter->getNiceName()}">
                                        <button type="button" class="filter-dropdown__button | btn dropdown-toggle form-control" data-toggle="dropdown" aria-expanded="false">
                                            {$subFilter->getFrontendName()} {$admIcon->renderIcon('caretDown', 'icon-content icon-content--default caret-custom')}
                                        </button>
                                        {include file='snippets/filter/genericFilterItem.tpl' itemClass='' class='dropdown-menu' filter=$subFilter sub=true}
                                    </div>
                                {/if}
                            {/foreach}
                        {/block}
                    {elseif  !in_array($admPro->getClass($filter), ['JTL\\Filter\\Items\\Search', 'JTL\\Filter\\Items\\Availability'])}
                        {block name='productlist-result-options-'|cat:$filter->getNiceName()}
                            {if $filter->getInputType() === admProInputType::SELECT}
                                {assign var=outerClass value='dropdown filter-type-'|cat:$filter->getNiceName()}
                                {assign var=innerClass value='dropdown-menu'}
                                {assign var=itemClass value=''}
                            {elseif $filter->getInputType() === admProInputType::BUTTON}
                                {assign var=outerClass value='no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                {assign var=innerClass value='no-dropdown'}
                                {assign var=itemClass value='btn btn-secondary'}
                            {else}
                                {assign var=outerClass value='no-dropdown filter-type-'|cat:$filter->getNiceName()}
                                {assign var=innerClass value='no-dropdown'}
                                {assign var=itemClass value=''}
                            {/if}
                            <div class="{$outerClass}">
                                {if $filter->getInputType() === admProInputType::SELECT}
                                    <button type="button" class="filter-dropdown__button | btn dropdown-toggle form-control" data-toggle="dropdown" aria-expanded="false">
                                        {$filter->getFrontendName()} {$admIcon->renderIcon('caretDown', 'icon-content icon-content--default caret-custom')}
                                    </button>
                                {/if}
                                {include file='snippets/filter/genericFilterItem.tpl' class=$innerClass itemClass=$itemClass filter=$filter}
                            </div>
                        {/block}
                    {/if}
                {/foreach}
            </div>
        </div>{* /collapse *}
        {/strip}
    </div>
{/block}