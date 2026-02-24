<style>
    .subheading1.credentials{
        text-transform: none;
    }
</style>
<div id="ppcCredentials" class="container-fluid">
    <div class="d-flex justify-content-start align-items-center">
        <div class="subheading1">
            {__('Zugangsdaten')}
        </div>
        {include file="$basePath/adminmenu/template/snippets/workingModeSwitch.tpl" switchPos="credentials"}
    </div>
    <hr class="mb-3">
    <form method="post" enctype="multipart/form-data" name="wizard" class="settings navbar-form">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        <input id="settingsTabID" type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
        <div class="card">
            <div class="card-body">
                {foreach $settingSections as $sectionName => $section}
                    <span class="subheading1 credentials">{$section['heading']}</span>
                    <hr class="mb-n3">
                    {if isset($section['settings'])}
                        {include file="$basePath/adminmenu/template/snippets/section.tpl" }
                    {/if}
                {/foreach}
            </div>
        </div>
    <div class="save-wrapper">
        <div class="row">
            <div class="mr-auto col-sm-6 col-xl-auto">
                <button id="disconnectPaypal" type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#disconnectPaypal-actionModal">
                    <i class="fal fa-chain-broken mr-0 mr-lg-2"></i> {__('PayPal Account trennen')}
                </button>
            </div>
        </div>
    </div>
    </form>
</div>

{include file="$basePath/adminmenu/template/credentials-resetmodal.tpl"}

<script>
    $(window).on('load', function() {
        let welcomeAlert = $('#welcomeAlertModal');
        if (welcomeAlert.length > 0) {
            welcomeAlert.modal('show');
        }
    });
</script>
