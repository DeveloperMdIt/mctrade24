{* custom *}
{block name='snippets-categories-recursive'}
    {if (!empty($categories) ||isset($categoryId)) && (!isset($i) || isset($i) && isset($limit) && $i < $limit)}
        {strip}
            {if !isset($i)}
                {assign var='i' value=0}
            {/if}
            {if !isset($limit)}
                {assign var='limit' value=3}
            {/if}
            {if !isset($caret)}
                {assign var='caret' value={$admIcon->renderIcon('caretDown', 'icon-content icon-content--default nav-toggle float-right')}}
            {/if}
            {if !isset($activeId)}
                {assign var='activeId' value='0'}
                {* custom *}
                {if empty($NaviFilter)}
                    {$NaviFilter = \JTL\Shop::run()}
                {/if}
                {if $NaviFilter->hasCategory()}
                    {assign var='activeId' value=$NaviFilter->getCategory()->getValue()}
                {elseif $nSeitenTyp == 1 && isset($Artikel)}
                    {assign var='activeId' value=$Artikel->gibKategorie()}
                {elseif $nSeitenTyp == 1 && isset($smarty.session.LetzteKategorie)}
                    {assign var='activeId' value=$smarty.session.LetzteKategorie}
                {/if}
            {/if}
            {if !isset($activeParents)
            && ($nSeitenTyp === $smarty.const.PAGE_ARTIKEL || $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE)}
                {get_category_parents categoryId=$activeId assign='activeParents'}
            {/if}
            {if !isset($activeParents)}
                {assign var='activeParents' value=null}
            {/if}
            {if empty($categories)}
                {if !isset($categoryBoxNumber)}
                    {assign var='categoryBoxNumber' value=null}
                {/if}
    
                {get_category_array categoryId=$categoryId categoryBoxNumber=$categoryBoxNumber assign='categories'}
            {/if}
            {if !empty($categories)}
                {foreach $categories as $category}
                    {$fnAttr = $category->getFunctionalAttributes()}
                    {$seoUrlAttr = $category->getAttribute('category_seo_url')}
                    {if empty($fnAttr.category_hide)}{* custom - hidden attribure *}
                        {assign var='hasItems' value=$category->hasChildren() && (($i+1) < $limit)}
                        {if isset($activeParents) && is_array($activeParents) && isset($activeParents[$i])}
                            {assign var='activeParent' value=$activeParents[$i]}
                        {/if}
                        <li class="nav-item{if $category->getID() == $activeId || ((isset($activeParent) && isset($activeParent->getID())) && $activeParent->getID() == $category->getID())} active{/if}{if isset($fnAttr.category_class)} {$fnAttr.category_class}{/if}">{* custom - category class *}
                            <a href="{if isset($seoUrlAttr)}{$seoUrlAttr->cWert}{else}{$category->getURL()}{/if}" class="nav-link{if $hasItems} nav-sub{/if}" data-ref="{$category->getID()}">
                                {$catIcon = $admPro->categoryIcon($category)}
                                <span class="icon-text--center{if $catIcon} megamenu__category-icon-wrapper{/if}">{$catIcon} {$category->getShortName()}</span>
                                {if $hasItems}{$caret}{/if}
                            </a>
                            {if $hasItems}
                                <ul class="nav flex-column">
                                    {if !empty($category->getChildren())}
                                        {include file='snippets/categories_recursive_offcanvas.tpl' i=$i+1 categories=$category->getChildren() limit=$limit activeId=$activeId activeParents=$activeParents}
                                    {else}
                                        {include file='snippets/categories_recursive_offcanvas.tpl' i=$i+1 categoryId=$category->getID() limit=$limit categories=null activeId=$activeId activeParents=$activeParents}
                                    {/if}
                                </ul>
                            {/if}
                        </li>
                    {/if}{* /custom - hidden attribute *}
                {/foreach}
            {/if}
        {/strip}
    {/if}
    {/block}
    