<script>
    (function(w,k) {
        if(typeof w[k] !== 'function'){
            w[k] = function() {
                (w[k].q = w[k].q || []).push(arguments);
            }
        }
    })(window, 'doofinderApp');

    doofinderApp('config', 'currency', '{$ws5_doofinder_config.currency}');
    doofinderApp('config', 'language', '{$ws5_doofinder_config.language}');
    doofinderApp('config', 'zone', '{$ws5_doofinder_config.zone}');
    {if isset($ws5_doofinder_config.priceName)}
    doofinderApp('config', 'priceName', '{$ws5_doofinder_config.priceName}');
    {/if}
</script>