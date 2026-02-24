{$fileType = $propdesc.filetype|default:''}

<div class="form-group">
    <label for="config-{$propname}"
           class="d-block"
            {if !empty($propdesc.desc)}
                data-toggle="tooltip" title="{$propdesc.desc|default:''}" data-placement="auto"
            {/if}>
        {$propdesc.label|default:''}
        {if !empty($propdesc.desc)}
            <i class="fas fa-info-circle fa-fw"></i>
        {/if}
    </label>

    <button type="button" class="opc-btn-secondary opc-mini-btn file-upload-btn"
            data-selected="{if empty($propval)}false{else}true{/if}"
            onclick="opc.selectFileProp(this, '{$propname}', '{$fileType}')">
        <span>
            <i class="fas fa-upload"></i> {__('chooseFile')}
        </span>

        <span>
            <i class="fas fa-trash"></i> <span class="upload-btn-filename">{$propval}</span>
        </span>
    </button>

    <input type="hidden"
           name="{$propname}"
           value="{$propval|escape:'html'}">
</div>
