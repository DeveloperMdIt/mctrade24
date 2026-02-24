<form method="post" enctype="multipart/form-data" name="mapping">
    {$jtl_token}
    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
    <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
    <input type="hidden" name="stepPlugin" value="{$stepPlugin}" />

    <div>
        <div class="subheading1">
            {__('Create new mapping')}:
        </div>
        <hr>
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cType">{__('type')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <select class="custom-select" id="cType" name="cType" >
                    <option value="0">-{__('please select')}-</option>
                    <option value="Merkmal"{if isset($requestData.cType) && $requestData.cType === 'Merkmal'} selected="selected"{/if}>{__('Merkmal')}</option>
                    <option value="Merkmalwert"{if isset($requestData.cType) && $requestData.cType === 'Merkmalwert'} selected="selected"{/if}>{__('Merkmalwert')}</option>
                </select>
            </div>
        </div>
        <div class="form-group form-row align-items-center zeile">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cVon">{__('From')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <input class="form-control" type="text" name="cVon" id="cVon"{if isset($requestData.cVon)} value="{$requestData.cVon}"{/if} />
                <span class="cZu Merkmalwert">
                    <small>({__('e.g.')} {__('very large')})</small>
                </span>
                <span class="cZu Merkmal">
                    <small>({__('e.g.')} {__('Clothing size')})</small>
                </span>
            </div>
        </div>
        <div class="form-group form-row align-items-center zeile">
            <label class="col col-sm-4 col-form-label text-sm-right" for="cZuMerkmal">{__('To')}:</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <span class="cZu Merkmalwert">
                    <input class="form-control" id="cZu" type="text" name="cZuMerkmalwert"{if isset($requestData.cZuMerkmalwert)} value="{$requestData.cZuMerkmalwert}"{/if} />
                    <small>({__('e.g.')} {__('XL')})</small>
                </span>
                <span class="cZu Merkmal">
                    <select class="custom-select" name="cZuMerkmal" id="cZuMerkmal">
                        <option value="farbe"{if isset($requestData.cType) && $requestData.cZuMerkmal === 'farbe'} selected="selected"{/if}>{__('farbe')}</option>
                        <option value="groesse"{if isset($requestData.cType) && $requestData.cZuMerkmal === 'groesse'} selected="selected"{/if}>{__('groesse')}</option>
                        <option value="geschlecht"{if isset($requestData.cType) && $requestData.cZuMerkmal === 'geschlecht'} selected="selected"{/if}>{__('geschlecht')}</option>
                        <option value="altersgruppe"{if isset($requestData.cType) && $requestData.cZuMerkmal === 'altersgruppe'} selected="selected"{/if}>{__('altersgruppe')}</option>
                        <option value="muster"{if isset($requestData.cType) && $requestData.cZuMerkmal === 'muster'} selected="selected"{/if}>{__('muster')}</option>
                        <option value="material"{if isset($requestData.cType) && $requestData.cZuMerkmal === 'material'} selected="selected"{/if}>{__('material')}</option>
                    </select>
                    <small>({__('e.g.')} {__('Size')})</small>
                </span>
            </div>
        </div>
        <div class="row mr-0 mb-4">
            <div class="ml-auto col-sm-6 col-lg-auto">
                <button type="submit" class="btn btn-primary btn-block zeile" name="btn_save_new" value="Neues Attribut speichern">
                    <i class="far fa-save"></i> {__('Save new mapping')}
                </button>
            </div>
        </div>
    </div>
    <div class="mt-7">
        <div class="subheading1">
            {__('Mappings')}
        </div>
        <hr class="mb-4">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th class="text-center">{__('ID')}</th>
                        <th class="TD2">{__('Type')}</th>
                        <th class="TD3">{__('To')}</th>
                        <th class="TD4">{__('From')}</th>
                        <th class="text-center">{__('Delete')}</th>
                    </tr>
                </thead>
                <tbody>
                    {if $mappings|count > 0}
                        {foreach $mappings as $mapping}
                            <tr>
                                <td class="text-center" style="width: 110px;">{$mapping.kMapping}</td>
                                <td class="TD2">{__($mapping.cType)}</td>
                                <td class="TD3">{__($mapping.cZu)}</td>
                                <td class="TD4">{$mapping.cVon}</td>
                                <td class="text-center">
                                    <button type="submit" name="btn_delete[{$mapping.kMapping}]" value="{__('delete')}" class="btn btn-link">
                                        <span class="icon-hover">
                                            <span class="fal fa-trash-alt"></span>
                                            <span class="fas fa-trash-alt"></span>
                                        </span>
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        <tr>
                            <td colspan="5">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i> {__('Currently no optional mappings exists')}
                                </div>
                            </td>
                        </tr>
                    {/if}
                </tbody>
            </table>
        </div>
    </div>
</form>
{literal}
<script type="text/javascript">
    $(function() {
        toogleZu();
        $('#cType').change(function() {
            toogleZu();
        });
    });

    function toogleZu() {
        var cValue = $('#cType').val();
        if (cValue == 0) {
            $('.zeile').hide();
        } else {
            $('.zeile').show();
            $('.cZu').hide();
            $('.'+cValue).show();
        }
    }
</script>
{/literal}
