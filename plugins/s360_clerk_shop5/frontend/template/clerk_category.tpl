{block name='clerk_category'}
    {if !empty($s360_clerk_category.template) && $AktuelleKategorie}
        {$filter = ""}
        {$templates = explode(',', $s360_clerk_category.template)}
        {$excludeDuplicates = $s360_clerk_category.exclude}

        {foreach from=$templates item=$template key=$key}
            {block name='clerk_category_snippets'}
                {$clerkSnippets = $s360_clerk_snippets->get()}
            {/block}

            {block name='clerk_category_api'}
                <span class="clerk {if $excludeDuplicates}clerk_{$key|escape:"htmlall"}{/if}"
                    data-template="{trim($template)|escape:"htmlall"}"
                    data-snippets='{json_encode($clerkSnippets)}'
                    data-category="{$AktuelleKategorie->getID()}"
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
