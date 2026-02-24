{if !empty($setting['value']) }
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right order-1">{$setting['label']}:</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 text-sm-left">
            <span>{$setting['value']}</span>
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
        <span data-html="true" data-toggle="tooltip" data-placement="left" title="{$setting['description']}" data-original-title="{$setting['description']}">
            <span class="fas fa-info-circle fa-fw"></span>
        </span>
        </div>
    </div>
{/if}
