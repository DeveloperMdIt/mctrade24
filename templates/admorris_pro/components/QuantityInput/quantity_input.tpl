{$Artikel = $params.article->getValue()}



{$max = ($params.max->hasValue()) ? $params.max->getValue() : $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|default:''}
{$min = ($params.min->hasValue()) ? $params.min->getValue() : $admPro->getMinValueArticle($Artikel)}
{$buttonClass = $params.buttonClass->getValue()}
{$idPrefix = $params.idPrefix->getValue()}
{if $params.disabled->getValue()|default:false}
    {$disabled = ' disabled="true"'}
{else}
    {$disabled = ''}
{/if}

{if $params.value->hasValue()}
    {$value = $params.value->getValue()}
{elseif $min > 0}
    {$value = $min}
{else}
    {$value = 1}
{/if}

{$interval = $Artikel->fAbnahmeintervall|default:0}
{$mbm = $Artikel->fMindestbestellmenge|default:0}

{if $params.step->hasValue()}
    {$step = $params.step->getValue()}
{elseif $Artikel->cTeilbar === 'Y' && $Artikel->fAbnahmeintervall == 0}
    {$step = 'any'}
{elseif $Artikel->fAbnahmeintervall > 0}
    {$step = $Artikel->fAbnahmeintervall}
{else}
    {$step = 1}
{/if}

{* input-group added for js-validation to work *}
<div class="form-group mb-0">
    <div class="js-spinner choose_quantity {$params.wrapperClass->getValue()}">
        <button type="button" class="js-spinner-button {$buttonClass}" aria-label="{lang key='decreaseQuantity' section='aria'}" data-spinner-button="down"{$disabled}></button>
        <div class="js-spinner-input{if $Artikel->cEinheit} js-spinner--unit-addon{/if}">

            {strip}
            <input type="number" min="{$min}" 
                {if !empty($max)}max="{$max}"{/if} 
                {if !empty($step)} 
                    step="{$step}"
                {/if}  
                autocomplete="off"
                id="{if $params.id->hasValue()}{$params.id->getValue()}{else}{$idPrefix}quantity{$Artikel->kArtikel}{/if}" class="quantity form-control" 
                name="{$params.name->getValue()}" 
                value="{$value}" 
                aria-label="{lang key='quantity'}"
                autocomplete="off"
                {$disabled}
            />
            {if $Artikel->cEinheit}
                <div class="js-spinner__unit-addon unit">{$Artikel->cEinheit}</div>
            {/if}
            {/strip}
        </div>
        <button type="button" class="js-spinner-button {$buttonClass}" aria-label="{lang key='increaseQuantity' section='aria'}" data-spinner-button="up"{$disabled}></button>
    </div>
</div>