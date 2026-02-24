{*custom*}
{* Nova code in snippets/categories_mega.tpl *}
{strip}
{assign var=max_subsub_items value=12}


{block name='megamenu-categories'}
{if $Einstellungen.template.megamenu.show_categories !== 'N'
    && ($Einstellungen.global.global_sichtbarkeit != 3
        || isset($smarty.session.Kunde->kKunde)
        && $smarty.session.Kunde->kKunde != 0)}
    {assign var='show_subcategories' value=false}
    {if $Einstellungen.template.megamenu.show_subcategories !== 'N'}
        {assign var='show_subcategories' value=true}
    {/if}

    {get_category_array categoryId=0 assign='categories'}
    {if !empty($categories)}
        {if !isset($activeId)}
            {if $NaviFilter->hasCategory()}
                {$activeId = $NaviFilter->getCategory()->getValue()}
            {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($Artikel)}
                {assign var='activeId' value=$Artikel->gibKategorie()}
            {elseif $nSeitenTyp === $smarty.const.PAGE_ARTIKEL && isset($smarty.session.LetzteKategorie)}
                {$activeId = $smarty.session.LetzteKategorie}
            {else}
                {$activeId = 0}
            {/if}
        {/if}
        {if !isset($activeParents)
        && ($nSeitenTyp === $smarty.const.PAGE_ARTIKEL || $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE)}
            {get_category_parents categoryId=$activeId assign='activeParents'}
        {/if}
        {foreach $categories as $category}
            {$fnAttr = $category->getFunctionalAttributes()}
            {$seoUrlAttr = $category->getAttribute('category_seo_url')}
            {assign var='isDropdown' value=$category->hasChildren()}
            {$allChildsHidden = true}
            {if $isDropdown}
                {if !empty($category->getChildren())}
                    {assign var=sub_categories value=$category->getChildren()}
                {else}
                    {get_category_array categoryId=$category->getID() assign='sub_categories'}
                {/if}

                {foreach $sub_categories as $sub}
                    {$fnAttributes = $sub->getFunctionalAttributes()}
                    {if empty($fnAttributes.category_hide)}
                        {$allChildsHidden = false}
                        {break}
                    {/if}
                {/foreach}
            {/if}
            {if empty($fnAttr.category_hide)}
                <li class="nav-item nav-scrollbar-item{if $isDropdown && !$allChildsHidden} has-dropdown megamenu-fw{/if}{if $category->getID() == $activeId || (isset($activeParents[0]) && $activeParents[0]->getID() == $category->getID())} active{/if}{if isset($fnAttr.category_class)} {$fnAttr.category_class}{/if}">
                    <a href="{if isset($seoUrlAttr)}{$seoUrlAttr->cWert}{else}{$category->getURL()}{/if}" class="nav-link{if $isDropdown && !$allChildsHidden} dropdown-toggle{/if}"{if $isDropdown && !$allChildsHidden} data-toggle="dropdown" aria-expanded="false" aria-controls="category-dropdown-{$category->getID()}"{/if}>
                        {$catIcon = $admPro->categoryIcon($category)}

                        <span class="icon-text--center">{if $catIcon}<span class="megamenu__category-icon-wrapper">{/if}{$catIcon} {$category->getShortName()}{if $catIcon}</span>{/if}</span>
                        {if $isDropdown && !$allChildsHidden} {$admIcon->renderIcon('chevronDown', 'icon-content icon-content--center icon-content--toggle')}{/if}
                    </a>
                    {if $isDropdown && !$allChildsHidden}
                        {include 'header/category_dropdown.tpl'}
                    {/if}
                </li>
            {/if}
        {/foreach}
    {/if}
{/if}

{/block}{* /megamenu-categories*}



{block name="megamenu-global-characteristics"}
{*
{if isset($Einstellungen.template.megamenu.show_global_characteristics) && $Einstellungen.template.megamenu.show_global_characteristics !== 'N'}
    {get_global_characteristics assign='characteristics'}
    {if !empty($characteristics)}

    {/if}
{/if}
*}
{/block}{* megamenu-global-characteristics *}
{/strip}
