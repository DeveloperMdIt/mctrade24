{* custom - vanilla js load event used *}
{if isset($nWarenkorb2PersMerge) && $nWarenkorb2PersMerge === 1}
    {block name='snippets-basket-merge'}
    {if $template|default:'' === "checkout"}
        {$cancelRedirectRoute = 'bestellvorgang.php'}
    {else}
        {$cancelRedirectRoute = 'jtl.php'}
    {/if}
    <script type="module">
        window.addEventListener('load', function() {
            eModal.addLabel('{lang key='yes' section='global' addslashes=true}', '{lang key='no' section='global' addslashes=true}');
            var options = {
                message: '{lang key='basket2PersMerge' section='login' addslashes=true}',
                badge: '{lang key='yes' section='global' addslashes=true}',
                title: '{lang key='basket' section='global' addslashes=true}'
            };
            eModal.confirm(options).then(
                function() {
                    window.location = "{get_static_route id='bestellvorgang.php'}?basket2Pers=1&token={$smarty.session.jtl_token}"
                },
                function() {
                    window.location = "{get_static_route id=$cancelRedirectRoute}?updatePersCart=1&token={$smarty.session.jtl_token}"
                }
            );
        });
    </script>
    {/block}
{/if}
