{if isset($oExtendedJTLSearchResponse)}
    <script src="{$path_jq_migrate}"></script>
    <script src="{$path_jq_ui}"></script>
    <link type="text/css" href="{$oBox->getExtension()->getPaths()->getFrontendURL()}css/base.css" rel="stylesheet" />
    {foreach $oExtendedJTLSearchResponse->oSearch->oFilterGroup_arr as $filterGroup}
        <div class="sidebox panel panel-default box box-jtl-search">
            <div class="panel-heading">
                <h5 class="panel-title">{$filterGroup->cMapping}</h5>
            </div>
            {if $filterGroup->nType === 2} {* Slider *}
                <div class="box-body panel-body sidebox_content">
                    {assign var="oFilterItem" value=$filterGroup->oFilterItem_arr[0]}
                    <div class="layout-slider" style="width: 100%; height: 50px; padding-top: 10px;">
                        <input id="slider_{$filterGroup->cName}"
                               type="slider"
                               name="price"
                               value="{$oFilterItem->cStateFrom};{$oFilterItem->cStateTo}" />
                    </div>
                    <script type="text/javascript" charset="utf-8">
                        $(document).ready(function () {ldelim}
                            jQuery("#slider_{$filterGroup->cName}").slider({ldelim}
                                from:      {$oFilterItem->fFrom},
                                to:        {$oFilterItem->fTo},
                                limits:    false,
                                step:      {$oFilterItem->fStep},
                                scale:     [
                                    {foreach name=scale from=$oFilterItem->nScale_arr key=key item=nScale}
                                    {if $key % 2 === 0}
                                    {$nScale}{if !$smarty.foreach.scale.last}, {/if}
                                    {else}
                                    '|'{if !$smarty.foreach.scale.last}, {/if}
                                    {/if}
                                    {/foreach}
                                ],
                                dimension: '&nbsp;{$oFilterItem->cUnit}',
                                min:       {$oFilterItem->fSolrFrom},
                                max:       {$oFilterItem->fSolrTo},
                                skin:      'round_plastic',
                                smooth:    true,
                                callback:  function (value) {ldelim}
                                    var xScale_arr = value.split(';');
                                    window.location.href = '{$oFilterItem->cURL}&fq{$nStatedFilterCount}={$filterGroup->cName}:{$oFilterItem->fFrom}_{$oFilterItem->fTo}-' + xScale_arr[0] + '_' + xScale_arr[1];
                                    {rdelim}
                                {rdelim});
                            {rdelim});
                    </script>
                </div>
            {elseif $filterGroup->nType === 3} {* Colorbox *}
                <div class="box-body panel-body sidebox_content">
                    {foreach $filterGroup->oFilterItem_arr as $oFilterItem}
                        <div class="color{if $oFilterItem->bSet} active{/if}">
                            <a rel="nofollow" href="{$oFilterItem->cURL}" class="color {$oFilterItem->cValue}" title="{$oFilterItem->cValue} {$oFilterItem->nCount}">
                                <span>{$oFilterItem->cValue}</span>
                            </a>
                        </div>
                    {/foreach}
                </div>
            {elseif $filterGroup->nType === 4} {* Tinybox *}
                <div class="box-body panel-body sidebox_content">
                    {foreach $filterGroup->oFilterItem_arr as $oFilterItem}
                        <a rel="nofollow" href="{$oFilterItem->cURL}" class="btn btn-sm btn-outline-secondary mr-1 mb-1 {if $oFilterItem->bSet} active{/if}" title="{$oFilterItem->cValue} {$oFilterItem->nCount}">
                            <span>{$oFilterItem->cValue}</span>
                        </a>
                    {/foreach}
                </div>
            {else} {* Checkbox *}
                <div class="box-body sidebox_content">
                    <div class="filter_state">
                        {foreach $filterGroup->oFilterItem_arr as $oFilterItem}
                            <a rel="nofollow"
                               href="{$oFilterItem->cURL}"
                               title="{$oFilterItem->cValue}"
                               class="filter-item">
                                <div class="align-items-center d-flex">
                                    <i class="{if $isNova}far{else}fa{/if} mr-2 fa-{if $oFilterItem->bSet}check-{/if}square text-muted"></i> {$oFilterItem->cValue}
                                    <span class="badge badge-outline-secondary ml-auto count">{$oFilterItem->nCount}</span>
                                </div>
                            </a>
                        {/foreach}
                    </div>
                </div>
            {/if}
            <hr class="mb-4">
        </div>
    {/foreach}
{/if}
