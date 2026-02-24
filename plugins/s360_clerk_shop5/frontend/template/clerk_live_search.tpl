{block name='clerk_live_search'}

    {block name='clerk_live_search_snippets'}
        {$clerkSnippets = [
            "searchUrl" => $s360_search_cSeo,
            "showAllResults" => {lang key='search_show_all_results' section='s360_clerk_shop5'},
            "textNoResults" => {lang key='no_results' section='s360_clerk_shop5'},
            "loadMore" => {lang key='search_label_load_more' section='s360_clerk_shop5'},
            "headlineProducts" => {lang key='search_headline_products' section='s360_clerk_shop5'},
            "headlineCategories" => {lang key='search_headline_categories' section='s360_clerk_shop5'},
            "headlineSuggestions" => {lang key='search_headline_suggestions' section='s360_clerk_shop5'},
            "headlinePages" => {lang key='search_headline_pages' section='s360_clerk_shop5'},
            "priceStarting" => {lang key="priceStarting"},
            "vpePer" => {lang key="vpePer"}
        ]}
    {/block}

    {block name='clerk_live_search_api'}
        <span class="clerk"
            data-template="{$s360_clerk_livesearch.template|escape:"htmlall"}"
            data-snippets='{json_encode($clerkSnippets)}'
            data-instant-search="{$s360_clerk_livesearch.selector|escape:"htmlall"}"
            data-instant-search-suggestions="{$s360_clerk_livesearch.search_suggestions|escape:"htmlall"}"
            data-instant-search-categories="{$s360_clerk_livesearch.category_suggestions|escape:"htmlall"}"
            data-instant-search-pages="{$s360_clerk_livesearch.page_suggestions|escape:"htmlall"}"
            data-instant-search-positioning="{$s360_clerk_livesearch.position_livesearch|escape:"htmlall"}"
            data-instant-search-search-url="{$s360_search_cSeo|escape:"htmlall"}"
        >
        </span>
    {/block}

    {block name='clerk_live_search_js'}
        <script>
            $(function () {
                //destroy default typeahead on search and set clerk search result page
                $('{$s360_clerk_livesearch.selector|escape:"htmlall"}')
                    .typeahead('destroy')
                    .prop('name', 'query')
                    .closest('form')
                        .prop('method', 'GET')
                        .prop('action', {json_encode($s360_search_cSeo)});
            });
        </script>
    {/block}

{/block}
