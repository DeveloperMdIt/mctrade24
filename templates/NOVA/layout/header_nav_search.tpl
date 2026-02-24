{block name='layout-header-nav-search'}
    {block name='layout-header-nav-search-search'}
        <{$tag|default:'div'} class="nav-item" id="search">
            <div class="search-wrapper">
                <form action="{$ShopURL}/search/" method="get" role="search">
                    <div class="form-icon">
                        {inputgroup}
                            {input id="search-header" name="qs" type="text" class="ac_input" placeholder="{lang key='search'}" autocomplete="off" aria=["label"=>"{lang key='search'}"]}
                            {inputgroupaddon append=true}
                                {button type="submit" variant="secondary" aria=["label"=>{lang key='search'}] title="{lang key='search'}"}<span class="fas fa-search"></span>{/button}
                            {/inputgroupaddon}
                            <span class="form-clear d-none" aria-label="{lang key='clearSearch'}" title="{lang key='clearSearch'}"><i class="fas fa-times"></i></span>
                        {/inputgroup}
                    </div>
                </form>
            </div>
        </{$tag|default:'div'}>
    {/block}
    {block name='layout-header-nav-search-search-dropdown'}
        {if $Einstellungen.template.header.mobile_search_type === 'dropdown'}
            {$navIcon="<i id='mobile-search-dropdown' class='fas fa-search' aria-label='{lang key='search'}' title='{lang key='search'}'></i>"}
            {navitemdropdown tag='div' class='search-wrapper-dropdown d-block d-lg-none'
                text=$navIcon
                right=true
                no-caret=true
                router-aria=['label'=>{lang key='findProduct'}]}
                <div class="dropdown-body" role="search" aria-label="{lang key='findProduct'}" title="{lang key='findProduct'}">
                    {include file='snippets/search_form.tpl' id='search-header-desktop'}
                </div>
            {/navitemdropdown}
        {/if}
    {/block}
{/block}
