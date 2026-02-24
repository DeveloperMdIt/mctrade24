/**
 * Order Management functions.
 * Note: This depends on jquery-confirm to be present.
 */
(function ($) {
    var lpaOrderManagement = function () {

        var options = {
            pageLimit: 100
        };

        var lastAction = null;
        var currentPageIndex = 0;
        var currentOrderDetail = '';
        var currentSortBy = 'shopOrderId';
        var currentSortDirection = 'DESC';
        var currentChargePermissionStateFilters = [];
        var currentChargePermissionStateReasonFilters = [];
        var chargePermissionStates = [];
        var chargePermissionStateReasons = [];

        var init = function () {
            loadPossibleStatesAndReasons();
            loadOrders();
        };
        var getChargePermission = function (chargePermissionId) {
            if (currentOrderDetail === chargePermissionId) {
                currentOrderDetail = '';
                $('.lpa-order-item-detail').slideUp();
                return;
            }
            var $container = $('.lpa-order-item-detail[data-charge-permission-id="' + chargePermissionId + '"]');
            if (!$container.length) {
                currentOrderDetail = '';
                return;
            }
            // Loads detail data for the given order reference id
            doAjaxCall('getChargePermission',
                {
                    chargePermissionId: chargePermissionId
                },
                function (data) {
                    if (data.order && data.order.html) {
                        $('.lpa-order-item-detail').slideUp();
                        $container.html(data.order.html);
                        $container.slideDown();
                        currentOrderDetail = chargePermissionId;
                    }
                }
            )
        };
        var refreshChargePermission = function (chargePermissionId) {
            // Refreshes the given order reference id from Amazon Pay, then reloads the current view (i.e. repeats the search or loads orders for the current view)
            doAjaxCall('refreshChargePermission',
                {
                    chargePermissionId: chargePermissionId
                },
                function (data) {
                    currentOrderDetail = chargePermissionId;
                    // success callback - make everything uptodate:
                    if (lastAction === 'search') {
                        search($('[name=searchValue]').val());
                    } else {
                        loadOrders();
                    }
                }
            );
        };
        var search = function (parameter) {
            lastAction = 'search';
            // searches for an order reference with the given order reference id or jtl order number
            if (!parameter || parameter === '') {
                loadOrders();
                return;
            }
            var previousOrderDetail = currentOrderDetail;
            currentOrderDetail = '';
            var params = {searchValue: parameter};
            doAjaxCall(
                'searchOrders',
                params,
                function (data) {
                    if (data.orders && data.orders.length) {
                        renderOrders(data.orders);
                        if (previousOrderDetail !== '') {
                            getChargePermission(previousOrderDetail);
                        }
                    } else {
                        $.alert({title: window.lpaLang['searchFailedTitle'], content: window.lpaLang['searchFailedNoOrderFound'].replace('%parameter', sanitize(parameter))});
                    }
                }
            );
        };
        var cancelChargePermission = function (chargePermissionId) {
            var params = {
                chargePermissionId: chargePermissionId,
                cancelPendingCharges: 'on'
            };
            $.confirm({
                title: window.lpaLang['cancelChargePermissionTitle'],
                content: '<p>' + window.lpaLang['cancelChargePermissionDescription'].replace('%chargePermissionId', sanitize(chargePermissionId)) + '</p>' +
                '<form action="" class="formName">' +
                '<div class="form-group">' +
                '<label>' + window.lpaLang['internalCommentOptional'] + '</label>' +
                '<input type="text" placeholder="' + window.lpaLang['internalCommentOptional'] + '" class="cancelationReason form-control" maxlength="1024"/>' +
                '</div>' +
                '</form>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['cancelChargePermissionConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            var closureReason = this.$content.find('.cancelationReason').val();
                            if (closureReason !== '') {
                                params['closureReason'] = closureReason;
                            }
                            doAjaxCall(
                                /* Note that this is internally the same operation - closeChargePermission - but with a different parameter for cancelPendingCharges! */
                                'closeChargePermission',
                                params,
                                function (data) {
                                    loadOrders();
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
        var closeChargePermission = function (chargePermissionId) {
            var params = {
                chargePermissionId: chargePermissionId,
                cancelPendingCharges: 'off'
            };
            $.confirm({
                title: window.lpaLang['closeChargePermissionTitle'],
                content: '<p>' + window.lpaLang['closeChargePermissionDescription'].replace('%chargePermissionId', sanitize(chargePermissionId)) + '</p>' +
                '<form action="" class="formName">' +
                '<div class="form-group">' +
                '<label>' + window.lpaLang['internalCommentOptional'] + '</label>' +
                '<input type="text" placeholder="' + window.lpaLang['internalCommentOptional'] + '" class="closureReason form-control" maxlength="1024"/>' +
                '</div>' +
                '</form>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['closeChargePermissionConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            var closureReason = this.$content.find('.closureReason').val();
                            if (closureReason !== '') {
                                params['closureReason'] = closureReason;
                            }
                            doAjaxCall(
                                'closeChargePermission',
                                params,
                                function (data) {
                                    loadOrders();
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
        var cancelCharge = function (chargeId) {
            var params = {
                chargeId: chargeId
            };
            $.confirm({
                title: window.lpaLang['cancelChargeTitle'],
                content: '<p>' + window.lpaLang['cancelChargeDescription'].replace('%chargeId', sanitize(chargeId)) + '</p>' +
                '<form action="" class="formName">' +
                '<div class="form-group">' +
                '<label>' + window.lpaLang['internalCommentOptional'] + '</label>' +
                '<input type="text" placeholder="' + window.lpaLang['internalCommentOptional'] + '" class="cancellationReason form-control" maxlength="1024"/>' +
                '</div>' +
                '</form>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['cancelChargeConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            var cancellationReason = this.$content.find('.cancellationReason').val();
                            if (cancellationReason !== '') {
                                params['cancellationReason'] = cancellationReason;
                            }
                            doAjaxCall(
                                'cancelCharge',
                                params,
                                function (data) {
                                    loadOrders();
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
        var createCharge = function (chargePermissionId, amount, currencyCode) {
            var params = {
                chargePermissionId: chargePermissionId,
                chargeAmountAmount: amount,
                chargeAmountCurrencyCode: currencyCode
            };
            $.confirm({
                title: window.lpaLang['createChargeTitle'],
                content: '<p>' + window.lpaLang['createChargeDescription'].replace('%chargePermissionId', sanitize(chargePermissionId)).replace('%amount', sanitize(amount) + ' ' + sanitize(currencyCode)) + '</p>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['createChargeConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            doAjaxCall(
                                'createCharge',
                                params,
                                function (data) {
                                    loadOrders();
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
        var captureCharge = function (chargeId, amount, currencyCode) {
            var params = {
                chargeId: chargeId,
                captureAmountAmount: amount,
                captureAmountCurrencyCode: currencyCode
            };
            $.confirm({
                title: window.lpaLang['captureChargeTitle'],
                content: '<p>' + window.lpaLang['captureChargeDescription'].replace('%chargeId', sanitize(chargeId)).replace('%amount', sanitize(amount) + ' ' + sanitize(currencyCode)) + '</p>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['captureChargeConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            doAjaxCall(
                                'captureCharge',
                                params,
                                function (data) {
                                    loadOrders();
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
        var createRefund = function (chargeId, amount, currencyCode) {
            var params = {
                chargeId: chargeId,
                refundAmountAmount: amount,
                refundAmountCurrencyCode: currencyCode
            };
            $.confirm({
                title: window.lpaLang['createRefundTitle'],
                content: '<p>' + window.lpaLang['createRefundDescription'].replace('%chargeId', sanitize(chargeId)).replace('%amount', sanitize(amount) + ' ' + sanitize(currencyCode)) + '</p>',
                buttons: {
                    cancel: {
                        action: function () {
                        },
                        btnClass: 'btn-red',
                        text: window.lpaLang['noCancelOperation']
                    },
                    formSubmit: {
                        text: window.lpaLang['createRefundConfirm'],
                        btnClass: 'btn-green',
                        action: function () {
                            doAjaxCall(
                                'createRefund',
                                params,
                                function (data) {
                                    loadOrders();
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
        var loadPossibleStatesAndReasons = function() {
            var params = {};
            doAjaxCall(
                'loadPossibleStatesAndReasons',
                params,
                function (data) {
                    chargePermissionStates = data.chargePermissionStates;
                    chargePermissionStateReasons = data.chargePermissionStateReasons;
                    initFilters();
                }
            );
        };
        var initFilters = function() {
            var $chargePermissionStatusOptions = $('[data-filter="chargePermissionStatus"]').find('.lpa-order-filter-options');
            for(var i=0; i < chargePermissionStates.length; i++) {
                var chargePermissionState = chargePermissionStates[i];
                $('<option class="lpa-order-filter-option" value="'+chargePermissionState+'">' + chargePermissionState +'</label>').appendTo($chargePermissionStatusOptions);
            }
            $('#lpa-order-charge-permission-status-select').on('change', function() {
                currentPageIndex = 0;
                currentChargePermissionStateFilters = [this.value];
                loadOrders();
            });

            var $chargePermissionStatusReasonOptions = $('[data-filter="chargePermissionStatusReason"]').find('.lpa-order-filter-options');
            for(var j=0; j < chargePermissionStateReasons.length; j++) {
                var chargePermissionStateReason = chargePermissionStateReasons[j];
                $('<option class="lpa-order-filter-option" value="'+chargePermissionStateReason+'">' + chargePermissionStateReason +'</option>').appendTo($chargePermissionStatusReasonOptions);
            }
            $('#lpa-order-charge-permission-status-reason-select').on('change',function() {
                currentPageIndex = 0;
                currentChargePermissionStateReasonFilters = [this.value];
                loadOrders();
            });
        };
        var loadOrders = function () {
            lastAction = 'loadOrders';
            var previousOrderDetail = currentOrderDetail;
            currentOrderDetail = '';
            var offset = Math.floor(currentPageIndex * options.pageLimit);
            // load orders, update view
            var params = {
                offset: offset,
                limit: options.pageLimit,
                sortBy: currentSortBy,
                sortDirection: currentSortDirection,
                statusFilters: currentChargePermissionStateFilters,
                statusReasonFilters: currentChargePermissionStateReasonFilters
            };
            doAjaxCall(
                'loadOrders',
                params,
                function (data) {
                    if (data.orders && data.orders.length) {
                        renderOrders(data.orders);
                        updateCurrentPageIndicator();
                        updateSortingIndicator();
                    } else if (data.orders && !data.orders.length && currentPageIndex > 0) {
                        // end reached, go back. (Check for > 0 is needed to avoid endless loop for globally zero orders)
                        prevPage();
                    } else if(data.orders && !data.orders.length) {
                        // simply no orders received
                        renderOrders([]);
                    }
                    if (previousOrderDetail !== '') {
                        getChargePermission(previousOrderDetail);
                    }
                }
            );
        };
        var nextPage = function () {
            currentPageIndex = currentPageIndex + 1;
            loadOrders();
        };
        var prevPage = function () {
            currentPageIndex = Math.max(currentPageIndex - 1, 0);
            loadOrders();
        };
        var firstPage = function () {
            currentPageIndex = 0;
            loadOrders();
        };
        var changeSortBy = function(newSortBy) {
            if(newSortBy === currentSortBy) {
                // switch sorting direction
                currentSortDirection = currentSortDirection === 'DESC' ? 'ASC':'DESC';
            } else {
                currentSortBy = newSortBy;
                // when changing sorting logic, also go back to the first page
                currentPageIndex = 0;
            }
            loadOrders();
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
            $('.lpa-current-page-indicator').text(currentPageIndex + 1);
        };
        var updateSortingIndicator = function() {
            $('[data-sort-by]').removeClass('lpa-sorting lpa-sorting-desc lpa-sorting-asc');
            var $currentlySorting = $('[data-sort-by="'+currentSortBy+'"]');
            $currentlySorting.addClass('lpa-sorting');
            $currentlySorting.addClass('lpa-sorting-' + currentSortDirection.toLowerCase());
        };
        var renderOrders = function (orders) {
            var $ordersContainer = $('.lpa-orders');
            $ordersContainer.html('');
            orders.forEach(function (order) {
                $ordersContainer.append(order.html);
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
            getChargePermission: getChargePermission,
            refreshChargePermission: refreshChargePermission,
            search: search,
            cancelChargePermission: cancelChargePermission,
            closeChargePermission: closeChargePermission,
            cancelCharge: cancelCharge,
            createCharge: createCharge,
            captureCharge: captureCharge,
            createRefund: createRefund,
            nextPage: nextPage,
            prevPage: prevPage,
            firstPage: firstPage,
            changeSortBy: changeSortBy
        }
    };
    $(document).ready(function () {
        window.lpaOrderManagement = lpaOrderManagement();
    });
})(jQuery);