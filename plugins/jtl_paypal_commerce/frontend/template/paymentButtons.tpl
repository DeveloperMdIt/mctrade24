<script>
    if (typeof(window.PPCcomponentInitializations) === 'undefined') {
        window.PPCcomponentInitializations = [];
    }

    (function() {
        let methodID              = {$ppcPaymentMethodID};
        let ppcModuleID           = '{$ppcModuleID}';
        let ppcOption             = '{$ppcFundingSource}';
        let paymentFundsMapping   = {$ppcFundingMethodsMapping};
        let ppcSingleFunding      = {if $ppcSingleFunding}true{else}false{/if};
        let ppcPaymentResetTitle  = ppcSingleFunding ? '{lang section="checkout" key="modifyPaymentOption" addslashes=true}' : '';
        let activePaymentMethod   = {if is_int($AktiveZahlungsart)}{$AktiveZahlungsart}{else}'{$AktiveZahlungsart}'{/if};
        let initInProgress        = false;
        let paymentFeeName        = '{if $zahlungsart->cGebuehrname|has_trans}{$zahlungsart->cGebuehrname|trans}{/if}';
        let paymentPriceLocalized = '{$zahlungsart->cPreisLocalized}';
        let $nextForm             = $('.checkout-shipping-form');

        const resetButton = ppcPaymentResetTitle !== ''
            ? `<div class="custom-control custom-button custom-control-inline">
                    <button class="btn btn-outline-secondary btn-block" name="resetPayment" value="1"
                    type="submit">${ ppcPaymentResetTitle }</button>
               <\/div>`
            : '';

        const paymentButtonsOptionTemplate = fundingSource => {
            let paymentOptionId    = 'za_ppc_' + fundingSource;
            let fundingSourceTitle = paymentFundsMapping[fundingSource].title;

            return `{$ppcOptionsTemplate}`;
        }

        window.PPCcomponentInitializations.push(initShippingSelectionButtons);

        $(document).ready(function() {
            $(window).trigger('ppc:componentInit',[initShippingSelectionButtons, true]);
        });
        function clearAPMFields(fundingSource, msg, err)
        {
            $('button[type="submit"]', $nextForm).prop('disabled', false);
            $.evo.extended().stopSpinner();
            $.evo.extended().smoothScrollToAnchor('#za_ppc_' + fundingSource);
            if (err) {
                $.evo.extended().showNotify({ title: $('#za_ppc_' + fundingSource + '_name').text(), text: msg });
            } else {
                $('.form-error-msg', '#za_ppc_' + fundingSource + '-fields').remove();
                $('#za_ppc_' + fundingSource + '-fields').append('<div class="form-error-msg w-100">' +  msg + '</div>');
            }
        }
        function initShippingSelectionButtons(ppc_jtl)
        {
            if (initInProgress) {
                return;
            }
            initInProgress = true;
            let initCallback = function () {
                let ppcPaymentContainer = $('#' + ppcModuleID);

                if (typeof ppc_jtl !== 'undefined') {
                    let ppcInserted = false;
                    let fundingSources;

                    try {
                        fundingSources = ppc_jtl.getFundingSources();
                    } catch (err) {
                        fundingSources = [];
                        $('.checkout-payment-method').removeClass('d-none');
                    }

                    let eligibleOptionIds = [],
                        getSortNumber     = (fs) => {
                            return typeof paymentFundsMapping[fs] !== 'undefined' ? paymentFundsMapping[fs].sort : '0';
                        },
                        numberCompare = (a, b) => {
                            let nA = parseInt(a) || Infinity,
                                nB = parseInt(b) || Infinity;

                            if (nA < nB) {
                                return -1;
                            }
                            if (nA > nB) {
                                return +1;
                            }

                            return 0;
                        }
                    fundingSources.sort(function (a, b) {
                        return a === 'paypal' ? -1
                            : (b === 'paypal' ? 1 : numberCompare(getSortNumber(a), getSortNumber(b)));
                    }).forEach(function (fundingSource) {
                        if (typeof fundingSource === 'undefined'
                            || typeof paymentFundsMapping[fundingSource] === 'undefined'
                        ) {
                            return;
                        }
                        let mark = ppc_jtl.Marks({ fundingSource: fundingSource });
                        if (mark.isEligible() && (!ppcSingleFunding || ppcOption === fundingSource) &&
                            !ppcFundingDisabled.includes(fundingSource)
                        ) {
                            ppcInserted = true;
                            let paymentOptionId = 'za_ppc_' + fundingSource;
                            let template        = paymentButtonsOptionTemplate(fundingSource);

                            $template = $(template)
                                .find('.checkout-payment-method').unwrap()
                                .addClass('ppc-checkout-payment-method')
                                .append(resetButton);
                            $template.find('input[name="Zahlungsart"]').attr('checked', false);
                            ppcPaymentContainer.before($template);
                            $('#payment-' + paymentOptionId + '_input')
                                .val(methodID)
                                .attr('ppc-funding-source', fundingSource)
                                .attr('required', false)
                                .attr('checked', false);
                            if (paymentFundsMapping[fundingSource].picture !== '') {
                                $('img', $template).attr('src', paymentFundsMapping[fundingSource].picture);
                            } else {
                                $('img', $template)
                                    .replaceWith(
                                        '<div id="' + paymentOptionId + '_img" class="align-items-center d-inline-block ppc-option-img"><\/div>' +
                                        '<div id="' + paymentOptionId + '_name" class="funding-name d-inline-block ml-1">' + paymentFundsMapping[fundingSource].title + '<\/div>'
                                    );
                                eligibleOptionIds.push(paymentOptionId);
                                mark.render('#' + paymentOptionId + '_img');
                            }
                            if (paymentFundsMapping[fundingSource].note !== '') {
                                $('.checkout-payment-method-note', $template).html(
                                    '<small>' + paymentFundsMapping[fundingSource].note + '</small>'
                                )
                            } else {
                                $('.checkout-payment-method-note', $template).remove();
                            }
                            if (paymentFundsMapping[fundingSource].fields !== null) {
                                $template.append('<div id="' + paymentOptionId + '-fields" class="custom-control funding-fields-container fade collapse"><div class="card" role="document"><div id="' + paymentOptionId + '-fieldsContainer" class="card-body"></div></div></div>');
                                if (typeof paymentFundsMapping[fundingSource].fields === 'string') {
                                    $('#' + paymentOptionId + '-fieldsContainer').append(paymentFundsMapping[fundingSource].fields);
                                } else {
                                    let fields = {
                                        fundingSource: fundingSource,
                                        onInit: (data, actions) => {
                                            $nextForm.on('submit', function (e) {
                                                if (ppcOption === fundingSource) {
                                                    let $fundingOption = $('#payment-za_ppc_' + fundingSource + '_input');
                                                    if ($fundingOption.data('validation') === 'valid') {
                                                        $fundingOption.data('validation', '');
                                                        $('.form-error-msg', '#za_ppc_' + fundingSource + '-fields').remove();

                                                        return true;
                                                    }

                                                    e.preventDefault();
                                                    if ($fundingOption.data('validation') === 'invalid') {
                                                        $fundingOption.data('validation', '');
                                                        clearAPMFields(
                                                            fundingSource,
                                                            '{lang key='mandatoryFieldNotification' section='errorMessages' addslashes=true}',
                                                            false
                                                        );

                                                        return false;
                                                    }

                                                    $fundingOption.data('validation', 'checking')
                                                    actions.validate().then((valid) => {
                                                        $fundingOption.data('validation', valid ? 'valid' : 'invalid');
                                                    }).catch((err) => {
                                                        $fundingOption.data('validation', 'error');
                                                        clearAPMFields(
                                                            fundingSource,
                                                            '{lang key='unknownError' section='messages' addslashes=true}',
                                                            true
                                                        );
                                                    }).then(() => {
                                                        $nextForm.submit();
                                                    });

                                                    return false;
                                                }
                                            });
                                        },
                                        fields: {
                                        }
                                    }
                                    Object.keys(paymentFundsMapping[fundingSource].fields).forEach((key) => {
                                        fields.fields[key] = { value: paymentFundsMapping[fundingSource].fields[key] };
                                    });
                                    ppc_jtl.PaymentFields(fields).render('#' + paymentOptionId + '-fieldsContainer');
                                }
                            }

                            if (methodID === activePaymentMethod) {
                                ppcOption = ppcOption === '' ? sessionStorage.getItem('chosenPPCPaymentOption') : ppcOption;
                                if (ppcOption !== null && ppcOption !== '') {
                                    $('#payment-za_ppc_' + ppcOption + '_input').prop('checked', true);
                                    $('#za_ppc_' + ppcOption + '-fields').collapse('show');
                                }
                            }
                        }
                    });

                    {* wait for all payment mark images to load, then get the largest width of all *}
                    Promise.all(
                        eligibleOptionIds
                            .map(eligibleOptionId => $('#' + eligibleOptionId + '_img img'))
                            .map(img => new Promise(res => img.on('load', res)))
                    ).then(imgLoadEvents => {
                        let markWidths = imgLoadEvents.map(e => $(e.target).closest('.paypal-mark').outerWidth());
                        let maxWidth   = Math.max(...markWidths);
                        $('.ppc-option-img .paypal-marks').css('width', maxWidth + 'px');
                    });

                    if (ppcInserted) {
                        ppcPaymentContainer.remove();
                        $('.checkout-payment-method').removeClass('d-none');
                    }

                    setTimeout(function () {
                        $('input[type=radio][name=Zahlungsart]').change(function (e) {
                            let attr = $(this).attr('ppc-funding-source');
                            let attrIsSet = typeof (attr) !== 'undefined' && attr !== false;

                            ppcOption = $(this).is(':checked') && attrIsSet ? attr : '';
                            $('.funding-fields-container').collapse('hide')
                            if (e.target.id === 'payment-za_ppc_' + ppcOption + '_input') {
                                $('#za_ppc_' + ppcOption + '-fields').collapse('show');
                                $('#payment-za_ppc_' + ppcOption + '_input').data('validation', '');
                            }
                        });
                    });
                    $('#fieldset-payment .jtl-spinner').fadeOut(300,
                        function () {
                            $(this).remove();
                            $('[data-toggle="tooltip"]').tooltip();
                        }
                    );
                } else {
                    $('.checkout-payment-method').removeClass('d-none');
                }
                $nextForm.on('submit', function (e) {
                    if (parseInt($('input[name="Zahlungsart"]:checked', $(this)).val()) === methodID) {
                        sessionStorage.setItem('chosenPPCPaymentOption', ppcOption);
                        $('#ppc-funding-source_input').val(ppcOption);
                    } else {
                        $('#ppc-funding-source_input').val('');
                    }
                });
            }

            window.setTimeout(initCallback, 100);
        }
    })();
</script>
