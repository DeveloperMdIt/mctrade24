<link media="screen" href="{$baseCssURL}" type="text/css" rel="stylesheet" />
<div id="JTLSearch_testperiod">
    {if $startedTestperiod}
        <span class="alert alert-info">{__('trialStarted')}</span>
        <script type="text/javascript">
            $(document).ready(function () {ldelim}
                window.location.href = '{$pluginAdminURL}';
            {rdelim});
        </script>
    {else}
        {foreach $form->getErrorMessages() as $errorMessage}
            <div class="box_error alert alert-danger">{$errorMessage}</div>
        {/foreach}
        {$form->getFormStartHTML()}
        {$form->getHiddenElements()}
        <div class="setting">
            <div class="input-group">
                <span class="input-group-addon">{$form->getLabelHTML(cCode)}</span>
                {$form->getElementHTML(cCode)}
            </div>
            <hr>
            <div class="save_wrapper">{$form->getElementHTML(btn_serverinfo)}</div>
        </div>
        {$form->getFormEndHTML()}
    {/if}
</div>
