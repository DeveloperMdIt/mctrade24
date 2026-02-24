{include file='tpl_inc/header.tpl'}

<script>
    $(function() {
        $('.table tr[data-href]').each(function(){
            $(this).css('cursor','pointer').hover(
                function(){
                    $(this).addClass('active');
                },
                function(){
                    $(this).removeClass('active');
                }).on('click', function(){
                    document.location = $(this).attr('data-href');
                }
            );
        });

        $('.grid').masonry({
            itemSelector: '.grid-item',
            columnWidth: '.grid-item',
            percentPosition: true
        });

    });
</script>

{function renderStatusCheck check=null}
    {$more = $check->getURL()}
    {$val = $check->isOK()}
    {$title = $check->getTitle()}
    <tr class="text-vcenter"{if $more} data-href="{$more}"{/if}>
        <td {if !$more}colspan="2"{/if}>
            {if $val}
                <i class="fal fa-check-circle text-success fa-fw" aria-hidden="true"></i>
            {else}
                <i class="fa fa-exclamation-circle text-danger fa-fw" aria-hidden="true"></i>
            {/if}
            <span>{$title}</span>
        </td>
        {if $more}
            <td class="text-right">
                <a href="{$more}" class="btn btn-default btn-sm text-uppercase">{__('details')}</a>
            </td>
        {/if}
    </tr>
{/function}

{include file='tpl_inc/systemcheck.tpl'}

<div id="content">
    <div class="grid">
        <div class="grid-item">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('general')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <table class="table table-striped text-x1 last-child">
                        <tbody>
                        {foreach $statusChecks as $check}
                            {renderStatusCheck check=$check}
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid-item">
            <div class="card">
                <div class="card-header">
                    <div class="heading-body">
                        <div class="subheading1">{__('cache')}</div>
                    </div>
                    <div class="heading-right">
                        <div class="btn-group btn-group-xs">
                            <button class="btn btn-primary dropdown-toggle text-uppercase"
                                    type="button"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                {__('details')} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li class="dropdown-item"><a href="{$adminURL}/cache">{__('systemCache')}</a></li>
                                <li class="dropdown-item">
                                    <a href="{$adminURL}/imagemanagement">{__('imageCache')}</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="text-center">
                                {if $objectCache->getResultCode() === 1}
                                    {$cacheOptions = $objectCache->getOptions()}
                                    <i class="fal fa-check-circle text-four-times text-success"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">{__('activated')}</h3>
                                    <span style="color:#c7c7c7">{ucfirst($cacheOptions.method)}</span>
                                {else}
                                    <i class="fa fa-exclamation-circle text-four-times text-info"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">{__('deactivated')}</h3>
                                    <span style="color:#c7c7c7">{__('requirementsMet')}</span>
                                {/if}

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <i class="fa fa-file-image-o text-four-times text-success"></i>
                                <h3 style="margin-top:10px;margin-bottom:0">
                                    {(($imageCache->getGeneratedBySize(\JTL\Media\Image::SIZE_XS)
                                    + $imageCache->getGeneratedBySize(\JTL\Media\Image::SIZE_SM)
                                    + $imageCache->getGeneratedBySize(\JTL\Media\Image::SIZE_MD)
                                    + $imageCache->getGeneratedBySize(\JTL\Media\Image::SIZE_LG)
                                    + $imageCache->getGeneratedBySize(\JTL\Media\Image::SIZE_XL))/ 5)|round:0}
                                </h3>
                                <span style="color:#c7c7c7">{__('imagesInCache')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-item">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('subscription')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    {if $subscription === null}
                        <div class="alert alert-danger alert-sm">
                            <p><i class="fa fa-exclamation-circle"></i> {__('atmNoInfo')}</p>
                        </div>
                    {else}
                        <div class="row">
                            <div class="col {if $subscription->bUpdate === 0}col-md-3{/if} text-center">
                                {if $subscription->bUpdate === 0}
                                    <i class="fal fa-check-circle text-four-times text-success"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">{__('valid')}</h3>
                                {else}
                                    {if $subscription->nDayDiff <= 0}
                                        <i class="fa fa-exclamation-circle text-four-times text-danger"></i>
                                        <h3 style="margin-top:10px;margin-bottom:0">{__('expired')}</h3>
                                    {else}
                                        <i class="fa fa-exclamation-circle text-four-times text-info"></i>
                                        <h3 style="margin-top:10px;margin-bottom:0">{sprintf(__('expiresInXDays'), $subscription->nDayDiff)}</h3>
                                    {/if}
                                {/if}
                            </div>
                            {if $subscription->bUpdate === 0}
                                <div class="col-md-9">
                                    <table class="table table-blank text-x1 last-child">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted text-right"><strong>{__('version')}</strong></td>
                                                <td>
                                                    {formatVersion value=$subscription->oShopversion->nVersion}
                                                    <span class="label label-default">{$subscription->eTyp}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted text-right"><strong>{__('domain')}</strong></td>
                                                <td>{$subscription->cDomain}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted text-right">
                                                    <strong>{__('validUntil')}</strong>
                                                </td>
                                                <td>
                                                    {$subscription->dDownloadBis_DE}
                                                    <span class="text-muted">
                                                        ({$subscription->nDayDiff} {__('days')})
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            {/if}
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        <div class="grid-item">
            <div class="card">
                <div class="card-header">
                    <div class="subheading1">{__('extensions')}</div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    <ul class="infolist list-group list-group-flush">
                        {foreach $extensions as $extension}
                            <li class="list-group-item {if $extension@first}first{elseif $extension@last}last{/if}">
                                <p class="key">
                                    {if $extension->bActive}
                                        <i class="fal fa-check-circle text-success fa-fw" aria-hidden="true"></i>
                                    {else}
                                        <i class="fa fa-times-circle text-warning fa-fw" aria-hidden="true"></i>
                                    {/if}
                                    <span>{$extension->cName}</span>
                                    <span class="float-right">
                                        {if $extension->bActive}
                                            <span class="text-success">{__('active')}</span>
                                        {else}
                                           <a href="{$extension->cURL}"
                                              target="_blank"
                                              rel="noopener">
                                                {__('buyNow')}
                                            </a>
                                        {/if}
                                    </span>
                                </p>
                            </li>
                        {/foreach}
                    </ul>
                </div>
            </div>
        </div>
        {if count($paymentMethods) > 0}
            <div class="grid-item">
                <div class="card">
                    <div class="card-header">
                        <div class="subheading1">{__('paymentTypes')}</div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            {__('paymentTypesWithError')}
                        </div>

                        <table class="table table-condensed table-striped table-blank last-child">
                            <tbody>
                            {foreach $paymentMethods as $s}
                                <tr class="text-vcenter">
                                    <td class="text-left" width="55">
                                        <h4 class="label-wrap">
                                            <span class="label label-danger" style="display:inline-block;width:3em">
                                                {$s->logCount}
                                            </span>
                                        </h4>
                                    </td>
                                    <td class="text-muted"><strong>{$s->cName}</strong></td>
                                    <td class="text-right">
                                        <a class="btn btn-default text-uppercase"
                                           href="{$adminURL}/paymentmethods?a=log&kZahlungsart={$s->kZahlungsart}">
                                            {__('details')}
                                        </a>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        {/if}
        <div class="grid-item">
            <div class="card">
                <div class="card-header">
                    <div class="heading-body">
                        <div class="subheading1">{__('server')}</div>
                    </div>
                    <div class="heading-right">
                        <a href="{$adminURL}/systemcheck" class="btn btn-primary text-uppercase">{__('details')}</a>
                    </div>
                    <hr class="mb-n3">
                </div>
                <div class="card-body">
                    {if count($environmentTests.recommendations) > 0}
                        <table class="table table-condensed table-striped table-blank">
                            <thead>
                            <tr>
                                <th class="col-xs-7">&nbsp;</th>
                                <th class="col-xs-3 text-center">{__('recommendedValue')}</th>
                                <th class="col-xs-2 text-center">{__('yourSystem')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $environmentTests.recommendations as $test}
                                <tr class="text-vcenter">
                                    <td>
                                        <div class="test-name">
                                            {if $test->getDescription() !== null && strlen($test->getDescription()) > 0}
                                                <abbr title="{$test->getDescription()|escape:'html'}">
                                                    {$test->getName()}
                                                </abbr>
                                            {else}
                                                {$test->getName()}
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{$test->getRequiredState()}</td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="alert alert-success">
                            {__('requirementsMet')}
                        </div>
                    {/if}
                </div>
            </div>
        </div>

    </div>
</div>
{include file='tpl_inc/footer.tpl'}
