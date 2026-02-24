<div class="modal fade" id="disconnectPaypal-actionModal" tabindex="-1" role="dialog" aria-labelledby="disconnectPaypal-actionModal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="disconnectPaypal-actionModal-label">{__('PayPal Account trennen')}</h4>
            </div>
            <div id="disconnectPaypal-modalBody" class="modal-body">
                <div class="text-center"><i class="fa fa-spinner fa-spin"></i></div>
            </div>
            <div class="modal-footer">
                <div class="modal-footer">
                    <form id="send-resetmail-form" method="post" enctype="multipart/form-data">
                        <div class="row">
                            {$jtl_token}
                            <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                            <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
                            <input type="hidden" name="task" value="resetCredentials" />
                            <div class="ml-auto col-sm-6 col-lg-auto mb-2">
                                <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">
                                    {__('cancelWithIcon')}
                                </button>
                            </div>
                            <div class="ml-auto col-sm-6 col-lg-auto mb-2">
                                <button id="send-resetmail-button" type="submit" class="btn btn-outline-primary btn-block" name="subTask" value="sendResetMail">
                                    <i class="fal fa-mail-forward mr-0 mr-lg-2"></i> {__('Rücksetz-Code per E-Mail')}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    let $disconnectModal = $('#disconnectPaypal-actionModal'),
        $modalBody       = $('.modal-body', $disconnectModal);
    $disconnectModal.on('shown.bs.modal', function () {
        let $this = $(this);
        $modalBody.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i></div>');
        ioCall(
            'jtl_ppc_resetCredentials',
            [],
            ()=>{
                $('input.code-value-single:first').focus();
            },
            (data)=>{
                $disconnectModal.modal('hide');
                alert(data.error.message)
            },
            $this,
            true
        );
    }).on('hide.bs.modal', function () {
        $modalBody.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i></div>');
    }).keyup(function (ev){
        let $target = $(ev.target);
        if ($target.hasClass('code-value-single')) {
            $('input.code-value-single', $target.parent().next()).focus();
        }
    });
    $('#send-resetmail-button').on('click', function (e) {
        e.preventDefault();
        $modalBody.html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i></div>');
        ioCall(
            'jtl_ppc_resetCredentials_sendMail',
            [],
            () => { },
            (data)=>{
                $disconnectModal.modal('hide');
                alert(data.error.message);
                $('#send-resetmail-form')
                    .append($('<input>')
                        .attr('type', 'hidden')
                        .attr('name', 'subTask')
                        .val('sendResetMail'))
                    .submit();
            },
            $(this),
            true
        );
    });
</script>
