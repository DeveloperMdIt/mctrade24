<div class="alert alert-info">{__('Normally NOTHING will be changed here.')}</div>
<form method="post" enctype="multipart/form-data" name="export">
    {$jtl_token}
    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
    <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
    <input type="hidden" name="stepPlugin" value="alteAttr" />
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{__('ID')}</th>
                    <th>{__('Google name')}</th>
                    <th class="min-w-sm">{__('Value name')}</th>
                    <th class="min-w-sm">{__('Value type')}</th>
                    <th class="text-center">{__('Active')}</th>
                    <th>{__('Actions')}</th>
                </tr>
            </thead>
            <tbody>
            {if $attribute_arr}
                {foreach $attribute_arr as $oAttribut}
                    {assign var=kAttribut value=$oAttribut->kAttribut}

                    <tr>
                        <td>{$oAttribut->kAttribut}</td>
                        <td>
                            {if $oAttribut->bStandard == 1}
                                {$oAttribut->cGoogleName}
                            {else}
                                <input class="form-control" type="text" name="cGoogleName[{$oAttribut->kAttribut}]" value="{$oAttribut->cGoogleName}" />
                            {/if}
                        </td>
                        <td>
                            <input class="form-control" type="text" name="cWertName[{$oAttribut->kAttribut}]" value="{$oAttribut->cWertName}" {if isset($kindAttribute_arr[$kAttribut])}style="display: none;"{/if} />
                        </td>
                        <td>
                            {if isset($kindAttribute_arr[$kAttribut])}
                                {$eWertHerkunft}
                            {else}
                                <select class="custom-select" name="eWertHerkunft[{$oAttribut->kAttribut}]">
                                    {foreach $eWertHerkunft_arr as $eWertHerkunft}
                                        <option value="{$eWertHerkunft}"{if $eWertHerkunft === $oAttribut->eWertHerkunft} selected{/if}>{$eWertHerkunft}</option>
                                    {/foreach}
                                </select>
                            {/if}
                        <td class="text-center">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="active-{$oAttribut->kAttribut}" name="bAktiv[{$oAttribut->kAttribut}]" value="1" {if $oAttribut->bAktiv == 1}checked="true"{/if} />
                                <label class="custom-control-label" for="active-{$oAttribut->kAttribut}"></label>
                            </div>
                        </td>
                        <td>
                            {if isset($oAttribut->bStandard) && $oAttribut->bStandard == 1}
                                <button type="submit"
                                        name="btn_standard[{$oAttribut->kAttribut}]"
                                        value="{__('Reset')}"
                                        class="btn btn-link" {if isset($kindAttribute_arr[$kAttribut])}style="display: none;"{/if}
                                        title="{__('Reset')}"
                                        data-toggle="tooltip">
                                    <span class="icon-hover">
                                        <span class="fal fa-undo"></span>
                                        <span class="fas fa-undo"></span>
                                    </span>
                                </button>
                            {else}
                                <button type="submit"
                                        name="btn_delete[{$oAttribut->kAttribut}]"
                                        value="{__('Delete')}"
                                        class="btn btn-link"
                                        title="{__('Delete')}"
                                        data-toggle="tooltip">
                                     <span class="icon-hover">
                                        <span class="fal fa-trash-alt"></span>
                                        <span class="fas fa-trash-alt"></span>
                                    </span>
                                </button>
                            {/if}
                        </td>
                    </tr>
                    {if isset($kindAttribute_arr[$kAttribut])}
                        <tr>
                            <td>&Gt;</td>
                            <td colspan="5">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{__('ID')}</th>
                                                <th>{__('V-ID')}</th>
                                                <th>{__('Google name')}</th>
                                                <th>{__('Value name')}</th>
                                                <th>{__('Value type')}</th>
                                                <th class="text-center">{__('Active')}</th>
                                                <th>{__('Actions')}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $kindAttribute_arr[$kAttribut] as $oKindAttribut}
                                            <tr>
                                                <td>{$oKindAttribut->kAttribut}</td>
                                                <td>{if $oKindAttribut->bStandard == 1}{$oKindAttribut->kVaterAttribut}{else}<input class="form-control" type="text" name="kVaterAttribut[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->kVaterAttribut}" />{/if}</td>
                                                <td>{if $oKindAttribut->bStandard == 1}{$oKindAttribut->cGoogleName}{else}<input class="form-control" type="text" name="cGoogleName[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->cGoogleName}" />{/if}</td>
                                                <td><input class="form-control" type="text" name="cWertName[{$oKindAttribut->kAttribut}]" value="{$oKindAttribut->cWertName}" /></td>
                                                <td>
                                                    <select class="custom-select" name="eWertHerkunft[{$oKindAttribut->kAttribut}]">
                                                        {foreach $eWertHerkunft_arr as $cWertHerkunft => $eWertHerkunft}
                                                            <option value="{$eWertHerkunft}"{if $eWertHerkunft === $oKindAttribut->eWertHerkunft} selected{/if}>{$cWertHerkunft}</option>
                                                        {/foreach}
                                                    </select>
                                                <td class="text-center">
                                                    <div class="custom-control custom-checkbox">
                                                        <input class="custom-control-input" type="checkbox" id="active-child-{$oKindAttribut->kAttribut}"  name="bAktiv[{$oKindAttribut->kAttribut}]" value="1" {if $oKindAttribut->bAktiv == 1}checked="true"{/if} />
                                                        <label class="custom-control-label" for="active-child-{$oKindAttribut->kAttribut}"></label>
                                                    </div>
                                                </td>
                                                <td>
                                                    {if isset($oKindAttribut->bStandard) && $oKindAttribut->bStandard == 1}
                                                        <button type="submit"
                                                                name="btn_standard[{$oKindAttribut->kAttribut}]"
                                                                value="{__('Reset')}"
                                                                class="btn btn-link"
                                                                title="{__('Reset')}"
                                                                data-toggle="tooltip">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-undo"></span>
                                                                <span class="fas fa-undo"></span>
                                                            </span>
                                                        </button>
                                                    {else}
                                                        <button type="submit"
                                                                name="btn_delete[{$oKindAttribut->kAttribut}]"
                                                                value="{__('Delete')}"
                                                                class="btn btn-link"
                                                                title="{__('Delete')}"
                                                                data-toggle="tooltip">
                                                            <span class="icon-hover">
                                                                <span class="fal fa-trash-alt"></span>
                                                                <span class="fas fa-trash-alt"></span>
                                                            </span>
                                                        </button>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        <tr>
                    {/if}
                {/foreach}
            {else}
                <tr>
                    <td colspan="5">{__('Currently no attributes exists')}</td>
                </tr>
            {/if}
            </tbody>
        </table>
    </div>
    <div class="row mr-0 mt-5">
        <div class="ml-auto col-sm-6 col-lg-auto">
            <button id="btn_reset_all" type="submit" name="btn_reset_all" value="{__('Reset all')}" class="btn btn-outline-primary">
                <i class="far fa-remove"></i> {__('Reset all')}
            </button>
        </div>
        <div class="col-sm-6 col-lg-auto">
            <button type="submit" name="btn_save_old" value="{__('Save changes')}" class="btn btn-primary">
                <i class="far fa-save"></i> {__('Save changes')}
            </button>
        </div>
    </div>
</form>
<div class="mt-7">
    <div class="subheading1 mb-3">
        {__('Create new attribute')}
    </div>
    <form method="post" enctype="multipart/form-data" name="export">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
        <input type="hidden" name="stepPlugin" value="neuesAttr" />
        <table class="table">
            <tr>
                <td class="text-right"><label for="cGoogleName">{__('Google name')}:</label></td>
                <td><input class="form-control" id="cGoogleName" type="text" name="cGoogleName" value="{if isset($requestData.cGoogleName)}{$requestData.cGoogleName}{/if}" required /></td>
                <td>{__('How is the name of the attribute in Google export export file')}</td>
            </tr>
            <tr>
                <td class="text-right"><label for="eWertHerkunft">{__('Value type')}:</label></td>
                <td>
                    <select class="custom-select" name="eWertHerkunft" id="eWertHerkunft" required>
                        {foreach $eWertHerkunft_arr as $cWertHerkunft => $eWertHerkunft}
                            <option value="{$eWertHerkunft}" {if isset($requestData.eWertHerkunft) && $requestData.eWertHerkunft === $eWertHerkunft}selected {/if}>{$cWertHerkunft}</option>
                        {/foreach}
                    </select>
                </td>
                <td>{__('Which field type should be used to export the value')}</td>
            </tr>
            <tr>
                <td class="text-right"><label for="cWertName">{__('Value name')}:</label></td>
                <td><input class="form-control" id="cWertName" type="text" name="cWertName" value="{if isset($requestData.cWertName)}{$requestData.cWertName}{/if}" /></td>
                <td>
                    {__('Depending on the value type')}:<br/>
                    <b>{__('Article property')}:</b> {__('Which property of article object contains the value')}<br />
                    <b>{__('Function attribute')}:</b> {sprintf(__('Which type contains the value'), __('Function attribute'))}<br />
                    <b>{__('Attribute')}:</b> {sprintf(__('Which type contains the value'), __('Attribute'))}<br />
                    <b>{__('Feature')}:</b> {sprintf(__('Which type contains the value'), __('Feature'))}<br />
                    <b>{__('Static value')}:</b> {__('This value will be exported as it is')}<br />
                    <b>{__('Parent attribute')}:</b> {__('Leave this blank if attribute is a parent attribute')}<br />
                </td>
            </tr>
            <tr>
                <td class="text-right"><label for="bAktiv">{__('Active')}:</label></td>
                <td class="text-center">
                    <div class="custom-control custom-checkbox">
                        <input class="custom-control-input" id="bAktiv" type="checkbox" name="bAktiv" value="1" {if isset($requestData.bAktiv) && $requestData.bAktiv == 1}checked="true" {/if}/>
                        <label class="custom-control-label" for="bAktiv"></label>
                    </div>

                </td>
                <td>{__('Should this attribute be exported')}</td>
            </tr>
            <tr>
                <td class="text-right"><label for="kVaterAttribut">{__('V-ID')} ({__('Parent-ID')}:</label></td>
                <td><input class="form-control" id="kVaterAttribut" type="text" name="kVaterAttribut" value="{if isset($requestData.kVaterAttribut)}{$requestData.kVaterAttribut}{/if}" /></td>
                <td>
                    {__('Enter the ID of the parent attribute')}
                    (<b class="text-uppercase">{__('Attention')}:</b> {__('Only IDs are allowed for which the value type Parent attribute is selected')}<br />
                    {__('Leave this blank, if this attribute is not a child attribute')}
                </td>
            </tr>
        </table>
        <div class="row ml-0">
            <div class="ml-auto col-sm-6 col-lg-auto">
                <button type="submit" name="btn_save_new" value="Neues Attribut speichern" class="btn btn-primary btn-block">
                    <i class="fa fa-save"></i> {__('Save new attribute')}
                </button>
            </div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $('#btn_reset_all').on('click', function (e) {
        if (!window.confirm('{__('All attributes will be reseted')}')) {
            e.preventDefault();
        }
    });
</script>
