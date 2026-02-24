{if $lfsShopVoteReviewMode != 'N'}
    {if $lfsShopVoteReviewMode == 'A'}
        {block name="productdetails-reviews" append}
            {$reviewContent}
        {/block}
    {/if}

    {if $lfsShopVoteReviewMode == 'R'}
        {block name="productdetails-reviews"}
            {$reviewContent}
        {/block}
    {/if}
{/if}