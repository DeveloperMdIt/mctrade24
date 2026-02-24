<div class="form-group form-row align-items-center {$setting['class']}">
    <label class="col col-sm-4 col-form-label text-sm-right order-1" for="setting_{$settingsName}">{$setting['label']}:</label>
    <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 text-sm-right">
        <input type="password" class="form-control" name="settings[{$settingsName}]" id="setting_{$settingsName}" value="{$setting['value']}" placeholder="xxx" />
    </div>
    <div class="col-auto ml-sm-n4 order-2 order-sm-3">
        <span data-html="true" data-toggle="tooltip" data-placement="left" title="{__('anzeigen / verbergen')}" data-original-title="{__('anzeigen / verbergen')}">
            <span id="passwordToggle{$settingsName}" class="fas fa-eye fa-fw"></span>
        </span>
        <span data-html="true" data-toggle="tooltip" data-placement="left" title="{$setting['description']}" data-original-title="{$setting['description']}">
            <span class="fas fa-info-circle fa-fw"></span>
        </span>
    </div>
</div>

<script>
    {literal}
    (function () {
        {/literal}
        let inputId = '#setting_{$settingsName}';
        let buttonId = '#passwordToggle{$settingsName}';
        {literal}
        $(buttonId).on('click', function(e) {
            if ($(inputId)[0].type === "password") {
                $(inputId)[0].type = "text";
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                $(inputId)[0].type = "password";
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            }
        })
    })()
    {/literal}
</script>
