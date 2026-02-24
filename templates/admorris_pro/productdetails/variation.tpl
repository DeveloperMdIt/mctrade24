{*custom*}
{* admorris Pro: $variationPrefix added to ids for multiplier plugin *}
{block name='productdetails-variation'}
{assign var='modal' value=isset($smarty.get.quickView) && $smarty.get.quickView == 1}

{if !isset($form)}
    {$form = false}
{/if}
{if !isset($hr)}
    {$hr = true}
{/if}

{if isset($Artikel->Variationen) && $Artikel->Variationen|@count > 0 && !$showMatrix}
    {if $hr}
        <hr>
    {/if}
    {block name='productdetails-variation-assigns'}
        {assign var="VariationsSource" value="Variationen"}
        {if isset($ohneFreifeld) && $ohneFreifeld}
            {assign var="VariationsSource" value="VariationenOhneFreifeld"}
        {/if}
        {assign var="oVariationKombi_arr" value=$Artikel->getChildVariations()}
    {/block}
    {block name='productdetails-variation-spinner'}
        {row}
            {col class="updatingStockInfo text-center"}
                {lang key='updatingStockInformation' section='productDetails' assign='updatingStockInformationLangKey'}
                {$admIcon->renderIcon('spinner', 'icon-content icon-content--default icon-animated__spin', $updatingStockInformationLangKey)}
            {/col}
        {/row}
    {/block}
    {block name='productdetails-variation-variation'}
    <div class="variations {if $simple}simple{else}switch{/if}-variations{* top15 *} row">
        <div class="col col-12">
            {block name='productdetails-variation-name-outer'}
                <ul class="variations-freifelder list-unstyled">
                {foreach name=Variationen from=$Artikel->$VariationsSource key=i item=Variation}
                {block name='admorris_multiply_variations'}
                {/block}
                {strip}
                    <li class="form-group variation_{$Variation->cName|seofy|lower}">
                    <fieldset>
                        <legend class="freifeld__label">
                            {block name='productdetails-variation-name'}
                                {$Variation->cName}&nbsp;
                            {/block}
                            {block name='productdetails-variation-value-name'}
                            {if $Variation->cTyp === 'IMGSWATCHES'}
                                <span class="swatches-selected text-primary" data-id="{$Variation->kEigenschaft}">
                                    {foreach $Variation->Werte as $variationValue}
                                        {if isset($oVariationKombi_arr[$variationValue->kEigenschaft])
                                            && in_array($variationValue->kEigenschaftWert, $oVariationKombi_arr[$variationValue->kEigenschaft])}
                                        {$variationValue->cName}
                                        {break}
                                        {/if}
                                    {/foreach}
                                </span>
                            {/if}
                            {/block}
                        </legend>
                        {if $Variation->cTyp === 'SELECTBOX'}
                            {block name='productdetails-variation-select-outer'}
                            {block name='productdetails-info-variation-select'}
                            <select class="form-control" title="{if isset($smallView) && $smallView}{$Variation->cName} - {/if}{lang key='pleaseChooseVariation' section="productDetails"}" name="eigenschaftwert[{$Variation->kEigenschaft}]"{if !$showMatrix} required{/if}{if $form} form="{$form}" {/if}>
                                {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                                    {assign var="bSelected" value=false}

                                    {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                        {assign var="bSelected" value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                    {/if}
                                    {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                        {assign var="bSelected" value=$Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert}
                                    {/if}
                                    {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                                    $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                                    !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                                    {else}
                                        {block name='productdetails-variation-select-inner'}
                                            {block name='productdetails-variation-select-include-variation-value'}
                                                {include file='productdetails/variation_value.tpl' assign='cVariationsWert'}
                                            {/block}
                                            <option value="{$Variationswert->kEigenschaftWert}" class="variation"
                                                    data-content="<span data-value='{$Variationswert->kEigenschaftWert}'>{trim($cVariationsWert)|escape:'html'}
                                                {if $Variationswert->notExists} <span class='badge badge-danger badge-not-available'>{lang key='notAvailableInSelection'}</span>
                                                {elseif !$Variationswert->inStock}<span class='badge badge-danger badge-not-available'>{lang key='ampelRot'}</span>{/if}</span>"
                                                    data-type="option"
                                                    data-original="{$Variationswert->cName}"
                                                    data-key="{$Variationswert->kEigenschaft}"
                                                    data-value="{$Variationswert->kEigenschaftWert}"
                                                    {if !empty($Variationswert->getImage(\JTL\Media\Image::SIZE_XS))}
                                                        data-list='{prepare_image_details item=$Variationswert json=true}'
                                                        data-title='{$Variationswert->cName}'
                                                    {/if}
                                                    {if isset($Variationswert->oVariationsKombi)}
                                                        data-ref="{$Variationswert->oVariationsKombi->kArtikel}"
                                                    {/if}
                                                    {if $bSelected} selected="selected"{/if}>
                                                {trim($cVariationsWert)|strip_tags}
                                            </option>
                                        {/block}
                                    {/if}
                                {/foreach}
                            </select>
                            {/block}
                            {/block}
                        {elseif $Variation->cTyp === 'RADIO'}
                            {block name='productdetails-variation-radio-outer'}
                                <div class="radio-buttons">
                                    {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                                        {assign var="bSelected" value=false}
                                        {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                        {assign var="bSelected" value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                        {/if}
                                        {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                            {assign var="bSelected" value=$Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert}
                                        {/if}
                                        {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                                        $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                                        !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                                        {else}
                                            {block name='productdetails-variation-radio-inner'}
                                            {block name='productdetails-info-variation-radio'}
                                            <label class="variation {if $Variationswert->notExists}not-available{/if}" for="{if $modal}modal-{elseif isset($smallView) && $smallView}a-{$Artikel->kArtikel}{/if}{$variationPrefix|default:''}vt{$Variationswert->kEigenschaftWert}"
                                                data-type="radio"
                                                data-original="{$Variationswert->cName}"
                                                data-key="{$Variationswert->kEigenschaft}"
                                                data-value="{$Variationswert->kEigenschaftWert}"
                                                {if !empty($Variationswert->cBildPfadMini)}
                                                    data-list='{prepare_image_details item=$Variationswert json=true}'
                                                    data-title='{$Variationswert->cName}'
                                                {/if}
                                                {if isset($Variationswert->oVariationsKombi)}
                                                    data-ref="{$Variationswert->oVariationsKombi->kArtikel}"
                                                {/if}>
                                                <input type="radio"
                                                    name="eigenschaftwert[{$Variation->kEigenschaft}]"
                                                    id="{if $modal}modal-{elseif isset($smallView) && $smallView}a-{$Artikel->kArtikel}{/if}{$variationPrefix|default:''}vt{$Variationswert->kEigenschaftWert}"
                                                    value="{$Variationswert->kEigenschaftWert}"
                                                    aria-label="{$Variationswert->cName}"
                                                    {if $bSelected}checked="checked"{/if}
                                                    {if $smarty.foreach.Variationswerte.index === 0 && !$showMatrix} required{/if}
                                                    {if $form} form="{$form}" {/if}
                                                    >
                                                {block name='productdetails-variation-radio-include-variation-value'}
                                                {include file="productdetails/variation_value.tpl"}{if $Variationswert->notExists}<span class='badge badge-danger badge-not-available'>{lang key='notAvailableInSelection'}</span>{elseif !$Variationswert->inStock}<span class='badge badge-danger badge-not-available'>{lang key='ampelRot'}</span>{/if}
                                                {/block}
                                            </label>
                                            {/block}
                                            {/block}
                                        {/if}
                                    {/foreach}
                                </div>
                            {/block}
                        {elseif $Variation->cTyp === 'IMGSWATCHES' || $Variation->cTyp === 'TEXTSWATCHES'}
                            {block name='productdetails-variation-swatch-outer'}
                                <div class="cluster swatches {$Variation->cTyp|lower}">
                                    {foreach name=Variationswerte from=$Variation->Werte key=y item=Variationswert}
                                        {assign var="bSelected" value=false}
                                        {assign var=hasImage value=!empty($Variationswert->getImage(\JTL\Media\Image::SIZE_XS))}
                                        {if isset($oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                            {assign var="bSelected" value=in_array($Variationswert->kEigenschaftWert, $oVariationKombi_arr[$Variationswert->kEigenschaft])}
                                        {/if}
                                        {if isset($oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft])}
                                            {assign var="bSelected" value=($Variationswert->kEigenschaftWert == $oEigenschaftWertEdit_arr[$Variationswert->kEigenschaft]->kEigenschaftWert)}
                                        {/if}
                                        {if ($Artikel->kVaterArtikel > 0 || $Artikel->nIstVater == 1) && $Artikel->nVariationOhneFreifeldAnzahl == 1 &&
                                        $Einstellungen.global.artikeldetails_variationswertlager == 3 &&
                                        !empty($Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar) && $Artikel->VariationenOhneFreifeld[$i]->Werte[$y]->nNichtLieferbar == 1}
                                            {* /do nothing *}
                                        {else}
                                            {block name='productdetails-variation-swatch-inner'}
                                            {block name='productdetails-info-variation-swatch'}
                                            <label class="variation block{if $bSelected} active{/if}{if $Variationswert->notExists} not-available{/if}{if $hasImage && $Variation->cTyp === 'IMGSWATCHES'} variation--image{else} variation--text text-center{/if}"
                                                    data-type="swatch"
                                                    data-original="{$Variationswert->cName}"
                                                    data-key="{$Variationswert->kEigenschaft}"
                                                    data-value="{$Variationswert->kEigenschaftWert}"
                                                    for="{if $modal}modal-{elseif isset($smallView) && $smallView}a-{$Artikel->kArtikel}{/if}{$variationPrefix|default:''}vt{$Variationswert->kEigenschaftWert}"
                                                    {if !empty($Variationswert->cBildPfadMini)}
                                                        data-list='{prepare_image_details item=$Variationswert json=true}'
                                                    {/if}
                                                    {if $Variationswert->notExists}
                                                        title="{lang key='notAvailableInSelection'}"
                                                        data-title="{$Variationswert->cName} - {lang key='notAvailableInSelection'}"
                                                        data-toggle="tooltip"
                                                    {elseif $Variationswert->inStock}
                                                        data-title="{$Variationswert->cName}"
                                                    {else}
                                                        title="{lang key='ampelRot'}"
                                                        data-title="{$Variationswert->cName} - {lang key='ampelRot'}"
                                                        data-toggle="tooltip"
                                                        data-stock="out-of-stock"
                                                    {/if}
                                                    {if isset($Variationswert->oVariationsKombi)}
                                                        data-ref="{$Variationswert->oVariationsKombi->kArtikel}"
                                                    {/if}>
                                                <input type="radio"
                                                    class="control-hidden"
                                                    name="eigenschaftwert[{$Variation->kEigenschaft}]"
                                                    id="{if $modal}modal-{elseif isset($smallView) && $smallView}a-{$Artikel->kArtikel}{/if}{$variationPrefix|default:''}vt{$Variationswert->kEigenschaftWert}"
                                                    value="{$Variationswert->kEigenschaftWert}"
                                                    {if $bSelected}checked="checked"{/if}
                                                    {if $smarty.foreach.Variationswerte.index === 0 && !$showMatrix} required{/if}
                                                    {if $form} form="{$form}" {/if}
                                                    />
                                                <span class="label-variation">
                                                    {* from NOVA Template *}
                                                    {if $hasImage && $Variation->cTyp === 'IMGSWATCHES'}
                                                        {include file='snippets/image.tpl'  class='img-fluid' item=$Variationswert srcSize='xs' progressiveLoading=false}
                                                        <span class="sr-only">{$Variationswert->cName}</span>
                                                    {else}
                                                        {$Variationswert->cName}
                                                    {/if}
                                                    {* /NOVA *}

                                                    {if $Variationswert->notExists}
                                                        <span class="sr-only"> - {lang key='notAvailableInSelection'}</span>
                                                    {elseif !$Variationswert->inStock}
                                                        <span class="sr-only"> - {lang key='ampelRot'}</span>
                                                    {/if}
                                                </span>
                                                {include file='productdetails/variation_value.tpl' hideVariationValue=true}
                                            </label>
                                            {/block}
                                            {/block}
                                        {/if}
                                    {/foreach}
                                </div>
                            {/block}
                        {elseif $Variation->cTyp === 'FREIFELD' || $Variation->cTyp === 'PFLICHT-FREIFELD'}
                            {block name='productdetails-variation-info-variation-text'}
                            {block name='productdetails-info-variation-text'}
                                <label for="vari-{$Variation->kEigenschaft}" class="sr-only">{$Variation->cName}</label>
                                    {input id="vari-{$Variation->kEigenschaft}" name='eigenschaftwert['|cat:$Variation->kEigenschaft|cat:']'
                                       value=$oEigenschaftWertEdit_arr[$Variation->kEigenschaft]->cEigenschaftWertNameLocalized|default:''
                                       data=['key' => $Variation->kEigenschaft] required=$Variation->cTyp === 'PFLICHT-FREIFELD'
                                       maxlength=255}
                            {/block}
                            {/block}
                        {/if}
                    </fieldset>
                    </li>
                {/strip}
                {/foreach}
                </ul>
            {/block}
        </div>
    </div>
    {/block}
    {if $hr}
        <hr>
    {/if}
{/if}
{/block}