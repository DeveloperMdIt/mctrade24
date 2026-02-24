<script>
    (function() {
        if (!window.ApplePaySession || !window.ApplePaySession.canMakePayments()) {
            $('#{$applepayModuleId}').hide();
        }
    })();
</script>
