{* custom *}
{block name='productdetails-stock'}
{assign var=anzeige value=$Einstellungen.artikeldetails.artikel_lagerbestandsanzeige}
<div class="delivery-status">
{block name='delivery-status'}
    {if !isset($shippingTime)}
        {if $Artikel->inWarenkorbLegbar === $smarty.const.INWKNICHTLEGBAR_UNVERKAEUFLICH}
            <span class="status"><small>{lang key='productUnsaleable' section='productDetails'}</small></span>
        {elseif !$Artikel->nErscheinendesProdukt}
            {include file='snippets/stock_status.tpl' currentProduct=$Artikel}
        {else}
            {* custom - Erscheinendes Produkt (urspruenglich aus basket.tpl)  *}
            <div class="{if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'} status-0 {else} status-1{/if} coming_soon">
                {lang key="productAvailableFrom" section="global"}: <strong>{$Artikel->Erscheinungsdatum_de}</strong>
                {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $Artikel->inWarenkorbLegbar == 1}
                    ({lang key="preorderPossible" section="global"})
                {/if}
            </div>
            {* /custom *}
            {if $anzeige === 'verfuegbarkeit' || $anzeige === 'genau' && $Artikel->fLagerbestand > 0}
                <span class="status status-{$Artikel->Lageranzeige->nStatus}">{$admIcon->renderIcon('truck', 'icon-content icon-content--default icon-content--center')} <span class='icon-text--center'>{$Artikel->Lageranzeige->cLagerhinweis[$anzeige]}</span></span>
            {elseif $anzeige === 'ampel' && $Artikel->fLagerbestand > 0}
                <span class="status status-{$Artikel->Lageranzeige->nStatus}">{$admIcon->renderIcon('truck', 'icon-content icon-content--default icon-content--center')} <span class='icon-text--center'>{$Artikel->Lageranzeige->AmpelText}</span></span>
            {/if}
        {/if}

        {* rich snippet availability *}
        {* {if $Artikel->cLagerBeachten === 'N' || $Artikel->fLagerbestand > 0 || $Artikel->cLagerKleinerNull === 'Y'}
            <link itemprop="availability" href="http://schema.org/InStock" />
        {elseif $Artikel->nErscheinendesProdukt && $Artikel->Erscheinungsdatum_de !== '00.00.0000' && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'}
            <link itemprop="availability" href="http://schema.org/PreOrder" />
        {elseif $Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull === 'N' && $Artikel->fLagerbestand <= 0}
            <link itemprop="availability" href="http://schema.org/OutOfStock" />
        {/if} *}

        {if isset($Artikel->cLieferstatus) && ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'Y' ||
        ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'L' && $Artikel->fLagerbestand == 0 && $Artikel->cLagerBeachten === 'Y') ||
        ($Einstellungen.artikeldetails.artikeldetails_lieferstatus_anzeigen === 'A' && ($Artikel->fLagerbestand > 0 || $Artikel->cLagerKleinerNull === 'Y' || $Artikel->cLagerBeachten !== 'Y')))}
            <p class="delivery-status"><strong>{lang key='deliveryStatus'}</strong>: {$Artikel->cLieferstatus}</p>
        {/if}
    {/if}

    {if !isset($availability)}
        {if $Artikel->cEstimatedDelivery}
            {lang key='shippingInformation' section='productDetails' assign=silv}
            {getCountry iso=$shippingCountry assign='selectedCountry'}
            {if $selectedCountry !== null}
                {$estimatedDeliveryPopoverContent = sprintf($silv, $selectedCountry->getName(), $oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL(), $oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL())}
            {/if}
            <div class="estimated-delivery">
                {if !isset($shippingTime)}<strong class="icon-text--center">{lang key='shippingTime'}: </strong>{/if}
                <span class="a{$Artikel->Lageranzeige->nStatus}">
                    {$Artikel->cEstimatedDelivery}
                    <button type="button" class="estimated-delivery-info button-reset text-decoration-underline"
                        {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}
                            data-toggle="popover"
                            data-trigger="hover focus"
                            data-placement="top"
                            data-content="{$estimatedDeliveryPopoverContent|default:''}"
                        {/if}>
                        {$iconAndText = $admIcon->renderIcon('info', 'icon-content icon-content--default icon-content--center')|cat:' '|cat:$selectedCountry->getISO()}
                        {lang key='shippingInfoIcon' section='productDetails' printf=$iconAndText}
                    </a>
                </span>
            </div>
        {/if}
    {/if}
{/block}
</div>
{/block}