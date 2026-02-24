{* @var \Plugin\s360_clerk_shop5\src\Entities\DataFeedEntity[] $s360_clerk_store.items *}
<div id="overview" class="settings tab-pane fade active show">
    <div class="subheading1">{__('Stores')}</div>
    <hr class="mb-3">
    <div class="table-responsive">
        <form method="post">
            {$jtl_token}
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{__('ID')}</th>
                        <th>{__('Feed')}</th>
                        <th class="text-center">{__('Sprache')}</th>
                        <th class="text-center">{__('Kundengruppe')}</th>
                        <th class="text-center">{__('Status')}</th>
                        <th class="text-center">{__('Zuletzt geändert')}</th>
                        <th class="text-center">{__('Aktion')}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$s360_clerk_store.items item=$feed}
                        <tr>
                            <td>{$feed->getId()}</td>
                            <td>
                                <a href="{$s360_clerk_store.helpers->getFeedUrl($feed)}" target="_blank" download="{$feed->getHash()}.json">
                                    {$s360_clerk_store.helpers->getFeedUrl($feed)} <small><i class="fa fa-download"></i></small>
                                </a>
                                <button class="btn btn-link copy-feed" data-target="{$s360_clerk_store.helpers->getFeedUrl($feed)}" data-toggle="tooltip" data-original-title="{__('In die Zwischenablage kopieren')|escape}">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </td>
                            <td class="text-center">{$feed->getLanguage()->getLocalizedName()}</td>
                            <td class="text-center">{$feed->getCustomerGroup()->getName()}</td>
                            <td class="text-center">
                                {if $feed->getApiKey() === null}
                                    <span class="badge badge-warning">{__('Warnung')}</span>
                                    <i class="fas fa-info-circle" data-toggle="tooltip" data-original-title="{__('Data Feed ist noch nicht konfiguriert')|escape}"></i>
                                {else}
                                    {if $feed->getState() === 'SUCCESS'}
                                        <span class="badge badge-success">{__('Erfolgreich')}</span>
                                    {elseif $feed->getState() === 'WARNING'}
                                        <span class="badge badge-warning">{__('Warnung')}</span>
                                        {* <span class="badge badge-warning">{__('Nicht konfiguriert')}</span> *}
                                    {elseif $feed->getState() === 'ERROR'}
                                        <span class="badge badge-danger">{__('Fehler')}</span>
                                    {else}
                                        <span class="badge badge-warning">{__('Nicht erstellt')}</span>
                                    {/if}

                                    {if $feed->getStateMessage()}
                                        <i class="fas fa-info-circle" data-toggle="tooltip" data-original-title="{__($feed->getStateMessage())|escape}"></i>
                                    {/if}
                                {/if}
                            </td>
                            <td class="text-center">{if $feed->getUpdatedAt() === null}&dash;{else}{$feed->getUpdatedAt()->format('d.m.Y H:i')}{/if}</td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a class="btn btn-link px-2" href="{$s360_clerk_store.helpers->getFullAdminTabUrl($s360_clerk_store.tabname, ['action' => 'settings', 'id' => $feed->getId()])}" title=""
                                        data-toggle="tooltip" data-original-title="{__('Einstellungen')|escape}">
                                        <span class="icon-hover">
                                            <span class="fal fa-cog"></span>
                                            <span class="fas fa-cog"></span>
                                        </span>
                                    </a>
                                    <button class="btn btn-link px-2" type="submit" name="refresh" value="{$feed->getId()}" title=""
                                        data-toggle="tooltip" data-original-title="{__('Neu generieren')}">
                                        <span class="icon-hover">
                                            <span class="fal fa-refresh"></span>
                                            <span class="fas fa-refresh"></span>
                                        </span>
                                    </button>
                                    <button class="btn btn-link px-2 delete-confirm" type="submit" name="delete" value="{$feed->getId()}"
                                        title="" data-toggle="tooltip" data-modal-body="{__('Soll der Feed wirklich gelöscht werden?')}"
                                        data-original-title="{__('Löschen')}">
                                        <span class="icon-hover">
                                            <span class="fal fa-trash-alt"></span>
                                            <span class="fas fa-trash-alt"></span>
                                        </span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </form>
    </div>

    <div class="row">
        <div class="ml-auto col-sm-6 col-xl-auto">
            <a href="{$s360_clerk_store.helpers->getFullAdminTabUrl($s360_clerk_store.tabname, ['action' => 'add'])}" class="btn btn-info ">{__('Hinzufügen')}</a>
        </div>
    </div>

    <script>
    (function() {
        const copyTriggers = document.querySelectorAll('.copy-feed');
        copyTriggers.forEach((el) => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                navigator.clipboard.writeText(el.getAttribute('data-target'));
            })
        });
    })();
    </script>
</div>