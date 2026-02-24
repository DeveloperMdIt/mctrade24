{*custom*}
{block name='productdetails-reviews'}
{$tabLink = ($Einstellungen.artikeldetails.artikeldetails_tabs_nutzen !== 'N') ? "#article-tabs" : "#tab-votes"}
{$formTarget = "{get_static_route id='bewertung.php'}"|cat:$tabLink}

<div class="reviews row row-gap">
    <div class="col-lg-4{if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0} order-lg-2 offset-lg-1{/if}">
        {block name="productdetails-review-overview"}
        <div id="reviews-overview">
            {include file='productdetails/rating.tpl' total=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}
            <div class="d-print-none mt-3">
                <form method="post" action={$formTarget} id="article_rating" class="article-rating clearfix">
                    {$jtl_token}
                    {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0}
                    <div id="article_votes">
                        {foreach name=sterne from=$Artikel->Bewertungen->nSterne_arr item=nSterne key=i}
                            {assign var=int1 value=5}
                            {math equation='x - y' x=$int1 y=$i assign='schluessel'}
                            {assign var=int2 value=100}
                            {math equation='a/b*c' a=$nSterne b=$Artikel->Bewertungen->oBewertungGesamt->nAnzahl c=$int2 assign='percent'}
                            <div class="row">
                                <div class="col-4 col-md-2 col-lg-4 col-xl-3">
                                    {if isset($bewertungSterneSelected) && $bewertungSterneSelected === $schluessel}
                                        <strong>
                                    {/if}
                                    {if $nSterne > 0 && (!isset($bewertungSterneSelected) || $bewertungSterneSelected !== $schluessel)}
                                        <a href="{$Artikel->cURLFull}?btgsterne={$schluessel}{$tabLink}">{$schluessel} {if $i == 4}{lang key="starSingular" section="product rating"}{else}{lang key="starPlural" section="product rating"}{/if}</a>
                                    {else}
                                        {$schluessel} {if $i == 4}{lang key="starSingular" section="product rating"}{else}{lang key="starPlural" section="product rating"}{/if}
                                    {/if}
                                    {if isset($bewertungSterneSelected) && $bewertungSterneSelected === $schluessel}
                                        </strong>
                                    {/if}
                                </div>
                                <div class="col-8 col-md-10 col-lg-8 col-xl-9">
                                    <div class="progress-count">{$nSterne}</div>
                                    <div class="progress">
                                        {if $nSterne > 0}
                                            <div class="progress-bar" role="progressbar"
                                                aria-valuenow="{$percent|round}" aria-valuemin="0"
                                                aria-valuemax="100" style="width: {$percent|round}%;">
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        {/foreach}
                        {if isset($bewertungSterneSelected) && $bewertungSterneSelected > 0}
                            <p>
                                <a href="{$Artikel->cURLFull}#tab-votes" class="btn btn-secondary">
                                    {lang key="allReviews" section="product rating"}
                                </a>
                            </p>
                        {/if}
                    </div>
                    {/if}
                    <div>
                        {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl == 0}
                            <p class="text-justify">{lang key="firstReview" section="global"}: </p>
                        {else}
                            <p class="text-justify">{lang key="shareYourExperience" section="product rating"}: </p>
                        {/if}
                        <input name="bfa" type="hidden" value="1" />
                        <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                        <input name="bewerten" type="submit" value="{lang key="productAssess" section="product rating"}" class="submit btn btn-primary btn--wide{if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0} float-right{/if}" />
                    </div>
                </form>
            </div>
        </div>{* /reviews-overview *}
        {/block}
    </div> {* col *}
    <div class="col-lg-7 col-md-pull-5">
        {if isset($Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich) && $Artikel->HilfreichsteBewertung->oBewertung_arr[0]->nHilfreich > 0}
        <div class="review-wrapper reviews-mosthelpful">
            <form method="post" action={$formTarget}>
                {$jtl_token}
                {block name="productdetails-review-most-helpful"}
                <input name="bhjn" type="hidden" value="1" />
                <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
                <input name="btgsterne" type="hidden" value="{$BlaetterNavi->nSterne}" />
                <input name="btgseite" type="hidden" value="{$BlaetterNavi->nAktuelleSeite}" />
                <div class="panel-wrap">
                    <div class="review card">
                        <div class="card-header bottom17">
                            <h4 class="">{lang key="theMostUsefulRating" section="product rating"}</h4>
                        </div>
                        <div class="card-body">
                            {foreach name=artikelhilfreichstebewertungen from=$Artikel->HilfreichsteBewertung->oBewertung_arr item=oBewertung}
                                {include file="productdetails/review_item.tpl" oBewertung=$oBewertung bMostUseful=true}
                            {/foreach}
                        </div>
                    </div>
                </div>
                {/block}
            </form>
        </div>
        {/if}

        {if $ratingPagination->getPageItemCount() > 0}
        {* {include file="snippets/pagination.tpl" oPagination=$ratingPagination cThisUrl=$Artikel->cURLFull cAnchor='tab-votes' showFilter=false} *}
        <form method="post" action={$formTarget} id="reviews-list" class="reviews-list">
            {$jtl_token}
            <input name="bhjn" type="hidden" value="1" />
            <input name="a" type="hidden" value="{$Artikel->kArtikel}" />
            <input name="btgsterne" type="hidden" value="{$BlaetterNavi->nSterne}" />
            <input name="btgseite" type="hidden" value="{$BlaetterNavi->nAktuelleSeite}" />

            {foreach name=artikelbewertungen from=$ratingPagination->getPageItems() item=oBewertung}
                <div class="review card{if $smarty.foreach.artikelbewertungen.last} last{/if}">
                    <div class="card-body">
                        {include file="productdetails/review_item.tpl" oBewertung=$oBewertung}
                    </div>
                </div>
            {/foreach}
        </form>
        {include file="snippets/pagination.tpl" oPagination=$ratingPagination cThisUrl=$Artikel->cURLFull cAnchor='tab-votes'}
        {/if}
    </div>{* /col *}
    
</div>{* /row *}
{/block}
