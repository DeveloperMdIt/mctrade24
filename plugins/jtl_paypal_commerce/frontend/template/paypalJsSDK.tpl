<script src="{$ppcFrontendUrl}js/paypal.browser.min.js?v=1.1.0"></script>

<script>
        let ppcClientID          = '{$ppcClientID}',
            ppcClientToken       = {if isset($ppcClientToken)}'{$ppcClientToken}'{else}null{/if},
            ppcVaultToken        = {if isset($ppcVaultToken)}'{$ppcVaultToken}'{else}null{/if},
            buttonActions        = null,
            ppcOrderLocale       = '{$ppcOrderLocale}',
            ppcCurrency          = '{$ppcCurrency}',
            ppcComponents        = {json_encode($ppcComponents)},
            ppcFundingDisabled   = {json_encode(array_values($ppcFundingDisabled))},
            ppcCommit            = '{$ppcCommit}',
            ppcConsentID         = '{$ppcConsentID}',
            ppcConsentActive     = {$ppcConsentActive},
            ppcConsentGiven      = ppcConsentActive === false ? true : {$ppcConsentGiven},
            ppcBNCode            = '{$ppcBNCode}',
            wrapperLoaded        = false,
            ppcJtl               = null,
            loadedComponents     = [],
            reloadableComponents = [
                'productDetails', 'initProductDetailsECSButtons', 'initShippingSelectionButtons', 'orderProcess'
            ];
        let config = {
            "client-id": ppcClientID,
            "data-client-token": ppcClientToken,
            "data-user-id-token": ppcVaultToken,
            "currency": ppcCurrency,
            "commit": ppcCommit,
            "components": ppcComponents,
            "locale": ppcOrderLocale,
            "enable-funding": 'paylater',
            "data-partner-attribution-id": ppcBNCode,
            {if isset($ppcBuyerCountry) && $ppcBuyerCountry !== ''}"buyer-country": "{$ppcBuyerCountry}"{/if}
        }

        if (ppcConsentGiven === true) {
            loadPaypalWrapper(config);
        }

        $(window)
            .on('ppc:componentInit',function(event,initFunction,skipConsent) {
                if (skipConsent === true) {
                    ppcConsentGiven = true;
                }
                if (wrapperLoaded === false) {
                    checkConsent();
                }
            })
            .on('ppc:requestConsent',function() {
                $(window).trigger('ppc:getConsent',ppcConsentGiven);
            });

        document.addEventListener('consent.updated', function (e) {
            if (e.detail[ppcConsentID]) {
                ppcConsentGiven = true;
                $(window).trigger('ppc:getConsent', ppcConsentGiven);
                loadPaypalWrapper(config);
            }
        });

        $(document).ready(function() {
            $(window).trigger('ppc:requestConsent');
        })

        function loadPaypalWrapper(config) {
            if (wrapperLoaded === false) {
                wrapperLoaded = true;
                window.paypalLoadScript(config).then((ppc_jtl) => {
                    ppcJtl = ppc_jtl;
                    runComponents(ppc_jtl);
                    $(window).off('ppc:componentInit').on('ppc:componentInit',function(event, initComponent) {
                        if (reloadableComponents.indexOf(initComponent.name) !== -1) {
                            loadedComponents.push(initComponent.name);
                            initComponent(ppc_jtl);
                        }
                        if (loadedComponents.indexOf(initComponent.name) === -1) {
                            loadedComponents.push(initComponent.name);
                            initComponent(ppc_jtl);
                        }
                    })
                }).catch( () => {

                });
            }
        }

        function checkConsent() {
            if (ppcConsentGiven === false) {
                if (typeof CM !== 'undefined') {
                    if (CM.getSettings(ppcConsentID) === false) {
                        CM.openConfirmationModal(ppcConsentID, function () {
                            ppcConsentGiven = true;
                            $(window).trigger('ppc:getConsent',ppcConsentGiven);
                            loadPaypalWrapper(config);
                        });
                    }
                }
            } else {
                loadPaypalWrapper(config);
            }
        }

        function runComponents(ppc_jtl) {
            if (typeof(window.PPCcomponentInitializations) !== 'undefined') {
                window.PPCcomponentInitializations.forEach(component => {
                    if (!loadedComponents.includes(component.name)) {
                        loadedComponents.push(component.name);
                        component(ppc_jtl);
                    }
                });
            }
        }

        function ppcpIOManagedCall(name, params, context, callback) {
            let data = {
                'csrf': '{$ppcCSRFToken}',
                'name': name,
                'params': params
            };
            $.evo.io().request(data, context, callback);
        }
</script>
