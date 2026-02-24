<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {if $NaviFilter->hasPriceRangeFilter()}
        {if $NaviFilter->getPriceRangeFilter()->getOffsetStart() >= 0 && $NaviFilter->getPriceRangeFilter()->getOffsetEnd() > 0}
            <li>
                {*@todo: use getter*}
                <a href="{$NaviFilter->URL->getPriceRanges()}" rel="nofollow" class="active">
                    <span class="value">
                        {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default text-muted')} {$NaviFilter->getPriceRangeFilter()->getOffsetStartLocalized()} - {$NaviFilter->getPriceRangeFilter()->getOffsetEndLocalized()}
                    </span>
                </a>
            </li>
        {/if}
    {else}
        {foreach $Suchergebnisse->Preisspanne as $oPreisspannenfilter}
            <li>
                <a href="{$oPreisspannenfilter->cURL}" rel="nofollow">
                    <span class="badge badge-pill float-right">{$oPreisspannenfilter->nAnzahlArtikel}</span>
                    <span class="value">
                        {$admIcon->renderIcon('squareO', 'icon-content icon-content--default text-muted')} {$oPreisspannenfilter->getOffsetStartLocalized()} - {$oPreisspannenfilter->getOffsetEndLocalized()}
                    </span>
                </a>
            </li>
        {/foreach}
    {/if}
</ul>
