<form method="post" enctype="multipart/form-data" class="shipmentState">
    {$jtl_token}
    <input type="hidden" name="kPlugin" value="{$kPlugin}" />
    <input type="hidden" name="kPluginAdminMenu" value="{$kPluginAdminMenu}" />
    <input type="hidden" name="id" value="" id="deleteID" />
    {if count($mappingItems) > 0}
        <div class="table-responsive card-body">
            <span class="subheading1">{__('Zuordnung von Versandart und Versanddienstleister')}</span>
            <hr class="mb-3">
            <table class="list table table-striped table-align-top">
                <thead>
                <tr>
                    <th class="th-2">{__('Versandart in JTL-Wawi')}</th>
                    <th class="th-3">{__('Versanddienstleister bei PayPal')}</th>
                    <th class="th-4"></th>
                </tr>
                </thead>
                <tbody>
                {foreach $mappingItems as $mappingItem}
                    <tr>
                        <td>{htmlentities($mappingItem->carrier_wawi)}</td>
                        <td>{htmlentities(__($mappingItem->carrier_paypal))}</td>
                        <td class="text-center">
                            <div class="btn-group">
                                <span class="btn btn-link px-2 mappings-edit" title="{__('modify')}"
                                      data-toggle="modal"
                                      data-target="#mappings-modal"
                                      data-carrier_wawi="{htmlentities($mappingItem->carrier_wawi)}"
                                      data-carrier_paypal="{htmlentities($mappingItem->carrier_paypal)}"
                                      data-id="{$mappingItem->id}">
                                    <span class="icon-hover">
                                        <span class="fal fa-edit"></span>
                                        <span class="fas fa-edit"></span>
                                    </span>
                                </span>
                                <button type="submit" name="task" value="deleteCarrierMapping"
                                        class="btn btn-link px-2 delete-confirm mappings-delete"
                                        title="{__('delete')}"
                                        data-toggle="tooltip"
                                        data-id="{$mappingItem->id}"
                                        data-modal-body="{__('Soll das Carrier-Mapping gelöscht werden?')}"
                                >
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
        </div>
    {else}
        <div class="alert alert-info">{__('Es wurden noch keine Carrier-Mappings angelegt')}</div>
    {/if}

    <div class="save-wrapper">
        <div class="row">
            <div class="mr-auto col-sm-6 col-xl-auto">
                <button type="button"
                        class="btn btn-primary btn-block mappings-new"
                        data-toggle="modal"
                        data-target="#mappings-modal">
                    <i class="fal fa-save mr-0 mr-lg-2"></i> {__('Neue Zuordnung')}
                </button>
            </div>
        </div>
    </div>
</form>
