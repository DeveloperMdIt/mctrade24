{extends file="clerk_article.tpl"}

{block name='clerk_article_api'}
    {if !empty($s360_clerk_exit_intent.template)}
        <span class="clerk clerk-popup clerk-popup-hidden"
            id="s360-clerk-exit-intent"
            data-template="{$s360_clerk_exit_intent.template|escape:"htmlall"}"
            data-snippets='{json_encode($s360_clerk_snippets->get())}'
            data-exit-intent="true"
        ></span>

        <script>
            $(function() {
                Clerk('on', 'rendered', '#s360-clerk-exit-intent', function(content, data) {
                    Clerk('ui', 'popup', '#s360-clerk-exit-intent', 'show');
                });
            })
        </script>
    {/if}
{/block}