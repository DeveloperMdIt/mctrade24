{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/header.tpl"}
<div class="lpa-admin-content">
    <div class="row">
        <div class="col-12 col-xs-12">
            <h3>{__('lpaSubscriptions')}</h3>
        </div>
        <div class="col-12 col-xs-12 col-sm-12">
            <div class="lpa-on-ajax-loading">
                <div class="text-center">
                    <i class="fa fas fa-spinner fa-pulse"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-xs-12 col-sm-4">
            <div class="lpa-pagination">
                <div class="btn-group">
                    <button type="button" class="btn btn-default"><i class="fa fas fa-fast-backward" aria-hidden="true" onclick="window.lpaSubscriptionManagement.firstPage();"></i></button>
                    <button type="button" class="btn btn-default"><i class="fa fas fa-backward" aria-hidden="true" onclick="window.lpaSubscriptionManagement.prevPage();"></i></button>
                    <button type="button" class="btn btn-default disabled lpa-current-page-indicator" disabled="disabled">1</button>
                    <button type="button" class="btn btn-default"><i class="fa fas fa-forward" aria-hidden="true" onclick="window.lpaSubscriptionManagement.nextPage();"></i></button>
                </div>
            </div>
        </div>
        <div class="col-12 col-xs-12">
            <hr>
        </div>
        <div class="col-12 col-xs-12">
            <div class="lpa-subscription-table">
                <div class="lpa-subscription-table-head lpa-8-cols">
                    <div class="lpa-subscription-table-column">{__('lpaSubscriptionId')}</div>
                    <div class="lpa-subscription-table-column">{__('lpaCustomerEmail')}</div>
                    <div class="lpa-subscription-table-column">{__('lpaInitialOrderNumber')}</div>
                    <div class="lpa-subscription-table-column">{__('lpaSubscriptionInterval')}</div>
                    <div class="lpa-subscription-table-column">{__('lpaSubscriptionStatus')}</div>
                    <div class="lpa-subscription-table-column">{__('lpaChargePermissionId')}</div>
                    <div class="lpa-subscription-table-column">{__('lpaSubscriptionNext')}</div>
                    <div class="lpa-subscription-table-column">{__('lpaAction')}</div>
                </div>
                <div class="lpa-subscriptions"></div>
            </div>
        </div>
        <div class="col-12 col-xs-12 lpa-on-ajax-loading">
            <div class="text-center">
                <i class="fa fas fa-2x fa-spinner fa-pulse"></i>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="lpa-subscription-detail-modal" data-backdrop="true">
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