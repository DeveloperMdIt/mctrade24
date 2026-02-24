{include file='tpl_inc/header.tpl'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('systemcheck') cBeschreibung=__('systemcheckDesc') cDokuURL=__('systemcheckURL')}
{include file='tpl_inc/systemcheck.tpl'}
<style type="text/css">
{literal}
    .phpinfo pre {margin: 0; font-family: monospace;}
    .phpinfo a, .phpinfo a:link {color: #000; text-decoration: none;}
    .phpinfo a:hover {text-decoration: none;}
    .phpinfo table {width: 100%; max-width: 100%; margin-bottom: 20px;}
    .phpinfo .center {text-align: center;}
    .phpinfo .center table {margin: 1em auto; text-align: left;}
    .phpinfo .center th {text-align: center !important;}
    .phpinfo td, .phpinfo th {padding: 8px; line-height: 1.42857143; vertical-align: top; border-top: 1px solid #ddd;}
    .phpinfo h1 {font-size: 150%;}
    .phpinfo h2 {font-size: 125%;}
    .phpinfo .p {text-align: left;}
    .phpinfo .e {background-color: #f9f9f9; width: 300px; font-weight: bold;}
    .phpinfo .h {background-color: #ddd; font-weight: bold;}
    .phpinfo .v {max-width: 300px; overflow-x: auto;}
    .phpinfo .v i {color: #999;}
    .phpinfo img {float: right; border: 0;}
    .phpinfo hr {}
{/literal}
</style>

<div id="content">
    {if !empty($phpinfo)}
        <div class="card">
            <div class="card-body">
                <div class="phpinfo">{$phpinfo}</div>
            </div>
        </div>
    {/if}

    <div class="systemcheck">
        {if !$passed}
            <div class="alert alert-warning">
                {__('noteImportantCheckSettings')}
            </div>
        {/if}

        {if count($tests.recommendations) > 0}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('suggestedAdjustments')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th class="col-7">&nbsp;</th>
                            <th class="col-3 text-center">{__('suggestedValue')}</th>
                            <th class="col-2 text-center">{__('yourSystem')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $tests.recommendations as $test}
                            <tr>
                                <td>
                                    <div class="test-name">
                                        <strong>{$test->getName()}</strong><br>
                                        {$description=$test->getDescription()}
                                        {if $description !== null && $description|strlen > 0}
                                            <p class="hidden-xs expandable">{$description}</p>
                                        {/if}
                                    </div>
                                </td>
                                <td class="text-center">{$test->getRequiredState()}</td>
                                <td class="text-center">{call test_result test=$test}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}

        {if count($tests.programs) > 0}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('installedSoftware')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th class="col-7">{__('software')}</th>
                            <th class="col-3 text-center">{__('requirements')}</th>
                            <th class="col-2 text-center">{__('available')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $tests.programs as $test}
                            {if !$test->getIsOptional() || $test->getIsRecommended()}
                                <tr>
                                    <td>
                                        <div class="test-name">
                                            <strong>{$test->getName()}</strong><br>
                                            {$description=$test->getDescription()}
                                            {if $description !== null && $description|strlen > 0}
                                                <p class="hidden-xs expandable">{$description}</p>
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{$test->getRequiredState()}</td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                            {/if}
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}

        {if count($tests.apache_config) > 0}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('neededApacheModules')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th class="col-7">{__('designation')}</th>
                            <th class="col-3 text-center">{__('requirements')}</th>
                            <th class="col-2 text-center">{__('available')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $tests.apache_config as $test}
                            {if !$test->getIsOptional() || $test->getIsRecommended()}
                                <tr>
                                    <td>
                                        <div class="test-name">
                                            <strong>{$test->getName()}</strong><br>
                                            {$description = $test->getDescription()}
                                            {if $description !== null && $description|strlen > 0}
                                                <p class="hidden-xs expandable">{$description}</p>
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{$test->getRequiredState()}</td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                            {/if}
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}

        {if count($tests.php_modules) > 0}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('neededPHPExtensions')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th class="col-10">{__('designation')}</th>
                            <th class="col-2 text-center">{__('status')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $tests.php_modules as $test}
                            {if !$test->getIsOptional() || $test->getIsRecommended()}
                                <tr>
                                    <td>
                                        <div class="test-name">
                                            <strong>{$test->getName()}</strong><br>
                                            {$description = $test->getDescription()}
                                            {if $description !== null && $description|strlen > 0}
                                                <p class="hidden-xs expandable">{$description}</p>
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                            {/if}
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}

        {if count($tests.php_config) > 0}
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('needPHPSetting')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th class="col-7">{__('setting')}</th>
                            <th class="col-3 text-center">{__('neededValue')}</th>
                            <th class="col-2 text-center">{__('yourSystem')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $tests.php_config as $test}
                            {if !$test->getIsOptional() || $test->getIsRecommended()}
                                <tr>
                                    <td>
                                        <div class="test-name">
                                            <strong>{$test->getName()}</strong><br>
                                            {$description=$test->getDescription()}
                                            {if $description !== null && $description|strlen > 0}
                                                <p class="hidden-xs expandable">{$description}</p>
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{$test->getRequiredState()}</td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                            {/if}
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        {/if}
    </div>
</div>
{include file='tpl_inc/footer.tpl'}
