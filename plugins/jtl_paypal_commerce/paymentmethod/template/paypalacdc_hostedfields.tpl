{*
    This template renders the hosted fields for acdc when the ACDC payment option is selected
    rendered from frontend\ACDCFrontend.php::renderShippingPage
*}

<script>
    (function() {
        let acdcCard            = null,
            acdcPaymentSelected = $('#{$acdcModuleId} input[type=radio][name=Zahlungsart]').attr('checked'),
            acdcBNCode          = '{$acdcBNCode}',
            acdcRendered        = false,
            acdcCardFields      = null,
            acdcPaymentId       = {$acdcPaymentId},
            acdcSCAMode         = '{$acdcSCAMode}',
            $nextForm           = $('.checkout-shipping-form');

        if (typeof(window.PPCcomponentInitializations) === 'undefined') {
            window.PPCcomponentInitializations = [];
        } else if (wrapperLoaded) {
            initACDC(ppcJtl);
        }

        window.PPCcomponentInitializations.push(function(ppc_jtl) {
            initACDC(ppc_jtl);
        });

        function initACDC(ppc_jtl)
        {
            $('#fieldset-payment').on('change', 'input[name="Zahlungsart"]', function (e) {
                if (e.target.id === 'payment' + acdcPaymentId) {
                    selectACDC(ppc_jtl);
                    if (acdcCard) {
                        $('#acdc_card').collapse('show');
                    }
                } else {
                    $('#acdc_card').collapse('hide');
                }
            });

            if (acdcPaymentSelected) {
                selectACDC(ppc_jtl);
            }
        }

        function selectACDC(ppc_jtl)
        {
            if (acdcCard === null && ppc_jtl.HostedFields.isEligible() === true) {
                acdcCard = renderAcdcCard(ppc_jtl);
            }
        }

        function createCCIcons(possiblecards)
        {
            for(let c = 0; c < possiblecards.length; c++){
                let type     = possiblecards[c].type;
                let niceType = possiblecards[c].niceType;
                $("#ppc-cc-icon").append(
                    "<img id='icon_" + type +
                    "' alt='" + niceType +
                    "' title='" + niceType +
                    "' src='{$acdcImagePath}/" + type + ".png'" +
                    " class='col col-auto ppc-cc-icon ppc-cc-icon_disabled' width='70px'>"
                );
            }
        }

        function resetCCIcons()
        {
            $(".ppc-cc-icon").addClass("ppc-cc-icon_disabled");
        }

        function validateAcdcFields(acdcInstance)
        {
            let fields  = acdcInstance.getState().fields;
            let isValid = true;
            let formGroups = {
                number: $('#card-number').closest('.form-group'),
                cvv: $('#cvv').closest('.form-group'),
                expirationDate: $('#expiration-date').closest('.form-group'),
            };
            for (let field in formGroups) {
                if(!validateAcdcField(acdcInstance, fields, field)) {
                    isValid = false;
                }
            }
            return isValid;
        }

        function validateAcdcField(acdcInstance, fields, emittedBy)
        {
            let formGroups = {
                number: $('#card-number').closest('.form-group'),
                cvv: $('#cvv').closest('.form-group'),
                expirationDate: $('#expiration-date').closest('.form-group'),
            };
            formGroups[emittedBy].removeClass('has-error').find('.form-error-msg').remove();
            if (!fields[emittedBy].isValid) {
                let message = '{addslashes($msg_acdc_invalid_input)}';
                if (fields[emittedBy].isEmpty) {
                    message = '{lang key='fillOut' section='global' addslashes=true}';
                } else if (fields[emittedBy].isPotentiallyValid) {
                    message = '{addslashes($msg_acdc_potentially_valid)}';
                }
                formGroups[emittedBy]
                    .addClass('has-error')
                    .append('<div class="form-error-msg w-100">' +  message + '</div>');
                $.evo.extended().smoothScrollToAnchor('#{$acdcModuleId}');

                return false;
            }
            return true;
        }

        function clearAcdcCard($nextButton, reset)
        {
            $nextButton.prop('disabled', false);
            $.evo.extended().stopSpinner();
            if (reset) {
                acdcCardFields.teardown();
                acdcCard = renderAcdcCard(ppcJtl);
            }
        }

        function submitAcdcCard(event)
        {
            if (parseInt($('input[name="Zahlungsart"]:checked', $(this)).val()) !== acdcPaymentId) {
                return;
            }
            event.preventDefault();
            let $nextButton = $('button[type="submit"]', $nextForm);
            $nextButton.prop('disabled', true);
            $.evo.extended().startSpinner();
            if (validateAcdcFields(acdcCardFields)) {
                let submitData = {
                    cardholderName: document.getElementById("card-holder-name").value,
                    billingAddress: {
                        streetAddress: $("#card-billing-address-street").val(),
                        extendedAddress: $("#card-billing-address-unit").val(),
                        region: $("#card-billing-address-state").val(),
                        locality: $("#card-billing-address-city").val(),
                        postalCode: $("#card-billing-address-zip").val(),
                        countryCodeAlpha2: $("#card-billing-address-country").val(),
                    },
                };
                if (acdcSCAMode !== 'N') {
                    submitData.contingencies = [acdcSCAMode];
                }
                acdcCardFields.submit(submitData)
                    .then(() => {
                        $nextForm.unbind('submit', submitAcdcCard);
                        $nextForm.submit();
                    })
                    .catch((err) => {
                        clearAcdcCard($nextButton, true);
                        $.evo.extended().showNotify({
                            title: '{addslashes($acdcMethodName)}', text: err.message ? err.message : JSON.stringify(err)
                        });
                    });
            } else {
                clearAcdcCard($nextButton);
            }
        }

        function renderAcdcCard(ppc_jtl)
        {
            let acdcCard = $('#acdc_card');
            $('#{$acdcModuleId}').append(acdcCard);
            acdcCard.removeClass('d-none');

            ppc_jtl.HostedFields.render({
                createOrder: async function () {
                    return await new Promise((resolve, reject) => {
                        let formData = $nextForm.serializeObject();
                        ppcpIOManagedCall(
                            'jtl_paypal_commerce.createOrder',
                            ['card', 'PayPalACDC', acdcBNCode, formData],
                            { },
                            function (error, res) {
                                if (error) {
                                    reject(res.error);
                                } else if (res.orderId && res.orderId.length > 0) {
                                    resolve(res.orderId);
                                } else if (res.createResult) {
                                    reject({ message:res.createResult });
                                } else {
                                    reject({ message:'{addslashes($acdcGeneralError)}' });
                                }
                            }
                        );
                    });
                },
                styles: {
                    '.valid': {
                        'color': 'green'
                    },
                    '.invalid': {
                        'color': 'red'
                    }
                },
                fields: {
                    number: {
                        selector: "#card-number",
                    },
                    cvv: {
                        selector: "#cvv",
                        placeholder: "123"
                    },
                    expirationDate: {
                        selector: "#expiration-date",
                        placeholder: "MM/YY"
                    }
                }
            }).then(cardFields => {
                acdcCardFields = cardFields;
                if (!acdcRendered) {
                    $nextForm.on('submit', submitAcdcCard);
                    let state = cardFields.getState();
                    createCCIcons(state.cards);
                    acdcRendered = true;
                } else {
                    resetCCIcons();
                }
                cardFields.on('cardTypeChange', function(event) {
                    var cvvLabel, cvvPalceholder;
                    var cvvSecPlaceholder = '123456';

                    if (event.cards.length >= 1) {
                        cvvLabel = "("+event.cards[0].code.name+")";
                        cvvPalceholder = cvvSecPlaceholder.substring(0, event.cards[0].code.size);
                    } else {
                        cvvLabel = '(CVV)';
                        cvvPalceholder = '123';
                    }

                    $('#sec_type').html(cvvLabel);
                    cardFields.setAttribute({
                        field: 'cvv',
                        attribute: 'placeholder',
                        value: cvvPalceholder
                    });

                    $("[id^='icon_']").addClass("ppc-cc-icon_disabled");
                    for(let c = 0 ; c < event.cards.length; c++){
                        if(event.cards[c] !== undefined && !event.fields.number.isEmpty) {
                            $('#icon_' + event.cards[c].type).removeClass("ppc-cc-icon_disabled");
                        }
                    }
                });
                cardFields.on('blur', e => {
                    validateAcdcField(cardFields, e.fields, e.emittedBy);
                });
            });

            return acdcCard;
        }
    })();
</script>

<div id="acdc_card" class="custom-control collapse fade{if $AktiveZahlungsart === $acdcPaymentId} show{/if}">
    <div class="card" role="document">
        <div class="card-header">
            {$hostedFieldsTranslation.acdc_card_header}
        </div>
        <div class="label-slide card-body">
            <div id="ppc-cc-icon" class="row"></div>
            <div class="form-row">
                <div class="col col-12 col-lg-4">
                    <div class="form-group">
                        <div id="card-number" class="card_field form-control"></div>
                        <label for="card-number">{$hostedFieldsTranslation.acdc_card_number}</label>
                    </div>
                </div>
                <div class="col col-6 col-lg-4">
                    <div class="form-group">
                        <div id="expiration-date" class="card_field form-control"></div>
                        <label for="expiration-date">{$hostedFieldsTranslation.acdc_card_date}</label>
                    </div>
                </div>
                <div class="col col-6 col-lg-4">
                    <div class="form-group">
                        <div id="cvv" class="card_field form-control"></div>
                        <label for="cvv">{$hostedFieldsTranslation.acdc_card_security_code} <span id="sec_type"></span></label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <input type="text" id="card-holder-name" name="card-holder-name" autocomplete="off"
                    placeholder="Name on Card" class="form-control"
                    value="{$customer->cVorname} {$customer->cNachname}">
                <label for="card-holder-name">{$hostedFieldsTranslation.acdc_card_holder_name}</label>
            </div>
            {if $acdcShowVaultingEnable === true}
                {include file="$ppcFrontendPath/template/paymentCheckboxVaulting.tpl"}
            {/if}
            {checkbox id="acdc_card_holder_is_shipping_address"
                name="acdc_card_holder_is_shipping_address" value="0" checked=true
                data=["toggle"=>"collapse", "target"=>"#acdc_card_holder_address"]
            }
            {$hostedFieldsTranslation.acdc_card_holder_equals_billing}
            {/checkbox}
            {row style="margin-top: 1em;" class="collapse" id="acdc_card_holder_address"}
                {col cols=12 md=4}
                {$hostedFieldsTranslation.acdc_card_holder_adress}
                {/col}
                {col md=8}
                    <div class="form-group">
                        <input type="text" id="card-billing-address-street" name="card-billing-address-street"
                            autocomplete="off" placeholder="Billing Address" class="form-control"
                             value="{$customer->cStrasse} {$customer->cHausnummer}">
                        <label for="card-billing-address-street">{lang key='street' section='account data'}&nbsp;&amp;&nbsp;{lang key='streetnumber' section='account data'}</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="card-billing-address-unit" name="card-billing-address-unit"
                            autocomplete="off" placeholder="unit" class="form-control"
                             value="{$customer->cAdressZusatz}">
                        <label for="card-billing-address-unit">{lang key='street2' section='account data'}</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="card-billing-address-city" name="card-billing-address-city"
                            autocomplete="off" placeholder="city" class="form-control"
                             value="{$customer->cOrt}">
                        <label for="card-billing-address-city">{lang key='city' section='account data'}</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="card-billing-address-state" name="card-billing-address-state"
                            autocomplete="off" placeholder="state" class="form-control"
                             value="{$customer->cBundesland}">
                        <label for="card-billing-address-state">{lang key='state' section='account data'}</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="card-billing-address-zip" name="card-billing-address-zip" autocomplete="off"
                            placeholder="zip / postal code" class="form-control"
                             value="{$customer->cPLZ}">
                        <label for="card-billing-address-zip">{lang key='plz' section='account data'}</label>
                    </div>
                    <div class="form-group">
                        <input type="text" id="card-billing-address-country" name="card-billing-address-country"
                            autocomplete="off" placeholder="country code" class="form-control"
                             value="{$customer->cLand}">
                        <label for="card-billing-address-country">{lang key='country' section='account data'}</label>
                    </div>
                {/col}
            {/row}
        </div>
    </div>
</div>