{* Following parameters can be passed:
 * - total: The total number of ratings
 * - stars: The number of stars given in the rating
 * - link: The link to the rating section
 *}

{block name='productdetails-rating'}
    {if isset($total) && $total > 1}
        {lang key='averageProductRating' section='product rating' assign='ratingLabelText'}
    {else}
        {lang key='productRating' section='product rating' assign='ratingLabelText'}
    {/if}

    {lang key='starPlural' section='product rating' assign='starsLangvar'}

    {block name='productdetails-rating-main'}
        {if isset($link)}
            {* admorris Pro custom - disable tabindex="-1" because the rating link should be focusable via keyboard *}
            {* TODO: add aria-label reading the stars *}
            <a class="rating" href="{$link}#tab-votes" title="{$ratingLabelText}: {$stars}/5" {* {if $tplscope=='list'} tabindex="-1"{/if} *}>
        {else}
            <span class="rating" title="{$ratingLabelText}: {$stars}/5">
        {/if}
        {strip}
            <span class="sr-only">
                {$ratingLabelText}: {$stars} {lang key='from'} 5 {$starsLangvar}
            </span>
            <span aria-hidden="true" class="rating__stars">
            {for $i=1 to 5}
                {if $i <= $stars}
                    {$admIcon->renderIcon('star', 'icon-content icon-content--default icon-content--star')}
                {elseif ($i - 0.5) <= $stars}
                    {$admIcon->renderIcon('starHalfOutlined', 'icon-content icon-content--default icon-content--star')}
                {else}
                    {$admIcon->renderIcon('starOutlined', 'icon-content icon-content--default icon-content--star')}
                {/if}
            {/for}
            </span>
            
            {if isset($total) && $total > 1}
                <span class="rating__total">(<span class="rating__total-number">{$total}</span><span class="rating__total-text"> {lang key="rating" section="global"}</span>)</span>
            {/if}
        {/strip}
        {if isset($link)}
            </a>
        {else}
            </span>
        {/if}
    {/block}
{/block}