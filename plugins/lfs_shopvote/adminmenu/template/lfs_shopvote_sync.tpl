<div class="settings-content">
    <form method="post" action="{$adminUrl}" class="navbar-form">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$plugin->getID()}">
        <div class="panel-idx-0 first mb-3">
            <div class="subheading1">ShopVote-Produktbewertungen synchronisieren</div>
            <hr>
            <div class="">
                <div class="form-group form-row align-items-center">
                    <label class="col col-sm-4 col-form-label text-sm-right" for="sv_sync_days">
                        {__('Reichweite in Tagen')}
                    </label>
                    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                        <input class="form-control" id="sv_sync_days" name="sv_sync_days" type="text" value="7">
                    </div>
                    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                </div>
            </div>

            <div class="save-wrapper">
                <div class="row">
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button name="sync" type="submit" value="Synchronisieren" class="btn btn-primary btn-block">
                            <i class="fal fa-save"></i> Synchronisieren
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>