<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach $Suchergebnisse->Herstellerauswahl as $Hersteller}
        {if $Hersteller->nAnzahl >= 1}
            <li>
                <a rel="nofollow" href="{$Hersteller->cURL}">
                    <span class="badge badge-pill float-right">{if !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Hersteller->nAnzahl}{/if}<span class="sr-only"> {lang key='productsFound'}</span></span>
                    <span class="value">
                        {if $NaviFilter->hasManufacturerFilter() && $NaviFilter->getManufacturerFilter()->getValue() == $Hersteller->kHersteller}
                            {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default text-muted')}
                        {else}
                            {$admIcon->renderIcon('squareO', 'icon-content icon-content--default text-muted')}
                        {/if} 
                        {$Hersteller->getName()|escape:'html':'UTF-8':FALSE}
                    </span>
                </a>
            </li>
        {/if}
    {/foreach}
</ul>
