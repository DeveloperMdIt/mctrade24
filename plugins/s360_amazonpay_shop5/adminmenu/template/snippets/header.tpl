<div class="lpa-admin-header">
    {$includeSelfCheck=$includeSelfCheck|default:false}
    {if $includeSelfCheck}
        <div class="row mb-3">
            <div class="col-12 col-xs-12">
                <h3 class="lpa-self-check-title">{__('lpaSelfCheckTitle')}</h3>
            </div>
            <div class="col-12 col-xs-12 lpa-admin-self-check-placeholder">
                <div class="lpa-admin-self-check-loading">
                    <i class="fa fa-pulse fa-spinner"></i> {__('lpaSelfCheckLoading')}
                </div>
                <div class="lpa-admin-self-check-loading-failed text-warning" style="display:none;">
                    <i class="fa fa-times-circle"></i> {__('lpaSelfCheckFailed')}
                </div>
            </div>
        </div>
    {/if}
    <div class="row">
        {if !empty($lpaErrors)}
            {foreach $lpaErrors as $error}
                <div class="col-xs-12 col-12">
                    <div class="alert alert-danger"><b>{__('lpaError')}</b> {$error}</div>
                </div>
            {/foreach}
        {/if}
        {if !empty($lpaWarnings)}
            {foreach $lpaWarnings as $warning}
                <div class="col-xs-12 col-12">
                    <div class="alert alert-warning"><b>{__('lpaWarning')}</b> {$warning}</div>
                </div>
            {/foreach}
        {/if}
        {if !empty($lpaMessages)}
            {foreach $lpaMessages as $message}
                <div class="col-xs-12 col-12">
                    <div class="alert alert-info"><b>{__('lpaHint')}</b> {$message}</div>
                </div>
            {/foreach}
        {/if}
        {if !empty($lpaSuccesses)}
            {foreach $lpaSuccesses as $success}
                <div class="col-xs-12 col-12">
                    <div class="alert alert-success"><b>{__('lpaSuccess')}</b> {$success}</div>
                </div>
            {/foreach}
        {/if}
    </div>
</div>