{* @var Plugin\s360_clerk_shop5\src\Entities\StoreEntity $s360_clerk_store *}
{block name='clerk_config'}
    <script type="text/javascript">
        (function(w,d) {
            var e=d.createElement('script');e.type='text/javascript';e.async=true;
            e.src=(d.location.protocol=='https:'?'https':'http')+'://{$s360_clerk_js}';
            var s=d.getElementsByTagName('script')[0];s.parentNode.insertBefore(e,s);
            w.__clerk_q=w.__clerk_q||[];w.Clerk=w.Clerk||function(){ w.__clerk_q.push(arguments) };
        })(window,document);

        const formatters = {
            //todo Clerk-13 formatter   Preis+ mwst -> rückgabe
        };

        {block name='clerk_config_before'}

        {/block}
        Clerk('config', {block name='clerk_config_object'}{
            key: {json_encode($s360_clerk_store->getApiKey())},
            visitor: "{if $s360_clerk_settings->getValue(constant("\Plugin\s360_clerk_shop5\src\Utils\Config::SETTING_COOKIELESS_TRACKING")) != "on"}persistent{else}auto{/if}",
            globals: {
                currency_symbol : '{$smarty.session.Waehrung->getHtmlEntity()}',
                filterAndSort: "{lang|escape:"htmlall" key='filterAndSort'}",
                removeFilters: "{lang|escape:"htmlall" key='removeFilters'}",
                sorting: "{lang|escape:"htmlall" key='sorting' section='productOverview'}",
                sortPriceAsc: "{lang|escape:"htmlall" key='sortPriceAsc'}",
                sortPriceDesc: "{lang|escape:"htmlall" key='sortPriceDesc'}",
                sortNewestFirst: "{lang|escape:"htmlall" key='sortNewestFirst'}",
                sortNameAsc: "{lang|escape:"htmlall" key='sortNameAsc'}",
                sortNameDesc: "{lang|escape:"htmlall" key='sortNameDesc'}",
            },
            formatters: formatters
        }{/block});

        {* Reinit Clerk slider after ajax request like variation changes *}
        {block name='clerk_config_ajax'}
            $(document).on('evo:loaded.evo.content', function() {
                Clerk('content', '.clerk');
            });
        {/block}


        {block name='clerk_shopping_cart'}
            {if $s360_clerk_settings->getValue(constant("\Plugin\s360_clerk_shop5\src\Utils\Config::SETTING_CART_TRACKING")) == 'on' && !empty($s360_clerk_cart)}
                Clerk('cart', 'set', {json_encode($s360_clerk_cart)});
            {/if}
        {/block}

        {block name='clerk_config_after'}

        {/block}
    </script>
{/block}