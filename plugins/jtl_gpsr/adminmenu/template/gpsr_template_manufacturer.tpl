{assign var='gpsr_manufacturer' value=$gpsrData[$Artikel->kArtikel]['manufacturer']}
{if count($gpsr_manufacturer) > 0}
<div class="product-manufacturer mb-3">
    <strong>{$langGPSRManufacturerLabel}:</strong>
    <div class="product-manufacturer-manufacturer small">
        {if isset($gpsr_manufacturer.name)}{$gpsr_manufacturer.name}<br>{/if}
        {$gpsr_manufacturer.street} {$gpsr_manufacturer.housenumber}<br>
        {if isset($gpsr_manufacturer.state)}{$gpsr_manufacturer.state}<br>{/if}
        {$gpsr_manufacturer.city}, {$gpsr_manufacturer.country}, {$gpsr_manufacturer.postalcode}<br>
        {if isset($gpsr_manufacturer.email)}{$gpsr_manufacturer.email}<br>{/if}
        {if isset($gpsr_manufacturer.homepage)}{$gpsr_manufacturer.homepage}{/if}
    </div>
</div>
{/if}
