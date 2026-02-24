{block name='productlist-item-list-details'}
{block name="product-title"}
    <div class="product-list-item__title h4 mb-0">
        <a href="{$Artikel->cURLFull}">{$Artikel->cName}</a>
    </div>
    {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y'}
    {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}
{/if}
{/block}
{block name="product-manufacturer"}
    {if $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'N'}
        <div class="media top0 bottom5">
            {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
            || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'B') 
            && !empty($Artikel->cHerstellerBildKlein)}
                <div class="media-left">
                    {if !empty($Artikel->cHerstellerHomepage)}<a href="{$Artikel->cHerstellerHomepage}">{/if}
                        <img src="{$Artikel->cHerstellerBildKlein}" alt="" class="product-list-item__manufacturer img-sm">
                    {if !empty($Artikel->cHerstellerHomepage)}</a>{/if}
                </div>
            {/if}
            {if ($Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'BT'
                || $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen === 'Y') 
                && !empty($Artikel->cHersteller)}
                <div class="media-body">
                    <span class="small text-uppercase">
                        {if !empty($Artikel->cHerstellerHomepage)}<a href="{$Artikel->cHerstellerHomepage}">{/if}
                            {$Artikel->cHersteller}
                        {if !empty($Artikel->cHerstellerHomepage)}</a>{/if}
                    </span>
                </div>
            {/if}
        </div>
    {/if}
{/block}

<div class="product-info gap">
    {block name="product-info"}
            {if $Einstellungen.artikeluebersicht.artikeluebersicht_kurzbeschreibung_anzeigen === 'Y' && $Artikel->cKurzBeschreibung}
                <div class="shortdescription">
                    {$Artikel->cKurzBeschreibung}
                </div>
            {/if}
            <table class="attr-group small text-muted">
                <tbody>
                    {if !empty($Artikel->cBarcode)
                        && ($Einstellungen.artikeldetails.gtin_display === 'lists'
                            || $Einstellungen.artikeldetails.gtin_display === 'always')}
                        <tr class="item">
                            <td class="attr-label">{lang key='ean'}: </td> <td class="value">{$Artikel->cBarcode}</td>
                        </tr>
                    {/if}
                    {if !empty($Artikel->cISBN)
                        && ($Einstellungen.artikeldetails.isbn_display === 'L'
                            || $Einstellungen.artikeldetails.isbn_display === 'DL')}
                        <tr class="item">
                            <td class="attr-label">{lang key='isbn'}: </td> <td class="value">{$Artikel->cISBN}</td>
                        </tr>
                    {/if}
                    {if !empty($Artikel->cHAN)
                        && ($Einstellungen.artikeldetails.han_display === 'lists'
                        || $Einstellungen.artikeldetails.han_display === 'always')}
                        {block name='productlist-item-details-han'}
                            <tr class="item">
                                <td class="attr-label">{lang key='han'}: </td> <td class="value">{$Artikel->cHAN}</td>
                            </tr>
                        {/block}
                    {/if}
                    {if !empty($Artikel->cUNNummer) && !empty($Artikel->cGefahrnr)
                        && ($Einstellungen.artikeldetails.adr_hazard_display === 'L'
                            || $Einstellungen.artikeldetails.adr_hazard_display === 'DL')}
                        <tr class="item">
                            <td class="attr-label">
                                {lang key='adrHazardSign'}:
                            </td>
                            <td class="value">
                                <table class="adr-table value">
                                    <tr>
                                        <td>{$Artikel->cGefahrnr}</td>
                                    </tr>
                                    <tr>
                                        <td>{$Artikel->cUNNummer}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    {/if}
                    {if $Einstellungen.artikeldetails.show_shelf_life_expiration_date === 'Y'
                        && isset($Artikel->dMHD)
                        && isset($Artikel->dMHD_de)}
                        <tr class="item attr-best-before" title="{lang key='productMHDTool' section='global'}">
                            <td class="attr-label">{lang key="productMHD" section="global"}: </td> <td class="value">{$Artikel->dMHD_de}</td>
                        </tr>
                    {/if}
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_gewicht_anzeigen === 'Y' && isset($Artikel->cGewicht) && $Artikel->fGewicht > 0}
                        <tr class="item attr-weight">
                            <td class="attr-label">{lang key='shippingWeight'}: </td>
                            <td class="value">{$Artikel->cGewicht} {lang key="weightUnit" section="global"}</td>
                        </tr>
                    {/if}
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelgewicht_anzeigen === 'Y' && isset($Artikel->cArtikelgewicht) && $Artikel->fArtikelgewicht > 0}
                        <tr class="item attr-weight weight-unit-article">
                            <td class="attr-label">{lang key='productWeight'}: </td>
                            <td class="value">{$Artikel->cArtikelgewicht} {lang key="weightUnit" section="global"}</td>
                        </tr>
                    {/if}
                    {if $Einstellungen.artikeluebersicht.artikeluebersicht_artikelintervall_anzeigen === 'Y' && $Artikel->fAbnahmeintervall > 0}
                        <tr class="item attr-quantity-scale">
                            <td class="attr-label">{lang key='purchaseIntervall' section='productOverview'}: </td>
                            <td class="value">{$Artikel->fAbnahmeintervall} {$Artikel->cEinheit}</td>
                        </tr>
                    {/if}
                    {if count($Artikel->Variationen) > 0}
                        <tr class="item attr-variations">
                            <td class="attr-label">{lang key='variationsIn' section='productOverview'}: </td>
                            <td class="value-group">{foreach $Artikel->Variationen as $variation}{if !$variation@first}, {/if}
                            <span class="value">{$variation->cName}</span>{/foreach}</td>
                        </tr>
                    {/if}
                </tbody>
            </table>{* /attr-group *}
            <div class="attr-sku">
                <span class="attr-label">{lang key='productNo'}: </span> <span class="value">{$Artikel->cArtNr}</span>
            </div>
    {/block}
</div>{* /product-info *}
{/block}