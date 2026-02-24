<script type="application/json" fncls="fnparams-dede7cc5-15fd-4c75-a9f4-36c430ee3a99">
    {literal}
    {
        {/literal}{if $isSandbox}"sandbox":true,
        {/if}{literal}
        "f":"{/literal}{$fraudnetGUID}{literal}",
        "s":"{/literal}{$fraudnetPageID}{literal}"
    }
    {/literal}
</script>
<script>
    {literal}
    let fraudnetOptions = {
        fnUrl: 'https://c.paypal.com/da/r/fb.js'
    }
    $(document).ready(function() {
        var script = document.createElement('script');
        script.src = fraudnetOptions.fnUrl;
        document.body.appendChild(script);
    });
    {/literal}
</script>
{inline_script}<script>
    $(function() {
        $('#complete_order').on('submit', function() {
            history.pushState(null, null, '{$ppcStateURL}');

            return true;
        });
    });
</script>{/inline_script}
