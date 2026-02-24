{*custom*}
{block name='productdetails-details'}
{has_boxes position='left' assign='hasLeftBox'}

{if isset($bWarenkorbHinzugefuegt) && $bWarenkorbHinzugefuegt}
    {include file='productdetails/pushed_success.tpl' card=true}
{else}
    {$alertList->displayAlertByKey('productNote')}
{/if}

{* <div class="h1 visible-xs text-center">{$Artikel->cName}</div> *}

{opcMountPoint id='opc_before_buy_form'}

{block 'productdetails-buy-form'}
    {form id="buy_form{if !empty($smarty.get.quickView)}-quickview{/if}" action=$Artikel->cURLFull class="jtl-validate"}
        {button aria=["label"=>"{lang key='addToCart'}"]
            name="inWarenkorb"
            variant="hidden"
            type="submit"
            value="{lang key='addToCart'}"
            tabindex="-1"
            disabled=$Artikel->bHasKonfig && !$isConfigCorrect|default:false
            class="js-cfg-validate btn-hidden-default"}{/button}
        {block 'productdetails-product-primary'}
            {row no-gutters=true class="navigation-wrapper"}
                {col cols="auto"}
                    {include file='layout/breadcrumb.tpl'}
                {/col}
                {col class='navigation-arrows'}
                    {include file='snippets/product_pagination.tpl'}
                {/col}
            {/row}
            
            <div class="row product-primary" id="product-offer">
                <div class="product-gallery{if $hasLeftBox && $Einstellungen.template.theme.left_sidebar === 'Y'} col-md-5{else} col-md-6 col-lg-7 col-xl-6{/if}">
                    {opcMountPoint id='opc_before_gallery'}
                    {include file='productdetails/image.tpl'}
                    {opcMountPoint id='opc_after_gallery'}
                    <div class="clearfix"></div>
                </div>
                <div class="product-info{if $hasLeftBox && $Einstellungen.template.theme.left_sidebar === 'Y'} col-md-7{else} col-md-6 col-lg-5 col-xl-5 offset-xl-1{/if}">
                    {block name='productdetails-info'}
                    <div class="product-info-inner">
                        {block name='productdetails-info-manufacturer-wrapper'}
                        {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'N' && isset($Artikel->cHersteller)}
                            {block name='product-info-manufacturer'}
                            {* custom -  manufacturer moved to attributes.tpl *}
                            {/block}
                        {/if}
                        {/block}
        
                        {if ($Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0)}
                            {block name='productdetails-info-rating-wrapper'}
                            <div class="rating-wrapper">
                                {block name='productdetails-details-include-rating'}
                                    {if empty($smarty.get.quickView)}
                                        {link href="{$Artikel->cURLFull}#tab-votes"
                                            id="jump-to-votes-tab"
                                            aria=["label"=>{lang key='Votes'}]
                                        }
                                            {include file='productdetails/rating.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
                                        {/link}
                                    {else}
                                        {include file='productdetails/rating.tpl' stars=$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
                                    {/if}
                                {/block}
                            </div>{* /rating-wrapper*}
                            {/block}
                        {/if}
        
                        <div class="clearfix"></div>
            
                        <div class="product-headline">
                            {block name='productdetails-info-product-title'}
                            {opcMountPoint id='opc_before_headline'}
                            <h1 class="fn product-title mt-0">{$Artikel->cName}</h1>
                            {/block}
                            {if {$Artikel->cBildpfad_thersteller} && {$admorris_pro_templateSettings->showBrandLogo}}
                                {block name='productdetails-info-product-brand-logo'}
                                    <div class="manufacturer-logo">
                                        <a href="{$Artikel->cHerstellerURL}">
                                            <img src="{$Artikel->cBildpfad_thersteller}" alt="Logo {$Artikel->cHersteller}">
                                        </a>
                                    </div>
                                {/block}
                            {/if}
                        </div>
                        
                        {block name='productdetails-info-price'}
                            {if isset($Artikel->Preise->strPreisGrafik_Detail)}
                                {assign var=priceImage value=$Artikel->Preise->strPreisGrafik_Detail}
                            {else}
                                {assign var=priceImage value=null}
                            {/if}
                            {if !($Artikel->Preise->fVKNetto == 0 && isset($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_VOUCHER_FLEX]))}
                                {include file='productdetails/price.tpl' Artikel=$Artikel  tplscope='detail' outputID=true}{* id for itemref *}
                            {/if}
                            
                        {/block}
        
                        {block name='productdetails-info-description-wrapper'}
                        {if $Einstellungen.artikeldetails.artikeldetails_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                            {block name='productdetails-info-description'}
                            <div class="shortdesc">
                                {$Artikel->cKurzBeschreibung}
                            </div>
                            {/block}
                        {/if}
                        {/block}
        
                        {* {block name="productdetails-info-category-wrapper"}
                        {if $Einstellungen.artikeldetails.artikeldetails_kategorie_anzeigen === 'Y'}
                            {block name="productdetails-info-category"}
                            <hr>
                            <p class="product-category word-break">
                                <span class="text-muted">{lang key="category" section="global"}: </span>
                                {assign var=i_kat value=$Brotnavi|@count}{assign var=i_kat value=$i_kat-2}
                                <a href="{$Brotnavi[$i_kat]->url}" itemprop="category">{$Brotnavi[$i_kat]->name}</a>
                            </p>
                            {/block}
                        {/if}
                        {/block} *}
        
                        <div id="product-offer-data" class="product-offer">

                            {block name="productdetails-info-hidden"}

                            <input type="submit" name="inWarenkorb" value="1" class="d-none" />
                            {if $Artikel->kArtikelVariKombi > 0}
                                <input type="hidden" name="aK" value="{$Artikel->kArtikelVariKombi}" />
                            {/if}
                            {if isset($Artikel->kVariKindArtikel)}
                                <input type="hidden" name="VariKindArtikel" value="{$Artikel->kVariKindArtikel}" />
                            {/if}
                            {if isset($smarty.get.ek)}
                                <input type="hidden" name="ek" value="{$smarty.get.ek|intval}" />
                            {/if}
                            <input type="hidden" id="AktuellerkArtikel" class="current_article" name="a" value="{$Artikel->kArtikel}" />
                            <input type="hidden" name="wke" value="1" />
                            <input type="hidden" name="show" value="1" />
                            <input type="hidden" name="kKundengruppe" value="{$smarty.session.Kundengruppe->getID()}" />
                            <input type="hidden" name="kSprache" value="{$smarty.session.kSprache}" />
                            {/block}
                            {block name="productdetails-info-variation"}
                                {block name='productdetails-details-include-variation'}
                                    <!-- VARIATIONEN -->
                                    {include file='productdetails/variation.tpl' simple=$Artikel->isSimpleVariation showMatrix=$showMatrix}
                                {/block}
                            {/block}
                        </div>
                        <div class="u-container-inline">
                            {block 'productdetails-action-wrapper'}
                                <div class="basket-action-wrapper d-print-none product-buy{if $Artikel->inWarenkorbLegbar == 1 && empty($Artikel->FunktionsAttribute.unverkaeuflich)} is-buyable{/if}{if $Artikel->nErscheinendesProdukt} coming_soon{/if}">
                                    {*WARENKORB anzeigen wenn keine variationen mehr auf lager sind?!*}
                                    {if empty($Artikel->FunktionsAttribute.unverkaeuflich)}
                                        {* {if ($Artikel->inWarenkorbLegbar == 1 || $Artikel->nErscheinendesProdukt == 1) || $Artikel->Variationen} *}
                                        {* keine if Abfrage, damit der Sold Out Button und die Verfuegbarkeitsanfrage angezeigt werden *}
                                        {if $Artikel->bHasKonfig}
                                            {block name='productdetails-details-config-button'}
                                                {if isset($Artikel->Variationen) && $Artikel->Variationen|count > 0}
                                                    {block name='productdetails-details-config-button-info'}
                                                        {* {col cols=12 class="js-choose-variations-wrapper"} *}
                                                            {alert variation="info" class="choose-variations"}
                                                                {lang key='chooseVariations' section='messages'}
                                                            {/alert}
                                                        {* {/col} *}
                                                    {/block}
                                                {/if}
                                                {block name='productdetails-details-config-button-button'}
                                                    {link type="button"
                                                        class="btn btn-secondary start-configuration js-start-configuration"
                                                        value="{lang key='configure'}"
                                                        block=true
                                                        href="#cfg-container"
                                                        disabled=(isset($Artikel->Variationen) && $Artikel->Variationen|count > 0)
                                                    }
                                                        {$admIcon->renderIcon('cogs', 'icon-content')} <span>{lang key='configure'}</span>
                                                    {/link}
                                                {/block}
                                            {/block}
                                        {else}
                                            {block name='productdetails-details-include-basket'}
                                                {if empty($smarty.get.quickView)}
                                                    {include file='productdetails/basket.tpl'}
                                                {/if}
                                            {/block}
                                        {/if}
                                    {/if}

                                    <div id="plugin-placeholder-article"></div>
    
                                    {block name="payment_icons"}
                                        {if isset($admorris_pro_templateSettings->paymentIcons) && !empty($admorris_pro_templateSettings->paymentIcons) && $admorris_pro_templateSettings->paymentProviderLogosDetail}
                                            <section class="pay-icons-wrapper" aria-labelledby="payment_icons-title">
                                                <h2 id="payment_icons-title" class="sr-only">{lang key="paymenticons" section="aria"}</h2>
                                                <ul id="payment_icons_detail" class="pay-icons list-unstyled cluster">
                                                    {foreach from=$admorris_pro_templateSettings->paymentIcons item=icon key=key}
                                                        <li class="pay-icons__icon pf">
                                                            {$admIcon->usePaymentIcon($icon)}
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            </section>
                                        {/if}
                                    {/block}
    
                                    {block name="productdetails-info-stock"}
                                        {include file='productdetails/stock.tpl'}
                                    {/block}
    
                                    {if !($Artikel->nIstVater && $Artikel->kVaterArtikel == 0)}
                                        {block name='productdetails-details-include-actions'}
                                            {include file='productdetails/actions.tpl' }
                                        {/block}
                                    {/if}
    
    
                                </div>
                            {/block}
                        </div>

                        {if $admorris_pro_templateSettings->fixedAddToBasket && empty($Artikel->FunktionsAttribute.unverkaeuflich) && (!isset($smarty.get.quickView) || $smarty.get.quickView != 1)}
                            {block name='productdetails-details-include-basket-fixed'}
                                {include file='productdetails/basket.tpl' tplscope="fixed"}
                            {/block}
                        {/if}
                        
                        {*UPLOADS product-specific files, e.g. for customization*}
                        {block name='productdetails-details-include-uploads'}
                            {if empty($smarty.get.quickView)}
                            {include file="snippets/uploads.tpl" tplscope='product'}
                            {/if}
                        {/block}
                        
                                
                        
                        {* custom - moved to attributes.tpl *}
                        {* {block name="productdetails-info-essential-wrapper"}
                        {if isset($Artikel->cArtNr)}
                            <div class="info-essential">
                                {block name="productdetails-info-essential"}
                                {if isset($Artikel->cArtNr) || isset($Artikel->dMHD)}
                                    <p class="text-muted product-sku">{lang key="sortProductno" section="global"}: <span itemprop="sku">{$Artikel->cArtNr}</span></p>
                                    {if isset($Artikel->dMHD) && isset($Artikel->dMHD_de)}
                                        <p title="{lang key='productMHDTool' section='global'}" class="best-before text-muted">{lang key="productMHD" section="global"}: <span itemprop="best-before">{$Artikel->dMHD_de}</span></p>
                                    {/if}
                                {/if}
                                {/block}
                            </div>
                            <div class="clearfix top10"></div>
                        {/if}
                        {/block} *}
                    </div>{* /product-info-inner *}
                    {/block}{* productdetails-info *}
                    {opcMountPoint id='opc_after_product_info'}
                </div>{* /col *}


                {if $Artikel->bHasKonfig}
                    {block name='productdetails-config'}
                        {col cols=12 id="product-configurator" 
                            class="cfg-position-{$Einstellungen.template.productdetails.config_position} cfg-layout-{$Einstellungen.template.productdetails.config_layout}"}
                            {include file='productdetails/config_container.tpl'}
                        {/col}
                    {/block}
                {/if}
                
            </div>{* /row *}
            {block name='details-matrix'}
                {include file='productdetails/matrix.tpl'}
            {/block}
        {/block}
    {/form}
    
{/block}
<div class="productdetails-additional-info">
    
    <hr>
    
    {if !isset($smarty.get.quickView) || $smarty.get.quickView != 1}
    
        {block name='details-tabs'}
            {include file='productdetails/tabs.tpl'}
        {/block}
    
    
        {*SLIDERS*}
        {if isset($Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen) && $Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen === 'Y' && isset($Artikel->oStueckliste_arr) && $Artikel->oStueckliste_arr|@count > 0
            || isset($Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen) && $Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen === 'Y' && isset($Artikel->oProduktBundle_arr) && $Artikel->oProduktBundle_arr|@count > 0
            || isset($Xselling->Standard->XSellGruppen) && count($Xselling->Standard->XSellGruppen) > 0
            || isset($Xselling->Kauf->Artikel) && count($Xselling->Kauf->Artikel) > 0
            || isset($oAehnlicheArtikel_arr) && count($oAehnlicheArtikel_arr) > 0}
    
            {if isset($Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen) && $Einstellungen.artikeldetails.artikeldetails_stueckliste_anzeigen === 'Y' && isset($Artikel->oStueckliste_arr) && $Artikel->oStueckliste_arr|@count > 0}
                <hr>
                <div class="partslist">
                    {lang key='listOfItems' section='global' assign='slidertitle'}
                    {include file='snippets/product_slider.tpl' id='slider-partslist' productlist=$Artikel->oStueckliste_arr title=$slidertitle showPartsList=true}
                </div>
            {/if}
    
            {if isset($Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen) && $Einstellungen.artikeldetails.artikeldetails_produktbundle_nutzen === 'Y' && isset($Artikel->oProduktBundle_arr) && $Artikel->oProduktBundle_arr|@count > 0}
                <hr>
                <div class="bundle">
                    {include file='productdetails/bundle.tpl' ProductKey=$Artikel->kArtikel Products=$Artikel->oProduktBundle_arr ProduktBundle=$Artikel->oProduktBundlePrice ProductMain=$Artikel->oProduktBundleMain}
                </div>
            {/if}
    
            {if isset($Xselling->Standard) || isset($Xselling->Kauf) || isset($oAehnlicheArtikel_arr)}
                <hr>
                <div class="recommendations stack d-print-none">
                    {block name="productdetails-recommendations"}
                    {if isset($Xselling->Standard->XSellGruppen) && count($Xselling->Standard->XSellGruppen) > 0}
                        {foreach name=Xsell_gruppen from=$Xselling->Standard->XSellGruppen item=Gruppe}
                            {include file='snippets/product_slider.tpl' class='x-supplies' id='slider-xsell-group-'|cat:$smarty.foreach.Xsell_gruppen.iteration productlist=$Gruppe->Artikel title=$Gruppe->Name}
                        {/foreach}
                    {/if}
    
                    {if isset($Xselling->Kauf->Artikel) && count($Xselling->Kauf->Artikel) > 0}
                        {lang key='customerWhoBoughtXBoughtAlsoY' section='productDetails' assign='slidertitle'}
                        {include file='snippets/product_slider.tpl' class='x-sell' id='slider-xsell' productlist=$Xselling->Kauf->Artikel title=$slidertitle}
                    {/if}
    
                    {if isset($oAehnlicheArtikel_arr) && count($oAehnlicheArtikel_arr) > 0}
                        {lang key='RelatedProducts' section='productDetails' assign='slidertitle'}
                        {include file='snippets/product_slider.tpl' class='x-related' id='slider-related' productlist=$oAehnlicheArtikel_arr title=$slidertitle}
                    {/if}
                    {/block}
                </div>
            {/if}
        {/if}
        <div id="article_popups">
            {include file='productdetails/popups.tpl'}
        </div>
    {/if}
</div>
{/block}

{if !empty($bAjaxRequest)}
    {include file="layout/css_variables.tpl"}
{/if}
