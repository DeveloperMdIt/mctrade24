{block name='form-expandable'}
{if $showVariations}
    <div class="product-list-item__variations-col{*  {$variationCol} *}">
        <div class="basket-variations">
            {assign var="singleVariation" value=true}
            {include file="productdetails/variation.tpl" simple=$Artikel->isSimpleVariation showMatrix=false smallView=true ohneFreifeld=($hasOnlyListableVariations == 2) hr=false}
        </div>
    </div>
{/if}


<div class="product-list-item__basket-col{*  {$basketCol} *}" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
    <link itemprop="businessFunction" href="http://purl.org/goodrelations/v1#Sell" />
    {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
    <div class='delivery-status'>
        {block name='delivery-status'}
            {assign var=anzeige value=$Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandsanzeige}
            {if $Artikel->inWarenkorbLegbar === $smarty.const.INWKNICHTLEGBAR_UNVERKAEUFLICH}
                <span class="status"><small>{lang key='productUnsaleable' section='productDetails'}</small></span>
            {elseif $Artikel->nErscheinendesProdukt}
                <div class="availablefrom">
                    <small>{lang key='productAvailableFrom'}: {$Artikel->Erscheinungsdatum_de}</small>
                </div>
                {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $Artikel->inWarenkorbLegbar === 1}
                    <div class="attr attr-preorder"><small class="value">{lang key='preorderPossible'}</small></div>
                {/if}
            {elseif $anzeige !== 'nichts'
                && $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N'
                && $Artikel->getBackorderString() !== ''
                && ($Artikel->cLagerKleinerNull === 'N'
                    || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                <div class="signal_image status-1"><small>{$Artikel->getBackorderString()}</small></div>
            {elseif $anzeige !== 'nichts'
                && $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N'
                && $Artikel->cLagerBeachten === 'Y'
                && $Artikel->fLagerbestand <= 0
                && $Artikel->fLieferantenlagerbestand > 0
                && $Artikel->fLieferzeit > 0
                && ($Artikel->cLagerKleinerNull === 'N'
                    || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                <div class="signal_image status-1"><small>{lang key='supplierStockNotice' printf=$Artikel->fLieferzeit}</small></div>
            {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</small></div>
            {elseif $anzeige === 'ampel'}
                <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->AmpelText}</small></div>
            {/if}
            {if $Artikel->cEstimatedDelivery}
                <div class="estimated_delivery">
                    <small>{lang key='shippingTime'}: {$Artikel->cEstimatedDelivery}</small>
                </div>
            {/if}
        {/block}
    </div>

    <div class="basket-details">
        {block name="basket-details"}
            {if ($Artikel->inWarenkorbLegbar === 1
                     || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'))
                 && (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0)
                     || $hasOnlyListableVariations === 1)
                 && !$Artikel->bHasKonfig
                 && $Einstellungen.template.productlist.buy_productlist === 'Y'
                 && !isset($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_VOUCHER_FLEX])}

                <div class="product-list-item__quantity-wrapper product-list-item__quantity-wrapper--flex" data-bulk={!empty($Artikel->staffelPreis_arr)}>
                    {* {if $Artikel->cEinheit} *}
                    {* <div class="js-spinner quantity-input quantity-input--list quantity-input--flex">
                        <span class="js-spinner-button" data-spinner-button="down"></span>
                        <div class="js-spinner-input{if $Artikel->cEinheit} js-spinner--unit-addon{/if}">
                        {strip}
                            {$max = $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|default:''}
                            {$min = $admPro->getMinValueArticle($Artikel)}
                            <input type="number" 
                                min="{$min}"
                                {if !empty($max)}max="{$max}"{/if}
                                {if $Artikel->fAbnahmeintervall > 0}step="{$Artikel->fAbnahmeintervall}"{/if}
                                size="2" 
                                autocomplete="off" 
                                id="{$idPrefix|default:''}quantity{$Artikel->kArtikel}" class="quantity form-control" 
                                name="anzahl" 
                                value="{if $min > 0}{$min}{else}1{/if}" />
                            {if $Artikel->cEinheit}
                                <div class="js-spinner__unit-addon unit">{$Artikel->cEinheit}</div>
                            {/if}
                        {/strip}
                        </div>
                        <span class="js-spinner-button" data-spinner-button="up"></span>
                    </div> *}
                    {* <span class="change_quantity input-group-btn"> *}

                    {quantityInput name="anzahl" article=$Artikel wrapperClass="quantity-input quantity-input--list quantity-input--flex" idPrefix=$idPrefix|default:''} 

                    <button name="inWarenkorb" type="submit" class="btn btn-primary text-nowrap flex-grow-1{if $Artikel->inWarenkorbLegbar == -1} disabled btn--disabled-dashed{/if}" id="{$idPrefix|default:''}submit{$Artikel->kArtikel}" title="{lang key="addToCart" section="global"}">
                        <span class="add-to-basket__label">{if $Artikel->inWarenkorbLegbar == -1}{lang key="soldout"}{else}{lang key='addToCart'}{/if}</span>
                    </button>
                    {* </span> *}

                        {* unnecessary duplication *}
                    {* {else}
                        <div class="input-group input-group-sm">
                            <input type="number" min="0"{if $Artikel->fAbnahmeintervall > 0} step="{$Artikel->fAbnahmeintervall}"{/if} size="2" onfocus="this.setAttribute('autocomplete', 'off');" id="quantity{$Artikel->kArtikel}" class="quantity form-control text-right" name="anzahl" value="{if $Artikel->fAbnahmeintervall > 0}{if $Artikel->fMindestbestellmenge > $Artikel->fAbnahmeintervall}{$Artikel->fMindestbestellmenge}{else}{$Artikel->fAbnahmeintervall}{/if}{else}1{/if}" />

                            <span class="change_quantity input-group-btn">
                                <button name="inWarenkorb" type="submit" class="btn btn-primary" id="submit{$Artikel->kArtikel}" title="{lang key="addToCart" section="global"}"><span><i class="fa fa-shopping-cart"></i> {lang key="addToCart" section="global"}</span></button>
                            </span>
                        </div>
                    {/if} *}
                </div>
            {else}
                {* zum artikel button *}
                <div class="{* top7 *}{*  form-group  *}text-right">
                    <a class="btn btn-secondary" role="button" href="{$Artikel->cURLFull}">{lang key="details"}</a>
                </div>
            {/if}
        {/block}
    </div>
</div>
{if $Artikel->kArtikelVariKombi > 0}
    <input type="hidden" name="aK" value="{$Artikel->kArtikelVariKombi}" />
{/if}
{if isset($Artikel->kVariKindArtikel)}
    <input type="hidden" name="VariKindArtikel" value="{$Artikel->kVariKindArtikel}" />
{/if}
<input type="hidden" name="a" value="{$Artikel->kArtikel}" />
<input type="hidden" name="wke" value="1" />
<input type="hidden" name="overview" value="1" />
<input type="hidden" name="Sortierung" value="{if isset($Suchergebnisse->Sortierung)}{$Suchergebnisse->Sortierung}{/if}" />
{if $smarty.const.SHOW_CHILD_PRODUCTS === 2 && $Artikel->kVaterArtikel > 0 && !empty($Artikel->cVariationKombi)}
    {$variKombiArray = $admPro->varikombiStringToArray($Artikel->cVariationKombi)}
    {foreach $variKombiArray as $value}
        <input type="hidden" name="eigenschaftwert[{$value[0]}]" value="{$value[1]}">
    {/foreach}
{/if}
{if $Suchergebnisse->getPages()->getCurrentPage() > 1}
    <input type="hidden" name="seite" value="{$Suchergebnisse->getPages()->getCurrentPage()}" />
{/if}
{if $NaviFilter->hasCategory()}
    <input type="hidden" name="k" value="{$NaviFilter->getCategory()->getValue()}" />
{/if}
{if $NaviFilter->hasManufacturer()}
    <input type="hidden" name="h" value="{$NaviFilter->getManufacturer()->getValue()}" />
{/if}
{if $NaviFilter->hasSearchQuery()}
    <input type="hidden" name="l" value="{$NaviFilter->getSearchQuery()->getValue()}" />
{/if}
{if $NaviFilter->hasCharacteristicValue()}
    <input type="hidden" name="m" value="{$NaviFilter->getCharacteristicValue()->getValue()}" />
{/if}
{if $NaviFilter->hasCategoryFilter()}
    {assign var=cfv value=$NaviFilter->getCategoryFilter()->getValue()}
    {if is_array($cfv)}
        {foreach $cfv as $val}
            <input type="hidden" name="hf" value="{$val}" />
        {/foreach}
    {else}
        <input type="hidden" name="kf" value="{$cfv}" />
    {/if}
{/if}
{if $NaviFilter->hasManufacturerFilter()}
    {assign var=mfv value=$NaviFilter->getManufacturerFilter()->getValue()}
    {if is_array($mfv)}
        {foreach $mfv as $val}
            <input type="hidden" name="hf" value="{$val}" />
        {/foreach}
    {else}
        <input type="hidden" name="hf" value="{$mfv}" />
    {/if}
{/if}
{foreach $NaviFilter->getCharacteristicFilter() as $filter}
    <input type="hidden" name="mf{$filter@iteration}" value="{$filter->getValue()}" />
{/foreach}
{/block}