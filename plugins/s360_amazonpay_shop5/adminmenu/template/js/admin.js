jQuery(document).ready(function () {

    /* copy to clipboard function */
    jQuery('.s360-copy-to-clipboard').click(function (e) {
        e.preventDefault();
        var $that = jQuery(this).closest('.s360-copy-to-clipboard');
        var $target = jQuery($that.data('target'));
        if ($target.length) {
            $target.select();
            document.execCommand('copy');
        }
    });

    /* show video container */
    jQuery('.s360-video').click(function (e) {
        e.preventDefault();
        var $that = jQuery(this).closest('.s360-video');
        var $iframe = $that.find('iframe:not(.initialized)');
        if ($iframe.length) {
            $iframe.attr('src', $iframe.attr('data-src')).addClass('initialized');
        }
        $that.find('.s360-video-container').slideToggle();
    });

    /* react on check mws data */
    jQuery('#check-access-button').click(function (e) {
        e.preventDefault();
        window.lpaAdmin.checkAccess();
    });

    /* react on create key */
    jQuery('#create-key-button').click(function (e) {
        e.preventDefault();
        window.lpaAdmin.createKey();
    });

    /* react on setting default template values via button */
    jQuery('.lpa-pq-defaults').click(function (e) {
        e.preventDefault();
        var template = $(this).closest('.lpa-pq-defaults').data('template');
        window.lpaAdmin.setTemplateDefaults(template);
    });

    /* react to changing of the mode from sandbox to production */
    jQuery('[name="environment"]').on('change', () => {
        if(jQuery('[name="environment"]').val() === 'production' && jQuery('[name="hiddenButtonMode"]').prop('checked')) {
            jQuery.confirm({
                title: window.lpaLang['changeHiddenButtonModeTitle'],
                content: '<p>' + window.lpaLang['changeHiddenButtonModeDescription'] + '</p>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['changeHiddenButtonModeCancel']
                    },
                    formSubmit: {
                        text: window.lpaLang['changeHiddenButtonModeConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            jQuery('[name="hiddenButtonMode"]').prop('checked', false);
                        }
                    }
                }
            });
        }
    });

    // Start self-check with short delay to let other AJAX calls (order loading) process first.
    window.setTimeout(window.lpaAdmin.performSelfCheck, 200);

    /* Fail-safe handler when the Ajax request fails. */
    window.setTimeout(() => {
        if(jQuery('.lpa-admin-self-check-loading:visible').length) {
            jQuery('.lpa-admin-self-check-loading').hide();
            jQuery('.lpa-admin-self-check-loading-failed').show();
        }
    }, 30000);
});

(function ($) {
    var lpaAdmin = function (options) {
        return {
            checkAccess: function () {
                // This function checks if the currently entered data (not necessarily saved!) is valid for the MWS access.
                var $feedback = $('#check-access-feedback');
                var $form = $('#lpa-account-settings-form');
                $feedback.hide();
                $feedback.html('');

                var errorHtml = '';

                if (errorHtml) {
                    $feedback.html(errorHtml);
                    $feedback.show();
                    return;
                }

                /*
                 * The user entered potentially valid data. Start the check.
                 */
                $feedback.removeClass('success');
                $feedback.removeClass('error');
                $feedback.html(window.lpaLang['pleaseWait']);
                $feedback.show();
                $form.css('cursor', 'wait');

                var ajaxURL = window.lpaAdminAjaxUrl;

                var request = $.ajax({
                    url: ajaxURL + '&action=checkAccess',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        csrf: window.JTL_TOKEN
                    }
                });
                request.done(function (data) {
                    if (data.result === 'success') {
                        if (!$feedback.hasClass('success')) {
                            $feedback.addClass('success');
                        }
                        $feedback.html(window.lpaLang['checkSuccess']);
                    } else {
                        if (!$feedback.hasClass('error')) {
                            $feedback.addClass('error');
                        }
                        $feedback.html(window.lpaLang['checkFail']);
                        if (data.messages && data.messages.length) {
                            data.messages.forEach(function (value) {
                                $feedback.append('<br/>' + value);
                            });
                        }
                    }
                });
                request.fail(function (jqXHR, textStatus, errorThrown) {
                    console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
                    if (!$feedback.hasClass('failure')) {
                        $feedback.addClass('failure');
                    }
                    $feedback.html(window.lpaLang['technicalError']);
                });
                request.always(function () {
                    $form.css({'cursor': 'default'});
                });
            },
            createKey: function () {
                var $feedback = $('#create-key-feedback');
                var $publicKeyContainer = $('#create-key-publickey-container');
                $feedback.removeClass('success');
                $feedback.removeClass('error');
                $feedback.html(window.lpaLang['pleaseWait']);
                $feedback.show();

                var ajaxURL = window.lpaAdminAjaxUrl;

                var request = $.ajax({
                    url: ajaxURL + '&action=createKey',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        csrf: window.JTL_TOKEN
                    }
                });
                request.done(function (data) {
                    if (data.result === 'success' && data.data.publickey) {
                        if (!$feedback.hasClass('success')) {
                            $feedback.addClass('success');
                        }
                        $feedback.html(window.lpaLang['keyGenSuccess']);
                        $publicKeyContainer.html('<pre>' + data.data.publickey + '</pre>');
                    } else {
                        if (!$feedback.hasClass('error')) {
                            $feedback.addClass('error');
                        }
                        $feedback.html(window.lpaLang['keyGenFailure']);
                        if (data.messages && data.messages.length) {
                            data.messages.forEach(function (value) {
                                $feedback.append('<br/>' + value);
                            });
                        }
                    }
                });
                request.fail(function (jqXHR, textStatus, errorThrown) {
                    console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
                    if (!$feedback.hasClass('failure')) {
                        $feedback.addClass('failure');
                    }
                    $feedback.html(window.lpaLang['technicalError']);
                });
            },
            renderPreviewButton: function (merchantId, id, type, height, color) {
                // empty container before rendering
                var $element = $('#' + id);
                if (!$element.length) {
                    return;
                }
                // we need to do this, because amazon tries to add a shadow root which cannot be done twice
                var $parent = $element.parent();
                $parent.children().remove();
                $element = $('<div/>');
                $element.attr('id', id).appendTo($parent);
                amazon.Pay.renderButton('#' + id, {
                    merchantId: merchantId,
                    // Note: any callbacks are missing in these preview buttons - they do not work when clicked!
                    sandbox: true, // dev environment
                    ledgerCurrency: 'EUR', // Amazon Pay account ledger currency
                    checkoutLanguage: 'de_DE', // render language
                    productType: type === 'PwA' ? 'PayAndShip' : 'SignIn', // checkout type
                    buttonColor: color, // color of the button
                    placement: 'Cart' // button placement
                });
                $element.css({
                    height: height + 'px'
                });
            },
            setTemplateDefaults: function (template) {
                var templateConfigs = {
                    nova: {
                        loginSelector: '#quick-login, #login_form, fieldset.quick_login',
                        loginMethod: 'append',
                        loginHeight: '45',
                        paySelector: '.cart-summary .card > .card-body, .cart-dropdown-buttons',
                        payMethod: 'append',
                        payDetailSelector: '#add-to-cart',
                        payDetailMethod: 'append',
                        payCategorySelector: '#buy_form_#kArtikel# .productbox-actions',
                        payCategoryMethod: 'append',
                        payHeight: '60',
                        payDetailHeight: '60',
                        payCategoryHeight: '45',
                        loginCssColumns: 'col-12',
                        payCssColumns: 'col-12',
                        payDetailCssColumns: 'col-12 offset-md-6 col-md-6',
                        payCategoryCssColumns: 'col-12',
                    },
                    evo: {
                        loginSelector: '#quick-login, #login_form, fieldset.quick_login',
                        loginMethod: 'append',
                        loginHeight: '45',
                        paySelector: '.basket-well .proceed',
                        payMethod: 'append',
                        payDetailSelector: '#add-to-cart',
                        payDetailMethod: 'append',
                        payCategorySelector: '#result-wrapper_buy_form_#kArtikel# .expandable',
                        payCategoryMethod: 'append',
                        payHeight: '60',
                        payDetailHeight: '60',
                        payCategoryHeight: '45',
                        loginCssColumns: 'col-12',
                        payCssColumns: 'col-12',
                        payDetailCssColumns: 'col-12 offset-md-6 col-md-6',
                        payCategoryCssColumns: 'col-12',
                    },
                    easytemplate360: {
                        loginSelector: '#amapay-login, .amapay-login, #amapay-login-dropdown, .amapay-login-dropdown',
                        loginMethod: 'append',
                        loginHeight: '45',
                        paySelector: '#amapay-basket, .amapay-basket, #amapay-basket-dropdown, .amapay-basket-dropdown, #amapay-checkout-button-only, .amapay-checkout-button-only',
                        payMethod: 'append',
                        payDetailSelector: '#add-to-cart .basket-form-inline',
                        payDetailMethod: 'append',
                        payCategorySelector: '#buy_form_#kArtikel#',
                        payCategoryMethod: 'after',
                        payHeight: '60',
                        payDetailHeight: '60',
                        payCategoryHeight: '45',
                        loginCssColumns: 'col-12',
                        payCssColumns: 'col-12',
                        payDetailCssColumns: 'col-12',
                        payCategoryCssColumns: 'col-12',
                    },
                    hypnos: {
                        loginSelector: '#form-login',
                        loginMethod: 'after',
                        loginHeight: '45',
                        paySelector: '#paymentbuttons',
                        payMethod: 'append',
                        payDetailSelector: '#paymentbuttons',
                        payDetailMethod: 'append',
                        payCategorySelector: '.paymentbuttons_#kArtikel#',
                        payCategoryMethod: 'append',
                        payHeight: '60',
                        payDetailHeight: '45',
                        payCategoryHeight: '45',
                        loginCssColumns: 'col-12',
                        payCssColumns: 'col-12',
                        payDetailCssColumns: 'col-12',
                        payCategoryCssColumns: 'col-12',
                    },
                    snackys: {
                        loginSelector: '#login-popup .form, #login_form, #existing-customer .add-pays .amazon',
                        loginMethod: 'append',
                        loginHeight: '45',
                        paySelector: '.c-dp .add-pays .amazon, .cart-sum .add-pays .amazon',
                        payMethod: 'append',
                        payDetailSelector: '.buy-col .add-pays .amazon',
                        payDetailMethod: 'append',
                        payCategorySelector: '#result-wrapper_buy_form_#kArtikel# .exp',
                        payCategoryMethod: 'append',
                        payHeight: '60',
                        payDetailHeight: '45',
                        payCategoryHeight: '45',
                        loginCssColumns: 'col-12',
                        payCssColumns: 'col-12',
                        payDetailCssColumns: 'col-12',
                        payCategoryCssColumns: 'col-12',
                    }
                };
                var config = templateConfigs[template];
                $('#lpa-config-button-login-pq-selector').val(config['loginSelector']).trigger('change');
                $('#lpa-config-button-login-pq-method').val(config['loginMethod']).trigger('change');
                $('#lpa-config-button-login-css-columns').val(config['loginCssColumns']).trigger('change');
                $('#lpa-config-button-login-height').val(config['loginHeight']).trigger('change');
                $('#lpa-config-button-pay-pq-selector').val(config['paySelector']).trigger('change');
                $('#lpa-config-button-pay-pq-method').val(config['payMethod']).trigger('change');
                $('#lpa-config-button-pay-css-columns').val(config['payCssColumns']).trigger('change');
                $('#lpa-config-button-pay-height').val(config['payHeight']).trigger('change');
                $('#lpa-config-button-pay-detail-pq-selector').val(config['payDetailSelector']).trigger('change');
                $('#lpa-config-button-pay-detail-pq-method').val(config['payDetailMethod']).trigger('change');
                $('#lpa-config-button-pay-detail-css-columns').val(config['payDetailCssColumns']).trigger('change');
                $('#lpa-config-button-pay-detail-height').val(config['payDetailHeight']).trigger('change');
                $('#lpa-config-button-pay-category-pq-selector').val(config['payCategorySelector']).trigger('change');
                $('#lpa-config-button-pay-category-pq-method').val(config['payCategoryMethod']).trigger('change');
                $('#lpa-config-button-pay-category-css-columns').val(config['payCategoryCssColumns']).trigger('change');
                $('#lpa-config-button-pay-category-height').val(config['payCategoryHeight']).trigger('change');
            },
            reset: function (element) {
                var $this = $(element);
                var $container = $this.closest('.card');
                $container.find('[data-default]').each(function () {
                    var $element = $(this);
                    var defaultValue = $element.attr('data-default');
                    var nodeName = $element.prop('nodeName').toLowerCase();
                    var type = $element.attr('type');
                    if (nodeName === 'input') {
                        switch (type) {
                            case 'radio':
                            case 'checkbox':
                                if (defaultValue === '1') {
                                    $element.prop('checked', true);
                                } else {
                                    $element.prop('checked', false);
                                }
                                break;
                            default:
                                $element.val(defaultValue);
                                break;
                        }
                    } else if (nodeName === 'select' || nodeName === 'textbox') {
                        $element.val(defaultValue);
                    }
                    $element.trigger('input');
                    $element.trigger('change');
                });
            },
            performSelfCheck: function() {
                var ajaxURL = window.lpaAdminAjaxUrl;

                var request = $.ajax({
                    url: ajaxURL + '&action=performSelfCheck',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        csrf: window.JTL_TOKEN
                    }
                });
                request.done(function (data) {
                    if (data.result === 'success' && data.html) {
                        $('.lpa-admin-self-check-placeholder').empty().html(data.html);
                    } else {
                        $('.lpa-admin-self-check-loading').hide();
                        $('.lpa-admin-self-check-loading-failed').show();
                    }
                });
                request.fail(function (jqXHR, textStatus, errorThrown) {
                    console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
                    $('.lpa-admin-self-check-loading').hide();
                    $('.lpa-admin-self-check-loading-failed').show();
                });
            }
        }
    };

    window.lpaAdmin = lpaAdmin();
})(jQuery);