<div id="mappings-modal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="edit-mapping" method="post">
                <div class="modal-header">
                    <h2 class="modal-title"></h2>
                </div>
                <div class="modal-body">
                    {$jtl_token}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                    <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
                    <input id="mappingID" type="hidden" name="id" value="" />

                    <div class="form-group form-row align-items-center" id="suggestWawiCarrier">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="carrier_wawi">{__('Versandart in JTL-Wawi')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input type="text" id="carrier_wawi" name="carrier_wawi" value="" class="form-control" required>
                            <i class="fas fa-spinner fa-pulse typeahead-spinner"></i>
                        </div>
                        <script>
                            enableTypeahead('#carrier_wawi', 'jtl_ppc_carrier_mapping', 'cName', null, function(e, item) {
                                $('#carrier_wawi').val(item.cName);
                            }, $('#suggestWawiCarrier .fa-spinner'));
                        </script>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="carrier_paypal">{__('Versanddienstleister bei PayPal')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <select id="carrier_paypal" name="carrier_paypal" class="custom-select" required>
                                {foreach $paypalCarriers as $paypalCarrier}
                                    <option value="{$paypalCarrier}">
                                        {__($paypalCarrier)}
                                    </option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-lg-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">
                                {__('cancelWithIcon')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-lg-auto ">
                            <button type="submit" class="btn btn-primary btn-block" name="task" value="saveCarrierMapping">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    {literal}
    $(document).ready(function(){
        let $title = $('#mappings-modal .modal-title');
        $('.mappings-edit').on('click', function(){
            $title.html('{/literal} {addslashes(__('Mapping bearbeiten'))}{literal}');
            $('#carrier_wawi').val($(this).data('carrier_wawi'));
            $('#carrier_paypal').val($(this).data('carrier_paypal'));
            $('#mappingID').val($(this).data('id'));
        });
        $('.mappings-new').on('click', function(){
            $title.html('{/literal} {addslashes(__('Mapping anlegen'))}{literal}');
            $('#carrier_wawi').val('');
            $('#carrier_paypal').val('');
            $('#mappingID').val('');
        });
        $('.mappings-delete').on('click', function(){
            $('#deleteID').val($(this).data('id'));
        });
    });
    {/literal}
</script>
