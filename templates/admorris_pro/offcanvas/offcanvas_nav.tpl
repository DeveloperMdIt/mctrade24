{*custom*}
{* offcanvas navigation *}

{function include_template}
    {if isset($inc_item.templateOffcanvas)}
        {$template = $inc_item['templateOffcanvas']}
    {else}
        {$template = $inc_item['template']}
    {/if}

    {include $template itemSettings=$inc_item layoutType='offcanvasLayout' assign=template_inc}

    {if !empty(str_replace(" ", "", $template_inc))}
        <li class="nav-item offcanvas-nav__element{$inc_item.classes|default:''} {$inc_item.name}">
            {$template_inc}
        </li>
    {/if}
{/function}

{block 'offcanvas-nav'}
    <div class="offcanvas-nav offcanvas-nav--menu modal" id="navbar-offcanvas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content nav flex-column flex-nowrap navbar-offcanvas rounded-0">
                {strip}
                    <div class="text-right">
                        {block 'offcanvas-nav-close-button'}
                            <button class="navbar-toggler" type="button" data-toggle="modal" data-target="#navbar-offcanvas" aria-controls="navbar-offcanvas" aria-label="Close navigation">
                                {$admIcon->renderIcon('cross', 'icon-content icon-content--default')}
                            </button>
                        {/block}
                    </div>
                    <ul class="sidebar-offcanvas nav flex-column">
                        {$offcanvas_template_inc_array = $headerLayout->getOffcanvasItems()}
                        {foreach $offcanvas_template_inc_array as $index => $inc_item}
                            {if isset($inc_item.group)}
                                <li class="nav-item offcanvas-nav__group offcanvas-nav__group--{$inc_item@index + 1}{$inc_item.classes|default:''}">
                                    <ul class="nav flex-column">
                                        {foreach from=$inc_item.group item=group_item}
                                            {include_template inc_item=$group_item }
                                        {/foreach}
                                    </ul>
                                </li>
                            {else}
                                {include_template inc_item=$inc_item }
                            {/if}
                        {/foreach}
                    </ul>
                {/strip}
            </div>
        </div>
    </div>
{/block}
