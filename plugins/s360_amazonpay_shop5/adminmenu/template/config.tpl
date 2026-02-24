{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/header.tpl" includeSelfCheck=true}
<div class="lpa-admin-content">
    <div class="row">
        <div class="col-xs-12 col-12">
            <form method="post" action="{$lpaConfig.formTargetUrl}">
                {$jtl_token}
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">{__('lpaGeneral')}
                                <a class="lpa-reset-options pull-right" title="{__('lpaResetSettings')}" onclick="window.lpaAdmin.reset(this);return false;"><i class="fa fas fa-undo"></i></a>
                            </h3>
                            <hr class="mt-1 mb-1">
                        </div>
                        <div class="panel-body card-text">
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="environment">{__('lpaEnvironmentLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select id="environment" name="environment" class="form-control combo" data-default="{$lpaConfig.defaultConfig.environment}">
                                        <option value="sandbox"
                                                {if !isset($lpaConfig.currentConfig.environment) || $lpaAccount.currentConfig.environment === "sandbox" || empty($lpaConfig.currentConfig.environment)}selected{/if}>
                                            {__('lpaEnvironmentSandbox')}
                                        </option>
                                        <option value="production"
                                                {if isset($lpaConfig.currentConfig.environment) && $lpaConfig.currentConfig.environment !== "sandbox" && !empty($lpaConfig.currentConfig.environment)}selected{/if}>
                                            {__('lpaEnvironmentProduction')}
                                        </option>
                                    </select>
                                    <small class="form-text help-block text-muted">
                                        {__('lpaEnvironmentHint')}
                                    </small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-hidden-button-mode">{__('lpaHiddenButtonModeLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="hiddenButtonMode" id="lpa-config-hidden-button-mode" data-default="{$lpaConfig.defaultConfig.hiddenButtonMode}" {if $lpaConfig.currentConfig.hiddenButtonMode} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaHiddenButtonModeHint')}
                                    </small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-capture-mode">{__('lpaCaptureModeLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="captureMode" id="lpa-config-capture-mode" data-default="{$lpaConfig.defaultConfig.captureMode}">
                                        <option value="immediate"{if $lpaConfig.currentConfig.captureMode === 'immediate'} selected="selected"{/if}>{__('lpaCaptureModeImmediate')}</option>
                                        <option value="onShippingPartial"{if $lpaConfig.currentConfig.captureMode === 'onShippingPartial'} selected="selected"{/if}>{__('lpaCaptureModeOnPartialDelivery')}</option>
                                        <option value="onShippingComplete"{if $lpaConfig.currentConfig.captureMode === 'onShippingComplete'} selected="selected"{/if}>{__('lpaCaptureModeOnCompleteDelivery')}</option>
                                    </select>
                                    <small class="form-text help-block text-muted">
                                        {__('lpaCaptureModeHint')}
                                    </small>
                                </div>
                            </div>
                            {* EVO was discontinued and is not an option anymore -
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-template-mode">{__('lpaTemplateModeLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="templateMode" id="lpa-config-template-mode" data-default="{$lpaConfig.defaultConfig.templateMode}">
                                        <option value="nova"{if $lpaConfig.currentConfig.templateMode === 'nova'} selected="selected"{/if}>{__('lpaTemplateModeNova')}</option>
                                        <option value="evo"{if $lpaConfig.currentConfig.templateMode === 'evo'} selected="selected"{/if}>{__('lpaTemplateModeEvo')}</option>
                                    </select>
                                    <small class="form-text help-block text-muted">
                                        {__('lpaTemplateModeHint')}
                                    </small>
                                </div>
                            </div>
                            *}
                        </div>
                    </div>
                </div>
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">
                                {__('lpaCheckout')}
                                <a class="lpa-reset-options pull-right" title="{__('lpaResetSettings')}" onclick="window.lpaAdmin.reset(this);return false;"><i class="fa fas fa-undo"></i></a>
                            </h3>
                            <small class="form-text help-block text-muted">{__('lpaCheckoutHint')}</small>
                            <hr class="mt-1 mb-1">
                        </div>
                        <div class="panel-body card-text">
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-account-creation">{__('lpaAccountCreationLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="accountCreation" id="lpa-config-account-creation" data-default="{$lpaConfig.defaultConfig.accountCreation}">
                                        <option value="always"{if $lpaConfig.currentConfig.accountCreation === 'always'} selected="selected"{/if}>{__('lpaAccountCreationAlways')}</option>
                                        <option value="optional"{if $lpaConfig.currentConfig.accountCreation === 'optional'} selected="selected"{/if}>{__('lpaAccountCreationOptional')}</option>
                                        <option value="never"{if $lpaConfig.currentConfig.accountCreation === 'never'} selected="selected"{/if}>{__('lpaAccountCreationNever')}</option>
                                    </select>
                                    <small class="form-text help-block text-muted">
                                        {__('lpaAccountCreationHint')}
                                    </small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-password-creation">Kundenkonto Passwort generieren</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="passwordCreation" id="lpa-config-password-creation" data-default="{$lpaConfig.defaultConfig.passwordCreation}">
                                        <option value="generate"{if $lpaConfig.currentConfig.passwordCreation === 'generate'} selected="selected"{/if}>automatisch generieren</option>
                                        <option value="input"{if $lpaConfig.currentConfig.passwordCreation === 'input'} selected="selected"{/if}>Eingabe durch Kunde</option>
                                    </select>
                                    <small class="form-text help-block text-muted">Steuert, ob bei der Erzeugung eines Kundenkontos f&uuml;r den Kunden ein Passwort generiert werden soll, oder ob der Kunde selbst ein Passwort f&uuml;r das Shop-Konto festlegen soll.</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-show-comment-field">{__('lpaShowCommentFieldLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="showCommentField" data-default="{$lpaConfig.defaultConfig.showCommentField}" id="lpa-config-show-comment-field"{if $lpaConfig.currentConfig.showCommentField} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaShowCommentFieldHint')}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">
                                {__('lpaPayButtons')}
                                <a class="lpa-reset-options pull-right" title="{__('lpaResetSettings')}" onclick="window.lpaAdmin.reset(this);return false;"><i class="fa fas fa-undo"></i></a>
                            </h3>
                            <hr class="mt-1 mb-1">
                        </div>
                        <div class="panel-body card-text">
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-show-payment-method">{__('lpaShowPaymentMethodLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="showPaymentMethod" data-default="{!$lpaConfig.defaultConfig.hidePaymentMethod}" id="lpa-config-show-payment-method"{if !$lpaConfig.currentConfig.hidePaymentMethod} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaShowPaymentMethodHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option-header row mb-2">
                                <div class="col-12 col-xs-12">
                                    <hr>
                                </div>
                                <div class="col-xs-3 col-3">&nbsp;</div>
                                <div class="col-xs-3 col-3">{__('lpaPayButtonsGeneral')}
                                    <hr class="mt-1 mb-1">
                                </div>
                                <div class="col-xs-3 col-3">{__('lpaPayButtonsDetail')}
                                    <hr class="mt-1 mb-1">
                                </div>
                                <div class="col-xs-3 col-3">{__('lpaPayButtonsCategory')}
                                    <hr class="mt-1 mb-1">
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPayButtonsActivateLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    {* This is not an option - the general button is always active -
                                    <input type="checkbox" value="on" name="buttonPayActive" data-default="{$lpaConfig.defaultConfig.buttonPayActive}" id="lpa-config-button-pay-active"{if $lpaConfig.currentConfig.buttonPayActive} checked="checked"{/if}> {__('lpaActive')}
                                    *}
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input type="checkbox" value="on" name="buttonPayDetailActive" data-default="{$lpaConfig.defaultConfig.buttonPayDetailActive}" id="lpa-config-button-pay-detail-active"{if $lpaConfig.currentConfig.buttonPayDetailActive} checked="checked"{/if}> {__('lpaActive')}
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input type="checkbox" value="on" name="buttonPayCategoryActive" data-default="{$lpaConfig.defaultConfig.buttonPayCategoryActive}" id="lpa-config-button-pay-category-active"{if $lpaConfig.currentConfig.buttonPayCategoryActive} checked="checked"{/if}> {__('lpaActive')}
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPayButtonsCssColumnsLabel')}</label>
                                    <small class="form-text help-block text-muted">{__('lpaPayButtonsCssColumnsHint')}</small>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input class="form-control" name="buttonPayCssColumns" type="text" data-default="{$lpaConfig.defaultConfig.buttonPayCssColumns}" id="lpa-config-button-pay-css-columns"{if isset($lpaConfig.currentConfig.buttonPayCssColumns)} value="{$lpaConfig.currentConfig.buttonPayCssColumns}"{/if}/>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input class="form-control" name="buttonPayDetailCssColumns" type="text" data-default="{$lpaConfig.defaultConfig.buttonPayDetailCssColumns}" id="lpa-config-button-pay-detail-css-columns"{if isset($lpaConfig.currentConfig.buttonPayDetailCssColumns)} value="{$lpaConfig.currentConfig.buttonPayDetailCssColumns}"{/if}/>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input class="form-control" name="buttonPayCategoryCssColumns" type="text" data-default="{$lpaConfig.defaultConfig.buttonPayCategoryCssColumns}" id="lpa-config-button-pay-category-css-columns"{if isset($lpaConfig.currentConfig.buttonPayCategoryCssColumns)} value="{$lpaConfig.currentConfig.buttonPayCategoryCssColumns}"{/if}/>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPayButtonsHeightLabel')}</label>
                                    <small class="form-text help-block text-muted">{__('lpaPayButtonsHeightHint')}</small>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input class="form-control" name="buttonPayHeight" type="number" data-default="{$lpaConfig.defaultConfig.buttonPayHeight}" id="lpa-config-button-pay-height"{if isset($lpaConfig.currentConfig.buttonPayHeight)} value="{$lpaConfig.currentConfig.buttonPayHeight}"{/if}/>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input class="form-control" name="buttonPayDetailHeight" type="number" data-default="{$lpaConfig.defaultConfig.buttonPayDetailHeight}" id="lpa-config-button-pay-detail-height"{if isset($lpaConfig.currentConfig.buttonPayDetailHeight)} value="{$lpaConfig.currentConfig.buttonPayDetailHeight}"{/if}/>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input class="form-control" name="buttonPayCategoryHeight" type="number" data-default="{$lpaConfig.defaultConfig.buttonPayCategoryHeight}" id="lpa-config-button-pay-category-height"{if isset($lpaConfig.currentConfig.buttonPayCategoryHeight)} value="{$lpaConfig.currentConfig.buttonPayCategoryHeight}"{/if}/>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPayButtonsColorLabel')}</label>
                                    <small class="form-text help-block text-muted">{__('lpaPayButtonsColorHint')}</small>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <select class="form-control" name="buttonPayColor" data-default="{$lpaConfig.defaultConfig.buttonPayColor}" id="lpa-config-button-pay-color">
                                        <option value="Gold"{if $lpaConfig.currentConfig.buttonPayColor === 'Gold'} selected="selected"{/if}>{__('lpaButtonColorGold')}</option>
                                        <option value="LightGray"{if $lpaConfig.currentConfig.buttonPayColor === 'LightGray'} selected="selected"{/if}>{__('lpaButtonColorLightGray')}</option>
                                        <option value="DarkGray"{if $lpaConfig.currentConfig.buttonPayColor === 'DarkGray'} selected="selected"{/if}>{__('lpaButtonColorDarkGray')}</option>
                                    </select>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <select class="form-control" name="buttonPayDetailColor" data-default="{$lpaConfig.defaultConfig.buttonPayDetailColor}" id="lpa-config-button-pay-detail-color">
                                        <option value="Gold"{if $lpaConfig.currentConfig.buttonPayDetailColor === 'Gold'} selected="selected"{/if}>{__('lpaButtonColorGold')}</option>
                                        <option value="LightGray"{if $lpaConfig.currentConfig.buttonPayDetailColor === 'LightGray'} selected="selected"{/if}>{__('lpaButtonColorLightGray')}</option>
                                        <option value="DarkGray"{if $lpaConfig.currentConfig.buttonPayDetailColor === 'DarkGray'} selected="selected"{/if}>{__('lpaButtonColorDarkGray')}</option>
                                    </select>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <select class="form-control" name="buttonPayCategoryColor" data-default="{$lpaConfig.defaultConfig.buttonPayCategoryColor}" id="lpa-config-button-pay-category-color">
                                        <option value="Gold"{if $lpaConfig.currentConfig.buttonPayCategoryColor === 'Gold'} selected="selected"{/if}>{__('lpaButtonColorGold')}</option>
                                        <option value="LightGray"{if $lpaConfig.currentConfig.buttonPayCategoryColor === 'LightGray'} selected="selected"{/if}>{__('lpaButtonColorLightGray')}</option>
                                        <option value="DarkGray"{if $lpaConfig.currentConfig.buttonPayCategoryColor === 'DarkGray'} selected="selected"{/if}>{__('lpaButtonColorDarkGray')}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPqSelector')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input type="text" class="form-control" name="buttonPayPqSelector" data-default="{$lpaConfig.defaultConfig.buttonPayPqSelector}" id="lpa-config-button-pay-pq-selector"{if isset($lpaConfig.currentConfig.buttonPayPqSelector)} value="{$lpaConfig.currentConfig.buttonPayPqSelector}"{/if}/>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input type="text" class="form-control" name="buttonPayDetailPqSelector" data-default="{$lpaConfig.defaultConfig.buttonPayDetailPqSelector}" id="lpa-config-button-pay-detail-pq-selector"{if isset($lpaConfig.currentConfig.buttonPayDetailPqSelector)} value="{$lpaConfig.currentConfig.buttonPayDetailPqSelector}"{/if}/>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <input type="text" class="form-control" name="buttonPayCategoryPqSelector" data-default="{$lpaConfig.defaultConfig.buttonPayCategoryPqSelector}" id="lpa-config-button-pay-category-pq-selector"{if isset($lpaConfig.currentConfig.buttonPayCategoryPqSelector)} value="{$lpaConfig.currentConfig.buttonPayCategoryPqSelector}"{/if}/>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPqMethod')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <select class="form-control" name="buttonPayPqMethod" data-default="{$lpaConfig.defaultConfig.buttonPayPqMethod}" id="lpa-config-button-pay-pq-method">
                                        <option value="prepend"{if $lpaConfig.currentConfig.buttonPayPqMethod === 'prepend'} selected="selected"{/if}>{__('lpaPqMethodPrepend')}</option>
                                        <option value="append"{if $lpaConfig.currentConfig.buttonPayPqMethod === 'append'} selected="selected"{/if}>{__('lpaPqMethodAppend')}</option>
                                        <option value="before"{if $lpaConfig.currentConfig.buttonPayPqMethod === 'before'} selected="selected"{/if}>{__('lpaPqMethodBefore')}</option>
                                        <option value="after"{if $lpaConfig.currentConfig.buttonPayPqMethod === 'after'} selected="selected"{/if}>{__('lpaPqMethodAfter')}</option>
                                        <option value="replaceWith"{if $lpaConfig.currentConfig.buttonPayPqMethod === 'replaceWith'} selected="selected"{/if}>{__('lpaPqMethodReplaceWith')}</option>
                                    </select>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <select class="form-control" name="buttonPayDetailPqMethod" data-default="{$lpaConfig.defaultConfig.buttonPayDetailPqMethod}" id="lpa-config-button-pay-detail-pq-method">
                                        <option value="prepend"{if $lpaConfig.currentConfig.buttonPayDetailPqMethod === 'prepend'} selected="selected"{/if}>{__('lpaPqMethodPrepend')}</option>
                                        <option value="append"{if $lpaConfig.currentConfig.buttonPayDetailPqMethod === 'append'} selected="selected"{/if}>{__('lpaPqMethodAppend')}</option>
                                        <option value="before"{if $lpaConfig.currentConfig.buttonPayDetailPqMethod === 'before'} selected="selected"{/if}>{__('lpaPqMethodBefore')}</option>
                                        <option value="after"{if $lpaConfig.currentConfig.buttonPayDetailPqMethod === 'after'} selected="selected"{/if}>{__('lpaPqMethodAfter')}</option>
                                        <option value="replaceWith"{if $lpaConfig.currentConfig.buttonPayDetailPqMethod === 'replaceWith'} selected="selected"{/if}>{__('lpaPqMethodReplaceWith')}</option>
                                    </select>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <select class="form-control" name="buttonPayCategoryPqMethod" data-default="{$lpaConfig.defaultConfig.buttonPayCategoryPqMethod}" id="lpa-config-button-pay-category-pq-method">
                                        <option value="prepend"{if $lpaConfig.currentConfig.buttonPayCategoryPqMethod === 'prepend'} selected="selected"{/if}>{__('lpaPqMethodPrepend')}</option>
                                        <option value="append"{if $lpaConfig.currentConfig.buttonPayCategoryPqMethod === 'append'} selected="selected"{/if}>{__('lpaPqMethodAppend')}</option>
                                        <option value="before"{if $lpaConfig.currentConfig.buttonPayCategoryPqMethod === 'before'} selected="selected"{/if}>{__('lpaPqMethodBefore')}</option>
                                        <option value="after"{if $lpaConfig.currentConfig.buttonPayCategoryPqMethod === 'after'} selected="selected"{/if}>{__('lpaPqMethodAfter')}</option>
                                        <option value="replaceWith"{if $lpaConfig.currentConfig.buttonPayCategoryPqMethod === 'replaceWith'} selected="selected"{/if}>{__('lpaPqMethodReplaceWith')}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaButtonPreview')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <div class="lpa-config-button-pay-preview-container">
                                        {if isset($lpaConfig.currentConfig.merchantId) && !empty($lpaConfig.currentConfig.merchantId) && isset($lpaAdminGlobal.clientId) && !empty($lpaAdminGlobal.clientId)}
                                            <div class="row">
                                                <div class="col-12">
                                                <div id="lpa-config-button-pay-preview-button"></div>
                                                </div>
                                            </div>
                                        {else}
                                            <i>{__('lpaButtonPreviewNotAvailable')}</i>
                                        {/if}
                                    </div>
                                    <script type="text/javascript">
                                        $('body').on('readyForRender.lpa', function () {
                                            var lpaButtonPayPreviewFunc = function () {
                                                var height = $('#lpa-config-button-pay-height').val();
                                                var color = $('#lpa-config-button-pay-color').val();
                                                window.lpaAdmin.renderPreviewButton('{$lpaConfig.currentConfig.merchantId}', 'lpa-config-button-pay-preview-button', 'PwA', height, color);
                                            };
                                            $('#lpa-config-button-pay-height, #lpa-config-button-pay-color').on('input', lpaButtonPayPreviewFunc);
                                            $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                                                lpaButtonPayPreviewFunc();
                                            });
                                            lpaButtonPayPreviewFunc();
                                        });
                                    </script>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <div class="lpa-config-button-pay-detail-preview-container">
                                        {if isset($lpaConfig.currentConfig.merchantId) && !empty($lpaConfig.currentConfig.merchantId) && isset($lpaAdminGlobal.clientId) && !empty($lpaAdminGlobal.clientId)}
                                            <div class="row">
                                                <div class="col-12">
                                                    <div id="lpa-config-button-pay-detail-preview-button"></div>
                                                </div>
                                            </div>
                                        {else}
                                            <i>{__('lpaButtonPreviewNotAvailable')}</i>
                                        {/if}
                                    </div>
                                    <script type="text/javascript">
                                        $('body').on('readyForRender.lpa', function () {
                                            var lpaButtonPayDetailPreviewFunc = function () {
                                                var height = $('#lpa-config-button-pay-detail-height').val();
                                                var color = $('#lpa-config-button-pay-detail-color').val();
                                                window.lpaAdmin.renderPreviewButton('{$lpaConfig.currentConfig.merchantId}', 'lpa-config-button-pay-detail-preview-button', 'PwA', height, color);
                                            };
                                            $('#lpa-config-button-pay-detail-height, #lpa-config-button-pay-detail-color').on('input', lpaButtonPayDetailPreviewFunc);
                                            $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                                                lpaButtonPayDetailPreviewFunc();
                                            });
                                            lpaButtonPayDetailPreviewFunc();
                                        });
                                    </script>
                                </div>
                                <div class="lpa-admin-option-input col-xs-3 col-3">
                                    <div class="lpa-config-button-pay-category-preview-container">
                                        {if isset($lpaConfig.currentConfig.merchantId) && !empty($lpaConfig.currentConfig.merchantId) && isset($lpaAdminGlobal.clientId) && !empty($lpaAdminGlobal.clientId)}
                                            <div class="row">
                                                <div class="col-12">
                                                <div id="lpa-config-button-pay-category-preview-button"></div>
                                                </div>
                                            </div>
                                        {else}
                                            <i>{__('lpaButtonPreviewNotAvailable')}</i>
                                        {/if}
                                    </div>
                                    <script type="text/javascript">
                                        $('body').on('readyForRender.lpa', function () {
                                            var lpaButtonPayCategoryPreviewFunc = function () {
                                                var height = $('#lpa-config-button-pay-category-height').val();
                                                var color = $('#lpa-config-button-pay-category-color').val();
                                                window.lpaAdmin.renderPreviewButton('{$lpaConfig.currentConfig.merchantId}', 'lpa-config-button-pay-category-preview-button', 'PwA', height, color);
                                            };
                                            $('#lpa-config-button-pay-category-height, #lpa-config-button-pay-category-color').on('input', lpaButtonPayCategoryPreviewFunc);
                                            $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                                                lpaButtonPayCategoryPreviewFunc();
                                            });
                                            lpaButtonPayCategoryPreviewFunc();
                                        });
                                    </script>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="col-12 col-xs-12">
                                    <hr>
                                </div>
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaTemplateDefaults')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <div>
                                        <button class="btn btn-secondary lpa-pq-defaults" type="button" data-template="nova">NOVA</button>
                                        <button class="btn btn-secondary lpa-pq-defaults" type="button" data-template="evo">EVO</button>
                                        <button class="btn btn-secondary lpa-pq-defaults" type="button" data-template="easytemplate360">easyTemplate360</button>
                                        <button class="btn btn-secondary lpa-pq-defaults" type="button" data-template="hypnos">Hypnos</button>
                                        <button class="btn btn-secondary lpa-pq-defaults" type="button" data-template="snackys">Snackys</button>
                                    </div>
                                    <small class="form-text help-block text-muted">{__('lpaTemplateDefaultsHint')}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">
                                {__('lpaLoginButtonTitle')}
                                <a class="lpa-reset-options pull-right" title="{__('lpaResetSettings')}" onclick="window.lpaAdmin.reset(this);return false;"><i class="fa fas fa-undo"></i></a>
                            </h3>
                            <hr class="mt-1 mb-1">
                        </div>
                        <div class="panel-body card-text">
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-button-login-active">{__('lpaLoginButtonActivateLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="buttonLoginActive" data-default="{$lpaConfig.defaultConfig.buttonLoginActive}" id="lpa-config-button-login-active"{if $lpaConfig.currentConfig.buttonLoginActive} checked="checked"{/if}> {__('lpaActive')}
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPayButtonsCssColumnsLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" name="buttonLoginCssColumns" type="text" data-default="{$lpaConfig.defaultConfig.buttonLoginCssColumns}" id="lpa-config-button-login-css-columns"{if isset($lpaConfig.currentConfig.buttonLoginCssColumns)} value="{$lpaConfig.currentConfig.buttonLoginCssColumns}"{/if}/>
                                    <small class="form-text help-block text-muted">{__('lpaPayButtonsCssColumnsHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPayButtonsHeightLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" name="buttonLoginHeight" type="number" data-default="{$lpaConfig.defaultConfig.buttonLoginHeight}" id="lpa-config-button-login-height"{if isset($lpaConfig.currentConfig.buttonLoginHeight)} value="{$lpaConfig.currentConfig.buttonLoginHeight}"{/if}/>
                                    <small class="form-text help-block text-muted">{__('lpaPayButtonsHeightHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaPayButtonsColorLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="buttonLoginColor" data-default="{$lpaConfig.defaultConfig.buttonLoginColor}" id="lpa-config-button-login-color">
                                        <option value="Gold"{if $lpaConfig.currentConfig.buttonLoginColor === 'Gold'} selected="selected"{/if}>{__('lpaButtonColorGold')}</option>
                                        <option value="LightGray"{if $lpaConfig.currentConfig.buttonLoginColor === 'LightGray'} selected="selected"{/if}>{__('lpaButtonColorLightGray')}</option>
                                        <option value="DarkGray"{if $lpaConfig.currentConfig.buttonLoginColor === 'DarkGray'} selected="selected"{/if}>{__('lpaButtonColorDarkGray')}</option>
                                    </select>
                                    <small class="form-text help-block text-muted">{__('lpaPayButtonsColorHint')}</small>
                                </div>
                            </div>

                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-button-login-pq-selector">{__('lpaPqSelector')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="text" class="form-control" name="buttonLoginPqSelector" data-default="{$lpaConfig.defaultConfig.buttonLoginPqSelector}" id="lpa-config-button-login-pq-selector"{if $lpaConfig.currentConfig.buttonLoginPqSelector} value="{$lpaConfig.currentConfig.buttonLoginPqSelector}"{/if}/>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-button-login-pq-method">{__('lpaPqMethod')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="buttonLoginPqMethod" data-default="{$lpaConfig.defaultConfig.buttonLoginPqMethod}" id="lpa-config-button-login-pq-method">
                                        <option value="prepend"{if $lpaConfig.currentConfig.buttonLoginPqMethod === 'prepend'} selected="selected"{/if}>{__('lpaPqMethodPrepend')}</option>
                                        <option value="append"{if $lpaConfig.currentConfig.buttonLoginPqMethod === 'append'} selected="selected"{/if}>{__('lpaPqMethodAppend')}</option>
                                        <option value="before"{if $lpaConfig.currentConfig.buttonLoginPqMethod === 'before'} selected="selected"{/if}>{__('lpaPqMethodBefore')}</option>
                                        <option value="after"{if $lpaConfig.currentConfig.buttonLoginPqMethod === 'after'} selected="selected"{/if}>{__('lpaPqMethodAfter')}</option>
                                        <option value="replaceWith"{if $lpaConfig.currentConfig.buttonLoginPqMethod === 'replaceWith'} selected="selected"{/if}>{__('lpaPqMethodReplaceWith')}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label>{__('lpaButtonPreview')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <div class="lpa-config-button-login-preview-container">
                                        {if isset($lpaConfig.currentConfig.merchantId) && !empty($lpaConfig.currentConfig.merchantId) && isset($lpaAdminGlobal.clientId) && !empty($lpaAdminGlobal.clientId)
                                        && isset($lpaConfig.publicKeyId) && !empty($lpaConfig.publicKeyId)}
                                            <div class="row">
                                                <div class="col-12">
                                                    <div id="lpa-config-button-login-preview-button"></div>
                                                </div>
                                            </div>
                                        {else}
                                            <i>{__('lpaButtonPreviewNotAvailable')}</i>
                                        {/if}
                                    </div>
                                    <script type="text/javascript">
                                        $('body').on('readyForRender.lpa', function () {
                                            var lpaButtonLoginPreviewFunc = function () {
                                                var height = $('#lpa-config-button-login-height').val();
                                                var color = $('#lpa-config-button-login-color').val();
                                                window.lpaAdmin.renderPreviewButton('{$lpaConfig.currentConfig.merchantId}', 'lpa-config-button-login-preview-button', 'LwA', height, color);
                                            };
                                            $('#lpa-config-button-login-color, #lpa-config-button-login-height').on('input', lpaButtonLoginPreviewFunc);
                                            $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
                                                lpaButtonLoginPreviewFunc();
                                            });
                                            lpaButtonLoginPreviewFunc();
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">
                                {__('lpaExpertSettings')}
                                <a class="lpa-reset-options pull-right" title="{__('lpaResetSettings')}" onclick="window.lpaAdmin.reset(this);return false;"><i class="fa fas fa-undo"></i></a>
                            </h3>
                            <hr class="mt-1 mb-1">
                        </div>
                        <div class="panel-body card-text">
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-authorization-mode">{__('lpaAuthorizationModeLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="authorizationMode" data-default="{$lpaConfig.defaultConfig.authorizationMode}" id="lpa-config-authorization-mode">
                                        <option value="omni"{if $lpaConfig.currentConfig.authorizationMode === 'omni'} selected="selected"{/if}>{__('lpaAuthorizationModeOmni')}</option>
                                        <option value="sync"{if $lpaConfig.currentConfig.authorizationMode === 'sync'} selected="selected"{/if}>{__('lpaAuthorizationModeSync')}</option>
                                    </select>
                                    <small class="form-text help-block text-muted">{__('lpaAuthorizationModeHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-use-amazon-pay-billing-address">{__('lpaUseAmazonBillingAddressLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="useAmazonPayBillingAddress" data-default="{$lpaConfig.defaultConfig.useAmazonPayBillingAddress}" id="lpa-config-use-amazon-pay-billing-address"{if $lpaConfig.currentConfig.useAmazonPayBillingAddress} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaUseAmazonBillingAddressHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-login-required-fields-only">{__('lpaLoginRequiredFieldsOnlyLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="loginRequiredFieldsOnly" data-default="{$lpaConfig.defaultConfig.loginRequiredFieldsOnly}" id="lpa-config-login-required-fields-only"{if $lpaConfig.currentConfig.loginRequiredFieldsOnly} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaLoginRequiredFieldsOnlyHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-allow-packstation">{__('lpaAllowPackstationLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="allowPackstation" data-default="{$lpaConfig.defaultConfig.allowPackstation}" id="lpa-config-allow-packstation"{if $lpaConfig.currentConfig.allowPackstation} checked="checked"{/if}> {__('lpaAllow')}
                                    <small class="form-text help-block text-muted">{__('lpaAllowPackstationHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-allow-po-box">{__('lpaAllowPoBoxLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="allowPoBox" data-default="{$lpaConfig.defaultConfig.allowPoBox}" id="lpa-config-allow-po-box"{if $lpaConfig.currentConfig.allowPoBox} checked="checked"{/if}> {__('lpaAllow')}
                                    <small class="form-text help-block text-muted">{__('lpaAllowPoBoxHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-check-account-merge">{__('lpaCheckAccountMergeLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="checkAccountMerge" data-default="{$lpaConfig.defaultConfig.checkAccountMerge}" id="lpa-config-check-account-merge"{if $lpaConfig.currentConfig.checkAccountMerge} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaCheckAccountMergeHint')}</small>
                                </div>
                            </div>
                            {* This feature is disabled as long as APIV2 does not support currencies other than the ledger currency anyway -
                           <div class="lpa-admin-option row mb-2">
                               <div class="lpa-admin-option-title col-xs-3 col-3">
                                   <label for="lpa-config-check-account-merge">W&auml;hrungen ausschlie&szlig;en</label>
                               </div>
                               <div class="lpa-admin-option-input col-xs-9 col-9">
                                   {foreach $lpaConfig.configuredCurrencies as $currency}
                                       <input type="checkbox" value="{$currency}" name="excludedCurrencies[]" data-default="0" id="lpa-config-check-configured-currency-{$currency}"{if in_array($currency, $lpaConfig.currentConfig.excludedCurrencies)} checked="checked"{/if} />
                                       {$currency}
                                       <br/>
                                   {/foreach}
                                   <small class="form-text help-block text-muted">
                                       W&auml;hlen Sie hier aus, welche W&auml;hrungen <b>nicht</b> in Amazon Pay angeboten werden.<br/>
                                       Der Kunde wird automatisch auf eine passende W&auml;hrung umgeleitet, wenn er eine ausgeschlossene oder nicht von Amazon Pay unterst&uuml;tzte W&auml;hrung verwendet. (Vorzugsweise erfolgt die Umleitung auf die Shop-Standard-W&auml;hrung.)<br/>
                                       Beachten Sie, dass es W&auml;hrungen gibt, die grunds&auml;tzlich nicht von Amazon Pay unterst&uuml;tzt werden.
                                   </small>
                               </div>
                           </div>
                           *}
                            {* Feature is on hold -
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-use-behavioral-overlay">Verhaltensbasiertes Overlay nutzen</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="useBehavioralOverlay" data-default="{$lpaConfig.defaultConfig.useBehavioralOverlay}" id="lpa-use-behavioral-overlay"{if $lpaConfig.currentConfig.useBehavioralOverlay} checked="checked"{/if}> aktiv
                                    <small class="form-text help-block text-muted">
                                        Schaltet ein/aus, ob das verhaltensbasierte Overlay aktiv sein soll.<br/>
                                        Wenn dieses Overlay aktiv ist, wird dem Kunden ein Overlay mit der Aufforderung, den Checkout &uuml;ber Amazon Pay durchzuf&uuml;hren, eingeblendet, wenn er auf der Warenkorb/Bestellvorgang-Seite den Fokus au&szlig;erhalb des Fensters legt (Bewegung des Mauszeigers nach oben aus dem Fenster heraus).<br/>
                                        Dieses Overlay kann die Conversion-Rate erh&ouml;hen.
                                    </small>
                                </div>
                            </div>
                            *}
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-cron-mode">{__('lpaCronModeLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="cronMode" data-default="{$lpaConfig.defaultConfig.cronMode}" id="lpa-config-cron-mode">
                                        <option value="off"{if $lpaConfig.currentConfig.cronMode === 'off'} selected="selected"{/if}>{__('lpaCronModeOff')}</option>
                                        <option value="sync"{if $lpaConfig.currentConfig.cronMode === 'sync'} selected="selected"{/if}>{__('lpaCronModeSync')}</option>
                                        <option value="task"{if $lpaConfig.currentConfig.cronMode === 'task'} selected="selected"{/if}>{__('lpaCronModeTask')}</option>
                                    </select>
                                    <small class="form-text help-block text-muted">{__('lpaCronModeHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-add-incoming-payments">{__('lpaAddIncomingPaymentsLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="addIncomingPayments" data-default="{$lpaConfig.defaultConfig.addIncomingPayments}" id="lpa-config-add-incoming-payments"{if $lpaConfig.currentConfig.addIncomingPayments} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaAddIncomingPaymentsHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-multi-currency-enabled">{__('lpaMultiCurrencyEnabledLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="multiCurrencyEnabled" data-default="{$lpaConfig.defaultConfig.multiCurrencyEnabled}" id="lpa-config-multi-currency-enabled"{if $lpaConfig.currentConfig.multiCurrencyEnabled} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaMultiCurrencyEnabledHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-delivery-notifications-enabled">{__('lpaDeliveryNotificationsEnabledLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="deliveryNotificationsEnabled" data-default="{$lpaConfig.defaultConfig.deliveryNotificationsEnabled}" id="lpa-config-delivery-notifications-enabled"{if $lpaConfig.currentConfig.deliveryNotificationsEnabled} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaDeliveryNotificationsEnabledHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-always-add-reference-to-comment">{__('lpaAlwaysAddReferenceToCommentLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="alwaysAddReferenceToComment" data-default="{$lpaConfig.defaultConfig.alwaysAddReferenceToComment}" id="lpa-config-always-add-reference-to-comment"{if $lpaConfig.currentConfig.alwaysAddReferenceToComment} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaAlwaysAddReferenceToCommentHint')}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-body card-text">
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-12 col-12">
                                    <button class="btn btn-primary" name="saveConfig" value="1"><i class="fa fa-save"></i>&nbsp;{__('save')}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {* Checkout.js - this is used for the Amazon Pay button *}
    <script type="text/javascript">
        window.onAmazonPayReady = function () {
            $('body').trigger('readyForRender.lpa');
        };
    </script>
    <script src="{$lpaConfig.checkoutEndpointUrl}" defer="defer" onload="window.onAmazonPayReady();"></script>
</div>
{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/footer.tpl"}