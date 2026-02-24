{if isset($shopVoteGraphicsCode) && $shopVoteGraphicsCode !== ""}
    {block name='layout-footer-content' append}
        {$shopVoteGraphicsCode}
    {/block}

    {block name='main-wrapper-closingtag' prepend}
        {$shopVoteGraphicsCode}
    {/block}
{/if}