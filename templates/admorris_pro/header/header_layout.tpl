{*custom*}
{function include_header_item}
    {$mobileRowElementMod = ($layoutType === 'mobileLayout')?'  header-row__element--mobile':''}

    <div data-row="{$row}" data-column="{$column}" class="header-row__col header-row__col--{$column}">
        {$groupItems = []}
        {foreach from=$headerTemplateIncArray key=k item=inc_item}
            {if !empty($inc_item['group'])}
                <div class="header-row__element{$mobileRowElementMod} header-row__megamenu">
                    {include "header/megamenu.tpl" itemGroup=$inc_item}
                </div>
            {else}
                {include $inc_item.template assign=header_item itemSettings=$inc_item layoutType=$layoutType}

                {if !empty($header_item) && $header_item|strip !== ' '}
                    <div class="header-row__element{$mobileRowElementMod} {$inc_item.name}{$inc_item.class|default:''}">
                        {$header_item}
                    </div>
                {/if}
            {/if}
        {/foreach}
    </div>
{/function}

{for $row=1 to 3}
    {assign var=stickyRow value="headerRowSticky{($layoutType === 'mobileLayout') ? 'Mobile' : ''}{$row}"}

    {if $headerLayout->getRowLayout($layoutType, $row)}
        {strip}
            {$rowClasses = $headerLayout->getRowClasses($layoutType, $row)}
            {$header_row_array = $headerLayout->getRowItems($layoutType, $row)}

            {if isset($header_row_array['centerCol']) && $header_row_array['centerCol']}
                {$rowClasses = $rowClasses|cat:' header-row--center-col'}
            {/if}

            <div class="header-row-wrapper header-row-wrapper-{$row}">
                <div class="header-row row-{$row} {($admorris_pro_themeVars->$stickyRow) ? 'sticky-row' : ''}
                    {if $headerLayout->getRowSetting($layoutType, $row, 'inverted')} header-row--inverted{/if}
                    {if $rowClasses} {$rowClasses}{/if}">
                    <div class="header__container header__container--{$row} {$admPro->header_container_size()}">
                        {foreach $header_row_array as $colKey=>$colItems}
                            {if is_numeric($colKey)}
                                {include_header_item row=$row column=$colKey headerTemplateIncArray=$colItems}
                            {/if}
                        {/foreach}
                    </div>
                </div>
            </div>
        {/strip}
    {/if}
{/for}