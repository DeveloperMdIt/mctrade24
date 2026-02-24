<style>
    .text-longword-break{
        word-break: break-all;
    }
</style>
<div id="ppcInfos" class="container-fluid">
    <div class="d-flex justify-content-start align-items-center">
        <div class="subheading1">
            {__('PayPal Checkout')}
        </div>
    </div>
    <hr class="mb-3">
    <form method="post" action="" enctype="multipart/form-data" name="wizard" class="settings navbar-form">
        {$jtl_token}
        {assign info_text value=__('webhook_info')}
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
        {if $isWebhookConfigured === false}
        <p>
            {$info_text}
        </p>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12 col-xl-8">
                        {__('Webhook ist nicht vorhanden. Soll er erstellt werden?')}
                    </div>
                    <div class="col-sm-12 col-xl-auto">
                        <button type="submit" class="btn btn-primary btn-block" name="task" value="createWebhook">
                            <i class="fal fa-save"></i> {__('create')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {else}
            <p>
                {$info_text}
            </p>
            <table class="table" id="paypal-webhook">
                <thead>
                <tr>
                    <th>{__('Webhook Typ')}</th>
                    <th class="text-center">{__('Webhook URL')}</th>
                    <th class="text-center">{__('Webhook ID')}</th>
                    <th class="text-center">{__('Registriert')}</th>
                    <th class="text-center">{__('Aktionen')}</th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$webhookEvents item=event}
                <tr class="ppc">
                    <td>{$event->name}</td>
                    <td class="text-longword-break">{$webhookURL}</td>
                    <td>{$webhookID}</td>
                    <td class="text-center"><i class="fa {if $isWebhookRegistred}fa-check text-success{else}fa-times text-danger{/if}"></i></td>
                    <td class="text-center">
                        <div class="btn-group">
                            <button name="task"
                                    type="submit"
                                    value="refreshWebhook"
                                    class="btn btn-link px-2"
                                    title="{__('Neu registrieren')}"
                                    data-toggle="tooltip"
                                    aria-expanded="false">
                                <span class="icon-hover">
                                    <span class="fal fa-refresh"></span>
                                    <span class="fas fa-refresh"></span>
                                </span>
                            </button>
                            <button name="task"
                                    type="submit"
                                    value="deleteWebhook"
                                    class="btn btn-link px-2"
                                    title="{__('delete')}"
                                    data-toggle="tooltip"
                                    aria-expanded="false">
                                <span class="icon-hover">
                                    <span class="fal fa-trash"></span>
                                    <span class="fas fa-trash"></span>
                                </span>
                            </button>
                        </div>
                    </td>
                </tr>
                {/foreach}
                </tbody>
            </table>
        {/if}
    </form>
</div>