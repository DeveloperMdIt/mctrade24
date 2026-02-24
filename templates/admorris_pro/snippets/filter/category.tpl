<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach $Suchergebnisse->Kategorieauswahl as $Kategorie}
        {if $Kategorie->nAnzahl >= 1}
            <li>
                <a rel="nofollow" href="{$Kategorie->cURL}">
                    <span class="badge badge-pill float-right">{if !isset($nMaxAnzahlArtikel) || !$nMaxAnzahlArtikel}{$Kategorie->nAnzahl}{/if}</span>
                    <span class="value">
                        {if $NaviFilter->hasCategoryFilter() && $NaviFilter->getCategory()->getValue() == $Kategorie->getID()}
                        {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default text-muted')}
                        {else}
                        {$admIcon->renderIcon('squareO', 'icon-content icon-content--default text-muted')}
                        {/if} 
                        {$Kategorie->getName()|escape:'html':'UTF-8':FALSE}
                    </span>
                </a>
            </li>
        {/if}
    {/foreach}
</ul>
