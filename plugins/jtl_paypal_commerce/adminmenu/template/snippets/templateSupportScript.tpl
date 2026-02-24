<script>
    {ldelim}
        let sections    = {json_encode($setting['vars']['sections'])};
        let scopes      = {json_encode($setting['vars']['scopes'])};
        let tplDefaults = {json_encode($setting['vars']['tplDefaults'])};
        let id          = '#setting_{$settingsName}';
        {literal}
        $(document).ready(function() {
            $(id).on('change', function (e) {
                let templateName = $(this).val() !== 'custom' ? $(this).val() : 'NOVA' ;
                for (let i in sections) {
                    for( let s in scopes) {
                        $('#setting_' + sections[i] + '_' + scopes[s] + '_phpqMethod')
                            .val(tplDefaults[templateName][sections[i]].method[scopes[s]]);

                        $('#setting_' + sections[i] + '_' + scopes[s] + '_phpqSelector')
                            .val(tplDefaults[templateName][sections[i]].selector[scopes[s]]);
                    }
                }
            });
        })
        {/literal}
        {rdelim}
</script>
