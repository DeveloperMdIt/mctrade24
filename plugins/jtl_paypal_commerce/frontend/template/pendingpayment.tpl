{container}
    {row}
        {col class="text-center"}
            {alert variant="info" class="text-center mt-4 pt-2 pb-5"}
                {badge variant="light" class="bubble"}<i class="fas fa-credit-card"></i>{/badge}<br/>
                {$checkMessage}
            {/alert}
            {row}
                {col md=4 lg=3 offset-md=2 offset-lg=3}
                {link href=$orderStateURL class="btn btn-primary btn-block"}{lang key='showOrder' section='login'}{/link}
                {/col}
                {col md=4 lg=3}
                {link href=$ShopURL class="btn btn-secondary btn-block"}{lang key='continueShopping' section='checkout'}{/link}
                {/col}
            {/row}
        {/col}
    {/row}
{/container}

{if $waitingBackdrop === true}
    <div class="modal modal-center fade" id="ppp-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <h2 id="pp-loading-body"><i class="fa fa-spinner fa-spin fa-fw"></i>&nbsp;
                        <span>{$checkMessage}</span>
                    </h2>
                </div>
            </div>
        </div>
    </div>
    {inline_script}<script>
        {literal}
        $(function() {
            let paypalCheckTimeout = 0,
                paypalCountTimeout = 15,
                paypalCheckPending  = function () {
                    ppcpIOManagedCall(
                        'jtl_paypal_commerce.checkPaymentState',
                        {
                            methodID: {/literal}{$methodID}{literal},
                            timeout: paypalCheckTimeout > paypalCountTimeout
                        },
                        {},
                        function (error, data) {
                            if (error || ++paypalCheckTimeout > paypalCountTimeout) {
                                window.location.assign(
                                    window.location.href + (window.location.href.indexOf('?') >= 0 ? '&' : '?') + 'timeout'
                                );
                            } else {
                                window.setTimeout(paypalCheckPending, 5000);
                            }
                        }
                    )
                }
            $('#ppp-modal').modal({backdrop: 'static'}).show();
            window.setTimeout(paypalCheckPending, 5000);
        });
        {/literal}
    </script>{/inline_script}
{/if}
