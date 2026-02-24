{if isset($cType) && count($cType->oItem_arr) > 0}
    {if $isNova}
        <div class="h5">{$cName}</div>
        {foreach $cType->oItem_arr as $item}
            <div class="jtl-search-item form-row align-items-center">
                <div class="col col-auto">
                    <a class="rel-link"
                       href="{$item->cUrl}"
                       rel="{$item->cName|escape:'html'}"
                       forward="{$oSearchResponse->oSuggest->nForwarding}">
                        <img src="{if $item->cImageUrl|@strlen > 0}{$item->cImageUrl|replace:"http://":"//"}{else}{$noImagePath}{/if}"
                             alt="{$item->cName|escape:'html'}" />
                    </a>
                </div>
                <div class="col">
                    <a class="rel-link"
                       href="{$item->cUrl}"
                       rel="{$item->cName|escape:'html'}"
                       forward="{$oSearchResponse->oSuggest->nForwarding}">
                        {$item->cName|regex_replace:"/({$cSearch|escape:'url'})/i":"<span class='jtl_match'>\$1</span>"}
                        {if $item->nCount > 0}
                            <em class="count">({$item->nCount})</em>
                        {/if}
                    </a>
                </div>
            </div>
        {/foreach}
    {else}
        <p class="jtl-search-item-name">{$cName}</p>
        {foreach $cType->oItem_arr as $item}
            <a class="rel-link"
               href="{$item->cUrl}"
               rel="{$item->cName|escape:'html'}"
               forward="{$oSearchResponse->oSuggest->nForwarding}">
                {if $item->cImageUrl|@strlen > 0}
                    <div class="jtl-search-item article_wrapper clearall">
                        <div class="article_image">
                            <img src="{$item->cImageUrl|replace:"http://":"//"}" alt="{$item->cName|escape:'html'}" />
                        </div>
                        <div class="article_info">
                            {$item->cName|regex_replace:"/({$cSearch|escape:'url'})/i":"<span class='jtl_match'>\$1</span>"}
                        </div>
                    </div>
                {else}
                    {$item->cName|regex_replace:"/({$cSearch|escape:'url'})/i":"<span class='jtl_match'>\$1</span>"} {if $item->nCount > 0}
                    <em class="count">({$item->nCount})</em>{/if}
                {/if}
            </a>
        {/foreach}
    {/if}
{/if}
