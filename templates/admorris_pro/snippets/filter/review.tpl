<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach $Suchergebnisse->Bewertung as $oBewertung}
        {if $NaviFilter->hasRatingFilter() && $NaviFilter->getRatingFilter()->getValue() == $oBewertung->nStern}
            <li>
                {* @todo: use getter *}
                <a rel="nofollow" href="{$NaviFilter->URL->getRatings()}" class="active">
                    {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default text-muted')}
                    <span class="badge badge-pill float-right">{$oBewertung->nAnzahl}</span>
                    <span class="value">
                        {include file='productdetails/rating.tpl' stars=$oBewertung->nStern}
                        {if $NaviFilter->getRatingFilter()->getValue() < 5}
                            <em>({lang key='from' section='productDetails'} {$oBewertung->nStern}
                                {if $oBewertung->nStern > 1}
                                    {lang key='starPlural'}
                                {else}
                                    {lang key='starSingular'}

                                {/if})
                            </em>
                        {/if}
                    </span>
                </a>
            </li>
        {elseif $oBewertung->nAnzahl >= 1 && $oBewertung->nStern > 0}
            <li>
                <a rel="nofollow" href="{$oBewertung->cURL}">
                    {$admIcon->renderIcon('squareO', 'icon-content icon-content--default text-muted')}
                    <span class="badge badge-pill float-right">{$oBewertung->nAnzahl}</span>
                    <span class="value">
                        {include file='productdetails/rating.tpl' stars=$oBewertung->nStern}
                        {if $oBewertung->nStern < 5}
                            <em>
                                ({lang key='from' section='productDetails'} {$oBewertung->nStern}
                                {if $oBewertung->nStern > 1}
                                    {lang key='starPlural'}
                                {else}
                                    {lang key='starSingular'}
                                {/if})
                            </em>
                        {/if}
                    </span>
                </a>
            </li>
        {elseif $oBewertung->nAnzahl >= 1 && $oBewertung->nStern > 0}
            <li>
                <a rel="nofollow" href="{$oBewertung->cURL}">
                    {$admIcon->renderIcon('squareO', 'icon-content icon-content--default text-muted')}
                    <span class="badge badge-pill float-right">{$oBewertung->nAnzahl}</span>
                    <span class="value">
                        {include file='productdetails/rating.tpl' stars=$oBewertung->nStern}
                        {if $oBewertung->nStern < 5}
                            <em>
                                ({lang key='from' section='productDetails'} {$oBewertung->nStern}
                                {if $oBewertung->nStern > 1}
                                    {lang key='starPlural'}
                                {else}
                                    {lang key='starSingular'}
                                {/if})
                            </em>
                        {/if}
                    </span>
                </a>
            </li>
        {/if}
    {/foreach}
</ul>
