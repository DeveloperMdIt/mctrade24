{*custom*}
{block name='productlist-item-box'}
    
{if empty($Artikel->Variationen)}
    {$admPro->getArticleVariations($Artikel)}
{/if}

{if $Einstellungen.template.productlist.variation_select_productlist === 'N'}
    {assign var='hasOnlyListableVariations' value=0}
{else}
    {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign='hasOnlyListableVariations'}
{/if}

{$alignment = $admorris_pro_templateSettings->productCellTextAlignment}
{$hoverEnabled = $Einstellungen.template.productlist.hover_productlist === 'Y'}
{$hoverEnabledClass = (!empty($hoverEnabled)) ? 'hover-enabled' : 'hover-disabled'}

{* remove 'card card-body' class coming from opc productStream portlet, because it causes an unwanted animation *}
{if !empty($class)}
{$class = str_replace('card card-body', '', $class)}
{/if}

<div id="{$idPrefix|default:''}result-wrapper_buy_form_{$Artikel->kArtikel}" data-wrapper="true" class="product-cell mw-100 {$hoverEnabledClass}{if isset($listStyle) && $listStyle === 'gallery'} active {/if}{if isset($class)} {$class}{/if}{block name='item-box-product-cell-class'}{/block}"{if $idPrefix|default:false} data-id-prefix="{$idPrefix}"{/if}>
    {block name='item-box-wrapper'}   
        <div class="product-cell__wrapper">
            <div class="image-link-wrapper">
                {block name="productlist-item-list-image"}{* Nova Block for compatibility *}
                    {include 'productlist/item_box_image.tpl'}
                {/block}
            </div>

            <div class="product-cell__body{if $alignment === 'center'} product-cell__body--center{/if}">
                {block name='productlist-image-caption'}
                <div class="product-cell__caption caption{if $alignment === 'center'} text-center{/if} stack stack--collapse-margins">
                  {*   <form action="" method="post" class="product-actions" data-toggle="product-actions">
                        {$jtl_token}
                        {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y' && $Einstellungen.artikeluebersicht.artikeluebersicht_wunschzettel_anzeigen === 'Y'}
                            <button name="Wunschliste" type="submit" class="wishlist btn btn-secondary float-right" value="" title="{lang key="addToWishlist" section="productDetails"}" aria-label="{lang key="addToWishlist" section="productDetails"}">
                                <span class="fa fa-heart" aria-hidden="true"></span>
                            </button>
                        {/if}
                    </form>
                    *}

                    {block name='item-box-title-wrapper'}
                        <div class="product-cell__title-wrapper">
                            {block name='item-box-title'}
                                <div class="product-cell__title title h4"><a href="{$Artikel->cURLFull}">{$Artikel->cKurzbezeichnung}</a></div>
                            {/block}
                        </div>
                    {/block}

                    {block name='item-box-article-number'}
                        {if $admorris_pro_templateSettings->article_number_gallery === true}
                            <div class="product-cell__article-number">
                                <span>{lang key="productNo" section="global"}: {$Artikel->cArtNr}</span>
                            </div>
                        {/if}
                    {/block}

                    {block name='item-box-rating'}
                        {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                            <div class="product-cell__rating">
                                {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung link=$Artikel->cURLFull}
                            </div>
                        {/if}
                    {/block}
                    {block name='item-box-price'}
                        {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                    {/block}
                </div>{* /caption *}
                {/block}
                <div class="expandable">
                {block name='item-box-form'}
                <form id="{$idPrefix|default:''}buy_form_{$Artikel->kArtikel}" action="{$ShopURL}/" method="post" class="form form-basket jtl-validate{if isset($class)} {$class}{/if}" data-toggle="{block name='item-box-form-data-toggle'}basket-add{/block}">
                    {$jtl_token}
                    {block name='item-box-expandable'}                      
                        <div class="stack">
                            {block name='productlist-delivery-status'}
                                <div class="product-cell__delivery-status delivery-status{if $alignment === 'center'} text-center{/if}">
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
                                        && ($Artikel->cLagerKleinerNull === 'N' || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                                        <div class="signal_image status-1"><small>{$Artikel->getBackorderString()}</small></div>
                                {elseif $anzeige !== 'nichts'
                                    && $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N'
                                    && $Artikel->cLagerBeachten === 'Y'
                                    && $Artikel->fLagerbestand <= 0
                                    && $Artikel->fLieferantenlagerbestand > 0
                                    && $Artikel->fLieferzeit > 0
                                    && ($Artikel->cLagerKleinerNull === 'N' || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                                    <div class="signal_image status-1"><small>{lang key='supplierStockNotice' printf=$Artikel->fLieferzeit}</small></div>
                                    {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                                        <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</small></div>
                                    {elseif $anzeige === 'ampel'}
                                        <div class="signal_image status-{$Artikel->Lageranzeige->nStatus}"><small>{$Artikel->Lageranzeige->AmpelText}</small></div>
                                    {/if}
                                    {if $Artikel->cEstimatedDelivery}
                                        <div class="estimated_delivery{if $alignment === 'center'} text-center{/if}">
                                                <small>{lang key='shippingTime'}: {$Artikel->cEstimatedDelivery}</small>
                                        </div>
                                    {/if}
                                </div>
                            {/block}
                            {* custom - kurzbeschreibung hinzugefuegt *}
                            {block name='item-box-below-productlist-delivery-status'}
                                {if $Einstellungen.artikeluebersicht.artikeluebersicht_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                                    <div class="product-cell__shortdescription">
                                        {$Artikel->cKurzBeschreibung}
                                    </div>
                                {/if}
                            {/block}
                            {* /custom *}
                            {block name='item-box-variations'}
                                {if $hasOnlyListableVariations > 0 && !$Artikel->bHasKonfig && $Artikel->kEigenschaftKombi === 0}
                                    <div class="basket-variations">
                                        {assign var="singleVariation" value=true}
                                        {include file="productdetails/variation.tpl" hr=false simple=$Artikel->isSimpleVariation showMatrix=false ohneFreifeld=($hasOnlyListableVariations == 2)}
                                    </div>
                                {/if}
                            {/block}

                            <div class="{block name='item-box-add-basket-class'}product-cell__button-wrapper{/block}">
                                {block name='productlist-add-basket'}
                                    {if ($Artikel->inWarenkorbLegbar === 1
                                        || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'))
                                        && (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0)
                                            || $hasOnlyListableVariations === 1)
                                        && !$Artikel->bHasKonfig
                                        && $Einstellungen.template.productlist.buy_productlist === 'Y'
                                        && !isset($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_VOUCHER_FLEX])
                                        && !($Artikel->nIstVater && $Artikel->kVaterArtikel == 0)}
                                        <div class="quantity-wrapper text-center stack" data-bulk="{!empty($Artikel->staffelPreis_arr)}">
                                            {strip}
                                                {* <div class="alert alert-info choose-variations mb-0">{lang key='chooseVariations' section='messages'}</div>
                                            {else} *}
                                                {$wrapperClassName='product-cell__quantity'}
                                                {if !$admorris_pro_templateSettings->productSliderQuantitySelection}
                                                    {$wrapperClassName = $wrapperClassName|cat:' hide-quantity'}
                                                {/if}
                                                {quantityInput name="anzahl" article=$Artikel wrapperClass=$wrapperClassName|default:'' idPrefix=$idPrefix|default:''}
                                
                                                <div class="add-to-basket">
                                                    {block name='item-box-add-to-baseket-button'}
                                                        <button name="inWarenkorb" type="submit" value="In den Warenkorb" class="product-cell__add-to-basket-button btn btn-primary" id="{$idPrefix|default:''}submit{$Artikel->kArtikel}" title="{lang key='addToCart' section='global'}">
                                                            {lang key="addToCart" section="global"}
                                                        </button>
                                                    {/block}
                                                </div>
                                            {/strip}
                                        </div>
                                    {else}
                                        {block name='item-box-to-article-link'}
                                            <a class="product-cell__add-to-basket-button btn btn-outline-secondary btn-block" href="{$Artikel->cURLFull}">{lang key="details"}</a>
                                        {/block}
                                    {/if}
                                {/block}
                            </div>
                        </div>
            
                        {if $Artikel->kArtikelVariKombi > 0}
                            <input type="hidden" name="aK" value="{$Artikel->kArtikelVariKombi}" />
                        {/if}
                        
                        {* {Shop::dbg($Artikel->cVariationKombi)}
                        kArtikel<pre>{var_dump($Artikel->kArtikel)}</pre>
                        kVaterArtikel<pre>{var_dump($Artikel->kVaterArtikel)}</pre>
                        kVariKindArtikel<pre>{var_dump($Artikel->kVariKindArtikel)}</pre> *}
                        {if isset($Artikel->kVariKindArtikel)}
                            <input type="hidden" name="VariKindArtikel" value="{$Artikel->kVariKindArtikel}" />
                        {/if}

                        {* This is fot the SHOW_CHILD_PRODUCTS setting. Bestseller sliders can also show child products
                        and when the slider buy function is active we need this field for them too. But when loading
                        child products it shouldn't be output, thats why we check isAjax here *}
                        {if $Artikel->kVaterArtikel > 0 && !empty($Artikel->cVariationKombi) && !isset($smarty.get.isAjax)}
                            {$variKombiArray = $admPro->varikombiStringToArray($Artikel->cVariationKombi)}
                            {foreach $variKombiArray as $value}
                                <input type="hidden" name="eigenschaftwert[{$value[0]}]" value="{$value[1]}">
                            {/foreach}
                        {/if}
                        <input type="hidden" name="a" value="{$Artikel->kArtikel}" />

                        <input type="hidden" name="wke" value="1" />
                        {* <input type="hidden" name="overview" value=" 1" /> *}
                        <input type="hidden" name="Sortierung" value="{if !empty($Suchergebnisse->Sortierung)}{$Suchergebnisse->Sortierung}{/if}" />
                    {if isset($Suchergebnisse) && $Suchergebnisse->getPages()->getCurrentPage() > 1}
                        <input type="hidden" name="seite" value="{$Suchergebnisse->getPages()->getCurrentPage()}" />
                    {/if}
                    {if isset($NavFilter)}
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
                        {/if}
                    {/if}
                    {/block}
                    </form>
                    {/block}
                </div>{* /expandable *}
            </div>{* /product-cell__body *}
        </div>
    {/block}
</div>
{/block}