{*custom*}
{block name='productdetails-index'}
{block name='header'}
    {if !isset($bAjaxRequest) || !$bAjaxRequest}
        {include file='layout/header.tpl'}
    {elseif isset($smarty.get.quickView) && $smarty.get.quickView == 1}
        {include file='layout/modal_header.tpl'}
    {/if}
{/block}

{block name='content'}
    {if isset($bAjaxRequest) && $bAjaxRequest && isset($listStyle) && ($listStyle === 'list' || $listStyle === 'gallery')}
        {if $listStyle === 'list'}
            {assign var='tplscope' value='list'}
            {include file='productlist/item_list.tpl' lazyloading=false}
        {elseif $listStyle === 'gallery'}
            {assign var='tplscope' value='gallery'}
            {* {assign var="class" value="card card-body"} *}
            {$idPrefix = $smarty.get.idPrefix|default:''}
            {include file='productlist/item_box.tpl' lazyloading=false idPrefix=$idPrefix}
        {/if}
    {else}
        <div id="result-wrapper" data-wrapper="true" class="stack stack--collapse-margins">
        {include file='snippets/extension.tpl'}
        {include file='productdetails/details.tpl'}
        </div>
    {/if}
{/block}

{block name='footer'}
    {if !isset($bAjaxRequest) || !$bAjaxRequest}
        {include file='layout/footer.tpl'}
    {else}
        {if isset($smarty.get.quickView) && $smarty.get.quickView == 1}
            {include file='layout/modal_footer.tpl'}
        {/if}
    {/if}
{/block}
{/block}