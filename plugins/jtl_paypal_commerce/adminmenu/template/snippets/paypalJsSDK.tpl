<script src="{$baseUrl}frontend/js/paypal.browser.min.js?v=1.1.0"></script>
<script>
    var clientID         = '{$clientID}',
        locale           = '{$locale}',
        loadedComponents = [];
    {literal}
    window.paypalLoadScript({
        "client-id": clientID,
        "components": ["buttons", "funding-eligibility", "messages"],
        "locale": locale,
        "disable-funding": "bancontact,blik,eps,ideal,mercadopago,mybank,p24,sepa,sofort,venmo,card",
        "enable-funding": "paylater"
    }).then((ppc_jtl) => {
        runPPCComponents(ppc_jtl);
        $(window).on('ppc:backend:componentInit', function (event, initFunction) {
            initFunction(ppc_jtl);
        })
    });

    function runPPCComponents(ppc_jtl) {
        for( let i in window.PPCBackendComponents) {
            if (loadedComponents.indexOf(i) === -1) {
                window.PPCBackendComponents[i](ppc_jtl);
                loadedComponents.push(i);
            }
        }
    }
{/literal}
</script>
