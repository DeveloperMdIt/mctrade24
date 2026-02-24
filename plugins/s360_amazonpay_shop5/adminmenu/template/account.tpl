{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/header.tpl" includeSelfCheck=true}
<div class="lpa-admin-content">
    <div class="row">
        <div class="col-xs-12 col-12">
            <div id="lpa-admin-account">
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">{__('accountPanelRegisterTitle')}</h3>
                        </div>
                        <div class="panel-body card-text">
                            {__('accountPanelRegisterDesc')}<br/><br/>
                            <form method="post" id="lpa-simple-path-form" target="_blank" action="https://payments-eu.amazon.com/register">
                                <input type="hidden" name="spId" value="{$lpaAccount.spId}"/>
                                <input type="hidden" name="onboardingVersion" value="2" />
                                <input type="hidden" name="publicKey" value="{$lpaAccount.spPublicKey}"/>
                                <input type="hidden" name="keyShareURL" value="{$lpaAccount.keyShareURL}"/>
                                <input type="hidden" name="locale" value="{$lpaAccount.spLocale}"/>
                                <input type="hidden" name="spSoftwareVersion" value="{$lpaAccount.spSoftwareVersion}" />
                                <input type="hidden" name="spAmazonPluginVersion" value="{$lpaAccount.spAmazonPluginVersion}" />
                                <input type="hidden" name="merchantCountry" value="{$lpaAccount.merchantCountry}" />
                                <input type="hidden" name="merchantLoginDomains[]" value="{$lpaAccount.allowedJsOrigin}" />
                                <input type="hidden" name="merchantLoginRedirectURLs[]" value="{$lpaAccount.allowedReturnUrl}" />
                                <input type="hidden" name="merchantPrivacyNoticeURL" value="{$lpaAccount.merchantPrivacyNoticeURL}" />
                                <input type="hidden" name="merchantStoreDescription" value="{$lpaAccount.merchantStoreDescription}" />
                                <input type="hidden" name="merchantSandboxIPNURL" value="{$lpaAccount.ipnUrl}"/>
                                <input type="hidden" name="merchantProductionIPNURL" value="{$lpaAccount.ipnUrl}"/>
                                <input type="hidden" name="source" value="SPPL"/>{* SPPL = Coming from platform admin panel *}
                                <input type="hidden" name="ld" value="SPEXDEAPA-JTLPL" />
                                <button type="submit" value="submit" class="btn btn-primary"><i class="fa fa-external-link"></i> {__('accountPanelRegisterCTA')}</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card panel panel-default mb-3">
                    <div class="card-body">
                        <div class="panel-heading">
                            <h3 class="panel-title">{__('accountPanelCheckRegistrationTitle')}</h3>
                        </div>
                        <div class="panel-body">
                            <ul class="s360-unordered-list">
                                {__('accountPanelCheckRegistrationDesc')}
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">{__('accountPanelImportDataTitle')}</h3>
                        </div>
                        <div class="panel-body card-text">
                            <div class="s360-video s360-video-general mb-3">
                                <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;{__('accountPanelWatchIntroVideo')}</div>
                                <div class="s360-video-container">
                                    <iframe width="560" height="315" data-src="https://redirect.solution360.de/?r=lpa5v2vid_intro" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>
                            <form method="post" id="lpa-simple-path-return-form"
                                  action="{$lpaAccount.formTargetUrl}">
                                {$jtl_token}
                                <input type="hidden" name="saveSimplePathJson" value="1"/>
                                <ol>
                                    <li>{__('accountPanelImportDataStep1')}</li>
                                    <li>{__('accountPanelImportDataStep2')}</li>
                                    <li>{__('accountPanelImportDataStep3')}</li>
                                    <li>{__('accountPanelImportDataStep4')}</li>
                                    <li>{__('accountPanelImportDataStep5')}</li>
                                    <li>{__('accountPanelImportDataStep6')}</li>
                                    <li>{__('accountPanelImportDataStep7')}<br/><br/>
                                        <textarea name="simplePathJson" rows="8" class="form-control" placeholder='{
"merchant_id": "MERCHANT ID",
"access_key": "ACCESS KEY ID",
"secret_key": "SECRET ACCESS KEY",
"client_id": "CLIENT ID",
"client_secret": "CLIENT SECRET"
}'></textarea><br/>
                                    </li>
                                    <li>{__('accountPanelImportDataStep8')}<br/><br/>
                                        <button type="submit" value="submit" class="btn btn-primary"><i class="fa fa-save"></i>
                                            {__('accountPanelImportDataImportCTA')}
                                        </button>
                                        <br/><br/>
                                    </li>
                                    <li>{__('accountPanelImportDataStep9')}</li>
                                </ol>
                            </form>
                            <hr>
                            <form method="post" id="lpa-account-settings-form"
                                  action="{$lpaAccount.formTargetUrl}">
                                {$jtl_token}
                                <input type="hidden" name="saveAccountData" value="1"/>
                                <div class="row">
                                    <div class="col-xs-3 col-3 mb-1">
                                        <label for="region">{__('accountPanelImportDataManualRegionLabel')}</label>
                                    </div>
                                    <div class="col-xs-9 col-9 mb-1">
                                        <select id="region" name="region" class="form-control combo">
                                            <option value="de"
                                                    {if !isset($lpaAccount.currentConfig.region) || $lpaAccount.currentConfig.region === "de" || empty($lpaAccount.currentConfig.region)}selected{/if}>
                                                DE
                                            </option>
                                            <option value="uk"
                                                    {if isset($lpaAccount.currentConfig.region) && $lpaAccount.currentConfig.region === "uk"}selected{/if}>
                                                UK
                                            </option>
                                            <option value="us"
                                                    {if isset($lpaAccount.currentConfig.region) && $lpaAccount.currentConfig.region === "us"}selected{/if}>
                                                US
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-xs-3 col-3 mb-1">
                                        <label for="merchant-id">{__('accountPanelImportDataManualMerchantIdLabel')}</label>
                                    </div>
                                    <div class="col-xs-9 col-9 mb-1">
                                        <input id="merchant-id" class="form-control" type="text" name="merchantId"
                                               value="{if isset($lpaAccount.currentConfig.merchantId)}{$lpaAccount.currentConfig.merchantId}{/if}"
                                               size="60"/>
                                    </div>
                                    {* The client id *might* not be needed for the moment *}
                                    <div class="col-xs-3 col-3 mb-1">
                                        <label for="client-id">{__('accountPanelImportDataManualClientIdLabel')}</label>
                                    </div>
                                    <div class="col-xs-9 col-9 mb-1">
                                        <input id="client-id" class="form-control" type="text" name="clientId"
                                               value="{if isset($lpaAccount.currentConfig.clientId)}{$lpaAccount.currentConfig.clientId}{/if}"
                                               size="60"/>
                                    </div>
                                    <div class="col-xs-12 col-12 mb-1">
                                        {__('accountPanelImportDataManualHint')}
                                    </div>
                                    <div class="col-xs-12 col-12 mb-1">
                                        <button name="speichern" type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {__('save')}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">{__('accountPanelKeysTitle')}</h3>
                        </div>
                        <div class="panel-body card-text">
                            {if $lpaAccount.privateKeyExists}
                                {if empty($lpaAccount.spPublicKey)}
                                    {* This implies that the private key was manually set *}
                                    <div class="alert alert-success">{__('accountPanelKeysPrivateKeyExists')}</div>
                                {else}
                                    {* This implies that the private/public key was generated *}
                                    <div class="alert alert-success">{__('accountPanelKeysPrivateKeyGenerated')}</div>
                                {/if}
                            {else}
                                <div class="alert alert-warning">{__('accountPanelKeysPrivateKeyMissing')}</div>
                            {/if}
                            <ul class="nav nav-tabs" id="create-key-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#create-key-manual-tab" role="tab" aria-controls="profile" aria-selected="false">{__('accountPanelKeysTabSaveTitle')}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#create-key-automatic-tab" role="tab" aria-controls="home" aria-selected="true">{__('accountPanelKeysTabCreateTitle')}</a>
                                </li>
                            </ul>
                            <div class="tab-content mb-3 px-0" style="box-shadow: none;">
                                <div class="mb-3 tab-pane fade show active" id="create-key-manual-tab">
                                    <div class="mt-1 mb-3 text-muted small">
                                        {__('accountPanelKeysDescManual')}
                                    </div>
                                    <ol>
                                        <li>{__('accountPanelKeysManualStep1')}</li>
                                        <li>{__('accountPanelKeysManualStep2')}</li>
                                        <li>{__('accountPanelKeysManualStep3')}</li>
                                        <li>{__('accountPanelKeysManualStep4')}</li>
                                        <li>{__('accountPanelKeysManualStep5')}</li>
                                        <li>{__('accountPanelKeysManualStep6')}</li>
                                        <li>{__('accountPanelKeysManualStep7')}</li>
                                        <li>{__('accountPanelKeysManualStep8')}</li>
                                    </ol>
                                    <form method="post" id="lpa-publickey-manual-form"
                                          action="{$lpaAccount.formTargetUrl}">
                                        {$jtl_token}
                                        <input type="hidden" name="saveManualKeys" value="1"/>
                                        <div class="row">
                                            <div class="col-xs-12 col-12">
                                                <label for="private-key" class="mb-1">{__('accountPanelKeysLabelPrivateKey')}</label><br/>
                                                <textarea name="privateKey" class="form-control" id="private-key" rows="6"></textarea>
                                            </div>
                                            <div class="col-xs-12 col-12 mt-2">
                                                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i>
                                                    {__('accountPanelKeysSaveCTA')}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="mb-3 tab-pane fade" id="create-key-automatic-tab">
                                    <div class="mt-1 mb-3 text-muted small">
                                        {__('accountPanelKeysDescGenerate')}
                                    </div>
                                    <ol>
                                        <li>{__('accountPanelKeysGenerateStep1')}</li>
                                        <li>{__('accountPanelKeysGenerateStep2')}</li>
                                        <li>{__('accountPanelKeysGenerateStep3')}</li>
                                        <li>{__('accountPanelKeysGenerateStep4')}</li>
                                        <li>{__('accountPanelKeysGenerateStep5')}</li>
                                        <li>{__('accountPanelKeysGenerateStep6')}</li>
                                        <li>{__('accountPanelKeysGenerateStep7')}</li>
                                        <li>{__('accountPanelKeysGenerateStep8')}</li>
                                        <li>{__('accountPanelKeysGenerateStep9')}</li>
                                    </ol>
                                    <div id="lpa-create-key-publickey" class="mb-3">
                                        <div class="mt-1 mb-3">
                                            {__('accountPanelKeysCurrentPublicKey')}
                                        </div>
                                        <div id="create-key-publickey-container">
                                            {if !empty($lpaAccount.publicKey)}
                                                <pre>{$lpaAccount.publicKey}</pre>
                                            {else}
                                                <i>{__('accountPanelKeysNoPublicKey')}</i>
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="text-danger mb-2">{__('accountPanelKeysCreateWarning')}</div>
                                    <button id="create-key-button" class="btn btn-primary"><i class="fa fas fa-cog"></i>
                                        {__('accountPanelKeysCreateCTA')}
                                    </button>
                                    <div id="create-key-feedback" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">{__('accountPanelPublicKeyIdTitle')}</h3>
                        </div>
                        <div class="panel-body card-text">
                            <div class="mt-1 mb-3 text-muted small">
                                {__('accountPanelPublicKeyIdRequestHint')}
                            </div>
                            <ol>
                                <li>{__('accountPanelPublicKeyIdStep1')}</li>
                                <li>{__('accountPanelPublicKeyIdStep2')}</li>
                                <li>{__('accountPanelPublicKeyIdStep3')}</li>
                                <li>{__('accountPanelPublicKeyIdStep4')}</li>
                            </ol>
                            <form method="post" id="lpa-publickey-id-form"
                                  action="{$lpaAccount.formTargetUrl}">
                                {$jtl_token}
                                <input type="hidden" name="savePublicKeyId" value="1"/>
                                <div class="row">
                                    <div class="col-xs-3 col-3 mb-1">
                                        <label for="public-key-id">{__('accountPanelPublicKeyIdLabel')}</label>
                                    </div>
                                    <div class="col-xs-9 col-9 mb-1">
                                        <input id="public-key-id" class="form-control" type="text" name="publicKeyId"
                                               value="{if isset($lpaAccount.publicKeyId)}{$lpaAccount.publicKeyId}{/if}"/>
                                    </div>
                                </div>
                                <button type="submit" value="submit" class="btn btn-primary"><i class="fa fa-save"></i>
                                    {__('accountPanelPublicKeyIdCTA')}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="card-title">
                            <h3>{__('accountPanelCheckDataTitle')}</h3>
                        </div>
                        <div class="card-text">
                            <div class="row">
                                <div class="col-xs-12 col-12 mb-1">
                                    <button id="check-access-button" class="btn btn-primary mb-1"><i class="fa fa-search"></i>
                                        {__('accountPanelCheckDataCTA')}
                                    </button>
                                    <div id="check-access-feedback" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card panel panel-default mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">{__('accountPanelIpnTitle')}</h3>
                        </div>
                        <div class="panel-body card-text">
                            <div class="s360-video mb-3">
                                <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;{__('accountPanelWatchVideo')}</div>
                                <div class="s360-video-container">
                                    <iframe width="560" height="315" data-src="https://redirect.solution360.de/?r=lpa5v2vid_ipn" frameborder="0"
                                            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen></iframe>
                                </div>
                            </div>
                            <div class="mb-1">
                                {__('accountPanelIpnLabel')}
                            </div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input type="text" id="s360-ipn-url-ro" class="form-control" readonly="readonly" value="{$lpaAccount.ipnUrl}" style="max-width: 50%;"/>
                                    <span class="input-group-append input-group-btn">
                                     <button title="{__('accountPanelCopyToClipboard')}" class="btn btn-default s360-copy-to-clipboard" data-target="#s360-ipn-url-ro"><i class="fa fa-clipboard"></i>&nbsp;</button>
                                </span>
                                </div>
                            </div>
                            <ol class="s360-ordered-list">
                                <li>{__('accountPanelIpnStep1')}</li>
                                <li>{__('accountPanelIpnStep2')}</li>
                                <li>{__('accountPanelIpnStep3')}</li>
                                <li>{__('accountPanelIpnStep4')}</li>
                                <li>{__('accountPanelIpnStep5')}</li>
                                <li>{__('accountPanelIpnStep6')}</li>
                                <li>{__('accountPanelIpnStep7a')}<a href="{$lpaAccount.ipnUrl}?lpacheck" target="_blank">{__('lpaClickHere')}</a>{__('accountPanelIpnStep7b')}</li>
                            </ol>
                        </div>
                    </div>
                </div>
                <div class="card panel panel-default mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">{__('accountPanelJsOriginTitle')}</h3>
                        </div>
                        <div class="panel-body card-text">
                            <div class="s360-video mb-3">
                                <div class="s360-video-title"><i class="fa fa-eye"></i>&nbsp;{__('accountPanelWatchVideo')}</div>
                                <div class="s360-video-container">
                                    <iframe width="560" height="315" data-src="https://redirect.solution360.de/?r=lpa5v2vid_jsorigin" frameborder="0"
                                            allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen></iframe>
                                </div>
                            </div>
                            <div class="mb-1">
                                {__('accountPanelJsOriginLabel')}
                            </div>
                            <div class="mb-1">
                                <div class="input-group">
                                    <input type="text" id="s360-js-origin-url-ro" class="form-control" readonly="readonly" value="{$lpaAccount.allowedJsOrigin}" style="max-width: 50%;"/>
                                    <span class="input-group-append input-group-btn">
                                     <button title="{__('accountPanelCopyToClipboard')}" class="btn btn-default s360-copy-to-clipboard" data-target="#s360-js-origin-url-ro"><i class="fa fa-clipboard"></i>&nbsp;</button>
                                </span>
                                </div>
                            </div>
                            <ol class="s360-ordered-list">
                                <li>{__('accountPanelJsOriginStep1')}</li>
                                <li>{__('accountPanelJsOriginStep2')}</li>
                                <li>{__('accountPanelJsOriginStep3')}</li>
                                <li>{__('accountPanelJsOriginStep4')}</li>
                                <li>{__('accountPanelJsOriginStep5')}</li>
                                <li>{__('accountPanelJsOriginStep6')}</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/footer.tpl"}