{strip}
    {* This will be added to the end of the body, note that this script-tag executes before document.ready *}
    <script type="text/javascript">
        window.lpaOnAmazonPayReadyFired = false;

        {* This function is needed because of the asynchronous jQuery loading logic in JTL-Shop, e.g. NOVA Template - this enables inline scripts to register themselves with jquery document ready and window load events, even if JQ is not loaded yet. *}
        (function lpaJqueryReady() {
            if (typeof $ === "undefined") {
                setTimeout(lpaJqueryReady, 50);
                return;
            }
            window.lpaJqAsync = window.lpaJqAsync || [];

            {* Functions were added before, execute them now *}
            while (lpaJqAsync.length) {
                var obj = lpaJqAsync.shift();
                if (obj[0] === 'ready') {
                    $(document).ready(obj[1]);
                } else if (obj[0] === 'load') {
                    $(window).load(obj[1]);
                } else if (obj[0] === 'payReady') {
                    $('body').one('amazonPayReady.lpa', obj[1]);
                }
            }

            {* This object will replace the window array variable when this script is executed -
             * Inline scripts can register with this asynchronous function by pushing to lpaJqAsync an object with two parameters: ["ready"|"load"|"payReady", callbackfunction] *}
            window.lpaJqAsync = {
                push: function (param) {
                    if (param[0] === 'ready') {
                        $(document).ready(param[1]);
                    } else if (param[0] === 'load') {
                        $(window).load(param[1]);
                    } else if (param[0] === 'payReady') {
                        if(window.lpaOnAmazonPayReadyFired) {
                            param[1]();
                        } else {
                            $('body').one('amazonPayReady.lpa', param[1]);
                        }
                    }
                }
            };
        })();

        {* Register callback function that fires events when Login or Pay has loaded *}
        window.onAmazonPayReady = function () {
            if(window.lpaOnAmazonPayReadyFired) {
                return;
            }
            window.lpaOnAmazonPayReadyFired = true;
            {* trigger all registered callbacks *}
            window.lpaJqAsync = window.lpaJqAsync || [];
            lpaJqAsync.push(['ready', function() {
                $('body').trigger('amazonPayReady.lpa');
            }]);
            {* Fail safe - trigger the event again after a moment, in case any function really registered in between setting the flag and triggering the event.
                Due to the usage of "one" as registering method, there is no danger of initializing an element twice, *}
            window.setTimeout(function () {
                window.lpaJqAsync = window.lpaJqAsync || [];
                lpaJqAsync.push(['ready', function() {
                    $('body').trigger('amazonPayReady.lpa');
                }]);
            }, 500);
        };
    </script>
    {* Our own JS - needed for multiple functionalities *}
    <script src="{$lpa.pluginFrontendUrl}template/js/lpa.min.js?v={$lpa.pluginVersion}" defer="defer"></script>
    {* Checkout.js - this is used for the Amazon Pay button *}
    <script src="{$lpa.checkoutEndpointUrl}" defer="defer" onload="window.onAmazonPayReady();"></script>
{/strip}