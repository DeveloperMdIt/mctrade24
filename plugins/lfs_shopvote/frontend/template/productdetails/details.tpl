{if $lfsShopVoteReviewMode != 'N'}
    {if $lfsShopVoteReviewMode == 'R'}
        {block name="productdetails-details-info-rating-wrapper"}
            {if $review_data->rating_value > 0}
                <div class="rating-wrapper" itemprop="aggregateRating" itemscope="true"
                     itemtype="http://schema.org/AggregateRating">
                    <meta itemprop="itemReviewed" content="{$Artikel->cURLFull}"/>
                    <meta itemprop="ratingValue" content="{$review_data->rating_value}"/>
                    <meta itemprop="bestRating" content="5"/>
                    <meta itemprop="worstRating" content="1"/>
                    <meta itemprop="reviewCount" content="{$review_data->rating_count}"/>
                    {block name='productdetails-details-include-rating'}
                        {link href="{$Artikel->cURLFull}#tab-votes"
                        id="jump-to-votes-tab"
                        class="d-print-none text-decoration-none"
                        aria=["label"=>{lang key='Votes'}]
                        }
                            {include file='productdetails/rating.tpl' stars=$review_data->rating_value total=$review_data->rating_count}
                            ({$review_data->rating_count} {lang key='rating'})
                        {/link}
                    {/block}
                </div>
            {/if}
        {/block}
        {block name="productdetails-info-rating-wrapper"}
            {if $review_data->rating_value > 0}
                <div class="rating-wrapper" itemprop="aggregateRating" itemscope="true"
                     itemtype="http://schema.org/AggregateRating">
                    <meta itemprop="itemReviewed" content="{$Artikel->cURLFull}"/>
                    <meta itemprop="ratingValue" content="{$review_data->rating_value}"/>
                    <meta itemprop="bestRating" content="5"/>
                    <meta itemprop="worstRating" content="1"/>
                    <meta itemprop="reviewCount" content="{$review_data->rating_count}"/>
                    {block name='productdetails-details-include-rating'}
                        {link href="{$Artikel->cURLFull}#tab-votes"
                        id="jump-to-votes-tab"
                        class="d-print-none text-decoration-none"
                        aria=["label"=>{lang key='Votes'}]
                        }
                            {include file='productdetails/rating.tpl' stars=$review_data->rating_value total=$review_data->rating_count}
                            ({$review_data->rating_count} {lang key='rating'})
                        {/link}
                    {/block}
                </div>
            {/if}
        {/block}
    {/if}

    {if $lfsShopVoteReviewMode == 'A'}
        {block name="productdetails-details-info-rating-wrapper"}
            {if $lfsShopVoteAverageRating->average_rating > 0}
                <div class="rating-wrapper" itemprop="aggregateRating" itemscope="true"
                     itemtype="http://schema.org/AggregateRating">
                    <meta itemprop="itemReviewed" content="{$Artikel->cURLFull}"/>
                    <meta itemprop="ratingValue" content="{$lfsShopVoteAverageRating->average_rating}"/>
                    <meta itemprop="bestRating" content="5"/>
                    <meta itemprop="worstRating" content="1"/>
                    <meta itemprop="reviewCount" content="{$lfsShopVoteAverageRating->rating_count}"/>
                    {block name='productdetails-details-include-rating'}
                        {link href="{$Artikel->cURLFull}#tab-votes"
                        id="jump-to-votes-tab"
                        class="d-print-none text-decoration-none"
                        aria=["label"=>{lang key='Votes'}]
                        }
                            {include file='productdetails/rating.tpl' stars=$lfsShopVoteAverageRating->average_rating total=$lfsShopVoteAverageRating->review_count}
                            ({$lfsShopVoteAverageRating->review_count} {lang key='rating'})
                        {/link}
                    {/block}
                </div>
            {/if}
        {/block}
        {block name="productdetails-info-rating-wrapper"}
            {if $lfsShopVoteAverageRating->average_rating > 0}
                <div class="rating-wrapper" itemprop="aggregateRating" itemscope="true"
                     itemtype="http://schema.org/AggregateRating">
                    <meta itemprop="itemReviewed" content="{$Artikel->cURLFull}"/>
                    <meta itemprop="ratingValue" content="{$lfsShopVoteAverageRating->average_rating}"/>
                    <meta itemprop="bestRating" content="5"/>
                    <meta itemprop="worstRating" content="1"/>
                    <meta itemprop="reviewCount" content="{$lfsShopVoteAverageRating->rating_count}"/>
                    {block name='productdetails-details-include-rating'}
                        {link href="{$Artikel->cURLFull}#tab-votes"
                        id="jump-to-votes-tab"
                        class="d-print-none text-decoration-none"
                        aria=["label"=>{lang key='Votes'}]
                        }
                            {include file='productdetails/rating.tpl' stars=$lfsShopVoteAverageRating->average_rating total=$lfsShopVoteAverageRating->review_count}
                            ({$lfsShopVoteAverageRating->review_count} {lang key='rating'})
                        {/link}
                    {/block}
                </div>
            {/if}
        {/block}
    {/if}
{/if}