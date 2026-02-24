{block name='productdetails-manufacturer'}
    <li  class="product-manufacturer" itemprop="brand" itemscope itemtype="https://schema.org/Brand">
        <strong>{lang key='manufacturers'}:</strong>
        {if $Einstellungen.artikeldetails.artikel_weitere_artikel_hersteller_anzeigen === 'Y'}
            <a href="{if !empty($Artikel->cHerstellerHomepage)}{$Artikel->cHerstellerHomepage}{else}{$Artikel->cHerstellerURL}{/if}"
                {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'B'}
                    data-toggle="tooltip"
                    data-placement="left"
                    title="{$Artikel->cHersteller}"
                {/if}
                itemprop="url">
        {/if}
            {if ($Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'B'
                || $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'BT')
                && !empty($Artikel->cHerstellerBildURLKlein)}
                {image lazy=true
                    webp=true
                    src=$Artikel->cHerstellerBildURLKlein
                    alt=$Artikel->cHersteller|escape:'html'
                }
                <meta itemprop="image" content="{$Artikel->cHerstellerBildURLKlein}">
            {/if}
            {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'B'}
                <span itemprop="name">{$Artikel->cHersteller}</span>
            {/if}
        {if $Einstellungen.artikeldetails.artikel_weitere_artikel_hersteller_anzeigen === 'Y'}
            </a>
        {/if}
    </li>
{/block}
