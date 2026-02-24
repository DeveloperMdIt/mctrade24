{*custom*}

{block name='navbar-productsearch'}

    {if $layoutType === 'desktopLayout'}
        {$inputId = 'search-header'}
    {else}
        {$inputId = 'search-header-mobile-top'}
    {/if}

    {$dropdownSearch = false}  
    
    {if $layoutType == 'mobileLayout'}
        {if $itemSettings}  
            {$dropdownSearch = $itemSettings.dropdownMobile}
            {$searchStyle = $itemSettings.styleMobile}
        {/if}
        {$searchId = 'mobile-search'}
        {$searchButtonId = 'mobile-search-submit-button'}
        {$searchWidthMobile = $headerLayout->getItemSetting('search', 'widthMobile')}
    {elseif $layoutType == 'offcanvasLayout'}
        {$searchId = 'offcanvas-search'}
        {$searchButtonId = 'offcanvas-search-submit-button'}
        {if $itemSettings}
            {$searchStyle = $itemSettings.styleMobile}
        {/if}
    {else}
        {$searchId = 'search'}
        {$searchButtonId = 'search-submit-button'}
        {$searchWidth = $headerLayout->getItemSetting('search', 'width')}
        {if $itemSettings}  
            {$dropdownSearch = $itemSettings.dropdown}
            {$searchStyle = $itemSettings.styleDesktop}
        {/if}
    {/if}

    {$searchModifier = ''}
    {$styleClasses = ''}
    
    {if $dropdownSearch === true}
        {$searchModifier = 'header-search--dropdown '}
    {/if}
    
    {if $searchStyle === 'underline'}
        {$searchModifier = $searchModifier|cat:'header-search--underline'}
    {else if $searchStyle === 'input'}
        {$searchModifier = $searchModifier|cat:'header-search--input'}
        {$styleClasses = ' form-control'}
    {/if}

    <div id="{$searchId}" class="header-search {$searchModifier} search-wrapper">
        {if $dropdownSearch === true}
            <button aria-label="{lang key='search'}" type="button" id="search-button-{$layoutType}" aria-haspopup="true" aria-expanded="false" class="search__toggle js-toggle-search shopnav__link">{$admIcon->renderIcon('search', 'icon-content icon-content--default icon-content--center shopnav__icon')}</button>
        {/if}
        <form class="search__form js-search-form{$styleClasses}" action="{$ShopURL}/search/" method="get" role="search" {if !empty($searchWidth) || !empty($searchWidthMobile)} style="--search-width: {if !empty($searchWidth)}{$searchWidth}{elseif !empty($searchWidthMobile)}{$searchWidthMobile}{/if};"{/if}>
            <div class="search__wrapper">
                <input name="qs" type="text" id="{$inputId}" class="search__input ac_input" placeholder="{lang key='search'}" autocomplete="off" aria-label="{lang key='search'}"/>
                <button type="submit" name="search" id="{$searchButtonId}" class="search__submit" aria-label="{lang key='search'}">
                    {$admIcon->renderIcon('search', 'icon-content icon-content--default')}
                </button>
            </div>
        </form>   
    </div>
{/block}{* /navbar-productsearch *}