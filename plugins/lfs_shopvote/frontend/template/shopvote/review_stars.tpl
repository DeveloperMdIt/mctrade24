{if not isset($sv_stars)}
    {assign var=sv_stars value=$review_data->rating_value}
{/if}
{if $sv_stars > 0}
    {if isset($review_data->rating_value) && $review_data->rating_value > 1}
        {lang key='averageProductRating' section='product rating' assign='ratingLabelText'}
    {else}
        {lang key='productRating' section='product rating' assign='ratingLabelText'}
    {/if}
    <span class="rating" title="{$ratingLabelText}: {$sv_stars}/5">
    {strip}
        {if $sv_stars >= 5}
            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
        {elseif $sv_stars >= 4}
            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
            {if $sv_stars > 4}
            <i class="fa fa-star-half-o"></i>
            {else}
                <i class="fa fa-star-o"></i>
        {/if}
        {elseif $sv_stars >= 3}
            <i class="fa fa-star"></i><i class="fa fa-star"></i><i class="fa fa-star"></i>
            {if $sv_stars > 3}
            <i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i>
            {else}
                <i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
        {/if}
        {elseif $sv_stars >= 2}
            <i class="fa fa-star"></i><i class="fa fa-star"></i>
            {if $sv_stars > 2}
            <i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
            {else}
                <i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
        {/if}
        {elseif $sv_stars >= 1}
            <i class="fa fa-star"></i>
            {if $sv_stars > 1}
            <i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
            {else}
                <i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
        {/if}
        {elseif $sv_stars > 0}
            <i class="fa fa-star-half-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i><i class="fa fa-star-o"></i>
        {/if}
    {/strip}
    </span>
{/if}