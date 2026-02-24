/**
 * Subscription Management functions.
 * Note: This depends on jquery-confirm to be present.
 */
(function ($) {
    var lpaSubscriptionManagement = function () {

        var options = {
            pageLimit: 100
        };

        var lastAction = null;
        var currentPageIndex = 0;

        var init = function () {
            loadSubscriptions();
            // set maxPageIndex
        };
        var loadSubscriptions = function () {
            lastAction = 'loadSubscriptions';
            var offset = Math.floor(currentPageIndex * options.pageLimit);
            // load orders, update view
            var params = {
                offset: offset,
                limit: options.pageLimit
            };
            doAjaxCall(
                'loadSubscriptions',
                params,
                function (data) {
                    if (data.subscriptions && data.subscriptions.length) {
                        renderSubscriptions(data.subscriptions);
                        updateCurrentPageIndicator();
                    } else if (data.subscriptions && !data.subscriptions.length && currentPageIndex > 0) {
                        // end reached, go back. (Check for > 0 is needed to avoid endless loop for globally zero orders)
                        prevPage();
                    }
                }
            );
        };
        var nextPage = function () {
            currentPageIndex = currentPageIndex + 1;
            loadSubscriptions();
        };
        var prevPage = function () {
            currentPageIndex = Math.max(currentPageIndex - 1, 0);
            loadSubscriptions();
        };
        var firstPage = function () {
            currentPageIndex = 0;
            loadSubscriptions();
        };
        var getSubscriptionDetail = function(subscriptionId) {
            $('.lpa-subscription-item-detail[data-subscription-id="'+subscriptionId+'"]').toggle();
        };
        var cancelSubscription = function(subscriptionId, orderNumber) {
            var params = {
                subscriptionId: subscriptionId
            };
            $.confirm({
                title: window.lpaLang['cancelSubscriptionTitle'],
                content: '<p>' + window.lpaLang['cancelSubscriptionDescription'].replace('%subscriptionId', sanitize(subscriptionId)).replace('%orderNumber', sanitize(orderNumber)) + '</p>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['cancelSubscriptionConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            doAjaxCall(
                                'cancelSubscription',
                                params,
                                function (data) {
                                    loadSubscriptions();
                                }
                            );
                        }
                    }

                },
                onContentReady: function () {
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        };

        var pauseSubscription = function(subscriptionId) {
            var params = {
                subscriptionId: subscriptionId
            };
            $.confirm({
                title: window.lpaLang['pauseSubscriptionTitle'],
                content: '<p>' + window.lpaLang['pauseSubscriptionDescription'].replace('%subscriptionId', sanitize(subscriptionId)) + '</p>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['pauseSubscriptionConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            doAjaxCall(
                                'pauseSubscription',
                                params,
                                function (data) {
                                    loadSubscriptions();
                                }
                            );
                        }
                    }

                },
                onContentReady: function () {
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        };


        var resumeSubscription = function(subscriptionId) {
            var params = {
                subscriptionId: subscriptionId
            };
            $.confirm({
                title: window.lpaLang['resumeSubscriptionTitle'],
                content: '<p>' + window.lpaLang['resumeSubscriptionDescription'].replace('%subscriptionId', sanitize(subscriptionId)) + '<br/><br/><label><input type="checkbox" name="resume_subscription_create_now" value="on"> ' + window.lpaLang['resumeSubscriptionCreateNow'] + '</label></p>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['resumeSubscriptionConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            params['createNewOrderNow'] = this.$content.find('input[name="resume_subscription_create_now"]').is(':checked') ? 'Y' : 'N';
                            doAjaxCall(
                                'resumeSubscription',
                                params,
                                function (data) {
                                    loadSubscriptions();
                                }
                            );
                        }
                    }

                },
                onContentReady: function () {
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        };

        var createOrderForSubscription = function(subscriptionId) {
            var params = {
                subscriptionId: subscriptionId
            };
            $.confirm({
                title: window.lpaLang['createOrderForSubscriptionTitle'],
                content: '<p>' + window.lpaLang['createOrderForSubscriptionDescription'].replace('%subscriptionId', sanitize(subscriptionId)) + '</p>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['createOrderForSubscriptionConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            doAjaxCall(
                                'createOrderForSubscription',
                                params,
                                function (data) {
                                    loadSubscriptions();
                                }
                            );
                        }
                    }

                },
                onContentReady: function () {
                    var jc = this;
                    this.$content.find('form').on('submit', function (e) {
                        // if the user submits the form by pressing enter in the field.
                        e.preventDefault();
                        jc.$$formSubmit.trigger('click'); // reference the button and click it
                    });
                }
            });
        };
        var doAjaxCall = function (action, parameters, successCallback, failureCallback) {
            var $content = $('.lpa-admin-content');
            $content.addClass('lpa-ajax-loading');
            var ajaxURL = window.lpaAdminAjaxUrl;

            if(!parameters.csrf) {
                parameters.csrf = window.JTL_TOKEN;
            }

            var request = $.ajax({
                url: ajaxURL + '&action=' + action,
                method: 'POST',
                dataType: 'json',
                data: parameters
            });
            request.done(function (data) {
                if (data.result === 'success') {
                    if (typeof successCallback === 'function') {
                        successCallback(data);
                    }
                } else {
                    if (typeof failureCallback === 'function') {
                        failureCallback(data);
                    } else if (data.message) {
                        $.alert({
                            title: window.lpaLang['lpaError'],
                            content: data.message
                        });
                    }
                }
            });
            request.fail(function (jqXHR, textStatus, errorThrown) {
                console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
            });
            request.always(function () {
                $content.removeClass('lpa-ajax-loading');
            });
        };
        var updateCurrentPageIndicator = function () {
            $('.lpa-current-page-indicator-subscriptions').text(currentPageIndex + 1);
        };
        var renderSubscriptions = function (subscriptions) {
            var $subscriptionsContainer = $('.lpa-subscriptions');
            $subscriptionsContainer.html('');
            subscriptions.forEach(function (subscription) {
                $subscriptionsContainer.append(subscription.html);
            });
        };
        var sanitize = function(string) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#x27;',
                "/": '&#x2F;',
                "`": '&grave;',
            };
            const reg = /[&<>"'/]/ig;
            return string.replace(reg, (match)=>(map[match]));
        }

        init();

        return {
            cancelSubscription: cancelSubscription,
            createOrderForSubscription: createOrderForSubscription,
            pauseSubscription: pauseSubscription,
            resumeSubscription: resumeSubscription,
            getSubscriptionDetail: getSubscriptionDetail,
            nextPage: nextPage,
            prevPage: prevPage,
            firstPage: firstPage
        }
    };
    $(document).ready(function () {
        window.lpaSubscriptionManagement = lpaSubscriptionManagement();
    });
})(jQuery);