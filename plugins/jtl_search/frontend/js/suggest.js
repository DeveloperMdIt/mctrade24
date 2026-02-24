(function ($) {
    $.fn.jtl_search = function (options) {
        var MOVE_DOWN   = 'ArrowDown',
            MOVE_LEFT   = 'ArrowLeft',
            MOVE_RIGHT  = 'ArrowRight',
            MOVE_UP     = 'ArrowUp',
            KEY_ENTER   = 'Enter',
            KEY_CANCEL  = 'Escape',
            KEY_TAB       = 'Tab',
            top         = 0,
            left        = 0,
            cssClass    = options.class || '',
            result,
            _left,
            windowWidth = $(window).width(),
            input       = $(this); // base

        if (input.length === 0) {
            return;
        }
        // result wrapper
        result = $('<div />').addClass('jtl_search_results dropdown-menu ' + cssClass);
        $('body').append(result);

        resize();

        // clear
        input.unbind();
        input.val('');

        // rebind
        input.keyup(function (event) {
            if (event.key == MOVE_LEFT || event.key == MOVE_RIGHT || event.key == MOVE_DOWN || event.key == MOVE_UP || (!event.shiftKey && event.key == KEY_TAB)) {
                event.preventDefault();
            }
            handle(event.key);
        });

        input.keydown(function (event) {
            if (event.key == MOVE_LEFT || event.key == MOVE_RIGHT || event.key == MOVE_DOWN || event.key == MOVE_UP || (!event.shiftKey && event.key == KEY_TAB)) {
                event.preventDefault();
            }
            if (event.shiftKey && event.key == KEY_TAB) {
                hideResults();
            }
        });

        input.blur(function () {
            //hideResults();
        });

        input.focus(function () {
            search();
        });
        $(window).on('resize scroll', function () {
            resize();
        });

        function resize()
        {
            top = input.offset().top + input.outerHeight();
            switch (options.align) {
                default:
                case 'left':
                    _left = input.offset().left;
                    left  = ((_left + result.outerWidth()) > windowWidth) ? 0 : _left - 15;
                    break;
                case 'right':
                    _left = input.offset().left + input.outerWidth() - result.outerWidth();
                    left  = (_left > 0) ? _left : 0;
                    break;
                case 'center':
                    _left = input.offset().left + input.outerWidth() / 2 - result.outerWidth() / 2;
                    top   = input.offset().top + input.outerHeight();
                    left  = ((_left + result.width()) > windowWidth) ? 0 : _left;
                    break;
                case 'full-width':
                    left = 0;
                    break;
                case 'abs-right':
                    result.css({
                        top:  top + 5,
                        left: 'auto',
                        right: 5
                    });
                    return ;
            }

            result.css({
                top:  top + 5,
                left: left
            });
        }

        /**
         * @param key
         */
        function handle(key)
        {
            if (key == MOVE_LEFT || key == MOVE_DOWN) {
                move(key);
            } else if (key == KEY_ENTER || key == KEY_CANCEL) {
                keyevt(key);
            } else if (key == KEY_TAB) {
                keyevt(key);
            } else {
                search();
            }
        }

        /**
         * @param key
         */
        function move(key)
        {
            if (!hasResults()) {
                return;
            }

            selectNext(key);
        }

        /**
         * @param key
         */
        function keyevt(key)
        {
            switch (key) {
                case KEY_ENTER:
                    break;
                case KEY_CANCEL:
                    hideResults();
                    break;
                case KEY_TAB:
                    focusResults();
                    break;
            }
        }

        function focusResults()
        {
            result.find('a:first').focus();
            result.find('a:first').keydown(function (e) {
                if (e.shiftKey && e.key == KEY_TAB) {
                    e.preventDefault()
                    hideResults();
                    input.focus();
                }
            });
            result.find('a:last').keydown(function (e) {
                if (!e.shiftKey && e.key == KEY_TAB) {
                    e.preventDefault()
                    hideResults();
                    input.parent().find('button').focus();
                }
            });
        }

        function search()
        {
            if (input.val().length >= 3) {
                request(input.val());
            } else {
                hideResults();
            }
        }

        /**
         * @param text
         */
        function request(text)
        {
            $.ajax({
                type:    'POST',
                url:     options.url + 'suggest.php',
                data:    'k=' + encodeURIComponent(text) + '&jtl_token=' + window.jtl_search_token,
                success: function (data) {
                    response(data);
                }
            });
        }

        /**
         * @param data
         */
        function response(data)
        {
            data = $(data);
            if (data.length > 0) {
                data.find('a.rel-link').each(function (idx, item) {
                    $(item).on('mousedown', function(event) {
                        event.preventDefault();
                    });
                    $(item).click(function (evt) {
                        var url     = $(item).attr('href'),
                            query   = $(item).attr('rel'),
                            forward = $(item).attr('forward');
                        if (!(forward == 1 && url.length > 0)) {
                            input.val(query);
                            input.closest('form').submit();
                        } else {
                            $.ajax({
                                type:    'POST',
                                url:     options.url + 'suggestforward.php',
                                data:    'query=' + encodeURIComponent(query),
                                success: function (data) {
                                    window.location.href = url;
                                }
                            });
                        }
                        hideResults();

                        return false;
                    });
                });
                result.html(data);
                showResults();
            } else {
                result.html('');
                hideResults()
            }
        }

        /**
         *
         */
        function hideResults()
        {
            result.hide();
            $('#jtl-search-backdrop').remove();
        }

        /**
         *
         */
        function showResults()
        {
            result.stop().show();
            if (!$('#jtl-search-backdrop').length) {
                $('body').append('<div id="jtl-search-backdrop" class="modal-backdrop fade show zindex-dropdown"></div>');
                $('#jtl-search-backdrop').click(function(){
                    hideResults();
                });
            }
        }

        /**
         * @return int
         */
        function hasResults()
        {
            return result.children().length;
        }

        /**
         * @return int
         */
        function hasSelection()
        {
            return getSelected().length;
        }

        /**
         * @return {*}
         */
        function getSelected()
        {
            return result.find('.result_row > a.active:first, .is-nova > .row > .col a.active:first');
        }

        /**
         *
         */
        function selectFirst()
        {
            var next = result.find('.result_row > a:first, .is-nova > .row > .col a:first').addClass('active');
            input.val(next.attr('rel'));
        }

        /**
         * @param key
         */
        function selectNext(key)
        {
            var last,
                next = null;

            if (!hasSelection()) {
                selectFirst();
            } else {
                last = getSelected();

                switch (key) {
                    case MOVE_DOWN:
                        next = last.nextAll('a:first');
                        break;
                    case MOVE_UP:
                        next = last.prevAll('a:first');
                        break;
                    case MOVE_RIGHT:
                        next = result.find('.result_row:eq(1) > a:first');
                        break;
                    case MOVE_LEFT:
                        next = result.find('.result_row:eq(0) > a:first');
                        break;
                }

                if ($(next).length) {
                    last.removeClass('active');
                    next.addClass('active');
                    input.val(next.attr('rel'));
                }
            }
        }
    };
})(jQuery);
