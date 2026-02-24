/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

(function () {
    'use strict';

    if (!$.evo) {
        $.evo = {};
    }

    const getIconSvg = window.admProTemplate.getIconSvg;

    var l = window.admorris_pro_template_settings.iconFamily.chevronLeft;
    var r = window.admorris_pro_template_settings.iconFamily.chevronRight;
    var chevronLeftIsSingleColor = l !== "fluent" || l !== "officeXS"
    var chevronRightIsSingleColor = r !== "fluent" || r !== "officeXS"
    var sliderArrows = {
        left: '<button class="slick-prev ' + (chevronLeftIsSingleColor && 'slick-prev__custom-icon') + '" aria-label="Previous" type="button"><span class="icon-content icon-content--large">' + getIconSvg('chevronLeft') + '</span></button>',
        right: '<button class="slick-next ' + (chevronRightIsSingleColor && 'slick-next__custom-icon') + '" aria-label="Next" type="button"><span class="icon-content icon-content--large">' + getIconSvg('chevronRight') + '</span></button>',
        up: '<button class="slick-up" aria-label="Previous" type="button"><span class="icon-content icon-content--default">' + getIconSvg('chevronUp') + '</span></button>',
        down: '<button class="slick-down" aria-label="Next" type="button"><span class="icon-content icon-content--default">' + getIconSvg('chevronDown') + '</span></button>',
    }

    var EvoClass = function () { };

    EvoClass.prototype = {
        options: {
            sliderArrows: sliderArrows
        },

        constructor: EvoClass,

        currentSpinner: null,

        generateSlickSlider: function () {
            const observer = new window.IntersectionObserver(entries => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        let mainNode = $(entry.target);
                        mainNode.removeClass('slick-lazy');
                        if (!mainNode.hasClass('slick-initialized')) {
                            const slickConfig = getSliderConfig(mainNode.data('slick-type'));
                            mainNode.find('.variations select').selectpicker('destroy');
                            mainNode.slick(slickConfig);
                            mainNode.find('[data-toggle="tooltip"]').tooltip();
                            // admorris Pro changes: set tabindex for first image link only in slider
                            mainNode.find('.slick-active a.image-wrapper:first-child').attr({
                                'tabindex': '-1'
                            });
                        }
                    }
                }
            }, {
                rootMargin: '400px 0px 400px 0px'
            })

            document.querySelectorAll('.slick-lazy').forEach(function(slickItem) {
                observer.observe(slickItem);
            });


            /**
             * responsive slider (content)
             */

            function getSliderConfig(type) {
                const baseConfig = {
                    mobileFirst: true,
                    arrows: true,
                    swipeToSlide: true,
                    prevArrow: sliderArrows.left,
                    nextArrow: sliderArrows.right,
                    slidesToShow: 1,
                    slidesToScroll: 1
                };
                const configs = {
                    'box': () =>({}),

                    'product': () => {
                        const { sliderItems } = window.admorris_pro_template_settings;


                        const responsiveArr = getBreakpoints(sliderItems);
                        return getResponsiveConfig(responsiveArr)

                    },
                    'news': () => ({
                        responsive: [
                            {
                                breakpoint: 480, // xs
                                settings: {
                                    slidesToShow: 2,
                                    slidesToScroll: 2

                                }
                            },
                            {
                                breakpoint: 768, // sm
                                settings: {
                                    slidesToShow: 3,
                                    slidesToScroll: 3
                                }
                            },
                        ]
                    }),
                    'pushed-success': () => ({
                        responsive: [
                                {
                                breakpoint: 480, // xs
                                settings: {
                                    slidesToShow: 2
                                }
                            },
                            {
                                breakpoint: 1000,
                                settings: {
                                    slidesToShow: 3
                                }
                            },
                        ]
                    }),
                    'slider-half': () => ({
                        arrows:       false,
                        slidesToShow: 2,
                        slidesToScroll: 2,
                        responsive:   [
                            {
                                breakpoint: 992,
                                settings: {
                                    arrows: true,
                                }
                            },
                            {
                                breakpoint: 1300,
                                settings: {
                                    slidesToShow: 3,
                                    slidesToScroll: 3,
                                    arrows: true,
                                }
                            }
                        ]
                    }),

                }


                const conf = (configs[type] || configs['product'])();

                return {...baseConfig, ...conf};

            }

            function getResponsiveConfig(responsiveArr) {

                if (!responsiveArr) {
                    return {}
                }

                const responsive = responsiveArr.map(function (item) {
                    return {
                        breakpoint: item.breakpoint, // xs
                        settings: {
                            slidesToShow: item.num,
                            slidesToScroll: item.num
                        }
                    };
                });
                return { responsive };
            }

            function getBreakpoints(maxSlides) {
                const breakpoints = window.productSliderConfig || [
                    {
                        breakpoint: 480,
                        num: 2
                    },
                    {
                        breakpoint: 576,
                        num: 3
                    },
                    {
                        breakpoint: 768,
                        num: 4
                    },
                    {
                        breakpoint: 992,
                        num: 5
                    },
                    {
                        breakpoint: 1200,
                        num: maxSlides
                    }
                ];

                if (maxSlides == 1) {
                    return null;
                } else if (maxSlides < 5) {
                    // Find the index of the first breakpoint that has more or equal slides than maxSlides
                    const breakpointIndex = breakpoints.findIndex(item => item.num >= maxSlides);

                    // If a valid breakpoint is found, slice the array from that point onwards
                    if (breakpointIndex !== -1) {
                        return breakpoints.slice(0, breakpointIndex + 1);
                        // Use slicedBreakpoints as needed
                    }
                }
                return breakpoints;
            }

        },


        productTabsPriceFlow: function() {
            var dateFormat = 'DD.MM.YYYY';
            if ($('html').attr('lang') !== 'de') {
                dateFormat = 'MM/DD/YYYY';
            }
            var chartOptions = {
                responsive:       true,
                scaleBeginAtZero: false,
                aspectRatio:3,
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var label = window.chartDataTooltip;
                            label += Math.round(tooltipItem.yLabel * 100) / 100;
                            label += ' '+window.chartDataCurrency;
                            return label;
                        }
                    }
                },
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {
                            parser: 'DD.MM.YYYY',
                            // round: 'day'
                            tooltipFormat: dateFormat
                        },
                        display: false
                    }],
                }
            };
            if (typeof window.priceHistoryChart !== 'undefined' && window.priceHistoryChart === null) {
                window.priceHistoryChart = new Chart(window.ctx, {
                    type: 'line',
                    data: window.chartData,
                    options: chartOptions
                });
            }
        },


        tooltips: function () {
            $('[data-toggle="tooltip"]').tooltip();
        },


        bootlint: function () {
            (function () {
                var p = window.alert;
                var s = document.createElement('script');
                window.alert = function () {
                    console.info(arguments);
                };
                s.onload = function () {
                    bootlint.showLintReportForCurrentDocument([]);
                    window.alert = p;
                };
                s.src = 'https://maxcdn.bootstrapcdn.com/bootlint/latest/bootlint.min.js';
                document.body.appendChild(s);
            })();
        },

        showNotify: function (options) {
            eModal.alert({
                size: 'lg',
                buttons: false,
                title: options.title,
                message: options.text,
                keyboard: true,
                tabindex: -1,
            })
                /* admorris pro changes because of update of eModal library */
                .then(function (e) {
                    $.evo.generateSlickSlider();
                });
        },

        renderCaptcha: function (parameters) {
            this.trigger('captcha.render', parameters);
        },


        popupDep: function() {
            $('#main-wrapper').on('click', '.popup-dep', function(e) {
                var id    = '#popup' + $(this).attr('id'),
                    title = $(this).attr('title'),
                    html  = $(id).html();
                eModal.alert({
                    message: html,
                    title: title,
                    keyboard: true,
                    buttons: false,
                    tabindex: -1})
                    .then(
                        function () {
                            //the modal just copies all the html.. so we got duplicate IDs which confuses recaptcha
                            var recaptcha = $('.tmp-modal-content .g-recaptcha');
                            if (recaptcha.length === 1) {
                                var siteKey = recaptcha.data('sitekey'),
                                    newRecaptcha = $('<div />');
                                if (typeof  siteKey !== 'undefined') {
                                    //create empty recapcha div, give it a unique id and delete the old one
                                    newRecaptcha.attr('id', 'popup-recaptcha').addClass('g-recaptcha form-group');
                                    recaptcha.replaceWith(newRecaptcha);
                                    grecaptcha.render('popup-recaptcha', {
                                        'sitekey' : siteKey,
                                        'callback' : 'captcha_filled'

                                    });
                                }
                            }
                            addValidationListener();
                            $('.g-recaptcha-response').attr('required', true);
                        }
                    );
                return false;
            });
        },
        
        popover: function () {
            /*
             * <a data-toggle="popover" data-ref="#popover-content123">Click me</a>
             * <div id="popover-content123" class="popover">content here</div>
             */
            $('[data-toggle="popover"]').popover({
                html: true,
                sanitize: false,
                content: function () {
                    var ref = $(this).attr('data-ref');
                    return $(ref).html();
                }
            });
        },

        /** 
         * admorris Pro modified: handle full URLs with hashes and open 
         * tabs and collapse when target is inside 
         * */
        smoothScrollToAnchor: function (href, pushToHistory) {
            // Extract the hash part if href contains a full URL
            let hash = href;
            let isExternalLink = false;
            
            if (href.includes('#') && !href.startsWith('#')) {
                // Check if the URL part (before #) matches current page
                const currentUrlWithoutHash = window.location.href.split('#')[0];
                const hrefWithoutHash = href.split('#')[0];
                
                // If the URL doesn't match current page, it's an external link
                if (hrefWithoutHash && hrefWithoutHash !== currentUrlWithoutHash) {
                    isExternalLink = true;
                }
                
                hash = '#' + href.split('#').pop();
            }
            
            // Don't handle external links with smooth scroll
            if (isExternalLink) {
                return false;
            }
            
            // Skip if hash is empty or just "#"
            if (!hash || hash === '#') {
                return false;
            }
            
            // Extract the ID from the hash
            const targetId = hash.slice(1);
            
            // Skip if ID is empty after removing the #
            if (!targetId) {
                return false;
            }
            
            // Try to find the element by ID
            const target = document.getElementById(targetId);
            
            // If target not found by ID, check if there are named anchors (for legacy support)
            const legacyTarget = !target ? document.getElementsByName(targetId)[0] : null;
            
            // Use whichever target we found, or return if none
            const finalTarget = target || legacyTarget;
            if (!finalTarget) {
                return false;
            }

            // Update browser history if requested
            if (pushToHistory !== false) {
                history.pushState({}, document.title, location.pathname + hash);
            }

            // Check if user prefers reduced motion
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            
            // Check if target is inside an accordion or tab and handle accordingly
            const accordionParent = finalTarget.closest('.accordion-collapse, .collapse');
            const tabPane = finalTarget.closest('.tab-pane');
            
            // If the target itself is a toggle control, we should not handle it
            if (finalTarget.hasAttribute('data-toggle') || 
                finalTarget.hasAttribute('data-bs-toggle') ||
                finalTarget.hasAttribute('aria-controls')) {
                return false;
            }
            
            const focusAndScroll = function() {
                // Focus first (for accessibility and keyboard navigation)
                finalTarget.focus({ preventScroll: true });
                
                // Then scroll (needed because preventScroll is set to true above)
                finalTarget.scrollIntoView({
                    // Only use smooth scrolling if user hasn't requested reduced motion
                    behavior: prefersReducedMotion ? 'auto' : 'smooth',
                    block: 'start'
                });
            };
            
            if (accordionParent) {
                // Handle bootstrap accordion
                const accordionButton = document.querySelector(`[data-bs-target="#${accordionParent.id}"], [data-target="#${accordionParent.id}"], [href="#${accordionParent.id}"]`);
                if (accordionButton && !accordionParent.classList.contains('show')) {
                    // Let Bootstrap handle this - don't scroll ourselves
                    if (accordionButton === finalTarget) {
                        return false;
                    }
                    accordionButton.click();
                }
                setTimeout(focusAndScroll, 350);
            } else if (tabPane) {
                // Handle bootstrap tabs
                const tabId = tabPane.id;
                const tabTrigger = document.querySelector(`[data-bs-toggle="tab"][data-bs-target="#${tabId}"], [data-toggle="tab"][href="#${tabId}"]`);
                
                // Let Bootstrap handle this - don't scroll ourselves
                if (tabTrigger === finalTarget) {
                    return false;
                }
                
                if (tabTrigger && !tabPane.classList.contains('active')) {
                    tabTrigger.click();
                }
                
                // If reduced motion is preferred, reduce or eliminate delay
                const delay = prefersReducedMotion ? 50 : 350;
                setTimeout(focusAndScroll, delay);
            } else if (finalTarget.closest('.accordion, .tab-content')) {
                // Fallback for other accordion/tab implementations
                const delay = prefersReducedMotion ? 50 : 350;
                setTimeout(focusAndScroll, delay);
            } else {
                focusAndScroll();
            }

            return true;
        },

        /* admorris Pro changes: handles full urls with hashes now */
        smoothScroll: function () {
            var that = this;

            // Handle initial hash in URL when page loads
            if (location.hash) {
                this.smoothScrollToAnchor(location.hash, false); // Don't push to history on initial load
            }
            
            // Handle clicks on anchor links
            $(document).on('click', 'a[href*="#"]', function (e) {
                var elem = e.currentTarget;
                var href = elem.getAttribute('href');
                
                // Skip empty hash links (often used for JS actions)
                if (href === '#') {
                    return;
                }
                
                // Skip if this is a Bootstrap component toggle
                // Check for common Bootstrap data attributes used for toggling
                if ($(elem).attr('data-toggle') || $(elem).attr('data-bs-toggle')) {
                    return;
                }
                
                // Skip if link has aria-controls (typically used in accordions and tabs)
                if ($(elem).attr('aria-controls')) {
                    return;
                }
                
                // Skip if link has role="tab" or role="button" (used in Bootstrap components)
                if ($(elem).attr('role') === 'tab' || $(elem).attr('role') === 'button') {
                    return;
                }
                
                // Skip if link is inside a collapse toggle
                if ($(elem).closest('[data-toggle="collapse"],[data-bs-toggle="collapse"]').length) {
                    return;
                }
                
                // Check if this is a same-page anchor link
                let isSamePage = false;
                
                if (href.startsWith('#')) {
                    // Simple hash link
                    isSamePage = true;
                } else if (href.includes('#')) {
                    // URL with hash - check if it's for current page
                    const currentUrlWithoutHash = window.location.href.split('#')[0];
                    const hrefWithoutHash = href.split('#')[0];
                    
                    // Compare URLs without trailing slash to handle both /page and /page/
                    isSamePage = (hrefWithoutHash.replace(/\/$/, '') === currentUrlWithoutHash.replace(/\/$/, ''));
                }
                
                if (isSamePage) {
                    e.preventDefault();
                    that.smoothScrollToAnchor(href, true);
                }
            });
        },

        // admorris Pro changes: disabled script because it also works well without it
        // initSkipToScroll: function() {
        //     // set focus on skip-to links
        //     let links = $('.btn-skip-to');
        //     var that = this;

        //     links.on('click', function(e) {
        //         let url    = new URL(e.target.href);
        //         let target = url.hash;

        //         that.smoothScrollToAnchor(target, false);
        //         $(target).focus();
        //     });
        // },

        preventDropdownToggle: function () {
            $('a.dropdown-toggle').click(function (e) {
                var elem = e.currentTarget; /* admorris: replaced e.target here */
                var viewport = $('body').data('viewport');
                if (viewport !== 'xs' && viewport !== 'sm' && elem.getAttribute('aria-expanded') == 'true' && elem.getAttribute('href') != '#') {
                    window.location.href = elem.getAttribute('href');
                    e.preventDefault();
                }
            });
        },

        checkout: function () {
            // show only the first submit button (i.g. the button from payment plugin)
            var $submits = $('#checkout-shipping-payment')
                .closest('form')
                .find('input[type="submit"]');
            $submits.addClass('hidden');
            $submits.first().removeClass('hidden');

            $('input[name="Versandart"]', '#checkout-shipping-payment').on('change', function () {
                var id = parseInt($(this).val());
                var shipmentid = parseInt($(this).val());
                var paymentid = $('input[id^=\'payment\']:checked ').val();
                var $form = $(this).closest('form');

                if (isNaN(shipmentid)) {
                    return;
                }

                $form.find('fieldset, input[type="submit"]')
                    .attr('disabled', true);

                var url = 'bestellvorgang.php?kVersandart=' + shipmentid + '&kZahlungsart=' + paymentid;
                $.evo.loadContent(url, function () {
                    $.evo.checkout();
                }, null, true);
            });

            $('#country').on('change', function (e) {
                var val = $(this).find(':selected').val();

                $.evo.io().call('checkDeliveryCountry', [val], {}, function (error, data) {
                    var $shippingSwitch = $('#checkout_register_shipping_address');

                    if (data.response) {
                        $shippingSwitch.removeAttr('disabled');
                        $shippingSwitch.parent().removeClass('hidden');
                    } else {
                        $shippingSwitch.attr('disabled', true);
                        $shippingSwitch.parent().addClass('hidden');
                        if ($shippingSwitch.prop('checked')) {
                            $shippingSwitch.prop('checked', false);
                            $('#select_shipping_address').collapse('show');
                        }
                    }
                });
            });
        },

        setCompareListHeight: function () {
            var h = parseInt($('.comparelist .equal-height').outerHeight());
            $('.comparelist .equal-height').outerHeight(h);
        },

        setWishlistVisibilitySwitches: function() {
            $('.wl-visibility-switch').on('change', function () {
                $.evo.io().call(
                    'setWishlistVisibility',
                    [$(this).data('wl-id'), $(this).is(":checked"), $('.jtl_token').val()],
                    $(this),
                    function(error, data) {
                    if (error) {
                        return;
                    }
                    var $wlPrivate    = $('span[data-switch-label-state="private-' + data.response.wlID + '"]'),
                        $wlPublic     = $('span[data-switch-label-state="public-' + data.response.wlID + '"]'),
                        $wlURLWrapper = $('#wishlist-url-wrapper'),
                        $wlURL        = $('#wishlist-url');
                    if (data.response.state) {
                        $wlPrivate.addClass('d-none');
                        $wlPublic.removeClass('d-none');
                        $wlURLWrapper.removeClass('d-none');
                        $wlURL.val($wlURL.data('static-route') + data.response.url)
                    } else {
                        $wlPrivate.removeClass('d-none');
                        $wlPublic.addClass('d-none');
                        $wlURLWrapper.addClass('d-none');
                    }
                });
            });
        },

        setDeliveryAdressDefaultSwitches: function() {
            $('.la-default-switch').on('change', function () {
                let input = $(this);
                $('.la-default-switch').each(function() {
                    if (input.attr('id') !== $(this).attr('id')) {
                        $(this).prop('checked', false);
                    } else {
                        $(this).prop('checked', true);
                    }
                });
                $.evo.io().call(
                    'setDeliveryaddressDefault',
                    [$(this).data('la-id'), $('.jtl_token').val()],
                    $(this), function(error, data) {
                        if (error) {
                            return;
                        }
                        if (!data.response.result) {
                            input.prop('checked', false);
                        }
                    }
                );
            });
        },

        initEModals: function () {
            $('.author-modal').on('click', function (e) {
                e.preventDefault();
                let modalID = $(this).data('target');
                eModal.alert({
                    title: $(modalID).attr('title'),
                    message: $(modalID).html(),
                    buttons: false
                });
            });
        },

        loadContent: function (url, callback, error, animation, wrapper) {
            var that = this;
            var $wrapper = (typeof wrapper === 'undefined' || wrapper.length === 0) ? $('#result-wrapper') : $(wrapper);
            var ajaxOptions = { data: 'isAjax' };
            if (animation) {
                $wrapper.addClass('loading');
            }

            
            
            that.trigger('load.evo.content', { url: url });

            $.ajax(url, ajaxOptions).done(function (html) {
                var $data = $(html);
                if (animation) {
                    $data.addClass('loading');
                }
                // Save the current focused element ID before loading content
                that.saveFocusedElementID();
                $wrapper.replaceWith($data);
                $wrapper = $data;
                if (typeof callback === 'function') {
                    callback();
                }
            })
                .fail(function () {
                    if (typeof error === 'function') {
                        error();
                    }
                })
                .always(function () {
                    $wrapper.removeClass('loading');
                    that.trigger('contentLoaded'); // compatibility
                    that.trigger('loaded.evo.content', { url: url });
                    
                    // Restore focus to the last focused element if possible
                    that.restoreFocusedElement();
                });
        },

        // Set a cookie with expiration date
        setCookie: function(name, value, days) {
            var expires = '';
            if (days) {
                var date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            document.cookie = name + '=' + encodeURIComponent(value) + expires + '; path=/';
        },
        
        // Get a cookie by name
        getCookie: function(name) {
            var nameEQ = name + '=';
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1, c.length);
                }
                if (c.indexOf(nameEQ) === 0) {
                    return decodeURIComponent(c.substring(nameEQ.length, c.length));
                }
            }
            return null;
        },
        
        // Delete a cookie by name
        deleteCookie: function(name) {
            this.setCookie(name, '', -1);
        },
        
        // Save the focused element data to a cookie with 1-day expiration
        saveFocusedElementID: function() {
            try {
                var focusedElement = document.activeElement;
                if (!focusedElement || focusedElement === document.body) return;

                var elementData = {
                    elementID: focusedElement.id || '',
                    tagName: focusedElement.tagName || '',
                    name: focusedElement.name || '',
                    href: focusedElement.href || '',
                    value: focusedElement.value || ''
                };

                // Capture nearest product link href to scope restores on listings
                var prodAncestor = focusedElement.closest('[data-product-id], .productbox-inner, .product, .product-list-item__title');
                if (prodAncestor) {
                    var prodLink = prodAncestor.querySelector('a[href]');
                    if (prodLink && prodLink.href) elementData.containerHref = prodLink.href;
                }

                // Capture nearest form id; create one when absent so we can reliably scope
                var formAncestor = focusedElement.closest('form');
                if (formAncestor) {
                    if (!formAncestor.id) {
                        try { formAncestor.id = 'evo-form-' + Math.random().toString(36).substr(2, 9); } catch (e) { /* ignore */ }
                    }
                    if (formAncestor.id) elementData.containerFormId = formAncestor.id;
                }

                // If the focused element is a combobox trigger, prefer the underlying select
                try {
                    var isComboboxTrigger = focusedElement.getAttribute && (focusedElement.getAttribute('role') === 'combobox' || /bs-placeholder|dropdown-toggle/.test(focusedElement.className || ''));
                    if (isComboboxTrigger) {
                        var nearbySelect = focusedElement.previousElementSibling && focusedElement.previousElementSibling.tagName === 'SELECT' ? focusedElement.previousElementSibling : null;
                        if (!nearbySelect) nearbySelect = focusedElement.nextElementSibling && focusedElement.nextElementSibling.tagName === 'SELECT' ? focusedElement.nextElementSibling : null;
                        if (!nearbySelect && formAncestor) nearbySelect = formAncestor.querySelector('select[name^="eigenschaftwert"], select');
                        if (nearbySelect) {
                            if (nearbySelect.id) elementData.originalSelectId = nearbySelect.id;
                            if (nearbySelect.name) elementData.originalSelectName = nearbySelect.name;
                        }
                    }
                } catch (e) { /* ignore */ }

                // Only persist useful identifying information
                if (elementData.elementID || elementData.name || elementData.href || elementData.originalSelectId || elementData.originalSelectName || elementData.containerFormId || elementData.containerHref) {
                    this.setCookie('lastFocusedElement', JSON.stringify(elementData), 1);
                }
            } catch (e) {
                console.warn('Could not save focused element', e);
            }
        },
        
        // Get the CSS path of an element for better identification
        getElementPath: function(element) {
            if (!element) return '';
            
            // Max path length to prevent very deep paths
            const MAX_PATH_LENGTH = 5;
            
            let path = [];
            let currentElement = element;
            let pathLength = 0;
            
            while (currentElement && currentElement !== document.body && pathLength < MAX_PATH_LENGTH) {
                let selector = currentElement.tagName.toLowerCase();
                
                if (currentElement.id) {
                    selector += '#' + currentElement.id;
                } else if (currentElement.className) {
                    const classes = currentElement.className.split(/\s+/).filter(c => c);
                    if (classes.length) {
                        selector += '.' + classes.join('.');
                    }
                }
                
                // Add position among siblings for more precise selection
                const siblings = Array.from(currentElement.parentNode?.children || [])
                    .filter(node => node.tagName === currentElement.tagName);
                if (siblings.length > 1) {
                    const index = siblings.indexOf(currentElement);
                    if (index > -1) {
                        selector += ':nth-of-type(' + (index + 1) + ')';
                    }
                }
                
                path.unshift(selector);
                currentElement = currentElement.parentNode;
                pathLength++;
            }
            
            return path.join(' > ');
        },
        
        // Restore focus to the last focused element (simplified)
        restoreFocusedElement: function() {
            try {
                var storedData = this.getCookie('lastFocusedElement');
                if (!storedData) return;
                var data = JSON.parse(storedData);

                var element = null;

                // 1) If we have a container form id, try to scope lookup inside that form first
                if (data.containerFormId) {
                    try {
                        var formNow = document.getElementById(data.containerFormId);
                        if (formNow) {
                            // prefer originalSelectId/name (most specific for combobox widgets)
                            if (data.originalSelectId) {
                                try {
                                    var selId = (window.CSS && CSS.escape) ? ('#' + CSS.escape(data.originalSelectId)) : ('#' + data.originalSelectId);
                                    element = formNow.querySelector(selId);
                                } catch (e) { element = formNow.querySelector('#' + data.originalSelectId); }
                            }
                            if (!element && data.originalSelectName) {
                                element = formNow.querySelector('select[name="' + data.originalSelectName + '"]');
                            }
                            // then try elementID or name
                            if (!element && data.elementID) element = formNow.querySelector('#' + (data.elementID));
                            if (!element && data.name) element = formNow.querySelector((data.tagName || '*') + '[name="' + data.name + '"]');
                        }
                    } catch (e) { /* ignore */ }
                }

                // 2) If not found and containerHref is present, scope to that product block
                if (!element && data.containerHref) {
                    try {
                        var prodLinkNow = document.querySelector('a[href="' + data.containerHref + '"]');
                        if (prodLinkNow) {
                            var prodRoot = prodLinkNow.closest('[data-product-id], .productbox-inner, .product, li, div');
                            if (prodRoot) {
                                if (data.originalSelectId) element = prodRoot.querySelector('#' + data.originalSelectId);
                                if (!element && data.originalSelectName) element = prodRoot.querySelector('select[name="' + data.originalSelectName + '"]');
                                if (!element && data.elementID) element = prodRoot.querySelector('#' + data.elementID);
                            }
                        }
                    } catch (e) { /* ignore */ }
                }

                // 3) Global fallbacks
                if (!element && data.elementID) element = document.getElementById(data.elementID);
                if (!element && data.originalSelectName) element = document.querySelector('select[name="' + data.originalSelectName + '"]');
                if (!element && data.name) element = document.querySelector((data.tagName || '*') + '[name="' + data.name + '"]');
                if (!element && data.href) element = document.querySelector((data.tagName || 'a') + '[href="' + data.href + '"]') || document.querySelector('a[href="' + data.href + '"]');

                if (element) {
                    setTimeout(function() {
                        try {
                            if (typeof element.focus === 'function') {
                                try { element.focus({ preventScroll: true }); } catch (err) { element.focus(); }
                            }
                            if ((element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') && data.value && element.value !== data.value) {
                                element.value = data.value;
                            }
                        } catch (e) { /* ignore */ }
                    }, 100);
                }
            } catch (e) {
                console.warn('Could not restore focused element', e);
            }
        },

        spinner: function (target) {
            var opts = {
                lines: 12             // The number of lines to draw
                , length: 7             // The length of each line
                , width: 5              // The line thickness
                , radius: 10            // The radius of the inner circle
                , scale: 2.0            // Scales overall size of the spinner
                , corners: 1            // Roundness (0..1)
                , color: '#000'         // #rgb or #rrggbb
                , opacity: 1 / 4          // Opacity of the lines
                , rotate: 0             // Rotation offset
                , direction: 1          // 1: clockwise, -1: counterclockwise
                , speed: 1              // Rounds per second
                , trail: 100            // Afterglow percentage
                , fps: 20               // Frames per second when using setTimeout()
                , zIndex: 2e9           // Use a high z-index by default
                , className: 'spinner'  // CSS class to assign to the element
                , top: '50%'            // center vertically
                , left: '50%'           // center horizontally
                , shadow: false         // Whether to render a shadow
                , hwaccel: false        // Whether to use hardware acceleration (might be buggy)
                , position: 'absolute'  // Element positioning
            };

            if (typeof target === 'undefined') {
                target = document.getElementsByClassName('product-offer')[0];
            }
            if ((typeof target !== 'undefined' && target.id === 'result-wrapper') || $(target).hasClass('product-offer')) {
                opts.position = 'fixed';
            }
            /* admorris custom: lazy load spin.js */
            return new Promise(function(resolve) {
                loadjs.ready('spin.js', function() {
                    resolve(new Spinner(opts).spin(target));
                });

            })
        },

        startSpinner: function (target) {
            target = target || $('body');
            if ($('.jtl-spinner').length === 0) {
                target.append('<div class="jtl-spinner"><span class="icon-content icon-content--center icon-animated--spin" style="--size: 1"><svg><use href="#icon-spinner"></use></svg></span></div>');
            }
        },

        stopSpinner: function () {
            $('.jtl-spinner').remove();
        },

        initPriceSlider: function ($wrapper, redirect) {
            let priceRange      = $wrapper.find('[data-id="js-price-range"]').val(),
                priceRangeID    = $wrapper.find('[data-id="js-price-range-id"]').val(),
                priceRangeMin   = 0,
                priceRangeMax   = $wrapper.find('[data-id="js-price-range-max"]').val(),
                currentPriceMin = priceRangeMin,
                currentPriceMax = priceRangeMax,
                $priceRangeFrom = $("#" + priceRangeID + "-from"),
                $priceRangeTo = $("#" + priceRangeID + "-to"),
                $priceSlider = document.getElementById(priceRangeID);

            if($priceSlider === null) {
                return;
            }

            if (priceRange) {
                let priceRangeMinMax = priceRange.split('_');
                currentPriceMin = priceRangeMinMax[0];
                currentPriceMax = priceRangeMinMax[1];
                $priceRangeFrom.val(currentPriceMin);
                $priceRangeTo.val(currentPriceMax);
            }
            noUiSlider.create($priceSlider, {
                start: [parseInt(currentPriceMin), parseInt(currentPriceMax)],
                connect: true,
                range: {
                    'min': parseInt(priceRangeMin),
                    'max': parseInt(priceRangeMax)
                },
                step: 1,
                format: {
                    to: function (value) {
                        return parseInt(value);
                    },
                    from: function (value) {
                        return parseInt(value);
                    }
                }
            });
            $priceSlider.noUiSlider.on('change', function (values, handle) {
                setTimeout(function(){
                    $.evo.redirectToNewPriceRange(values[0] + '_' + values[1], redirect, $wrapper);
                },0);
            });
            $priceSlider.noUiSlider.on('update', function (values, handle) {
                $priceRangeFrom.val(values[0]);
                $priceRangeTo.val(values[1]);
            });
            $('.price-range-input').on('change', function () {
                let prFrom = parseInt($priceRangeFrom.val()),
                    prTo = parseInt($priceRangeTo.val());
                $.evo.redirectToNewPriceRange(
                    (prFrom > 0 ? prFrom : priceRangeMin) + '_' + (prTo > 0 ? prTo : priceRangeMax),
                    redirect,
                    $wrapper
                );
            });
        },

        initFilters: function (href) {
            let $wrapper = $('.js-collapse-filter');
            $.evo.extended().startSpinner($wrapper);

            $.ajax(href, {data: {'useMobileFilters':1}})
                .done(function(data) {
                    $wrapper.html(data);
                    $.evo.initPriceSlider($wrapper, false);
                    $.evo.initItemSearch('filter');
                })
                .always(function() {
                    $.evo.extended().stopSpinner();
                });
        },

        initFilterEvents: function() {
            let initiallized = false;
            $('#js-filters').on('click', function() {
                if (!initiallized) {
                    $.evo.initFilters(window.location.href);
                    initiallized = true;
                }
            });
        },

        redirectToNewPriceRange: function (priceRange, redirect, $wrapper) {
            let currentURL  = window.location.href;
            if (!redirect) {
                currentURL  = $wrapper.find('[data-id="js-price-range-url"]').val();
            }
            let redirectURL = $.evo.updateURLParameter(
                currentURL,
                'pf',
                priceRange
            );
            if (redirect) {
                window.location.href = redirectURL;
            } else {
                $.evo.initFilters(redirectURL);
            }
        },

        updateURLParameter: function (url, param, paramVal) {
            let newAdditionalURL = '',
                tempArray        = url.split('?'),
                baseURL          = tempArray[0],
                additionalURL    = tempArray[1],
                temp             = '';
            if (additionalURL) {
                tempArray = additionalURL.split('&');
                for (let i=0; i<tempArray.length; i++){
                    if(tempArray[i].split('=')[0] != param){
                        newAdditionalURL += temp + tempArray[i];
                        temp = '&';
                    }
                }
            }

            return baseURL + '?' + newAdditionalURL + temp + param + '=' + paramVal;
        },

        updateReviewHelpful: function(item) {
            let formData = $.evo.io().getFormValues('reviews-list');
            formData[item.prop('name')] = '';
            formData['reviewID'] = item.data('review-id');

            $.evo.io().call(
                'updateReviewHelpful',
                [formData],
                $(this) , function(error, data) {
                    if (error) {
                        return;
                    }
                    let review = data.response.review;

                    $('[data-review-id="' + review.kBewertung + '"]').removeClass('on-list');
                    item.addClass('on-list');
                    $('[data-review-count-id="hilfreich_' + review.kBewertung + '"]').html(review.nHilfreich);
                    $('[data-review-count-id="nichthilfreich_' + review.kBewertung + '"]').html(review.nNichtHilfreich);
                });
        },

        initReviewHelpful: function() {
            $('.js-helpful').on('click', function (e) {
                e.preventDefault();
                $.evo.extended().updateReviewHelpful($(this));
            });
        },

        addInactivityCheck: function(wrapper, timeoutMS = 500, stopEnter = false) {
            var timeoutID,
                that = this,
                currentBox;

            setup();

            function setup() {
                $(wrapper + ' .form-counter input, ' + wrapper + ' .choose_quantity input').on('change',resetTimer);
                $(wrapper + ' .form-counter .btn-decrement, ' + wrapper + ' .form-counter .btn-increment')
                    .on('click keydown',resetTimer)
                    .on('touchstart',resetTimer,{passive: true});
                if (stopEnter) {
                    $(wrapper + ' input.quantity').on('keypress', function (e) {
                        if (e.key === 'Enter') {
                            return false;
                        } else {
                            resetTimer(e);
                        }
                    });
                }
            }

            function startTimer() {
                timeoutID = window.setTimeout(goInactive, timeoutMS);
            }

            function resetTimer(e) {
                if (wrapper === '#wl-items-form') {
                    currentBox = $(e.target).closest('.productbox-inner');
                }
                if (timeoutID == undefined) {
                    startTimer();
                }
                window.clearTimeout(timeoutID);

                startTimer();
            }

            function goInactive() {
                if (wrapper === '#cart-form') {
                    $(wrapper).submit();
                } else if (wrapper === '#wl-items-form') {
                    that.updateWishlistItem(currentBox);
                }
            }
        },

        updateWishlistItem: function($wrapper) {
            let formID   = 'wl-items-form';
            $.evo.extended().startSpinner($wrapper);
            $.evo.io().call(
                'updateWishlistItem',
                [
                    parseInt($('#' + formID + ' input[name="kWunschliste"]').val()),
                    $.evo.io().getFormValues(formID)
                ],
                $(this) , function(error, data) {
                    $.evo.extended().stopSpinner();
                    $wrapper.removeClass('loading');
                });
        },

        initWishlist: function() {
            let wlFormID = '#wl-items-form';
            if ($(wlFormID).length) {
                $.evo.extended().addInactivityCheck(wlFormID, 300, true);
                $('.js-update-wl').on('change', function () {
                    $.evo.extended().updateWishlistItem($(this).closest('.productbox-inner'));
                });
                $(window).on('resize', function () {
                    setWishlistItemheights();
                });
                setWishlistItemheights();
            }
            function setWishlistItemheights() {
                $('.product-list').children().each(function() {
                    $(this).css('height', window.innerWidth > globals.breakpoints.xl ? $(this).height() : 'unset');
                });
            }
        },

        initPaginationEvents: function() {
            $('.pagination-wrapper select').on('change', function () {
                this.form.submit();
            });
        },

        trigger: function (event, args) {
            $(document).trigger('evo:' + event, args);
            return this;
        },

        error: function () {
            if (console && console.error) {
                console.error(arguments);
            }
        },

        /**
         * $.evo.extended() is deprecated, please use $.evo instead
         */
        extended: function () {
            return $.evo;
        },

        register: function () {
            // this.addSliderTouchSupport();
            this.productTabsPriceFlow();
            this.generateSlickSlider();
            this.tooltips();
            this.renderCaptcha();
            this.popupDep();
            this.popover();
            this.preventDropdownToggle();
            // wait until the header height is calculated
            $(window).one('headerHeightChange', this.smoothScroll.bind(this));
            this.checkout();
            if ($('body').data('page') == 3) {
                this.addInactivityCheck('#cart-form');
            }
            this.initFilterEvents();
            this.setCompareListHeight();
            this.setWishlistVisibilitySwitches();
            this.initWishlist();
            this.initPaginationEvents();
            this.setDeliveryAdressDefaultSwitches();
            this.initEModals();
            // this.initSkipToScroll();

            setTimeout(function() {
                if (!loadjs.isDefined('spin.js')) {
                    loadjs(admorris_pro_template_settings.templateDir + 'js/spin.min.js', 'spin.js');
                }
            }, 1000);
        }
    };

    $(function () {
        $.evo.register();
    });

    // $(window).on('resize', function () {
    //     $.evo.autoheight();
    // });

    // PLUGIN DEFINITION
    // =================
    $.evo = new EvoClass();
})(jQuery);
