{block name='clerk_omni_search'}

    {block name='clerk_omni_search_snippets'}
        {$clerkSnippets = [
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

    {block name='clerk_omni_search_api'}
        <span class="clerk"
            data-template="{$s360_clerk_omni_search.template|escape:"htmlall"}"
            data-snippets='{json_encode($clerkSnippets)}'
            data-api="search/omni"
            data-trigger-element="{$s360_clerk_omni_search.selector|escape:"htmlall"}"
        >
        </span>
    {/block}
{/block}
