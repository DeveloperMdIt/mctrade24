<link type="text/css" rel="stylesheet" href="{$URL}templates/style.css" />
<div id="jtl-template-editor">
    <div id="main-actions">
        <select id="theme" aria-label="{__('--- select theme ---')}">
            <option value="">{__('--- select theme ---')}</option>
            {foreach $themes as $theme}
                {if isset($theme.theme)}
                    <option value="{$theme.theme}" data-template="{$theme.template}">{$theme.theme|ucfirst}</option>
                {/if}
            {/foreach}
        </select>
        <a href="#" id="refresh"><span class="glyphicon glyphicon-refresh"></span></a>
        {*<span class="check"><input type="checkbox" id="switch-skins" name="show-all-themes" value="0"><label for="switch-skins">{__('show all themes')}</label></span>*}
        <div class="pull-right">
            <button id="compile" class="btn btn-success">
                <i id="loader" class="fa fa-spin fa-spinner"></i>
                {__('compile theme')}
            </button>
            {*<button id="minify" class="btn btn-success">{__('compile JavaScript')}</button>*}
        </div>
    </div>

    <div id="editor-sidebar">
        <ul id="files"></ul>
    </div>

    <div id="editor"></div>

    <div id="actions">
        <button id="save" class="btn btn-primary">{__('save file')}</button>
        <button id="compilefile" class="btn btn-secondary">{__('compile file')}</button>
        <button id="reset" class="btn btn-danger pull-right">{__('reset file to default')}</button>
    </div>

    <div id="messages" class="text-center">
        <div id="msg" class="alert"></div>
    </div>
</div>

<div id="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div id="dialog-content" class="modal-body"></div>
            <div class="modal-footer">
                <span class="btn-group">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{__('cancel')}</button>
                    <button id="dialog-action" type="button" class="btn btn-danger"></button>
                </span>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ace.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/theme-monokai.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/mode-less.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.1.3/ext-searchbox.js"></script>
<script type="text/javascript">
    var URL = '{$URL}api.php',
        token = '{$smarty.session.jtl_token}',
        warningCompile = '{__('warningCompile')}',
        warningReset = '{__('warningReset')}',
        actionCompile = '{__('actionCompile')}',
        actionReset = '{__('actionReset')}';
</script>
<script type="text/javascript" src="{$URL}script.js"></script>
