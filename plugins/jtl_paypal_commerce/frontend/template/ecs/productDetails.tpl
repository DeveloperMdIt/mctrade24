<div class="row">
    <div id="ppc-paypal-button-custom-{$ppcNamespace}-wrapper" class="d-none col-12 col-sm-6 col-lg-6 offset-lg-6 offset-sm-6">
        {include './components/paypalPreloadButton.tpl'}
    </div>
</div>
<div class="row">
    <div id="paypal-button-{$ppcNamespace}-container" class="col-12 col-lg-12 offset-lg-0">
        {include './components/loadingPlaceholder.tpl'}
        <div id ="ppc-{$ppcNamespace}-horizontal-container" class="row ppc-ecs-horizontal-container"></div>
    </div>
</div>
<script>
    if (typeof(window.PPCcomponentInitializations) === 'undefined') {
        window.PPCcomponentInitializations = [];
    }

    (function () {
        let cartButton           = $('#add-to-cart button[name="inWarenkorb"]'),
            isCartButtonVisible  = cartButton.css('display') !== 'none',
            isCartButtonEnabled  = cartButton.length > 0 ? !cartButton[0].hasAttribute('disabled') : false;
        {include './components/defaultVariables.tpl'}

        {literal}
        if (isCartButtonVisible && isCartButtonEnabled) {
            loadProductDetails();
        } else {
            $(window).on('evo:priceChanged', function (e) {
                loadProductDetails();
            })
        }

        function loadProductDetails() {
            window.PPCcomponentInitializations.push(initProductDetailsECSButtons);
            $(window).on('ppc:getConsent',function(event, consent) {
                if (consent === false) {
                    $(wrapperID).removeClass('d-none hidden');
                    $(buttonID).on('click', function () {
                        $(spinnerID).removeClass('d-none hidden');
                        $(this).addClass('disabled').prop('disabled', true).off('click');
                        $(window).trigger('ppc:componentInit',[initProductDetailsECSButtons, true]);
                    });
                } else {
                    $(buttonID).addClass('disabled').prop('disabled', true).off('click');
                    if ($(renderContainerID + ' iframe').length <= 0) {
                        $(loadingPlaceholderID).removeClass('d-none hidden');
                    }
                    $(window).trigger('ppc:componentInit',[initProductDetailsECSButtons, true]);
                }
            });
            $(document).ready(function() {
                $(window).trigger('ppc:requestConsent');
            })
        }

        function initProductDetailsECSButtons(ppc_jtl) {
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
            let customEventListener = {
                ...ppcEventListener(fundingSource, errorMessage, renderContainerID, ppcECSUrl, ppcVaultingActive),
                ...customEvents(fundingSource, ppcVaultingActive)
            }
            return ppc_jtl.Buttons({
                fundingSource: fundingSource,
                style: {
                    ...style,
                    label: "checkout",
                    height: 43
                },
                ...customEventListener
            });
        }

        const customEvents = (fundingSource, ppcVaulting) => {
            let listener = {
                onInit: function (data, actions) {
                    if (parseFloat($('meta[itemprop="price"]', $('#buy_form')).attr('content')) > 0.0) {
                        actions.enable();
                    } else {
                        actions.disable();
                    }
                    $(document).on('evo:changed.article.price', function (e, data) {
                        if (data.price > 0) {
                            actions.enable();
                        } else {
                            actions.disable();
                        }
                    });
                },
                onClick: function (data, actions) {
                    return $('#buy_form')[0].checkValidity()
                        ? actions.resolve()
                        : actions.reject('{/literal}{addslashes($ecs_wk_error_title)}{literal}');
                },
                createOrder: async function (data, actions) {
                    try {
                        let $form = $('#buy_form'),
                            $basket   = $.evo.basket(),
                            formData  = $form.serializeObject(),
                            varId     = typeof formData['VariKindArtikel'] === 'undefined' ? 0 : parseInt(formData['VariKindArtikel']),
                            productId = varId > 0 ? varId : parseInt(formData[$basket.options.input.id]),
                            quantity  = parseFloat(formData[$basket.options.input.quantity].replace(',', '.'));
                        $basket.toggleState($form, true);

                        if (productId > 0 && quantity > 0 && $form[0].checkValidity()) {
                            formData[$basket.options.input.id] = productId;

                            return await new Promise((resolve, reject) => {
                                $.evo.io().call('pushToBasket', [productId, quantity, formData], $basket, function (error, data) {
                                    $basket.toggleState($form, false);
                                    if (error) {
                                        reject(error);
                                    } else {
                                        if (data.response) {
                                            if (data.response.nType === 0) {
                                                reject(data.response.cHints[0]);
                                                return;
                                            }
                                        } else {
                                            reject('response is empty');
                                        }
                                    }
                                    let createOrder = {};
                                    ppcpIOManagedCall('jtl_paypal_commerce.createOrder', [
                                        fundingSource,
                                        'PayPalCommerce',
                                        null,
                                        {'ppc_vaulting_active':ppcVaulting}
                                    ], createOrder, function (error, data) {
                                        if (error) {
                                            reject(data.error);
                                        } else {
                                            if (data.orderId) {
                                                $basket.updateCart();
                                                resolve(data.orderId);
                                            } else {
                                                if (data.createResultDetails) {
                                                    errorMessage = data.createResult;
                                                    reject(data.createResultDetails);
                                                } else if(data.createResult) {
                                                    reject(data.createResult);
                                                } else {
                                                    reject('{/literal}{addslashes($ecs_wk_error_desc)}{literal}');
                                                }
                                            }
                                        }
                                    });
                                });
                            });
                        } else {
                            errorMessage = '{/literal}{addslashes($ecs_wk_error_title)}{literal}';
                            throw '{/literal}{addslashes($ecs_wk_error_desc)}{literal}';
                        }
                    } catch (e) {
                        $.evo.basket().updateCart();
                        $.evo.extended().showNotify({title: errorMessage, text: e});
                    }
                }
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
    })()
    {/literal}
</script>
