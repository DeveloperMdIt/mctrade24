<div class="card" role="document">
    <div class="card-body">
        <div class="alert alert-warning text-dark" role="alert">
            <h3 class="alert-heading">{$payment_pi_auto_complete_header}</h3>
            <p class="mt-3">{$payment_pi_auto_complete_description}</p>
        </div>
        <div id="{$ppcpFundingsource}-fieldsContainer" ></div>
        <span id="complete-order-button"></span>
    </div>
</div>
<form method="post" id="complete_order" class="jtl-validate">
    <input type="hidden" name="ppcpOrderId" value="{$ppcpOrderId}">
    <input type="hidden" name="shopOrderId" value="{$shopOrderId}">
</form>
<script>
    (function () {
        let fundingSource       = '{$ppcpFundingsource}',
            paymentFundsMapping = {$ppcFundingMethodsMapping};
        $(window).on('ppc:buttonInit', function (e, data, actions) {
            history.pushState(null, null, data.ppcCancelURL);
        });
        if (typeof(window.PPCcomponentInitializations) === 'undefined') {
            window.PPCcomponentInitializations = [initPPCFieldsContainer];
        } else {
            window.PPCcomponentInitializations.push(initPPCFieldsContainer);
        }
        function initPPCFieldsContainer(ppc_jtl) {
            let fields = {
                fundingSource: fundingSource,
                fields: {
                }
            }
            Object.keys(paymentFundsMapping[fundingSource].fields).forEach((key) => {
                fields.fields[key] = { value: paymentFundsMapping[fundingSource].fields[key] };
            });
            ppc_jtl.PaymentFields(fields).render('#{$ppcpFundingsource}-fieldsContainer');
        }
    })();
</script>
