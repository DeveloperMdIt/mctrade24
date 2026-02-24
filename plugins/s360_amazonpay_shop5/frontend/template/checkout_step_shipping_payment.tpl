{strip}
    <div class="row">
        <div class="col-12 col-xs-12 col-sm-6 mb-5 bottom10">
            {* Shipping address as selected in Amazon Pay *}
            <h3>{lang key="shippingAdress" section="checkout"}</h3>
            <p>{include file='checkout/inc_delivery_address.tpl' Lieferadresse=$lpaCheckoutGlobal.displayShippingAddress}</p>
            <a id="lpaChangeAddressButton" href="javascript:void(0);">{$oPlugin->getLocalization()->getTranslation('change_amazonpay_deliveryaddress')}</a>
        </div>
        <div class="col-12 col-xs-12 col-sm-6 mb-5 bottom10">
            {* Payment method as selected in Amazon Pay *}
            <h3>{lang key="paymentMethod" section="checkout"}</h3>
            <p>{$lpaCheckoutGlobal.paymentDescription}</p>
            <a id="lpaChangePaymentButton" href="javascript:void(0);">{$oPlugin->getLocalization()->getTranslation('change_amazonpay_paymethod')}</a>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-12">
            {* Shipping method and packaging selection - the content of this will be filled by XHR calls *}
            <div class="alert alert-danger lpa-error-message" id="lpa-error-message-noshipping" style="display:none;">{$oPlugin->getLocalization()->getTranslation('no_shipping_method')}</div>
            <div class="alert alert-danger lpa-error-message" id="lpa-error-message-packstation" style="display:none;">{$oPlugin->getLocalization()->getTranslation('packstation_not_allowed')}</div>
            <div class="alert alert-danger lpa-error-message" id="lpa-error-message-generic" style="display:none;">{$oPlugin->getLocalization()->getTranslation('error_checkout_generic')}</div>
            <form class="form evo-validate" method="post">
                {$jtl_token}
                <input type="hidden" name="gotoSummary" value="1"/>
                <div id="lpa-shipping-methods-container" class="mb-5 bottom10">
                    {include file=$lpaCheckoutGlobal.templatePathShippingMethods}
                </div>
                {if $lpaCheckoutGlobal.creditPossible}
                    <div id="lpa-use-credit" class="mb-5 bottom10">
                        <legend>{lang key='useCredits' section='checkout'}</legend>
                        <p><b>{lang key='yourCreditIs' section='account data'}: </b>{$lpaCheckoutGlobal.creditLocalized}</p>
                        <div class="form-group">
                            <div class="checkbox custom-control custom-checkbox">
                                <input type="checkbox" name="useShopCredit" value="1" id="lpa-use-credit-checkbox" class="custom-control-input">
                                <label class="control-label custom-control-label" for="lpa-use-credit-checkbox">{lang key='useCredits' section='checkout'}</label>
                            </div>
                        </div>
                    </div>
                {/if}
                {if isset($lpaCheckoutGlobal.subscriptionInterval)}
                    <div class="row">
                        <div class="col-12 col-xs-12">
                            <div class="alert alert-info">
                                {$oPlugin->getLocalization()->getTranslation('subscription_checkout_hint')} {$lpaCheckoutGlobal.subscriptionInterval->toDisplayString()}
                            </div>
                        </div>
                    </div>
                {/if}
                <div class="row">
                    <div class="col-12 col-xs-12 col-sm-6 col-sm-push-6 lpa-submit order-1 order-sm-2 mb-5 bottom10">
                        <div id="lpa-shipping-payment-submit" style="display:none;">
                            <button class="btn btn-primary btn-lg submit submit_once" type="submit">{lang key="nextStepCheckout" section="checkout"}</button>
                            <p class="top5 mt-1">
                                <small>{$oPlugin->getLocalization()->getTranslation('check_order_hint')}</small>
                            </p>
                        </div>
                    </div>
                    <div class="col-12 col-xs-12 col-sm-6 col-sm-pull-6 lpa-cancel order-2 order-sm-1 mb-5 bottom10">
                        <a href="{$lpaCheckoutGlobal.checkoutUrl}?cancelAmazonPay" onclick="amazon.Pay.signout();" rel="nofollow" title="{$oPlugin->getLocalization()->getTranslation('cancel_checkout')}">{$oPlugin->getLocalization()->getTranslation('cancel_checkout')}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {* JS parts *}
    <script type="text/javascript">
        window.lpaJqAsync = window.lpaJqAsync || [];
        lpaJqAsync.push(['ready', function () {
            var $shippingMethodsContainer = $('#lpa-shipping-methods-container');
            var $creditsContainer = $('#lpa-use-credit');
            var $form = $shippingMethodsContainer.closest('form');

            {* This function is called after shipping method selection or packaging or using credit, we use this to update the checkout session against amazon pay *}
            var updateShippingMethodAndCredit = function () {
                $('#lpa-shipping-payment-submit').hide();
                $('.lpa-error-message').hide();
                $form.removeClass('lpa-valid');
                {* get selected shipping method *}
                var $selectedShippingMethod = $form.find('[name="Versandart"]:checked');
                if ($selectedShippingMethod.length) {
                    var params = {
                        'shippingMethodId': $selectedShippingMethod.val()
                    };

                    var packagings = [];
                    $form.find('[name="kVerpackung[]"]:checked').each(function () {
                        packagings.push($(this).val());
                    });
                    params['packagingIds'] = packagings;

                    if ($('input[name="useShopCredit"]:checked').length) {
                        params['useShopCredit'] = "1";
                    } else {
                        params['useShopCredit'] = "0";
                    }

                    {* selected packagings (note: those might be more than just one) *}
                    $form.find('input, textbox, select').attr('disabled', 'disabled');
                    $form.find('input, textbox, select').prop('disabled', true);
                    var ioRequestData = {
                        name: "lpaAjaxShippingMethodSelected",
                        params: [
                            params['shippingMethodId'],
                            params['packagingIds'],
                            params['useShopCredit']
                        ]
                    };
                    $.ajax({
                        url: '{$ShopURLSSL}{$lpaCheckoutGlobal.ioPath}',
                        data: 'io=' + JSON.stringify(ioRequestData),
                        method: 'POST'
                    }).done(function (data) {
                        if (typeof data !== "undefined" && data.result === 'success') {
                            $form.addClass('lpa-valid');
                            $('#lpa-shipping-payment-submit').show();
                        } else {
                            console.log('AmazonPay: No data received or error on shipping method select update call:', data);
                        }
                    }).always(function () {
                        $form.find('input, textbox, select').removeAttr('disabled');
                        $form.find('input, textbox, select').prop('disabled', false);
                        if (typeof $.evo !== 'undefined' && $.evo !== null && typeof $.evo.basket === 'function') {
                            var $evoBasket = $.evo.basket();
                            if (typeof $evoBasket !== 'undefined' && $evoBasket !== null && typeof $evoBasket.updateCart === 'function') {
                                $evoBasket.updateCart(0);
                            }
                        }
                    });
                }
            };
            if ($('[name="Versandart"]').val()) {
                updateShippingMethodAndCredit();
            }
            $shippingMethodsContainer.on('change', '[name="Versandart"], [name^="kVerpackung"]', updateShippingMethodAndCredit);
            $creditsContainer.on('change', '[name="useShopCredit"]', updateShippingMethodAndCredit);
        }]);
        lpaJqAsync.push(['payReady', function () {
            amazon.Pay.bindChangeAction('#lpaChangeAddressButton', {
                amazonCheckoutSessionId: '{$lpaCheckoutGlobal.checkoutSession->getCheckoutSessionId()}',
                changeAction: 'changeAddress'
            });
            amazon.Pay.bindChangeAction('#lpaChangePaymentButton', {
                amazonCheckoutSessionId: '{$lpaCheckoutGlobal.checkoutSession->getCheckoutSessionId()}',
                changeAction: 'changePayment'
            });
        }]);
    </script>
{/strip}