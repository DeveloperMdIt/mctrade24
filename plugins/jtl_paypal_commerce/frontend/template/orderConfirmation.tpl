<div class="spinner-border mr-2" id="ppc-loading-spinner-confirmation" role="status">
    <span class="sr-only">Loading...</span>
</div>
<div id="paypal-button-container" class="d-flex justify-content-center align-items-center ppcOrderConfirmation"></div>
<script>
    {literal}
    if (typeof(window.PPCcomponentInitializations) === 'undefined') {
        window.PPCcomponentInitializations = [];
    }

    (function () {
        {/literal}
        let ppcFundingSource = '{$ppcFundingSource}',
            ppcConfig        = {$ppcConfig},
            buttonActions    = null,
            ppcOrderId       = '{$ppcOrderId}',
            ppcStateURL      = '{$ppcStateURL}',
            ppcCancelURL     = '{$ppcCancelURL}';
        {literal}
        if (ppcFundingSource !== '') {
            $('#complete-order-button').hide();
            $('#ppc-loading-spinner-confirmation').show();
        }
        window.PPCcomponentInitializations.push(initOrderConfirmationButtons);
        $(document).ready(function() {
            $(window).trigger('ppc:componentInit', [initOrderConfirmationButtons, true]);
            {/literal}
            {$alertOvercapture = $alertList->getAlert('handlePayerActionRequired')}
            {if $alertOvercapture !== null}
            eModal.alert({
                message: {json_encode($alertOvercapture->getMessage())},
                title: {json_encode($alertOvercapture->getLinkText())},
                buttons: [
                    {
                        text: '{lang key='close' section='account data' addslashes=true}',
                        close: true,
                        click: function() {
                            $.evo.smoothScrollToAnchor('#complete_order', false);
                        }
                    }
                ],
            });
            {/if}
            {literal}
        });

        function initOrderConfirmationButtons(ppc_jtl) {
            initButtons(
                ppc_jtl,
                ppcConfig.smartPaymentButtons,
                null,
                renderStandaloneButton,
                null,
                null,
                null,
                true,
                ppcFundingSource,
                false
            );
        }

        function renderStandaloneButton(ppc_jtl,fundingSource,ppcConfig) {
            $('#ppc-loading-spinner-confirmation').hide();
            ppc_jtl.Buttons({
                fundingSource: fundingSource,
                style: ppcConfig,
                ...customEvents(buttonActions, ppcOrderId, ppcStateURL)
            }).render('#paypal-button-container');
        }

        const customEvents = (buttonActions, ppcOrderId, ppcStateURL) => {
            return {
                onInit: function (data, actions) {
                    buttonActions = actions;
                    data.ppcOrderId   = ppcOrderId;
                    data.ppcStateURL  = ppcStateURL;
                    data.ppcCancelURL = ppcCancelURL;
                    $(window).trigger('ppc:buttonInit', [data, actions]);
                },
                onClick: function (data, actions) {
                    data.eventHandled = false;
                    $(window).trigger('ppc:buttonClick', [data, actions]);
                    if (data.eventHandled) {
                        return typeof data.eventResult !== 'undefined' ? data.eventResult : actions.reject();
                    }

                    return $('#complete_order')[0].checkValidity()
                        ? actions.resolve()
                        : actions.reject();
                },
                createOrder: function (data, actions) {
                    data.ppcOrderId = ppcOrderId;
                    $(window).trigger('ppc:buttonCreateOrder', [data, actions]);
                    return data.ppcOrderId;
                },
                onApprove: function (data, actions) {
                    data.eventHandled = false;
                    $(window).trigger('ppc:buttonOnApprove', [data, actions]);
                    if (data.eventHandled) {
                        return;
                    }

                    history.pushState(null, null, ppcStateURL);
                    let commentField = $('#comment'),
                        commentFieldHidden = $('#comment-hidden');
                    if (commentField && commentFieldHidden) {
                        commentFieldHidden.val(commentField.val());
                    }
                    $('#ppc-loading-spinner-confirmation').show();
                    $('#paypal-button-container').addClass('opacity-half');
                    buttonActions.disable();
                    $('form#complete_order').submit();
                },
                onCancel: function (data, actions) {
                    data.eventHandled = false;
                    $(window).trigger('ppc:buttonOnCancel', [data, actions]);
                    if (data.eventHandled) {
                        return;
                    }

                    history.pushState(null, null, ppcCancelURL);
                    window.location.reload();
                },
                onError: function (msg, actions) {
                    let data = {
                        eventHandled: false
                    };

                    if (typeof msg === 'string' || msg instanceof String) {
                        data.msg = msg;
                    }
                    $(window).trigger('ppc:buttonOnError', [data, actions]);
                    if (data.eventHandled) {
                        return;
                    }

                    history.pushState(null, null, ppcCancelURL);
                    window.location.reload();
                },
            }
        }
    })()
    {/literal}
</script>
