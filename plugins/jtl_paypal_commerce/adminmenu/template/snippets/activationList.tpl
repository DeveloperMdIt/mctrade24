{if !is_array($setting['value'])}
    {assign var="enabledMethods" value=","|explode:$setting['value']}
{else}
    {assign var="enabledMethods" value=$setting['value']}
{/if}
<style>
    .activation-switch .custom-control-input:checked ~ .custom-control-label::before,
    .activation-switch .custom-control-input.checked ~ .custom-control-label::before {
        color: #435a6b;
        border-color: #435a6b;
        background-color: #5cbcf6;
    }
    .activation-switch.custom-switch .custom-control-input:checked ~ .custom-control-label::after,
    .activation-switch.custom-switch .custom-control-input.checked ~ .custom-control-label::after {
        background-color: #ffffff;
        transform: translateX(0.75rem);
    }
    .activation-switch .custom-control-input:focus:not(:checked) ~ .custom-control-label::before {
        border-color: #435a6b;
    }
</style>
<div id="component_{$settingsName}" class="m-n4 {$setting['class']}">
    {if isset($setting.vars.selectGroups) && count($setting.vars.selectGroups) > 0}
        <div class="pb-sm-5">
            <div class="d-flex flex-row text-sm-left">
                <div class="pr-sm-5 ml-2 custom-control custom-switch activation-switch">
                    <input type="checkbox" class="custom-control-input selectGroup-switch" value="all" id="selectgroups_all" aria-label="{__('Alle')}">
                    <label class="custom-control-label" for="selectgroups_all">{__('Alle')}</label>
                </div>
                <div class="pr-sm-5 ml-2 custom-control custom-switch activation-switch">
                    <input type="checkbox" class="custom-control-input selectGroup-switch" value="none" id="selectgroups_none" aria-label="{__('Keine')}">
                    <label class="custom-control-label" for="selectgroups_none">{__('Keine')}</label>
                </div>
                {foreach $setting.vars.selectGroups as $group => $val}
                    {assign var="groupHint" value=__("`$group`_hint")}
                    <div class="pr-sm-5 ml-2 custom-control custom-switch activation-switch">
                        <input type="checkbox" class="custom-control-input selectGroup-switch" value="{$group}" id="selectgroups_{$group}" aria-label="{__($group)}">
                        <label class="custom-control-label" for="selectgroups_{$group}">{__($group)}</label>
                        {if $groupHint !== "`$group`_hint"}
                        <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{$groupHint|escape:"html"}">
                            <span class="fas fa-info-circle fa-fw"></span>
                        </span>
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}
    <table class="table table-striped activation-table">
        <thead>
            <tr>
                <th class="centered">{__('Aktiv')}</th>
                <th class="text-nowrap">
                    {$setting['label']}&nbsp;<span data-html="true" data-toggle="tooltip" data-placement="left" title="{$setting['description']}" data-original-title="{$setting['description']}">
                        <span class="fas fa-info-circle fa-fw"></span>
                    </span>
                </th>
                <th>{if isset($setting['label2'])}{$setting['label2']}{/if}</th>
                <th class="centered">#</th>
            </tr>
        </thead>
        <tbody>
        {foreach $setting['options'] as $options}
            <tr>
                <td class="centered">
                    <div class="ml-2 custom-control custom-switch activation-switch">
                        <input type="checkbox" class="custom-control-input" id="setting_{$settingsName}_{$options.value}"
                               name="settings[{$settingsName}][]"
                               value="{$options.value}"
                               aria-label="{$options.label} {__('aktiv')}"
                                {if in_array($options['value'], $enabledMethods)} checked="checked" aria-checked="true"{/if}
                        >
                        <label class="custom-control-label" for="setting_{$settingsName}_{$options.value}">&nbsp;</label>
                    </div>
                </td>
                <td>
                    {assign var="optionHint" value=__("`$options.value`_hint")}
                    {$options.label}
                    {if $optionHint !== "`$options.value`_hint"}
                    <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{$optionHint|escape:"html"}">
                        <span class="fas fa-info-circle fa-fw"></span>
                    </span>
                    {/if}
                </td>
                <td>
                    {if isset($options['extended'])}
                        {$options['extended'][$options.value]}
                    {/if}
                </td>
                <td class="centered">
                    {if isset($options['action']) && in_array($options['value'], $enabledMethods)}
                        <span class="btn btn-link px-2" title=""
                              data-toggle="modal"
                              data-target="#actList-actionModal"
                              data-action="{$options['action'][$options.value]}"
                              data-id="{$options.value}"
                              data-name="{$options.label|escape:html}"
                        >
                            <span class="icon-hover">
                                <span class="fal fa-gear"></span>
                                <span class="fas fa-gear"></span>
                            </span>
                        </span>
                    {/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
<div class="modal fade" id="actList-actionModal" tabindex="-1" role="dialog" aria-labelledby="actList-actionModal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="actList-actionModal-label">Modal title</h4>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer">
                <div class="modal-footer">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-lg-auto mb-2">
                            <button type="button" class="btn btn-outline-primary btn-block" data-dismiss="modal">
                                {__('cancelWithIcon')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-lg-auto ">
                            <button id="actList-actionModal-save" type="button" class="btn btn-primary btn-block">
                                {__('saveWithIcon')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{if isset($setting.vars.selectGroups) && count($setting.vars.selectGroups) > 0}
<script>
    let selectGroups        = {json_encode($setting.vars.selectGroups)},
        $activationSwitches = $('.activation-table .activation-switch .custom-control-input');
    {literal}
    $('.selectGroup-switch').on('change', function(e) {
        let $target = $(e.target);
        if (e.target.value === 'all' && $target.prop('checked')) {
            $activationSwitches.prop('checked', true);
            checkActivationSelection();
        } else if (e.target.value === 'none' && $target.prop('checked')) {
            $activationSwitches.prop('checked', false);
            checkActivationSelection();
        } else {
            for (let group in selectGroups[e.target.value]) {
                $('.activation-table .activation-switch .custom-control-input[value="' + selectGroups[e.target.value][group] + '"]')
                    .prop('checked', $target.prop('checked'))
            }
            checkActivationSelection();
        }
    });
    $activationSwitches.on('change', function(e) {
        checkActivationSelection();
    });
    function checkActivationSelection() {
        let $checkedSelections = $('.activation-table .activation-switch .custom-control-input:checked');
        $('#selectgroups_all').prop('checked', $checkedSelections.length === $activationSwitches.length);
        $('#selectgroups_none').prop('checked', $checkedSelections.length === 0);
        for (let group in selectGroups) {
            let activated = 0;
            for (let part in selectGroups[group]) {
                if ($('.activation-table .activation-switch .custom-control-input[value="' + selectGroups[group][part] + '"]:checked').length) {
                    ++activated;
                }
            }
            $('.selectGroup-switch[value="' + group + '"]')
                .prop('checked', selectGroups[group].length === activated);
        }
    }
    checkActivationSelection();

    $("#actList-actionModal").on("show.bs.modal", function(e) {
        let $target    = $(e.relatedTarget),
            action     = $target.data('action'),
            spinnerOff = true,
            $this      = $(this);
        $this.data('action', action);
        $this.find(".modal-body").html('<div id="actionBody-' + action + '"><i class="fa fa-spinner fa-spin"></i></div>');
        $this.find(".modal-title").html($target.data('name'));
        ioCall(
            'jtl_ppc_listActionGet',
            [action, $target.data('id')],
            ()=>{},
            (data)=>{
                $("#actList-actionModal").modal('hide');
                alert(data.error.message)
            },
            $this,
            spinnerOff
        );
    });
    $("#actList-actionModal-save").on("click", function(e) {
        let $modal     = $("#actList-actionModal"),
            action     = $modal.data('action'),
            spinnerOff = true,
            data       = $("input, textarea, select", "#listAction" + action + "-Container").serialize();
        $modal.modal('hide');
        ioCall(
            'jtl_ppc_listActionPost',
            [action, data],
            ()=>{},
            (data)=>{
                alert(data.error.message)
            },
            $modal,
            spinnerOff
        );
    });
    {/literal}
</script>
{/if}
