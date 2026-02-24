<div id="ppc-paypal-button-custom-{$ppcNamespace}-wrapper" class="mt-3 d-none">
    {include './components/paypalPreloadButton.tpl'}
</div>
<div id="paypal-button-{$ppcNamespace}-container" class=" mt-3">
    {include './components/loadingPlaceholder.tpl'}
    <div id ="ppc-{$ppcNamespace}-horizontal-container" class="form-row ppc-ecs-horizontal-container"></div>
</div>

<script>
        if (typeof(window.PPCcomponentInitializations) === 'undefined') {
            window.PPCcomponentInitializations = [];
        }
        (function () {
            {include './components/defaultVariables.tpl'}
            {literal}
            window.PPCcomponentInitializations.push(initOrderProcessECSButtons);
            $(document).ready(function() {
                {/literal}
                {assign var=alertMissingPayerData value=$alertList->getAlert('missingPayerData')}
                {if $alertMissingPayerData !== null}
                eModal.setModalOptions({
                    backdrop: 'static'
                });
                eModal.alert({
                    message: '{htmlentities($alertMissingPayerData->getMessage())}',
                    title: '{$alertMissingPayerData->getLinkText()}',
                    buttons: [{
                        text: 'OK',
                        close: true
                    }],
                });
                {/if}
                {literal}
            });
            $(window).on('ppc:getConsent',function(event, consent) {
                if (consent === false) {
                    $(wrapperID).removeClass('d-none');
                    $(buttonID).on('click',function () {
                        $(spinnerID).removeClass('d-none');
                        $(this).addClass('disabled').prop('disabled', true).off('click');
                        $(window).trigger('ppc:componentInit',[initOrderProcessECSButtons, true]);
                    });
                } else {
                    $(buttonID).addClass('disabled').prop('disabled', true).off('click');
                    if ($(renderContainerID + ' iframe').length <= 0) {
                        $(loadingPlaceholderID).removeClass('d-none hidden');
                    }
                    $(window).trigger('ppc:componentInit',[initOrderProcessECSButtons, true]);
                }
            });

            function initOrderProcessECSButtons(ppc_jtl) {
                $(renderContainerID).html('');
                initButtons(
                    ppc_jtl,
                    ppcConfig,
                    ppcNamespace,
                    renderStandaloneButton,
                    renderContainerID,
                    buttonID,
                    activeButtonLabel,
                    false,
                    '',
                    false
                );
            }

            function renderStandaloneButton(ppc_jtl, fundingSource, style) {
                return ppc_jtl.Buttons({
                    fundingSource: fundingSource,
                    style: {
                        ...style,
                        label: "checkout",
                        height: 43
                    },
                    ...ppcEventListener(fundingSource, errorMessage, renderContainerID, ppcECSUrl)
                });
            }
        })();
        {/literal}
</script>
