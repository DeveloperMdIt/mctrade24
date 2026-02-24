<script>
    {literal}
    if (typeof (window.PPCcomponentInitializations) === 'undefined') {
        window.PPCcomponentInitializations = [];
    }
    (function () {
        {/literal}
        let ppcConsentPlaceholder  = '{addslashes($ppcConsentPlaceholder)}',
            placeHolderElement  = null,
            ppcComponentName       = '{$ppcComponentName}',
            ppcStyle            = {json_encode($ppcStyle)},
            containerName       = '#ppc-' + ppcComponentName + '-instalment-banner';
        {literal}
        window.PPCcomponentInitializations.push({/literal}{$ppcComponentName}{literal});
        
        if (ppcComponentName === 'orderProcess') {
            $(document).ready(function() {
                $(window).trigger('ppc:componentInit',[{/literal}{$ppcComponentName}{literal}, true]);
            });
        }
        $(window).on('ppc:getConsent',function(event, consent) {
            if (consent === false) {
                placeHolderElement =
                    $(containerName).html(instalmentBannerPlaceholderTemplate({ ppcConsentPlaceholder }))
                        .on('click', function (e) {
                            $(window).trigger('ppc:componentInit', {/literal}{$ppcComponentName}{literal});
                        });
            } else {
                $(window).trigger('ppc:componentInit', {/literal}{$ppcComponentName}{literal});
            }
        });


        function {/literal}{$ppcComponentName}{literal} (ppc_jtl) {
            if (placeHolderElement !== null) {
                $(placeHolderElement).html('');
            }
            ppc_jtl.Messages(ppcStyle).render(containerName);
        }
    })()
    {/literal}
</script>