{*custom*}
{block name='snippets-product-slider'}

{strip}
{if $productlist|@count > 0}

    {$sliderClass = (empty($sliderClass))?'evo-slider':$sliderClass}
    {$alignment = $admorris_pro_templateSettings->productSliderContentTextAlignment}
    
    {if !isset($tplscope)}
        {assign var='tplscope' value='slider'}
    {/if}
    {if $tplscope === 'box'}
        {$dataType = 'box'}
    {elseif $tplscope === 'half'}
        {$dataType = 'slider-half'}
    {else}
        {$dataType = 'product'}
    {/if}
    {if $sliderClass === 'pushed-success-slider'}
        {$dataType = 'pushed-success'}
    {/if}

    {$productBuyFunctions = $admorris_pro_templateSettings->productSliderPurchaseFunctions && $dataType !== 'pushed-success'}


    <div class="product-slider{if $tplscope === 'box'} box box-slider{/if}{if !empty($class) && $class|strlen > 0} {$class}{/if}{if !empty($start)} product-slider--start{/if}{if !empty($name)} product-slider--{$name}{/if}{if $alignment === 'center'} product-slider--centered{/if}{if $productBuyFunctions} product-slider--buy-functions{/if}"{if isset($id) && $id|strlen > 0} id="{$id}"{/if}>
        {if !empty($title)}
            {if $tplscope === 'box'}
                <div class="card-header">
                    <div class="">{$title}</div>
                </div>
            {else}
                <div class="product-slider__heading{if $alignment === 'center'} text-center{/if}">
                    <h2 class="product-slider__title">
                        {$title}
                    </h2>
                </div>
            {/if}
        {/if}
        {if !empty($subheading)}
            <div class="product-slider__subheading{if $alignment === 'center'} text-center{/if}">
                {$subheading}
            </div>
        {/if}
        <div {if !empty($title)}class="product-slider__wrapper"{/if}>
            <div class="slick-lazy product-slider__slick-slider {if $tplscope === 'box'}{block name='product-box-slider-class'}evo-box-slider{/block}{else}{block name='product-slider-class'}{$sliderClass}{/block}{/if}" data-slick-type="{$dataType}">
                {foreach $productlist as $product}
                    <div class="product-wrapper{if isset($style)} {$style}{/if}">
                        {if $productBuyFunctions && $sliderClass != "pushed-success-slider"}
                            {$idPrefix = 'product-slider-'|cat:$admPro->uniqid()}
                            {* {$idPrefix = ''} *}
                            {include file='productlist/item_box.tpl' Artikel=$product tplscope=$tplscope class='' idPrefix=$idPrefix}
                        {else}
                            {include file='productlist/item_slider.tpl' Artikel=$product tplscope=$tplscope class=''}
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
        {if !empty($moreLink)}
            <div class="product-slider__more{if $alignment === 'center'} text-center{/if}">
                <a class="product-slider__more-button btn btn-primary" href="{$moreLink}" {* title="{$moreTitle}" *} {* data-toggle="tooltip" data-placement="auto right" *} aria-label="{$moreTitle}">
                    {$moreTitle}
                </a>
            </div>
        {/if}
    </div>{* /panel *}
{/if}
{/strip}
{/block}