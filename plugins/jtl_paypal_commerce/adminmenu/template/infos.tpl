<div id="ppcInfos" class="container-fluid">
    <div class="d-flex justify-content-start align-items-center">
        <div class="subheading1">
            {__('PayPal Checkout')}
        </div>
        {include file="$basePath/adminmenu/template/snippets/workingModeSwitch.tpl" switchPos="infos"}
    </div>
    <hr class="mb-3">
    {if !empty($paymentAlertList)}
        <div id="alert-list">
            {foreach $paymentAlertList as $alert}
                {$alert->display()}
            {/foreach}
        </div>
    {/if}
    <form method="post" enctype="multipart/form-data" name="wizard" class="settings navbar-form">
        {$jtl_token}
        <input type="hidden" name="kPlugin" value="{$kPlugin}" />
        {if $isAuthConfigured === false}
        <div class="card">
            <div class="card-body">
                <div class="col-md-12 col-lg-6 offset-lg-3 align-items-center mb-2 d-flex justify-content-center align-items-center flex-column">
                    {if $cookieSettings['samesite'] === "Strict"}
                    <p class="small text-danger text-justify mb-2">
                        {__('Hinweis: Sie müssen sich nach dem Onboarding (Seite lädt automatisch neu) erneut im Admin-Bereich einloggen!')}
                    </p>
                    {/if}
                    <p class="small text-info text-justify mb-2">
                        {__('Onboarding Url Information')}
                    </p>
                    <a id="ppc-onboarding-link" style="" data-paypal-onboard-complete="onboardedCallback"
                       href="{strip}{$onboardingUri}{/strip}"
                       class="btn btn-primary mx-auto d-block disabled"
                       data-paypal-button="true"
                       data-securebuttonmsg="Weiter"> <i class="fab fa-paypal fa-2x align-middle mr-1"></i>
                        {__('JTL-Shop jetzt mit PayPal verbinden')}</a>
                    <div class="ppc-onboarding-loading-container mt-2 d-none flex-column">
                        <div class="spinner-border mx-auto" id="ppc-onboarding-loading-spinner" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <div class="ppc-onboarding-loading-message">{__('Bitte warten, Ihre Daten werden importiert. Die Seite wird automatisch neu geladen.')}</div>
                    </div>
                    <script id="paypal-js" src="{$scriptUrl}"></script>
                    <script>
                        let fetchUrl = '{$partnerCredentials['fetchUrl']}';
                        let nonce = '{$partnerCredentials['nonce']}';
                        {literal}
                        function onboardedCallback(authCode, sharedId) {
                            $('.ppc-onboarding-loading-container').removeClass('d-none').addClass('d-flex');
                            $('#ppc-onboarding-link').addClass('disabled');
                            $('#manual-credentials-toggle').addClass('disabled');

                            fetch(fetchUrl + '?authCode=' + authCode + '&sharedID=' + sharedId + '&nonce=' + nonce, {
                                method: "get"
                            }).then((res) => {
                                console.log('done');
                            }).catch((err)=>{
                                console.log(err);
                            })
                        }
                        $(document).ready(function() {
                           $('#ppc-onboarding-link').removeClass('disabled');
                        })
                        {/literal}
                    </script>
                </div>
                <div class="col-md-12 col-lg-6 offset-lg-3 align-items-center mb-3 d-flex justify-content-center align-items-center">
                    <a id="manual-credentials-toggle" class="btn btn-link text-muted mx-auto" data-toggle="collapse" href="#collapseManualCredentials" role="button" aria-expanded="false" aria-controls="collapseManualCredentials">
                        {__('Zugangsdaten manuell hinterlegen')}
                    </a>
                </div>
                <div class="collapse" id="collapseManualCredentials">
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right order-1" for="clientID">{__('Merchant ID')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 text-sm-right">
                            <input type="text" class="form-control" name="ppcManualCredentials[merchantID]" id="merchantID" value="{$ppcMerchantID}" placeholder="" />
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Geben Sie hier Ihre Händler-ID ein.')}">
                                <span class="fas fa-info-circle fa-fw"></span>
                            </span>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center">
                        <label class="col col-sm-4 col-form-label text-sm-right order-1" for="clientID">{__('Client ID')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 text-sm-right">
                            <input type="text" class="form-control" name="ppcManualCredentials[clientID]" id="clientID" value="" placeholder="" />
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Geben Sie hier Ihre API-Client-ID ein.')}">
                                <span class="fas fa-info-circle fa-fw"></span>
                            </span>
                        </div>
                    </div>
                    <div class="form-group form-row align-items-center ">
                        <label class="col col-sm-4 col-form-label text-sm-right order-1" for="clientSecret">{__('Client Secret')}:</label>
                        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 text-sm-right">
                            <input type="text" class="form-control" name="ppcManualCredentials[clientSecret]" id="clientSecret" value="" placeholder="" />
                        </div>
                        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
                            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Geben Sie hier Ihre API-Secret ein.')}">
                                <span class="fas fa-info-circle fa-fw"></span>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer save-wrapper">
                        <div class="row">
                            <div class="col-sm-6 col-xl-auto">
                                <button type="submit" class="btn btn-primary btn-block" name="task" value="saveCredentialsManually">
                                    <i class="fal fa-save"></i> {__('Speichern')}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
    {/if}
    {if $isAuthConfigured === true}
    <table class="table" id="paypal-test-credentials">
        <thead>
        <tr>
            <th>{__('Payment method')}</th>
            <th>
                {__('JTL-Wawi Zahlartname')}
                <span data-html="true" data-toggle="tooltip" data-placement="left" title="{__('JTL-Wawi Zahlartname Beschreibung')}" data-original-title="{__('JTL-Wawi Zahlartname Beschreibung')}">
                    <span class="fas fa-info-circle fa-fw"></span>
                </span>
            </th>
            <th class="text-center">{__('Mode')}</th>
            <th class="text-center">{__('Verfügbar')}</th>
            <th class="text-center">{__('Konfiguration')}</th>
            <th class="text-center">{__('Aktionen')}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$ppc_methods item=payment}
            {assign var="methode" value=$payment->getMethod()}
            <tr class="ppc">
                <td>{__('PayPal Checkout')}: {__($payment->mappedLocalizedPaymentName())}</td>
                <td>{$methode->getName()}</td>
                <td id="{$methode->getMethodID()}_ppc-modus" class="text-center">{__($ppc_mode)}</td>
                <td id="{$methode->getMethodID()}_ppc-connectable" class="text-center"><i class="fa fa-spinner fa-spin"></i></td>
                <td id="{$methode->getMethodID()}_payment-linked" class="text-center"><i class="fa fa-spinner fa-spin"></i></td>
                <td id="refresh-button" class="text-center">
                    <div class="btn-group">
                        <button name="{$methode->getMethodID()}_reload"
                                type="button"
                                class="btn btn-link px-2"
                                title="{__('update')}"
                                data-toggle="tooltip"
                                aria-expanded="false">
                            <span class="icon-hover">
                                <span class="fal fa-refresh"></span>
                                <span class="fas fa-refresh"></span>
                            </span>
                        </button>
                        {if !empty($ppcToken)}
                        <button name="{$methode->getMethodID()}_config"
                                type="button"
                                class="btn btn-link px-2"
                                title="{__('Bearbeiten')}"
                                data-toggle="tooltip"
                                data-panel="{$payment->getSettingPanel()}"
                                aria-expanded="false">
                            <span class="icon-hover">
                                <span class="fal fa-cogs"></span>
                                <span class="fas fa-cogs"></span>
                            </span>
                        </button>
                        {else}
                            <form method="post" enctype="multipart/form-data" name="wizard" class="settings navbar-form">
                                {$jtl_token}
                                <input type="hidden" name="kPlugin" value="{$kPlugin}" />
                                <button name="task"
                                        value="resetCredentialsController"
                                        type="submit"
                                        class="btn btn-link px-2"
                                        title="{__('Anmeldedaten zurücksetzen')}"
                                        data-toggle="tooltip">
                                    <span class="icon-hover">
                                        <span class="fal fa-chain-broken"></span>
                                        <span class="fal fa-chain-broken"></span>
                                    </span>
                                </button>
                            </form>
                        {/if}
                    </div>
                </td>
            </tr>
                {append var='methode_ids' value=$methode->getMethodID()}
            {/foreach}
        </tbody>
    </table>

    <script>
        let ppc_methode_ids = {json_encode($methode_ids)};
        let tabItems = {json_encode($oPlugin->getAdminMenu()->getItems()->toArray())};
        let settingsTabId = tabItems.filter((item)=>item.name==='Einstellungen').map((item)=>item.kPluginAdminMenu)[0];
        {literal}
        async function ppc_infos(place) {
            let spinnerOff = true;
            ioCall(
                'jtl_ppc_infos_handleAjax',
                [place],
                ()=>{},
                ()=>{},
                window,
                spinnerOff
            );
        }
        function ppc_infos_reload(mid) {
            // spinners on
            $('#'+mid+'_ppc-connectable').html('<i class="fa fa-spinner fa-spin"></i>');
            $('#'+mid+'_payment-linked').html('<i class="fa fa-spinner fa-spin"></i>');
            // io-calls (spinners goes off after that)
            ppc_infos(mid+'_ppc-connectable');
            ppc_infos(mid+'_payment-linked');
        }
        ppc_methode_ids.forEach(function(mid) {
            ppc_infos(mid+'_ppc-connectable');
            ppc_infos(mid+'_payment-linked');

            $("button[name='"+mid+"_reload']").click(function() {
                $(this).tooltip('hide');
                ppc_infos_reload(mid);
            });

            $("button[name='"+mid+"_config']").click(function(element) {
                $(this).tooltip('hide');
                element.preventDefault();
                $('.tab-link-'+settingsTabId).trigger('click');
                let panel = $(this).data('panel');
                if (panel !== '') {
                    $('#' + panel + '-tab').trigger('click');
                }
            });
        });
        {/literal}
    </script>
    {/if}
</div>
