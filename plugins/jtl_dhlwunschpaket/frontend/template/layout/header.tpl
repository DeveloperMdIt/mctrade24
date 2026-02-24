{if $isNova}
    {block name="layout-header-head" append}
        <script>
            var jtlPackFormTranslations = {$jtlPackFormTranslations};
        </script>
    {/block}
{else}
    {block name="head-resources" append}
        <script>
            var jtlPackFormTranslations = {$jtlPackFormTranslations};
        </script>
    {/block}
{/if}
