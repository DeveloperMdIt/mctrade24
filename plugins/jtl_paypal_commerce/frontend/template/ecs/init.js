function initButtons(
    ppc_jtl,
    ppcConfig,
    ppcNamespace,
    renderStandaloneButton,
    renderContainerID,
    buttonID,
    activeButtonLabel,
    isOrderConfirmationPage,
    preDefinedFundingSource,
    ppcVaulting
) {
    if (isOrderConfirmationPage === false) {
        let loadingPlaceholderID = '#ppc-loading-placeholder-' + ppcNamespace;
        $(renderContainerID).html('');
        setTimeout(function () {
            let btnCount = 0;
            ppc_jtl.getFundingSources().forEach(function (fundingSource) {
                if (typeof fundingSource === 'undefined') {
                    return;
                }
                let id = ppcNamespace + '-ppc-' + fundingSource + '-standalone-button';
                if ($('#' + id).length > 0) {
                    return;
                }
                let button = null;
                try {
                    button = renderStandaloneButton(ppc_jtl, fundingSource, ppcConfig);
                } catch (error) {
                    if (error.message.includes('style.color')) {
                        button = renderStandaloneButton(ppc_jtl, fundingSource, {
                            shape: ppcConfig.shape,
                            layout: ppcVaulting ? 'vertical' : 'horizontal'
                        });
                    } else {
                        button = renderStandaloneButton(ppc_jtl, fundingSource, {});
                    }
                }
                if (button.isEligible() && !ppcFundingDisabled.includes(fundingSource)) {
                    $(renderContainerID).append(standaloneButtonTemplate({id, fundingSource, layout:ppcVaulting ? 'vertical' : 'horizontal'}));
                    button.render('#' + id);
                    $('#' + id).hide();
                    btnCount++;
                }
            });
            if (btnCount === 1 && ppcNamespace !== 'productDetails') {
                let $renderContainer = $(renderContainerID);
                $renderContainer.addClass(['pr-3', 'pl-3']);
                $('.ppc-standalone-buttons', $renderContainer)
                    .removeClass('col-md-6')
                    .addClass('col-md-12');
            }
            $(buttonID).parent().hide();
            $(buttonID).fadeOut(600, function () {
                $('.ppc-standalone-buttons').fadeIn('slow');
            });
            $(loadingPlaceholderID).hide();
            $('.ppc-standalone-buttons').fadeIn('slow');
        },500)
    } else {
        try {
            renderStandaloneButton(ppc_jtl,preDefinedFundingSource, ppcConfig);
        } catch (err) {
            if (err.message.includes('style.color')) {
                renderStandaloneButton(ppc_jtl,preDefinedFundingSource, {
                    label: ppcConfig.label,
                    shape: ppcConfig.shape
                });
            } else {
                renderStandaloneButton(ppc_jtl,preDefinedFundingSource,{});
            }
        }
    }
}

let ppcEventListener = (fundingSource, errorMessage, renderContainerID, ppcECSUrl, ppcVaulting) => {
    let listener = {
        createOrder: async function (data, actions) {
            let context = {};
            try {
                return await new Promise((resolve, reject) => {
                    ppcpIOManagedCall('jtl_paypal_commerce.createOrder', [
                        fundingSource,
                        'PayPalCommerce',
                        null,
                        {'ppc_vaulting_active':ppcVaulting}
                    ], context, function (error, res) {
                        if (error) {
                            reject(res.error);
                        } else {
                            resolve(res.orderId);
                        }
                    });
                });
            } catch (e) {
                $.evo.extended().showNotify({title: errorMessage, text: e});
            }
        },
        onInit: function (data, actions) {
        },
        onApprove: async function (data, actions) {
            $(renderContainerID).addClass('opacity-half');
            try {
                $.evo.extended().startSpinner();
            } catch (e) {
                $.evo.extended().spinner();
            }
            location.href = ppcECSUrl;
        },
        onCancel: async function (data, actions) {
            try {
                $.evo.extended().startSpinner();
            } catch (e) {
                $.evo.extended().spinner();
            }
            location.reload();
        },
    }

    if (!ppcVaulting) {
        listener.onShippingChange = async function (data, actions) {
            try {
                let context = {};
                return await new Promise((resolve, reject) => {
                    ppcpIOManagedCall('jtl_paypal_commerce.shippingChange', [data], context, function (error, res) {
                        if (error) {
                            reject(res.error);
                        } else {
                            resolve(context);
                        }
                    });
                }).then(function (response) {
                    return context.patch ? actions.resolve() : actions.reject();
                });
            } catch (e) {
                $.evo.extended().showNotify({title: errorMessage, text: e});
            }
        }
    }

    return listener;
}
