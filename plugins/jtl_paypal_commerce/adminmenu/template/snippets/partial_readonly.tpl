<div id="component_{$settingsName}" class="form-group form-row align-items-center {$setting['class']}">
    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="settings_{$settingsName}">{$setting['label']}:</label>
    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 text-sm-right">
        <input type="text" readonly="readonly" class="form-control readonl" id="setting_{$settingsName}" value="{$setting['value']}" placeholder="{$setting['value']}" />
    </div>
    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
        <span data-html="true" data-toggle="tooltip" data-placement="left" title="{$setting['description']}" data-original-title="{$setting['description']}">
            <span class="fas fa-info-circle fa-fw"></span>
        </span>
    </div>
</div>