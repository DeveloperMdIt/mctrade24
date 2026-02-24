{* custom - modified for extraproduct salesbooster *}
{* Added $oPosition->cResponsibility === 'core' check to $oPosition->istKonfigVater() to fix extraproduct salesbooster items *}

{block name='account-order-item'}
<table class="table table-striped order-items layout-fixed hyphens table-responsive">
    <thead>
    <tr>
        <th class="col-8 col-md-5">
            {lang key='product' section='global'}
        </th>
        <th class="col-2 col-md-1">
            {lang key='quantity' section='global'}
        </th>
        {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
            <th class="d-none d-sm-table-cell col-md-2 text-right">{lang key='pricePerUnit' section='productDetails'}</th>
        {/if}
        <th class="col-2">{lang key='shippingStatus' section='login'}</th>
        <th class="col-2 text-right">{lang key='merchandiseValue' section='checkout'}</th>
    </tr>
    </thead>
    <tbody>
    {foreach $Bestellung->Positionen as $oPosition}
        {if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem > 0)} {*!istKonfigKind()*}
            <tr class="type-{$oPosition->nPosTyp}">
                <td>
                    {if $oPosition->nPosTyp == $smarty.const.C_WARENKORBPOS_TYP_ARTIKEL}
                        {* custom - extraproduct salesbooster *}
                        {if empty($oPosition->Artikel->FunktionsAttribute["extra_product_not_buyable"])}
                            <a href="{$oPosition->Artikel->cURLFull}" title="{$oPosition->cName|transByISO}">{$oPosition->cName|transByISO}</a>
                        {else}
                            {$oPosition->cName|transByISO}
                        {/if}
                        {if empty($oPosition->Artikel->FunktionsAttribute['extra_product_hide_info'])}
                            <ul class="list-unstyled text-muted small">
                                <li class="sku"><strong>{lang key='productNo' section='global'}:</strong> {$oPosition->Artikel->cArtNr}</li>
                                {if $Einstellungen.artikeldetails.show_shelf_life_expiration_date === 'Y'
                                    && isset($oPosition->Artikel->dMHD, $oPosition->Artikel->dMHD_de)}
                                    <li title="{lang key='productMHDTool' section='global'}" class="best-before">
                                        <strong>{lang key='productMHD' section='global'}:</strong> {$oPosition->Artikel->dMHD_de}
                                    </li>
                                {/if}
                                {if $Einstellungen.kaufabwicklung.warenkorb_varianten_varikombi_anzeigen === 'Y' && isset($oPosition->WarenkorbPosEigenschaftArr) && !empty($oPosition->WarenkorbPosEigenschaftArr)}
                                    {foreach $oPosition->WarenkorbPosEigenschaftArr as $Variation}
                                        <li class="variation">
                                            <strong>{$Variation->cEigenschaftName|transByISO}:</strong> {$Variation->cEigenschaftWertName|transByISO} {if !empty($Variation->cAufpreisLocalized[$NettoPreise])}&raquo;
                                                {if $Variation->cAufpreisLocalized[$NettoPreise]|substr:0:1 !== '-'}+{/if}{$Variation->cAufpreisLocalized[$NettoPreise]} {/if}
                                        </li>
                                    {/foreach}
                                {/if}
                                {if $Einstellungen.kaufabwicklung.bestellvorgang_lieferstatus_anzeigen === 'Y' && $oPosition->cLieferstatus|transByISO}
                                    <li class="delivery-status"><strong>{lang key='deliveryStatus' section='global'}:</strong> {$oPosition->cLieferstatus|transByISO}</li>
                                {/if}
                                {if !empty($oPosition->cHinweis)}
                                    <li class="text-info notice">{$oPosition->cHinweis}</li>
                                {/if}

                                {* Buttonloesung eindeutige Merkmale *}
                                {if $oPosition->Artikel->cHersteller && $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen != "N"}
                                    <li class="manufacturer">
                                        <strong>{lang key='manufacturer' section='productDetails'}</strong>:
                                        <span class="values">
                                        {$oPosition->Artikel->cHersteller}
                                        </span>
                                    </li>
                                {/if}

                                {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelmerkmale == 'Y' && !empty($oPosition->Artikel->oMerkmale_arr)}
                                    {foreach $oPosition->Artikel->oMerkmale_arr as $oMerkmale_arr}
                                        <li class="characteristic">
                                            <strong>{$oMerkmale_arr->getName()}</strong>:
                                            <span class="values">
                                                {foreach $oMerkmale_arr->getCharacteristicValues() as $oWert}
                                                    {if !$oWert@first}, {/if}
                                                    {$oWert->getValue()}
                                                {/foreach}
                                            </span>
                                        </li>
                                    {/foreach}
                                {/if}

                                {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelattribute == 'Y' && !empty($oPosition->Artikel->Attribute)}
                                    {foreach $oPosition->Artikel->Attribute as $oAttribute_arr}
                                        <li class="attribute">
                                            <strong>{$oAttribute_arr->cName}</strong>:
                                            <span class="values">
                                                {$oAttribute_arr->cWert}
                                            </span>
                                        </li>
                                    {/foreach}
                                {/if}

                                {if $Einstellungen.kaufabwicklung.bestellvorgang_artikelkurzbeschreibung == 'Y' && $oPosition->Artikel->cKurzBeschreibung|strlen > 0}
                                    <li class="shortdescription">{$oPosition->Artikel->cKurzBeschreibung}</li>
                                {/if}
                            </ul>
                        {/if}
                    {else}
                        {$oPosition->cName|transByISO}{if isset($oPosition->discountForArticle)}{$oPosition->discountForArticle|transByISO}{/if}
                        {if isset($oPosition->cArticleNameAffix)}
                            {if is_array($oPosition->cArticleNameAffix)}
                                <ul class="small text-muted">
                                    {foreach $oPosition->cArticleNameAffix as $cArticleNameAffix}
                                        <li>{$cArticleNameAffix|transByISO}</li>
                                    {/foreach}
                                </ul>
                            {else}
                                <ul class="small text-muted">
                                    <li>{$oPosition->cArticleNameAffix|transByISO}</li>
                                </ul>
                            {/if}
                        {/if}
                        {if !empty($oPosition->cHinweis)}
                            <small class="text-info notice">{$oPosition->cHinweis}</small>
                        {/if}
                    {/if}
                    
                    {* custom - salesbooster extraproduct *}
                    {if is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0  && $oPosition->cResponsibility === 'core'} {*istKonfigVater()*}
                        <ul class="config-items text-muted small">
                            {foreach $Bestellung->Positionen as $KonfigPos}
                                {if $oPosition->cUnique == $KonfigPos->cUnique && $KonfigPos->kKonfigitem > 0 }
                                    <li>
                                        <span class="qty">{if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0)}{$KonfigPos->nAnzahlEinzel}{else}1{/if}x</span>
                                        {$KonfigPos->cName|transByISO} &raquo;<br/>
                                        <span class="price_value">
                                            {if $KonfigPos->cEinzelpreisLocalized[$NettoPreise]|substr:0:1 !== '-'}+{/if}{$KonfigPos->cEinzelpreisLocalized[$NettoPreise]}
                                            {lang key='pricePerUnit' section='checkout'}
                                        </span>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>

                    {/if}
                </td>

                <td class="qty-col">
                    {$oPosition->nAnzahl|replace_delim} {if !empty($oPosition->Artikel->cEinheit)}{$oPosition->Artikel->cEinheit}{/if}
                </td>
                {if $Einstellungen.kaufabwicklung.bestellvorgang_einzelpreise_anzeigen === 'Y'}
                    <td class="price-col text-right d-none d-sm-table-cell">
                        {if $oPosition->nPosTyp == $smarty.const.C_WARENKORBPOS_TYP_ARTIKEL}
                            {if !(is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0) || $oPosition->cResponsibility === 'admorris_pro_extra_product'} {*!istKonfigVater()*}
                                {$oPosition->cEinzelpreisLocalized[$NettoPreise]}
                            {else}
                                {$oPosition->cKonfigeinzelpreisLocalized[$NettoPreise]}
                            {/if}
                        {/if}
                    </td>
                {/if}
                <td>
                    {if $oPosition->nPosTyp == 1}
                        {if $oPosition->bAusgeliefert}
                            {lang key='statusShipped' section='order'}
                        {elseif $oPosition->nAusgeliefert > 0}
                            {if $oPosition->cUnique|strlen == 0}{lang key='statusShipped' section='order'}: {$oPosition->nAusgeliefertGesamt}{else}{lang key='statusPartialShipped' section='order'}{/if}
                        {else}
                            {lang key='notShippedYet' section='login'}
                        {/if}
                    {/if}
                </td>
                <td class="price-col text-right">
                    <strong class="price_overall">
                        {* custom - extraproduct salesbooster *}
                        {if is_string($oPosition->cUnique) && !empty($oPosition->cUnique) && (int)$oPosition->kKonfigitem === 0 && $oPosition->cResponsibility === 'core'}
                            {$oPosition->cKonfigpreisLocalized[$NettoPreise]}
                        {else}
                            {$oPosition->cGesamtpreisLocalized[$NettoPreise]}
                        {/if}
                    </strong>
                </td>
            </tr>
        {/if}
    {/foreach}
    </tbody>
    <tfoot>
    {if $NettoPreise}
        <tr>
            <td colspan="4" class="text-right"><span class="price_label">{lang key='totalSum' section='global'}:</span></td>
            <td colspan="2" class="text-right"><span>{$Bestellung->WarensummeLocalized[$NettoPreise]}</span></td>
        </tr>
    {/if}
    {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N'}
        {foreach $Bestellung->Steuerpositionen as $taxPosition}
            <tr>
                <td colspan="3" class="text-right">{$taxPosition->cName}</td>
                <td colspan="2" class="text-right">{$taxPosition->cPreisLocalized}</td>
            </tr>
        {/foreach}
    {/if}
    {if $Bestellung->GuthabenNutzen == 1}
        <tr>
            <td colspan="3" class="text-right"><span class="price_label">{lang key='useCredit' section='account data'}:</span></td>
            <td colspan="2" class="text-right">{$Bestellung->GutscheinLocalized}</span></td>
        </tr>
    {/if}
    <tr class="info">
        <td colspan="3" class="text-right"><span class="price_label"><strong>{lang key='totalSum' section='global'}</strong>{if $NettoPreise} {lang key='gross' section='global'}{/if}:</span></td>
        <td colspan="2" class="text-right"><span class="price">{$Bestellung->WarensummeLocalized[0]}</span></td>
    </tr>
    {if !empty($Bestellung->OrderAttributes)}
        {foreach $Bestellung->OrderAttributes as $attribute}
            {if $attribute->cName === 'Finanzierungskosten'}
                <tr class="type-{$smarty.const.C_WARENKORBPOS_TYP_ZINSAUFSCHLAG}">
                    <td class="text-right" colspan="2">
                        {lang key='financeCosts' section='order'}
                    </td>
                    <td class="text-right price-col" colspan="3">
                        {$attribute->cValue}
                    </td>
                </tr>
            {/if}
        {/foreach}
    {/if}
    </tfoot>
</table>
{/block}