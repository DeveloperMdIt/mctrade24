<div id="component_preview-instalment-{$setting['vars']['scope']}" class="form-group form-row align-items-center">
    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="instalment-preview-label"></label>
    <div class="col-sm col-lg-8 pl-sm-3 pr-sm-5 order-last order-sm-2 text-sm-right">
        <button id="paypal-preview-instalment-{$setting['vars']['scope']}" type="button" class="btn btn-primary">{__('Vorschau')}</button>
        <div class="mt-3" id="paypal-preview-instalment-wrapper-{$setting['vars']['scope']}"></div>
    </div>
</div>
<script>
    {literal}
    (function() {
    {/literal}
    var clientID    = '{$clientID}';
    var locale      = '{$locale}';
    var pageScope   = '{$setting['vars']['scope']}';
    var baseSetting = '#setting_instalmentBannerDisplay_' + pageScope + '_';

    {literal}
    var conf = {
        'placement' : 'product',
        'amount' : 200,
        'style' : {
            'layout': $(baseSetting + 'layout').val(),
            'logo': {
                'type': $(baseSetting + 'logoType').val()
            },
            'text': {
                'size': $(baseSetting + 'textSize').val(),
                'color': $(baseSetting + 'textColor').val()
            },
            'color': $(baseSetting + 'layoutType').val(),
            'ratio': $(baseSetting + 'layoutRatio').val()
        }
    };
    let parentContainer = $('#component_instalmentBannerDisplay_' + pageScope +'_phpqMethod').parent();
    $('#component_preview-instalment-'+pageScope).appendTo(parentContainer);
    $('#paypal-preview-instalment-' + pageScope).click(function() {
        $('+paypal-preview-instalment-wrapper-' + pageScope).html('');
         conf = {
            'placement' : 'product',
            'amount' : 200,
            'style' : {
                'layout': $(baseSetting + 'layout').val(),
                'logo': {
                    'type': $(baseSetting + 'logoType').val()
                },
                'text': {
                    'size': $(baseSetting + 'textSize').val(),
                    'color': $(baseSetting + 'textColor').val()
                },
                'color': $(baseSetting + 'layoutType').val(),
                'ratio': $(baseSetting + 'layoutRatio').val()
            }
        };
        $(window).trigger('ppc:backend:componentInit', initInstalmentBanner{/literal}{$setting['vars']['scope']}{literal});
    });

    {/literal}
    function initInstalmentBanner{$setting['vars']['scope']}(ppc_jtl) {
        {literal}
        try {
            if (conf.style.text.color === 'white' && conf.style.layout === 'text' ) {
                $('#paypal-preview-instalment-wrapper-' + pageScope).css({'background': '#9cadbc', 'padding':'0.45rem'});
            } else {
                $('#paypal-preview-instalment-wrapper-' + pageScope).css({'background': 'none', 'padding':'0'});
            }
            ppc_jtl.Messages(conf).render('#paypal-preview-instalment-wrapper-' + pageScope);
        } catch(e) {
            console.log(e);
        }
    }
    })();
    {/literal}


</script>
