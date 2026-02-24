{*custom*}
{block 'header-megamenu'}
    {$navAriaLabelArray = []}
    {foreach $itemGroup.items as $item}
        {if $item.name === 'categories'}
            {lang key="categories" section="global" assign="labelText"}
        {elseif $item.name === 'manufacturers'}
            {lang key="manufacturers" section="global" assign="labelText"}
        {elseif $item.name === 'cms-megamenu'}
            {$megamenuLinkGroup = JTL\Shop::Container()->getLinkService()->getLinkGroups()->getLinkgroupByTemplate('megamenu')}
            {if $megamenuLinkGroup}
                {$labelText = $megamenuLinkGroup->getName()}
            {else}
                {$labelText = ''}
            {/if}
        {/if}
        {if $labelText}
            {append var=navAriaLabelArray value=$labelText}
        {/if}
    {/foreach}
    {$navAriaLabel = implode(', ', $navAriaLabelArray)}

    <nav aria-label="{$navAriaLabel}" class="category-nav megamenu">
        <ul class="nav nav-scrollbar-inner">
            {foreach $itemGroup.items as $item}
                {include file=$item.template itemSettings=item layoutType=$layoutType}
                {* {$item|var_dump} *}

            {/foreach}
        </ul>
    </nav>
{/block}