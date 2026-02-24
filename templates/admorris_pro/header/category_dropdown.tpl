{*custom*}
{block name="category-dropdown"}
    {strip}
    {* count categories to decide if columns are needed *}
    {$columns = $admPro->subcategories_columns_count($category->getChildren(), $show_subcategories)}
    
    {$funcAttr = $category->getFunctionalAttributes()}
    {$seoUrlAttr = $category->getAttribute('category_seo_url')}
    
    {* dropdown image *}
    {if !empty($funcAttr['category_dropdown_image'])}
       {$dropdownImage = $ShopURL|cat:'/bilder/kategorien/dropdowns/'|cat:$funcAttr['category_dropdown_image']}
    {/if}
    {if isset($funcAttr['category_dropdown_padding'])}
       {$dropdownPadding = $funcAttr['category_dropdown_padding']}
    {/if}
    {$isFullwidth = $admorris_pro_themeVars->headerDropdownMenuWidth === 'full-width'}
    {* moved from /Evo/snippets/categories_mega.tpl *}
    
    {assign var=hasInfoColumn value=false}
    {if isset($Einstellungen.template.megamenu.show_maincategory_info) && $Einstellungen.template.megamenu.show_maincategory_info !== 'N' && !empty($category->getAttribute('category_summary'))}
        {assign var=hasInfoColumn value=true}
    {/if}

    <div id="category-dropdown-{$category->getID()}" class="dropdown-menu dropdown-menu--megamenu{if $isFullwidth} dropdown-menu--full-width{/if}{if isset($dropdownImage)} dropdown-menu--background-image lazy lazypreload{/if}"
    {if isset($dropdownImage)}
        data-bg="{$dropdownImage}" style="{if !empty($dropdownPadding)}--admorris-dropdown-padding: {$dropdownPadding}px;{/if}"
    {/if}>
        {if $isFullwidth}
            <div class="admPro-container {$admPro->header_container_size()}">
        {/if}
        <div class="megamenu-content{if $isFullwidth} megamenu-content--fullwidth{/if}{if $hasInfoColumn} info-column{/if}{if $columns > 1} columns columns-{$columns}{/if}">
            {if $hasInfoColumn}
                <div class="megamenu-content__info">
                    <div class="h3 megamenu-content__category-title">
                        <a href="{if isset($seoUrlAttr)}{$seoUrlAttr->cWert}{else}{$category->getURL()}{/if}">
                            {$category->getName()}
                        </a>
                    </div>
                    {* {if $category->cBildURL !== 'gfx/keinBild.gif'}
                        <a href="{$category->cURL}">
                            <img src="{$category->cBildURL}" class="img-responsive"
                                    alt="{$category->cKurzbezeichnung|escape:'html':'UTF-8':FALSE}">
                        </a>
                        <div class="clearall top15"></div>
                    {/if} *}
                    <div class="megamenu-content__description">{$category->getAttribute('category_summary')->cWert}</div>
                </div>
            {else}
                <a class="keyboard-focus-link h3 megamenu-content__category-title" href="{if isset($seoUrlAttr)}{$seoUrlAttr->cWert}{else}{$category->getURL()}{/if}">
                    {$category->getName()}
                </a>
            {/if}

            <div class="megamenu-content__row">
                {if $category->hasChildren()}
                    {if !empty($category->getChildren())}
                        {assign var=sub_categories value=$category->getChildren()}
                    {else}
                        {get_category_array categoryId=$category->getID() assign='sub_categories'}
                    {/if}
                    {$sub_categories = $admPro->removeHiddenCategories($sub_categories)}

                    {block name="megamenu-subcategories"}
                    {foreach $sub_categories as $sub}
                        {$fnAttr = $sub->getFunctionalAttributes()}
                        {$subSeoUrlAttr = $sub->getAttribute('category_seo_url')}
                        <div class="category-wrapper {if $sub->getID() == $activeId || (isset($activeParents[1]) && $activeParents[1]->getID() == $sub->getID())} active{/if}{if isset($fnAttr.category_class)} {$fnAttr.category_class}{/if}">
                            {* {if isset($Einstellungen.template.megamenu.show_category_images) && $Einstellungen.template.megamenu.show_category_images !== 'N'}
                                <div class="img text-center">
                                    <a href="{$sub->cURL}">
                                        <img src="{$sub->cBildURL}" class="image"
                                                alt="{$category->cKurzbezeichnung|escape:'html':'UTF-8':FALSE}">
                                    </a>
                                </div>
                            {/if} *}
                            <div class="megamenu-content__sub-category-title">
                                <a href="{if isset($subSeoUrlAttr)}{$subSeoUrlAttr->cWert}{else}{$sub->getURL()}{/if}">
                                    {$catIcon = $admPro->categoryIcon($sub)}
                                    <span {if $catIcon}class="megamenu__category-icon-wrapper"{/if}>
                                        {$catIcon} {$sub->getShortName()}
                                    </span>
                                </a>
                            </div>
                            {if $show_subcategories && $sub->hasChildren()}
                                {if !empty($sub->getChildren())}
                                    {assign var=subsub_categories value=$sub->getChildren()}
                                {else}
                                    {get_category_array categoryId=$sub->getID() assign='subsub_categories'}
                                {/if}
                                {$subsub_categories = $admPro->removeHiddenCategories($subsub_categories)}

                                <ul class="list-unstyled subsub">
                                    {foreach $subsub_categories as $subsub}
                                        {$fnAttrSub = $subsub->getFunctionalAttributes()}
                                        {$subSubSeoUrlAttr = $subsub->getAttribute('category_seo_url')}
                                        
                                        {if $subsub@iteration <= $max_subsub_items}
                                            <li class="{if $subsub->getID() == $activeId || (isset($activeParents[2]) && $activeParents[2]->getID() == $subsub->getID())} active{/if}{if isset($fnAttrSub.category_class)} {$fnAttrSub.category_class}{/if}">
                                                <a href="{if isset($subSubSeoUrlAttr)}{$subSubSeoUrlAttr->cWert}{else}{$subsub->getURL()}{/if}">
                                                    {$subsub->getShortName()}
                                                </a>
                                            </li>
                                        {else}
                                            <li class="more"><a href="{if isset($subSeoUrlAttr)}{$subSeoUrlAttr->cWert}{else}{$sub->getURL()}{/if}"> {$admIcon->renderIcon('chevronRightCircle', 'icon-content icon-content--default')} {lang key="more" section="global"} <span class="remaining">({math equation='total - max' total=$subsub_categories|count max=$max_subsub_items})</span></a></li>
                                            {break}
                                        {/if}
                                    {/foreach}
                                </ul>
                            {/if}
                        </div>
                    {/foreach}
                    {/block}
                {/if}
            </div>{* /row *}
        </div>{* /megamenu-content *}
        {if $admorris_pro_themeVars->headerDropdownMenuWidth === 'full-width'}
            </div>{* /container *}
        {/if}
    </div>
    {/strip}
{/block}