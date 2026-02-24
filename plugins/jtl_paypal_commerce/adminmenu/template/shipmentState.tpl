<div id="shipmentState" class="container-fluid">
    <div class="d-flex justify-content-start align-items-center">
        <div class="subheading1">
            {__('Versandinformationen übermitteln')}
        </div>
        <span data-html="true" data-toggle="tooltip" data-placement="left" title="{__('shipmenttrackingActivateDescription')}" data-original-title="{__('shipmenttrackingActivateDescription')}">
            <span class="fas fa-info-circle fa-fw"></span>
        </span>
        <form method="post" enctype="multipart/form-data" class="shipmenttrackingMode navbar-form" id="activateShipmentTrackingForm">
            {$jtl_token}
            <input type="hidden" name="kPlugin" value="{$kPlugin}" />
            <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
            <div class="ml-2 custom-control custom-switch activation-switch">
                <input type="submit" name="task" value="changeShipmentTracking" id="shipmenttracking-switch-input"
                       class="custom-control-input{if $trackingEnabled === 'Y'} checked{/if}">
                <label class="custom-control-label" for="shipmenttracking-switch-input">&nbsp;</label>
            </div>
        </form>
    </div>
    <hr class="mb-3">
    {if $trackingEnabled === 'Y'}
        <p>{$settingDescription}</p>
        {include file="$basePath/adminmenu/template/snippets/shipmentFormEnabled.tpl"}
    {/if}
</div>

{if $trackingEnabled === 'Y'}
    {include file="$basePath/adminmenu/template/snippets/shipmentModalEdit.tpl"}
    <script>
        $(document).ready(function(){
            let $shipmentTrackingModal    = $('#modal-footer').clone(),
                shipmentTrackingConfirmed = false;
            $('.modal-dialog', $shipmentTrackingModal).removeClass('modal-xl');
            $('.modal-body', $shipmentTrackingModal).html(
                '{addslashes(__("Wollen Sie die Übermittlung der Versandinformationen deaktivieren?"))}'
            );
            $('.modal-footer', $shipmentTrackingModal).html(
                '<div class="row">' +
                    '<div class="col-sm-6 col-xl-auto">' +
                    '<button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">' +
                    '{__("cancelWithIcon")}' +
                    '</button>' +
                    '</div>' +
                    '<div class="ml-auto col-sm-6 col-xl-auto mb-2">' +
                        '<button type="button" id="deactivateShipmentTrackingYes" class="btn btn-danger btn-block">' +
                            '<i class="fa fa-close"></i> {__("Deaktivieren")}' +
                        '</button>' +
                    '</div>' +
                '</div>'
            );
            $('#deactivateShipmentTrackingYes', $shipmentTrackingModal).on('click', function () {
                shipmentTrackingConfirmed = true;
                $shipmentTrackingModal.modal('hide');
                $('#shipmenttracking-switch-input').click();
            });

            $('#activateShipmentTrackingForm').submit(function () {
                if (shipmentTrackingConfirmed) {
                    return true;
                }

                $shipmentTrackingModal.modal('show');

                return false;
            })
        });
    </script>
{else}
    {include file="$basePath/adminmenu/template/snippets/shipmentModalEnable.tpl"}
{/if}
