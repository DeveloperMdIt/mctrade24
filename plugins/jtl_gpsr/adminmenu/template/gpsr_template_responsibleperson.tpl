{assign var='gpsr_responsibleperson' value=$gpsrData[$Artikel->kArtikel]['responsibleperson']}
{if count($gpsr_responsibleperson) > 0}
<div class="product-manufacturer mb-3">
    <strong>{$langGPSRRespPersonLabel}:</strong>
    <div class="product-manufacturer-responsibleperson small">
        {if isset($gpsr_responsibleperson.name)}{$gpsr_responsibleperson.name}<br>{/if}
        {$gpsr_responsibleperson.street} {$gpsr_responsibleperson.housenumber}<br>
        {if isset($gpsr_responsibleperson.state)}{$gpsr_responsibleperson.state}<br>{/if}
        {$gpsr_responsibleperson.city}, {$gpsr_responsibleperson.country}, {$gpsr_responsibleperson.postalcode}<br>
        {if isset($gpsr_responsibleperson.email)}{$gpsr_responsibleperson.email}<br>{/if}
        {if isset($gpsr_responsibleperson.homepage)}{$gpsr_responsibleperson.homepage}{/if}
    </div>
</div>
{/if}
