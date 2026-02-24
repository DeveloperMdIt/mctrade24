<div class="alert alert-info">
    {__('If you want to export more languages...')}
</div>

<div>
    <form method="post" enctype="multipart/form-data" name="export">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}"/>
        <input type="hidden" name="isReviewFeed" value="0"/>
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}"/>
        <input type="hidden" name="stepPlugin" value="{$stepPlugin}"/>
        <div class="subheading1">{__('Create new export format')}:</div>
        <hr>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('Export name')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <input class="form-control" type="text" id="cName" name="cName" value="" required/>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cDateiname">{__('File name')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <input class="form-control" type="text" id="cDateiname" name="cDateiname" value="" required/>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="kSprache">{__('Language')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" id="kSprache" name="kSprache" required>
                    {foreach $gs_languages as $language}
                        <option value="{$language->kSprache}">{__($language->cNameDeutsch)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('Customer group')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" name="kKundengruppe" id="kKundengruppe" required>
                    {foreach $gs_customerGroups as $customerGroup}
                        <option value="{$customerGroup->kKundengruppe}">{__($customerGroup->cName)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="kWaehrung">{__('Currency')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" name="kWaehrung" id="kWaehrung" required>
                    {foreach $gs_currencies as $currency}
                        <option value="{$currency->kWaehrung}">{__($currency->cName)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cLieferlandIso">
                {__('Shipping country')}:
            </label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" name="cLieferlandIso" id="cLieferlandIso" required>
                    {foreach $gs_shippingCountries as $country}
                        <option value="{$country}">{$country}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row mr-0">
            <div class="ml-auto col-sm-6 col-lg-auto">
                <button type="submit" name="btn_save_new" value="1" class="btn btn-primary btn-block">
                    <i class="fa fa-save"></i> {__('Create new export format')}
                </button>
            </div>
        </div>
    </form>
    <form method="post" enctype="multipart/form-data" name="export">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}"/>
        <input type="hidden" name="isReviewFeed" value="1"/>
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}"/>
        <input type="hidden" name="stepPlugin" value="{$stepPlugin}"/>
        <div class="subheading1">{__('Create new review feed')}:</div>
        <hr>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cName">{__('Feed name')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <input class="form-control" type="text" id="cName" name="cName" value="" required/>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cDateiname">{__('File name')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <input class="form-control" type="text" id="cDateiname" name="cDateiname" value="" required/>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="kSprache">{__('Language')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" id="kSprache" name="kSprache" required>
                    {foreach $gs_languages as $language}
                        <option value="{$language->kSprache}">{__($language->cNameDeutsch)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="kKundengruppe">{__('Customer group')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" name="kKundengruppe" id="kKundengruppe" required>
                    {foreach $gs_customerGroups as $customerGroup}
                        <option value="{$customerGroup->kKundengruppe}">{__($customerGroup->cName)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="kWaehrung">{__('Currency')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" name="kWaehrung" id="kWaehrung" required>
                    {foreach $gs_currencies as $currency}
                        <option value="{$currency->kWaehrung}">{__($currency->cName)}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cLieferlandIso">
                {__('Shipping country')}:
            </label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" name="cLieferlandIso" id="cLieferlandIso" required>
                    {foreach $gs_shippingCountries as $country}
                        <option value="{$country}">{$country}</option>
                    {/foreach}
                </select>
            </div>
        </div>
        <div class="row mr-0">
            <div class="ml-auto col-sm-6 col-lg-auto">
                <button type="submit" name="btn_save_new" value="1" class="btn btn-primary btn-block">
                    <i class="fa fa-save"></i> {__('Create new review feed')}
                </button>
            </div>
        </div>
    </form>
</div>

<div class="mt-7">
    <div class="subheading1">
        {__('Exportformats')}
    </div>
    <hr class="mb-4">
    <form method="post" enctype="multipart/form-data" name="export_delete">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}"/>
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}"/>
        <input type="hidden" name="stepPlugin" value="{$stepPlugin}"/>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>{__('Export name')}</th>
                    <th>{__('File name')}</th>
                    <th class="text-center">{__('Language')}</th>
                    <th class="text-center">{__('Currency')}</th>
                    <th>{__('Customer group')}</th>
                    <th class="text-center">{__('Shipping country')}</th>
                    <th class="text-center">{__('Actions')}</th>
                </tr>
                </thead>
                <tbody>
                {if $oExportformate}
                    {foreach $oExportformate as $oExportformat}
                        <tr>
                            <td>{$oExportformat->cName}</td>
                            <td>{$oExportformat->cDateiname}</td>
                            <td class="text-center">{__($oExportformat->cSprache)}</td>
                            <td class="text-center">{__($oExportformat->cWaehrung)}</td>
                            <td>{__($oExportformat->cKundengruppe)}</td>
                            <td class="text-center">{$oExportformat->cLieferlandIso}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button type="submit"
                                            name="btn_delete"
                                            value="{$oExportformat->kExportformat}"
                                            class="btn btn-link px-2">
                                        <span class="icon-hover">
                                            <span class="fal fa-trash-alt"></span>
                                            <span class="fas fa-trash-alt"></span>
                                        </span>
                                    </button>
                                    <a href="{$adminURL}/{$exportPath}" class="btn btn-link px-2">
                                        <span class="icon-hover">
                                            <span class="fal fa-share"></span>
                                            <span class="fas fa-share"></span>
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                {/if}
                </tbody>
            </table>
        </div>
    </form>
</div>

<script type="text/javascript">
    $('form button[name="btn_delete').on('click', function (e) {
        if (!window.confirm('{__('Are you really sure to delete this export format')}')) {
            e.preventDefault();
        }
    });
</script>
