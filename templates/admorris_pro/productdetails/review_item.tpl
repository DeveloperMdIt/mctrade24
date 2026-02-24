{block name='productdetails-review-item'}
<div id="comment{$oBewertung->kBewertung}" class="review-comment {if $Einstellungen.bewertung.bewertung_hilfreich_anzeigen === 'Y' && isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde > 0 && $smarty.session.Kunde->kKunde != $oBewertung->kKunde}use_helpful{/if} {if isset($bMostUseful) && $bMostUseful}most_useful{/if}">
    {if $oBewertung->nHilfreich > 0}
        {block name='productdetails-review-helpful'}
        <div class="review-helpful-total">
            <small class="text-muted">
                {if $oBewertung->nHilfreich > 0}
                    {$oBewertung->nHilfreich}
                {else}
                    {lang key='nobody' section='product rating'}
                {/if}
                {lang key='from' section='product rating'} {$oBewertung->nAnzahlHilfreich}
                {if $oBewertung->nAnzahlHilfreich > 1}
                    {lang key='ratingHelpfulCount' section='product rating'}
                {else}
                    {lang key='ratingHelpfulCountExt' section='product rating'}
                {/if}
            </small>
        </div>
        {/block}
    {/if}
    {block name='productdetails-review-content'}
    <div class="top5">
        <span itemprop="name" class="d-none">{$oBewertung->cTitel}</span>

        <span class="subheadline">
            <span class="float-right">
                {include file='productdetails/rating.tpl' stars=$oBewertung->nSterne}
                <small class="hide">
                    <span itemprop="ratingValue">{$oBewertung->nSterne}</span> {lang key='from' section='global'}
                    <span itemprop="bestRating">5</span>
                    <meta itemprop="worstRating" content="1">
                </small>
            </span>
            <strong>{$oBewertung->cTitel}</strong>
            {if $Einstellungen.bewertung.bewertung_hilfreich_anzeigen === 'Y'}
                {if isset($smarty.session.Kunde) && $smarty.session.Kunde->kKunde > 0 && $smarty.session.Kunde->kKunde != $oBewertung->kKunde}
                    <span class="review-helpful vmiddle" id="help{$oBewertung->kBewertung}">
                        <button class="helpful btn btn-blank btn-sm" title="{lang key='yes'}" name="hilfreich_{$oBewertung->kBewertung}" type="submit">
                            {$admIcon->renderIcon('thumbsUp', 'icon-content icon-content--default')}
                        </button>
                        <button class="not_helpful btn btn-blank btn-sm" title="{lang key='no'}" name="nichthilfreich_{$oBewertung->kBewertung}" type="submit">
                            {$admIcon->renderIcon('thumbsDown', 'icon-content icon-content--default')}
                        </button>
                    </span>
                {/if}
            {/if}
        </span>
        <hr class="hr-sm">
        <blockquote>
            <p itemprop="reviewBody">{$oBewertung->cText|nl2br}</p>
            <small class="blockquote-footer">
                <cite>><span itemprop="name">{$oBewertung->cName}</span>.</cite>,
                {* <meta itemprop="datePublished" content="{$oBewertung->dDatum}" /> *}{$oBewertung->Datum}
            </small>
        </blockquote>
        {* <meta itemprop="thumbnailURL" content="{$Artikel->cVorschaubildURL}"> *}
        {if !empty($oBewertung->cAntwort)}
            <div class="review-reply">
                <strong>{lang key='reply' section='product rating'} {$cShopName}:</strong>
                <hr class="hr-sm">
                <blockquote>
                    <p>{$oBewertung->cAntwort}</p>
                    <small>{$oBewertung->AntwortDatum}</small>
                </blockquote>
            </div>
        {/if}
    </div>
    {/block}
</div>
{/block}