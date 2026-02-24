<script src="{$ppcFrontendUrl}js/logger.min.js?v=1.3"></script>
<script src="{$ppcFrontendUrl}js/googlepay.min.js?v=1.3"></script>
<script async src="https://pay.google.com/gp/p/js/pay.js" onload="onGooglePayLoaded()"></script>
<div class="spinner-border mr-2" id="ppc-loading-spinner-confirmation" role="status">
    <span class="sr-only">Loading...</span>
</div>
<div id="paypal-button-container" class="d-flex justify-content-end align-items-center ppcOrderConfirmation"></div>
<script>
    let ppcGPay = new PPCGooglePayHandler(jQuery);
    ppcGPay.init({
        locale:          '{$ppcLocale}',
        fundingSource:   '{$ppcFundingSource}',
        orderId:         '{$ppcOrderId}',
        transactionInfo: {$ppcTransactionInfo},
        cancelURL:       '{$ppcCancelURL}',
        stateURL:        '{$ppcStateURL}',
        isSandbox:       {if $ppcSandbox === false}false{else}true{/if},
        callbacks:       {
            onGooglePayNotAvailable: onGooglePayNotAvailable,
            onPayerActionError:      onGooglePayerActionError,
            onProcessPaymentError:   onGoogleProcessPaymentError
        }
    });

    function onGooglePayLoaded() {
        if (typeof(window.PPCcomponentInitializations) === 'undefined') {
            window.PPCcomponentInitializations = [ppcGPayRender];
        }
        $(window).trigger('ppc:componentInit', [ppcGPayRender, true]);
    }

    function onGooglePayNotAvailable(data) {
        ppcGPay.cancelPayment(
            '{lang key='yourChosenPaymentOption' section='checkout' addslashes=true}',
            '{htmlentities($ppcGPayNotAvailable)}'
        )
    }

    function onGooglePayerActionError(data) {
        ppcGPay.cancelPayment(
            '{htmlentities($ppcPaymentName)}',
            '{htmlentities($ppcPayerActionError)}'
        )
    }

    function onGoogleProcessPaymentError(data) {
        ppcGPay.cancelPayment(
            '{htmlentities($ppcPaymentName)}',
            '{htmlentities($ppcProcessPaymentError)}'
        )
    }

    async function ppcGPayRender(ppc_jtl) {
        await ppcGPay.render(ppc_jtl, new PPCPaymentLogger('PayPalGPay', {$ppcLogLevel}));
    }
</script>
