<div class="jtlsearch_actioncolumn">
    <div class="jtlsearch_inner">
        <a class="btn btn-primary" href="{$pluginAdminURL}"><i class="fa fa-refresh"></i> {__('refresh')}</a>
    </div>
</div>
<div class="jtlsearch_infocolumn">
    <div class="jtlsearch_inner">
        {if $indexStatus|@count > 0}
            <div class="alert alert-info">
                {sprintf(__('yourShopID'), $indexStatus.0->kUserShop)}
            </div>
            <ul class="list-group infolist list-unstyled">
                {foreach $indexStatus as $status}
                    {if $status->nItemCount > 0}
                        <li class="list-group-item">
                            <h4 class="label-wrap">
                                <span class="label label-success">
                                    {sprintf(__('infoIndexLanguageAvailable'), $status->cLanguageISO)}
                                </span>
                            </h4>
                        </li>
                    {else}
                        <li>
                            <h4 class="label-wrap">
                                <span class="label label-info">
                                    {sprintf(__('infoIndexNoDataImported'), $status->cLanguageISO)}
                                </span>
                            </h4>
                        </li>
                    {/if}
                {/foreach}
            </ul>
        {else}
            <div class="alert alert-info">{__('infoNoDataExport')}</div>
        {/if}
    </div>
</div>
<div class="jtlsearch_clear"></div>
