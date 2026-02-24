<div id="ppcPendingOrders" class="container-fluid">
    <div class="d-flex justify-content-start align-items-center">
        <div class="subheading1">
            {__('Offene Bestellungen')}
        </div>
    </div>
    <hr class="mb-3">
    <p>
        {__('pendingOrders_info')}
    </p>
    {include file='tpl_inc/pagination.tpl' oPagination=$pagination cParam_arr=['kPluginAdminMenu'=>$kPluginAdminMenu]}
    <form method="post" action="" enctype="multipart/form-data" name="wizard" class="settings pendingOrders navbar-form">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
        <input type="hidden" name="task" value="">
        <input type="hidden" name="txnId" value="">
        <input type="hidden" name="paymentId" value="">
        <table class="table" id="paypal-pending-orders">
            <thead>
            <tr>
                <th></th>
                <th>{__('Bestell-Nr.')}</th>
                <th>{__('Bestellung vom')}</th>
                <th>{__('Kunde')}</th>
                <th>{__('Zahlungsart')}</th>
                <th>{__('Transaktions-ID')}</th>
                <th class="text-center">{__('Status')}</th>
                <th class="text-center">{__('Aktionen')}</th>
            </tr>
            </thead>
            <tbody>
            {foreach $pagination->getPageItems() as $pendingOrder}
            <tr class="pending-order-row" data-id="{$pendingOrder->txn_id}" data-key="{$pendingOrder->kZahlungsart}">
                <td>
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" name="paymentIds[{$pendingOrder->txn_id}]" id="cb-check-{$pendingOrder->txn_id}" type="checkbox" value="{$pendingOrder->kZahlungsart}" />
                        <label class="custom-control-label" for="cb-check-{$pendingOrder->txn_id}"></label>
                    </div>
                </td>
                <td>{$pendingOrder->cBestellNr}</td>
                <td>{$pendingOrder->dErstellt}</td>
                <td>{$pendingOrder->customerName}</td>
                <td>{$pendingOrder->paymentName}</td>
                <td>{$pendingOrder->txn_id}</td>
                <td class="text-center" id="{$pendingOrder->txn_id}"><i class="fa fa-spinner fa-spin"></i></td>
                <td class="text-center">
                    <div class="btn-group">
                        <button id="reload-{$pendingOrder->txn_id}"
                                name="reload"
                                type="button"
                                class="btn btn-link px-2"
                                title="{__('update')}"
                                data-toggle="tooltip"
                                aria-expanded="false"
                                disabled="disabled">
                            <span class="icon-hover">
                                <span class="fal fa-refresh"></span>
                                <span class="fas fa-refresh"></span>
                            </span>
                        </button>
                        <button id="delete-{$pendingOrder->txn_id}"
                                name="deletePendingOrder"
                                type="button"
                                class="btn btn-link px-2"
                                title="{__('Löscht die Zuordnung zur Zahlung und übernimmt die Bestellung in JTL-Wawi')}"
                                data-toggle="tooltip"
                                aria-expanded="false"
                                disabled="disabled">
                            <span class="icon-hover">
                                <span class="fal fa-share"></span>
                                <span class="far fa-share"></span>
                            </span>
                        </button>
                        <button id="apply-{$pendingOrder->txn_id}"
                                name="applyPendingOrder"
                                type="button"
                                class="btn btn-link px-2"
                                title="{__('Übernimmt Zahlungseingang und Bestellung in JTL-Wawi')}"
                                data-toggle="tooltip"
                                aria-expanded="false"
                                disabled="disabled">
                            <span class="icon-hover">
                                <span class="fas fa-share"></span>
                                <span class="far fa-share"></span>
                            </span>
                        </button>
                    </div>
                </td>
            </tr>
            {/foreach}
            </tbody>
        </table>
        <div class="card-footer save-wrapper">
            <div class="row">
                <div class="col-sm-6 col-xl-auto text-left">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS" type="checkbox" onclick="AllMessages(this.form);">
                        <label class="custom-control-label" for="ALLMSGS">{__('globalSelectAll')}</label>
                    </div>
                </div>
                <div class="ml-auto col-sm-6 col-xl-auto">
                    <button name="task" class="btn btn-outline-primary btn-block" type="submit" value="deletePendingOrderAll" data-toggle="tooltip" title="{__('Löscht die Zuordnung zur Zahlung und übernimmt die Bestellung in JTL-Wawi')}">
                        {__('Ohne Transaktions-ID übernehmen')}
                    </button>
                </div>
                <div class="col-sm-6 col-xl-auto">
                    <button name="task" class="btn btn-primary btn-block" type="submit" value="applyPendingOrderAll" data-toggle="tooltip" title="{__('Übernimmt Zahlungseingang und Bestellung in JTL-Wawi')}">
                        {__('Mit Transaktions-ID übernehmen')}
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<script>
    let $tabPane = $('.tab-link-{$kPluginAdminMenu}'),
        $form    = $('form.pendingOrders');
    {literal}
    function ppc_orderState(pId, txnId) {
        let spinnerOff = true;
        ioCall(
            'jtl_ppc_orderstate',
            [pId, txnId],
            ()=>{},
            ()=>{},
            window,
            spinnerOff
        );
    }
    {/literal}
    function ppc_orderStateReload(pId, txnId) {
        let $txnCell = $('#' + txnId),
            $txnRow  = $txnCell.closest('.pending-order-row');
        $('button', $txnRow).attr('disabled', true);
        $txnCell.html('<i class="fa fa-spinner fa-spin"></i>');
        ppc_orderState(pId, txnId);
    }
    function ppc_orderStateReloadAll() {
        $('button.btn-link', $form).attr('disabled', true);
        $('.pending-order-row[data-id]').each(function(id, txnRow) {
            let $txnRow = $(txnRow);
            ppc_orderState(parseInt($txnRow.data('key')), $txnRow.data('id'));
        });
    }

    if ($tabPane.hasClass('active')) {
        ppc_orderStateReloadAll();
    } else {
        $tabPane.on('shown.bs.tab', function () {
            ppc_orderStateReloadAll();
            $tabPane.off('shown.bs.tab');
        });
    }
    $('button', $form).click(function () {
        let $this   = $(this),
            $txnRow = $this.closest('.pending-order-row');
        $this.tooltip('hide');
        if ($this.attr('name') === 'reload') {
            ppc_orderStateReload($txnRow.data('key'), $txnRow.data('id'));
        } else {
            $('input[name="task"]', $form).val($this.attr('name'));
            $('input[name="txnId"]', $form).val($txnRow.data('id'));
            $('input[name="paymentId"]', $form).val($txnRow.data('key'));
            $form.submit();
        }
    });
</script>
