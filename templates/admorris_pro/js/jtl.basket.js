/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
(function() {
    'use strict';

    var BasketClass = function(options) {
        this.init(options);
    };

    BasketClass.DEFAULTS = {
        input: {
            id: 'a',
            quantity: 'anzahl'
        },
        selector: {
            list: {
                main: '*[data-toggle="basket-add"]',
                form: 'form.form-basket',
                quantity: 'input.quantity',
                submit: '*[type="submit"]',
                loading: 'io-loading'
            },
            cart: {
                container: '*[data-toggle="basket-items"]'
            }
        }
    };

    BasketClass.prototype = {

        constructor: BasketClass,

        init: function(options) {
            this.options = $.extend({}, BasketClass.DEFAULTS, options);
        },

        addToBasket: function($form, data) {
            var $main = $form;

            if (typeof data === 'undefined') {
                data = $form.serializeObject();
            }

            var productId = parseInt(data[this.options.input.id]);
            var quantity = parseFloat(
                data[this.options.input.quantity]
            );

            if (productId > 0 && quantity > 0) {
                this.pushToBasket($main, productId, quantity, data);
            }
        },

        pushToBasket: function($main, productId, quantity, data) {
            var that = this;

            that.toggleState($main, true);

            $.evo.io().call('pushToBasket', [productId, quantity, data], that, function(error, data) {

                that.toggleState($main, false);

                if (error) {
                    return;
                }

                var response = data.response;

                if (response) {
                    switch (response.nType) {
                        case 0: // error
                            that.error(response);
                            break;
                        case 1: // forwarding
                            that.redirectTo(response);
                            break;
                        case 2: // added to basket
                            that.updateCart(0);
                            that.pushedToBasket(response);
                            break;
                    }
                }
            });
        },

        toggleState: function($main, loading) {
            var cls = this.options.selector.list.loading;
            if (loading) {
                $main.addClass(cls);
            } else {
                $main.removeClass(cls);
            }
        },

        redirectTo: function(response) {
            window.location.href = response.cLocation;
        },

        error: function(response) {
            var errorlist = '<ul><li>' + response.cHints.join('</li><li>') + '</li></ul>';
            $.evo.extended().showNotify({
                text: errorlist,
                title: response.cLabel
            });
        },

        pushedToBasket: function(response) {
            $.evo.extended().showNotify({
                text: response.cPopup,
                title: response.cNotification
            });
        },

        updateCart: function(type) {
            var that = this,
                t = parseInt(type);

            if (type === undefined) {
                t=0;
            }

            $.evo.io().call('getBasketItems', [t], this, function(error, data) {
                if (error) {
                    return;
                }

                var tpl = data.response.cTemplate;

                // Create a temporary div to parse the HTML string
                var tempDiv = document.createElement('div');
                tempDiv.innerHTML = tpl;
                
                let badge = tempDiv.querySelector('.shopnav__badge');
                let price = tempDiv.querySelector('.shopping-cart-label');
                let cartDropdown = tempDiv.querySelector('.cart-dropdown');

                let currentMiniCart = document.querySelector(that.options.selector.cart.container);

                if (currentMiniCart.querySelector('.shopnav__badge') === null) {
                    document.querySelector('#cart-dropdown-button').append(badge);
                } else {
                    currentMiniCart.querySelector('.shopnav__badge').innerHTML = badge.innerHTML;
                }
                if (currentMiniCart.querySelector('.shopping-cart-label') === null) {
                    document.querySelector('#cart-dropdown-button').append(price);
                } else {
                    currentMiniCart.querySelector('.shopping-cart-label').innerHTML = price.innerHTML;
                }

                currentMiniCart.querySelector('.cart-dropdown').innerHTML = cartDropdown.innerHTML;

                // $(that.options.selector.cart.container)
                //     .empty()
                //     .append(tpl);
                $(document).trigger('admorrispro:minicartUpdated');
                
            });
        }
    };

    // PLUGIN DEFINITION
    // =================

    $.evo.basket = function() {
        return new BasketClass();
    };

    // PLUGIN DATA-API
    // ===============
    $('#main-wrapper').on('submit', '[data-toggle="basket-add"]', function(event) {
        /* custom */
        if ($(event.currentTarget).data('submitWishlist')) {
            $(event.currentTarget).data('submitWishlist', false);
            return;
        }
        /* /custom */
        event.preventDefault();
        var $form = $(this);
        var data  = $form.serializeObject();
        if (data.VariKindArtikel) {
            data.a = data.VariKindArtikel;
        }
        $.evo.basket().addToBasket($form, data);
    })


    /* preventing the wishlist button to trigger the addToBasket function 
     * added only for admorris pro
     */

    $('[data-toggle="basket-add"]').on('click', 'button.wishlist', function(event) {
        $(event.delegateTarget).data('submitWishlist', true);
    });

})(jQuery);