{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/includes.tpl"}
{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/header.tpl" includeSelfCheck=true}
<div class="lpa-admin-content">
    <div class="row">
        <div class="col-12 col-xs-12">
            <h3>{__('lpaOrders')}</h3>
        </div>
        <div class="col-11 col-xs-11 col-sm-6">
            <div class="lpa-search">
                <form class="form" onsubmit="window.lpaOrderManagement.search($(this).find('[name=searchValue]').val());return false;">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" autocomplete="off" placeholder="{__('lpaSearchOrderPlaceholder')}" name="searchValue"/>
                        <div class="input-group-append input-group-btn">
                            <button class="btn btn-success" type="submit">{__('lpaSearchCTA')}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-1 col-xs-1 col-sm-2">
            <div class="lpa-on-ajax-loading">
                <div class="text-center">
                    <i class="fa fas fa-spinner fa-pulse"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-xs-12 col-sm-4">
            <div class="lpa-pagination">
                <div class="btn-group">
                    <button type="button" class="btn btn-default"><i class="fa fas fa-fast-backward" aria-hidden="true" onclick="window.lpaOrderManagement.firstPage();"></i></button>
                    <button type="button" class="btn btn-default"><i class="fa fas fa-backward" aria-hidden="true" onclick="window.lpaOrderManagement.prevPage();"></i></button>
                    <button type="button" class="btn btn-default disabled lpa-current-page-indicator" disabled="disabled">1</button>
                    <button type="button" class="btn btn-default"><i class="fa fas fa-forward" aria-hidden="true" onclick="window.lpaOrderManagement.nextPage();"></i></button>
                </div>
            </div>
        </div>
        <div class="col-12 col-xs-12">
            <hr>
            <div class="row">
                <div class="lpa-order-filter col-12 col-xs-12 col-md-6" data-filter="chargePermissionStatus">
                    <label for="lpa-order-charge-permission-status-select" class="lpa-order-filter-label">{__('lpaChargePermissionStatusFilter')}</label>
                    <select name="chargePermissionStatusFilter" class="lpa-order-filter-options form-control" id="lpa-order-charge-permission-status-select">
                        <option value="" selected="selected" class="lpa-order-filter-option">{__('lpaChargePermissionStatusFilterAll')}</option>
                    </select>
                </div>
                <div class="lpa-order-filter col-12 col-xs-12 col-md-6" data-filter="chargePermissionStatusReason">
                    <label for="lpa-order-charge-permission-status-reason-select" class="lpa-order-filter-label">{__('lpaChargePermissionStatusReasonFilter')}</label>
                    <select name="chargePermissionStatusReasonFilter" class="lpa-order-filter-options form-control" id="lpa-order-charge-permission-status-reason-select">
                        <option value="" selected="selected" class="lpa-order-filter-option">{__('lpaChargePermissionStatusReasonFilterAll')}</option>
                    </select>
                </div>
            </div>
            <hr>
        </div>
        <div class="col-12 col-xs-12">
            <div class="lpa-order-table">
                <div class="lpa-order-table-head lpa-7-cols">
                    <div class="lpa-order-table-column" data-sort-by="shopOrderNumber" onclick="window.lpaOrderManagement.changeSortBy('shopOrderNumber');">{__('lpaOrderNumber')}</div>
                    <div class="lpa-order-table-column" data-sort-by="shopOrderStatus" onclick="window.lpaOrderManagement.changeSortBy('shopOrderStatus');">{__('lpaOrderStatusShop')}</div>
                    <div class="lpa-order-table-column" data-sort-by="chargePermissionId" onclick="window.lpaOrderManagement.changeSortBy('chargePermissionId');">{__('lpaChargePermissionId')}</div>
                    <div class="lpa-order-table-column" data-sort-by="chargePermissionStatus" onclick="window.lpaOrderManagement.changeSortBy('chargePermissionStatus');">{__('lpaChargePermissionStatus')}</div>
                    <div class="lpa-order-table-column" data-sort-by="chargePermissionAmount" onclick="window.lpaOrderManagement.changeSortBy('chargePermissionAmount');">{__('lpaAmount')}</div>
                    <div class="lpa-order-table-column" data-sort-by="chargePermissionExpiration" onclick="window.lpaOrderManagement.changeSortBy('chargePermissionExpiration');">{__('lpaExpirationDate')}</div>
                    <div class="lpa-order-table-column">{__('lpaAction')}</div>
                </div>
                <div class="lpa-orders lpa-7-cols"></div>
            </div>
        </div>
        <div class="col-12 col-xs-12 lpa-on-ajax-loading">
            <div class="text-center">
                <i class="fa fas fa-2x fa-spinner fa-pulse"></i>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="lpa-order-detail-modal" data-backdrop="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{__('lpaCloseWindow')}</button>
                </div>
            </div>
        </div>
    </div>

</div>
{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/footer.tpl"}