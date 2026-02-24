<div id="ppc-paypal-button-custom-{$ppcNamespace}-wrapper" class="mt-3 d-none">
    {include './components/paypalPreloadButton.tpl'}
</div>
<div id="paypal-button-{$ppcNamespace}-container" class="mt-3">
    {include './components/loadingPlaceholder.tpl'}
    <div id ="ppc-{$ppcNamespace}-horizontal-container"
         class="row ppc-ecs-{if isset($ppcVaultingActive) && $ppcVaultingActive}vertical{else}horizontal{/if}-container">
    </div>
</div>

<script>
        if (typeof(window.PPCcomponentInitializations) === 'undefined') {
            window.PPCcomponentInitializations = [];
        }
        (function () {
            let cartButton          = $('#cart-checkout-btn'),
                isCartButtonVisible = cartButton.css('display') !== 'none',
                isCartButtonEnabled = !cartButton[0].hasAttribute('disabled');
            {include './components/defaultVariables.tpl'}
            {literal}
            if (isCartButtonVisible && isCartButtonEnabled) {
                window.PPCcomponentInitializations.push(initCartECSButtons);
                $(window).on('ppc:getConsent',function(event, consent) {
                    if (consent === false) {
                        $(wrapperID).removeClass('d-none');
                        $(buttonID).on('click',function () {
                            $(spinnerID).removeClass('d-none');
                            $(this).addClass('disabled').prop('disabled', true).off('click');
                            $(window).trigger('ppc:componentInit',[initCartECSButtons, true]);
                        });
                    } else {
                        $(buttonID).addClass('disabled').prop('disabled', true).off('click');
                        if ($(renderContainerID + ' iframe').length <= 0) {
                            $(loadingPlaceholderID).removeClass('d-none hidden');
                        }
                        $(window).trigger('ppc:componentInit',[initCartECSButtons, true]);
                    }
                });

            }
            function initCartECSButtons(ppc_jtl) {
                $(renderContainerID).removeClass('d-none').html('');
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
                    ppcVaultingActive
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
                    ...ppcEventListener(fundingSource, errorMessage, renderContainerID, ppcECSUrl, ppcVaultingActive)
                });
            }
        })()
        {/literal}
</script>
