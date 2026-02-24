{if $isNova === false}
<div class="row">
    <div class="col-md-12">
{/if}

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">
            <span class="pull-leftt">
                 <img src="{$lfsShopVotePlugin->getPaths()->getFrontendUrl()}images/sv-logo-32x32.png" style="padding-right: 15px;">
                 SHOPVOTE - Bewertungen
            </span>
            </h5>
            <div>
                {if $review_data->rating_count > 0}
                    {if $review_data->rating_value > 0}
                        &Oslash; {$review_data->rating_value} / 5 {$lfsShopVotePlugin->getLocalization()->getTranslation('sv_stars')}
                        {$lfsShopVotePlugin->getLocalization()->getTranslation('sv_from')}
                    {/if}
                    {$review_data->rating_count} {$lfsShopVotePlugin->getLocalization()->getTranslation('sv_productreviews_available')}
                {else}
                    {$lfsShopVotePlugin->getLocalization()->getTranslation('sv_no_productreviews')}
                {/if}
            </div>

            {if $review_data->rating_count > 0}
                <div>
                    {foreach from=$review_data->reviews item=$review name=oSvBewertung}
                        {if $smarty.foreach.oSvBewertung.first}
                            <hr>
                        {/if}
                        <div class="card review {if $smarty.foreach.oSvBewertung.last}last{/if}" itemprop="review" itemscope itemtype="http://schema.org/Review">
                            {if $isNova === false}
                                <span itemprop="name" class="hidden">{$Artikel->cName}</span>
                            {else}
                                <span itemprop="name" class="d-none">{$Artikel->cName}</span>
                            {/if}
                            <div class="card-body">
                                <div class="col-xs-12" itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" style="padding-bottom: 10px;">
                                    <div>
                                        {include file=$lfsShopVoteReviewStarsTemplate sv_stars=$review->rating_value}
                                        <small class="hide">
                                            <span itemprop="ratingValue">{$review->rating_value}</span> {lang key="from" section="global"}
                                            <span itemprop="bestRating">5</span>
                                            <meta itemprop="worstRating" content="1">
                                        </small>
                                        -
                                        <small>
                                            <cite>{$review->author_name}</cite>,
                                            <meta itemprop="itemReviewed" content="{$Artikel->cURLFull}"/>
                                            <meta itemprop="datePublished" content="{$review->created_at}" />{$review->created_at|date_format:"d.m.Y"}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-xs-12">
                                    <p itemprop="reviewBody">{$review->text|nl2br}</p>
                                    <span itemprop="author" itemscope itemtype="http://schema.org/Person" class="d-none">
                                <span itemprop="name">
                                    {$review->author_name}
                                </span>
                            </span>
                                </div>
                            </div>
                        </div>
                        {if $isNova === false}
                            <div class="clearfix"></div>
                        {/if}
                        {if not $smarty.foreach.oSvBewertung.last}
                            <hr>
                        {/if}
                    {/foreach}
                    <br />
                    {if isset($review_data->reviewpage) && $review_data->reviewpage !== ''}
                    <span class="pull-right">
                        <a href="{$review_data->reviewpage}" target="_blank" class="btn btn-default pull-right">
                            {$lfsShopVotePlugin->getLocalization()->getTranslation('sv_showall_productreviews')}
                        </a>
                    </span>
                    {/if}
                </div>
            {/if}
        </div>
    </div>

{if $isNova === false}
    </div>
</div>
{/if}