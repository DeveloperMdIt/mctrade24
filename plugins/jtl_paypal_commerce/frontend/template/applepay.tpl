<script src="{$ppcFrontendUrl}js/logger.min.js?v=1.3.0"></script>
<script src="{$ppcFrontendUrl}js/applepay.min.js?v=1.4.0"></script>
<script async src="https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js" onLoad="onApplePayLoaded()"></script>
<div class="spinner-border mr-2" id="ppc-loading-spinner-confirmation" role="status">
    <span class="sr-only">Loading...</span>
</div>
<style>
    apple-pay-button {
        --apple-pay-button-width: 100%;
        --apple-pay-button-height: inherit;
    }
</style>
<div id="paypal-button-container" class="d-flex h-100 justify-content-end align-items-center ppcOrderConfirmation" style="background-color: black"></div>
<script>
    let ppcApplePay = new PPCApplePayHandler(jQuery),
        ppcLogger   = null;

    ppcApplePay.init({
        shopName:        {json_encode($ppcShopName)},
        locale:          '{$ppcLocale}',
        fundingSource:   '{$ppcFundingSource}',
        orderId:         '{$ppcOrderId}',
        transactionInfo: {$ppcTransactionInfo},
        billingContact:  {$ppcBillingContact},
        cancelURL:       '{$ppcCancelURL}',
        stateURL:        '{$ppcStateURL}',
        isSandbox:       {if $ppcSandbox === false}false{else}true{/if},
        callbacks:       {
            onApplePayNotAvailable: onApplePayNotAvailable,
            onApplePayCanceled: onApplePayCanceled,
        }
    });

    function onApplePayLoaded() {
        ppcLogger = new PPCPaymentLogger('PayPalApplePay', {$ppcLogLevel})
        ppcLogger.debug('onApplePayLoaded');
        if (typeof(window.PPCcomponentInitializations) === 'undefined') {
            ppcLogger.debug('init PPCcomponentInitializations');
            window.PPCcomponentInitializations = [ppcApplePayRender];
        }
        $(window).trigger('ppc:componentInit', [ppcApplePayRender, true]);
    }

    function onApplePayNotAvailable(data) {
        ppcLogger ? ppcLogger.debug('onApplePayNotAvailable', data) : null;
        ppcApplePay.cancelPayment(
            '{lang key='yourChosenPaymentOption' section='checkout' addslashes=true}',
            '{htmlentities($ppcApplePayNotAvailable)}'
        )
    }

    function onApplePayCanceled(data) {
        ppcLogger ? ppcLogger.debug('onApplePayCanceled', data) : null;
        ppcApplePay.cancelPayment(
            '{lang key='yourChosenPaymentOption' section='checkout' addslashes=true}',
            '{htmlentities($ppcApplePayCanceled)}'
        )
    }

    async function ppcApplePayRender(ppc_jtl) {
        await ppcLogger.debug('ppcApplePayRender', ppc_jtl);
        await ppcApplePay.render(ppc_jtl, ppcLogger);
    }
</script>
