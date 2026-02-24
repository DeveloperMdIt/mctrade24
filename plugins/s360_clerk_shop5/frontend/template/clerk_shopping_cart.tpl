{block name='clerk_shopping_cart'}
    {if !empty($s360_clerk_shoppingcart.template)}
        {$filter = ""}
        {$templates = explode(',', $s360_clerk_shoppingcart.template)}
        {$excludeDuplicates = $s360_clerk_shoppingcart.exclude}

        {foreach from=$templates item=$template key=$key}
            {block name='clerk_shopping_cart_snippets'}
                {$clerkSnippets = $s360_clerk_snippets->get()}
            {/block}

            {block name='clerk_shopping_cart_api'}
                <span class="clerk {if $excludeDuplicates}clerk_{$key|escape:"htmlall"}{/if}"
                    data-template="{trim($template)|escape:"htmlall"}"
                    data-snippets='{json_encode($clerkSnippets)}'
                    data-products='{json_encode($s360_clerk_shoppingcart.product)}'
                    {if $excludeDuplicates && not $template@first}data-exclude-from="{$filter|escape:"htmlall"}"{/if}
                >
                </span>

                {* Build exclude filter *}
                {if not $template@first}
                    {$filter = "{$filter}, "}
                {/if}
                {$filter = "{$filter}.clerk_{$key|escape:"htmlall"}"}
            {/block}
        {/foreach}
    {/if}
{/block}
