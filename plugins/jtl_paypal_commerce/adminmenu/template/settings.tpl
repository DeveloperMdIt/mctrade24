<style>
    div[class*="advancedSettingsWrapper_"] {
        background: #f5f7fa;
        display: none;
        padding-right: 15px;
        padding-left: 5px;
    }
</style>

<div id="ppcSettings" class="container-fluid">
    <div class="d-flex justify-content-start align-items-center">
        <div class="subheading1">
            {__('Einstellungen')}
        </div>
        {include file="$basePath/adminmenu/template/snippets/workingModeSwitch.tpl" switchPos="settings"}
    </div>
    <hr class="mb-3">
    <form method="post" id="saveSettingsForm" enctype="multipart/form-data" name="wizard" class="settings navbar-form">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
        <input type="hidden" name="panelActive" value="{$panelActive}" />
        {include file="$basePath/adminmenu/template/snippets/panels.tpl" }
        {include file="$basePath/adminmenu/template/snippets/paypalJsSDK.tpl" }
    </form>
</div>
