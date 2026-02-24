<div id="enableShipmentTrackingModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="edit-ShipmentTracking" method="post">
                <div class="modal-header">
                    <h2 class="modal-title">{__('Versandinformationen übermitteln')}</h2>
                </div>
                <div class="modal-body">
                    {$jtl_token}
                    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                    <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
                    <div class="form-group form-row align-items-center">
                        <p>{$settingDescription}</p>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cron-freq">{__('headingFrequency')} ({__('hours')}):</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 config-type-number">
                            <div class="input-group form-counter">
                                <div class="input-group-prepend">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-down>
                                        <span class="fas fa-minus"></span>
                                    </button>
                                </div>
                                <input id="cron-freq" type="number" min="1" value="6" name="frequency" class="form-control" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary border-0" data-count-up>
                                        <span class="fas fa-plus"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right" for="cron-start">{__('headingStartTime')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                            <input id="cron-start" type="time" name="time" value="02:00" class="form-control" required>
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
                            <button type="submit" class="btn btn-primary btn-block" name="task" value="changeShipmentTracking">
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
    $(document).ready(function(){
        let $shipmentTrackingModal = $('#enableShipmentTrackingModal');

        $('#activateShipmentTrackingForm').submit(function () {
            $shipmentTrackingModal.modal('show');

            return false;
        });
    });
</script>
