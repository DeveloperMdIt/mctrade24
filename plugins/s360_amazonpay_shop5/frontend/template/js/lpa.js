window.lpa = function () {
    var initLoginButton = function (buttonId) {
        var buttonElement = document.getElementById(buttonId);
        if (typeof window.amazon === 'undefined' || window.amazon === null) {
            console.log('Amazon Pay: Not completely initialized. Skipping button rendering.');
            return;
        }
        if (null === buttonElement || typeof buttonElement === 'undefined') {
            console.log('Amazon Pay: Warning: Login button element with ID "' + buttonId + '" not found.');
            return;
        }
        if (buttonElement.classList.contains('lpa-initialized')) {
            /* This button was initialized before */
            return;
        }
        window.amazon.Pay.renderButton('#' + buttonId, {
            // set checkout environment
            merchantId: buttonElement.getAttribute('data-merchant-id'),
            ledgerCurrency: buttonElement.getAttribute('data-ledger-currency'),
            checkoutLanguage: buttonElement.getAttribute('data-language'),
            productType: buttonElement.getAttribute('data-product-type'),
            buttonColor: buttonElement.getAttribute('data-color'),
            placement: buttonElement.getAttribute('data-placement'),
            sandbox: (buttonElement.getAttribute('data-sandbox') === 'true'),
            // configure sign in
            signInConfig: {
                payloadJSON: buttonElement.getAttribute('data-payload'),
                signature: buttonElement.getAttribute('data-signature'),
                publicKeyId: buttonElement.getAttribute('data-publickeyid')
            }
        });
        buttonElement.classList.add('lpa-initialized');
    };
    var initPayButton = function (buttonId) {
        var buttonElement = document.getElementById(buttonId);
        if (typeof window.amazon === 'undefined' || window.amazon === null) {
            console.log('Amazon Pay: Not completely initialized. Skipping button rendering.');
            return;
        }
        if (null === buttonElement || typeof buttonElement === 'undefined') {
            console.log('Amazon Pay: Warning: Pay button element with ID "' + buttonId + '" not found.');
            return;
        }
        if (buttonElement.classList.contains('lpa-initialized')) {
            /* This button was initialized before */
            return;
        }
        var context = buttonElement.getAttribute('data-context');
        var subscriptionInterval = buttonElement.getAttribute('data-subscription-interval');
        var amazonPayButton = null;
        var createCheckoutSessionParams = '[]';
        if(subscriptionInterval !== null && subscriptionInterval !== '' && subscriptionInterval !== undefined) {
            createCheckoutSessionParams = '["'+buttonElement.getAttribute('data-subscription-interval')+'"]';
        }
        var amazonPayButtonConfiguration = {
            merchantId: buttonElement.getAttribute('data-merchant-id'),
            createCheckoutSession: function () {
                // Perform add to basket operations
                return new Promise(function (resolve, reject) {
                    var basketSuccessCallbackFunction = function () {
                        $.ajax({
                            url: buttonElement.getAttribute('data-shop-url') + buttonElement.getAttribute('data-io-path'),
                            data: 'io={"name":"lpaCreateCheckoutSession","params":'+createCheckoutSessionParams+'}',
                            dataType: 'json',
                            method: 'POST'
                        }).done(function (data) {
                            if (typeof data !== "undefined" && data.result === 'success' && data.checkoutSessionId) {
                                resolve(data.checkoutSessionId);
                            } else {
                                console.log('AmazonPay: Checkout session could not be created: ', data);
                                reject(data);
                            }
                        });
                    };
                    if (context === 'payDetail' || context === 'payCategory') {
                        /* In detail page or listing context, we call the addToBasketFunction first */
                        addToBasketFunction(buttonId, basketSuccessCallbackFunction, reject);
                    } else {
                        basketSuccessCallbackFunction();
                    }
                });
            },
            ledgerCurrency: buttonElement.getAttribute('data-ledger-currency'),
            checkoutLanguage: buttonElement.getAttribute('data-language'),
            productType: buttonElement.getAttribute('data-product-type'),
            placement: buttonElement.getAttribute('data-placement'),
            buttonColor: buttonElement.getAttribute('data-color'),
            sandbox: (buttonElement.getAttribute('data-sandbox') === 'true')
        };
        if(subscriptionInterval === null || subscriptionInterval === '' || subscriptionInterval === undefined) {
            // Only set estimated order amount when not in subscription context and only if we have valid values
            var estimatedOrderAmountAmount = buttonElement.getAttribute('data-estimated-order-amount-amount');
            var estimatedOrderAmountCurrency = buttonElement.getAttribute('data-estimated-order-amount-currency');
            if(estimatedOrderAmountAmount && estimatedOrderAmountCurrency) {
                amazonPayButtonConfiguration['estimatedOrderAmount'] = {
                    amount: estimatedOrderAmountAmount,
                    currencyCode: estimatedOrderAmountCurrency
                }
            }
            amazonPayButton = window.amazon.Pay.renderButton('#' + buttonId, amazonPayButtonConfiguration);
            window.lpaPayButtons.push(amazonPayButton);
        } else {
            amazonPayButton = window.amazon.Pay.renderButton('#' + buttonId, amazonPayButtonConfiguration);
            buttonElement.style.display = 'none'; // hide subscription buttons after rendering them.
            // this implies we are in subscription mode, attempt to initialize the select button events, too
            var mainButtonId = buttonId.replaceAll(/-subscription-\d+\w+/g, '');
            var selectElement = document.getElementById(mainButtonId + '-subscription-select');
            if(selectElement && !selectElement.classList.contains('lpa-initialized')) {
                selectElement.addEventListener('change', function(event) {
                    var selectedValue = event.target.value;
                    var buttonsInGroup = document.getElementsByClassName('lpa-button-group-' + mainButtonId);
                    if(buttonsInGroup.length) {
                        for(var i = 0; i < buttonsInGroup.length; i++) {
                            buttonsInGroup[i].style.display = 'none';
                        }
                    }
                    var buttonToShow;
                    if(selectedValue === 'none') {
                        buttonToShow = document.getElementById(mainButtonId);
                    } else {
                        buttonToShow = document.getElementById(mainButtonId + '-subscription-' + selectedValue);
                    }
                    if(buttonToShow) {
                        buttonToShow.style.display = '';
                    }
                }, { passive: true });
                selectElement.classList.add('lpa-initialized');
            }
            window.lpaPaySubscriptionButtons.push(amazonPayButton);
        }
        buttonElement.classList.add('lpa-initialized');
    };
    /* Note: addToBasketFunction depends on jQuery being loaded */
    var addToBasketFunction = function (buttonId, onSuccessCallback, onErrorCallback) {
        var buttonElement = document.getElementById(buttonId);
        if (null === buttonElement) {
            onErrorCallback('AmazonPay: Failed to load button element with ID "' + buttonId + '"');
            return;
        }
        var $button = $(buttonElement);
        var context = $button.data('context');
        try {
            var buyFormId = '#buy_form' + ((context === 'payCategory') ? '_' + $button.data('product-id') : '');
            var $buyForm = $(buyFormId);
            /* check required fields */
            if ($buyForm.find(':invalid').length) {
                $button.closest('.lpa-button-wrapper').find('.lpa-pay-button-express-feedback').html($button.data('msg-required-field-missing'));
                onErrorCallback('AmazonPay: Required form fields missing.');
                return;
            }
            var data = $buyForm.serializeObject();
            var productId = 0;
            if (typeof data['VariKindArtikel'] !== 'undefined') {
                productId = parseInt(data['VariKindArtikel']);
            } else {
                productId = parseInt(data['a']);
            }
            var quantity = parseFloat(data['anzahl']);
            if (isNaN(quantity)) {
                onErrorCallback('AmazonPay: Quantity could not be determined.');
                return;
            }
            var that = $.evo.basket();
            $.evo.io().call('pushToBasket', [productId, quantity, data], that, function (error, data) {
                $button.css('cursor', 'initial');
                if (error) {
                    /* an error occured during the io call */
                    onErrorCallback(error);
                    return;
                }
                var response = data.response;
                if (response) {
                    switch (response.nType) {
                        case 0:
                            /* case 0 is an error */
                            if (typeof response.cHints !== 'undefined') {
                                var hints = '';
                                for (var i = 0; i < response.cHints.length; i++) {
                                    hints = hints + response.cHints[i] + "\n";
                                }
                                $button.closest('.lpa-button-wrapper').find('.lpa-pay-button-express-feedback').html(hints);
                            }
                            onErrorCallback('AmazonPay: Adding to basket failed.');
                            return;
                        case 1:
                        case 2:
                            /* both cases are a success */
                            $.ajax({
                                url: buttonElement.getAttribute('data-shop-url') + buttonElement.getAttribute('data-io-path'),
                                data: 'io={"name":"lpaGetEstimatedOrderAmount","params":[]}',
                                dataType: 'json',
                                method: 'POST'
                            }).done(function (data) {
                                if (typeof data !== "undefined" && data.result === 'success' && data.estimatedOrderAmount) {
                                    if(data.estimatedOrderAmount.amount && data.estimatedOrderAmount.currencyCode) {
                                        // Result might also be empty if the currency is not supported or the estimation could not be done - this is not an error, but we should not update the estimated order amount in this case.
                                        for (var i = 0; i < window.lpaPayButtons.length; i++) {
                                            if (window.lpaPayButtons[i] && typeof window.lpaPayButtons[i].updateButtonInfo === 'function') {
                                                window.lpaPayButtons[i].updateButtonInfo(data.estimatedOrderAmount);
                                            }
                                        }
                                    }
                                    onSuccessCallback();
                                } else {
                                    console.log('AmazonPay: Failed to get estimated order amount: ', data);
                                    onErrorCallback(data);
                                }
                            });
                            return;
                    }
                }
                /* we should not have gotten here */
                onErrorCallback(response);
                return;
            });
        } catch (err) {
            onErrorCallback(err);
            return;
        }
    };
    var initAdditionalPayButtonRedirect = function(buttonId) {
        // Note that we decouple rendering and checkout initialization to automatically trigger the redirection.
        var buttonElement = document.getElementById(buttonId);
        if (typeof window.amazon === 'undefined' || window.amazon === null) {
            console.log('Amazon Pay: Not completely initialized. Skipping button rendering.');
            return;
        }
        if (null === buttonElement || typeof buttonElement === 'undefined') {
            console.log('Amazon Pay: Warning: Amazon Pay additional pay button element with ID "' + buttonId + '" not found.');
            return;
        }
        if (buttonElement.classList.contains('lpa-initialized')) {
            /* This button was initialized before */
            return;
        }
        var additionalPayButtonConfiguration = {
            // set checkout environment
            merchantId: buttonElement.getAttribute('data-merchant-id'),
            ledgerCurrency: buttonElement.getAttribute('data-ledger-currency'),
            checkoutLanguage: buttonElement.getAttribute('data-language'),
            productType: buttonElement.getAttribute('data-product-type'),
            buttonColor: buttonElement.getAttribute('data-color'),
            placement: buttonElement.getAttribute('data-placement'),
            sandbox: (buttonElement.getAttribute('data-sandbox') === 'true')
        };
        var estimatedOrderAmountAmount = buttonElement.getAttribute('data-estimated-order-amount-amount');
        var estimatedOrderAmountCurrency = buttonElement.getAttribute('data-estimated-order-amount-currency');
        if(estimatedOrderAmountAmount && estimatedOrderAmountCurrency) {
            additionalPayButtonConfiguration['estimatedOrderAmount'] = {
                amount: estimatedOrderAmountAmount,
                currencyCode: estimatedOrderAmountCurrency
            }
        }
        var additionalPayButton = window.amazon.Pay.renderButton('#' + buttonId, additionalPayButtonConfiguration);
        window.initAmazonPayCheckoutRedirect = function() {
            additionalPayButton.initCheckout({
                createCheckoutSessionConfig: {
                    payloadJSON: buttonElement.getAttribute('data-payload'),
                    signature: buttonElement.getAttribute('data-signature'),
                    publicKeyId: buttonElement.getAttribute('data-publickeyid')
                }
            });
        }
        // Automatically init the checkout.
        window.initAmazonPayCheckoutRedirect();
        buttonElement.classList.add('lpa-initialized');
    };
    var initButtons = function () {

        // We need to keep track of rendered pay buttons so we can update their info if needed.
        window.lpaPayButtons = window.lpaPayButtons || [];
        window.lpaPaySubscriptionButtons = window.lpaPaySubscriptionButtons || [];

        /* Look for uninitialized pay buttons */
        var payButtonContainers = document.getElementsByClassName('lpa-button-pay-container');
        for (var i = 0; i < payButtonContainers.length; i++) {
            if (payButtonContainers[i].classList.contains('lpa-initialized')) {
                continue;
            }
            initPayButton(payButtonContainers[i].id);
        }
        var loginButtonContainers = document.getElementsByClassName('lpa-button-login-container');
        for (var j = 0; j < loginButtonContainers.length; j++) {
            if (loginButtonContainers[j].classList.contains('lpa-initialized')) {
                continue;
            }
            initLoginButton(loginButtonContainers[j].id);
        }
        var additionalPayButtonContainers = document.getElementsByClassName('lpa-button-apb-redirect-container');
        for (var k = 0; k < additionalPayButtonContainers.length; k++) {
            if (additionalPayButtonContainers[k].classList.contains('lpa-initialized')) {
                continue;
            }
            initAdditionalPayButtonRedirect(additionalPayButtonContainers[k].id);
        }
    };
    var registerInitAjaxLoadedButtons = function () {
        if (typeof $ === "undefined") {
            setTimeout(registerInitAjaxLoadedButtons, 50);
            return;
        }
        $(document).ajaxSuccess(function (event, xhr, options) {
            if (options.url && (options.url.includes('?isAjax') || options.url.includes('&isAjax'))) {
                /*
                 * This implies loaded DOM content that may contain uninitialized amazon pay buttons.
                 * Initialize buttons after a short timeout to allow other scripts to finish inserting the DOM.
                 */
                window.setTimeout(initButtons, 100);
            }
        });
    };
    var triggerFunctionOn = function (func, trigger) {
        window.lpaJqAsync = window.lpaJqAsync || [];
        lpaJqAsync.push([trigger, func]);
    };

    /* End of function definitions, define proxies for methods that may have to be deferred */
    var initLoginButtonProxy = function () {
        if (window.lpaOnAmazonLoginReadyFired) {
            return initLoginButton;
        } else {
            return function (buttonId) {
                triggerFunctionOn(function () {
                    initLoginButton(buttonId);
                }, 'loginReady');
            };
        }
    };
    var initPayButtonProxy = function () {
        if (window.lpaOnAmazonPayReadyFired) {
            return initPayButton;
        } else {
            return function (buttonId) {
                triggerFunctionOn(function () {
                    initPayButton(buttonId);
                }, 'payReady');
            };
        }
    };

    var initButtonsProxy = function () {
        if (window.lpaOnAmazonPayReadyFired) {
            return initButtons;
        } else {
            return function () {
                triggerFunctionOn(function () {
                    initButtons();
                }, 'payReady');
            };
        }
    };

    var registerDropdownResizeFallback = function() {
        if (typeof $ === "undefined") {
            setTimeout(registerDropdownResizeFallback, 50);
            return;
        }
        $('.dropdown').each(function(index, element) {
            var $this = $(element);
            if($this.find('.lpa-button').length) {
                $this.on('shown.bs.dropdown', function() {
                    // This resize event will trigger recalculation of button sizes for amazon pay
                    window.dispatchEvent(new Event('resize'));
                });
            }
        });
    };


    return {
        initLoginButton: initLoginButtonProxy(),
        initPayButton: initPayButtonProxy(),
        initButtons: initButtonsProxy(),
        registerInitAjaxLoadedButtons: registerInitAjaxLoadedButtons,
        registerDropdownResizeFallback: registerDropdownResizeFallback
    }

};

/* Init buttons rendered in the DOM */
window.lpa().initButtons();
/* Register on jQuery ajaxSuccess to initialize buttons loaded via ajax - the method will call itself again until jQuery exists */
window.lpa().registerInitAjaxLoadedButtons();
/* Register fallback logic for buttons within dropdowns - they might be rendered wrong before the dropdown is displayed and Amazon Pay only rerenders them on window resize - the method will call itself again until jQuery exists */
window.lpa().registerDropdownResizeFallback();



