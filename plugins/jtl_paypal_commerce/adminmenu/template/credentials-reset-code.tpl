<form method="post" enctype="multipart/form-data">
    <div class="row">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
        <input type="hidden" name="task" value="resetCredentials" />
        <div class="ml-auto mr-auto col-sm-8 mb-2 d-flex justify-content-center">
            <div class="row">
            {for $i = 1 to $parts}
                <div class="ml-auto pl-1 pr-1 mr-auto col">
                <input class="form-control text-center code-value-single" type="text" name="resetCode[{$i}]" maxlength="1" value="">
                </div>
            {/for}
            </div>
        </div>
    </div>
    <p>{__('Wenn Sie Ihren PayPal Account trennen, können Sie Dienste und Produkte von PayPal nicht mehr in Ihrem Shop nutzen. Fortfahren?')}</p>
    <div class="row">
        <div class="ml-auto mr-auto col-sm-6 col-lg- mb-2">
            <button type="submit" class="btn btn-primary btn-block" name="subTask" value="doResetCredentials">
                <i class="fal ffa-chain-broken mr-0 mr-lg-2"></i> {__('PayPal Account trennen')}
            </button>
        </div>
    </div>
</form>
