<div class="form-group form-row align-items-center">
    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="button-preview-label">{__('Vorschau')}:</label>
    <div class="col-sm col-lg-8 pl-sm-3 pr-sm-5 order-last order-sm-2 text-sm-right">
        <div id="paypal-preview-button-wrapper" class="ml-0 mr-0 row">
        </div>
    </div>
</div>

<style>
    #paypal-preview-button-wrapper > div{
        z-index:5;
    }
    .ppc-preview-funding-source.horizontal:first-of-type{
        padding-right: 0.25rem!important;
    }
    @media screen and (max-width: 1180px) {
        .ppc-preview-funding-source.horizontal{
            max-width:100%!important;
            flex: 0 0 100%!important;
        }
        .ppc-preview-funding-source.horizontal:first-of-type{
            padding-right: 0!important;
        }
    }
</style>
<script>
    if (typeof(window.PPCBackendComponents) === 'undefined') {
        window.PPCBackendComponents = [];
    }
    {literal}
    var conf = {
        color:{/literal}'{$section['settings']['smartPaymentButtons_color']['value']}'{literal},
        shape:{/literal}'{$section['settings']['smartPaymentButtons_shape']['value']}'{literal},
        layout:'horizontal',
        height:43,
        label:'buynow'
    };
    window.PPCBackendComponents.push(renderStandAloneButtons);
    $('#setting_smartPaymentButtons_shape,' +
        '#setting_smartPaymentButtons_color').change(function (e) {
        let selected = $(e.target);
        if (selected.attr('id') === 'setting_smartPaymentButtons_shape') {
            conf.shape = selected.val();
        }
        if (selected.attr('id') === 'setting_smartPaymentButtons_color') {
            conf.color = selected.val();
        }
        $('#paypal-preview-button-wrapper').html('');
        $(window).trigger('ppc:backend:componentInit', renderStandAloneButtons);
    })

    function renderStandAloneButtons(ppc_jtl) {
        ppc_jtl.getFundingSources().forEach(function (fundingSource) {
            if (typeof fundingSource === 'undefined') {
                return;
            }
            var button = null;
            try {
                button = renderStandaloneButton(ppc_jtl, fundingSource, conf);
            } catch (error) {
                if (error.message.includes('style.color')) {
                    button = renderStandaloneButton(ppc_jtl, fundingSource, {
                        shape: conf.shape,
                        layout:conf.layout
                    });
                } else {
                    button = renderStandaloneButton(ppc_jtl, fundingSource, {});
                }
            }
            if (button.isEligible()) {
                let cssClass = conf.layout === 'horizontal' ? 'horizontal col-lg-6 pl-0 pr-0' : 'col-lg-12 pl-0 pr-0';
                $('#paypal-preview-button-wrapper').append(`
                    <div id="ppc-preview-${fundingSource}" class=" ${cssClass} ppc-preview-funding-source"><div>
                `)
                button.render("#ppc-preview-" + fundingSource);
            }
        });
    }
    function renderStandaloneButton(ppc_jtl, fundingSource, style) {
        return ppc_jtl.Buttons({
            fundingSource: fundingSource,
            style: {...style},
            onInit: function (data, actions) {
                actions.disable();
            }
        });
    }
    {/literal}


</script>
