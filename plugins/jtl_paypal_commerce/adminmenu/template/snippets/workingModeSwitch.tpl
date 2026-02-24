<style>
    .environment-switch .custom-control-input.checked ~ .custom-control-label::before {
        color: #435a6b;
        border-color: #435a6b;
        background-color: #5cbcf6;
    }
    .environment-switch.custom-switch .custom-control-input.checked ~ .custom-control-label::after {
        background-color: #ffffff;
        transform: translateX(0.75rem);
    }
    .environment-switch .custom-control-input:focus:not(:checked) ~ .custom-control-label::before {
        border-color: #435a6b;
    }
</style>

<div class="ml-2 custom-control custom-switch environment-switch">
    <form method="post" enctype="multipart/form-data" id="workingModeForm-{$switchPos}" name="workingModeForm" class="workingMode navbar-form">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
        <input type="submit" name="task" value="changeWorkingMode" class="environment-switch-input
            custom-control-input
            {if $ppc_mode === 'sandbox'}
            checked
            {/if}" id="environment-switch-input-{$switchPos}">
        <label class="custom-control-label" for="environment-switch-input-{$switchPos}">
            {if $ppc_mode === 'sandbox'}
                {__('Testmodus aktiv (Sandbox)')}
            {else}
                {__('Produktivmodus aktiv (Live)')}
            {/if}
        </label>
    </form>
</div>