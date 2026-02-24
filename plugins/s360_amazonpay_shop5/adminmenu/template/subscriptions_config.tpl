{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/header.tpl"}
<div class="lpa-admin-content">
    <div class="row">
        <div class="col-xs-12 col-12">
            <form method="post" action="{$lpaSubscription.formTargetUrl}">
                {$jtl_token}
                {* Subscription configuration here. *}
                <div class="panel panel-default card mb-3">
                    <div class="card-body">
                        <div class="panel-heading card-title">
                            <h3 class="panel-title">
                                {__('lpaSubscriptionSettings')}
                                <a class="lpa-reset-options pull-right" title="{__('lpaResetSettings')}" onclick="window.lpaAdmin.reset(this);return false;"><i class="fa fas fa-undo"></i></a>
                            </h3>
                            <hr class="mt-1 mb-1">
                        </div>
                        <div class="panel-body card-text">
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-enabled">{__('lpaSubscriptionModeLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select id="lpa-config-subscription-mode" name="subscriptionMode" class="form-control combo" data-default="{$lpaSubscription.defaultConfig.subscriptionMode}">
                                        <option value="inactive"{if !isset($lpaSubscription.currentConfig.subscriptionMode) || $lpaSubscription.currentConfig.subscriptionMode === "inactive" || empty($lpaAccount.currentConfig.subscriptionMode)} selected{/if}>{__('lpaSubscriptionModeInactive')}</option>
                                        <option value="existingOnly"{if isset($lpaSubscription.currentConfig.subscriptionMode) && $lpaSubscription.currentConfig.subscriptionMode === "existingOnly"} selected{/if}>{__('lpaSubscriptionModeExistingOnly')}</option>
                                        <option value="active"{if isset($lpaSubscription.currentConfig.subscriptionMode) && $lpaSubscription.currentConfig.subscriptionMode === "active"} selected{/if}>{__('lpaSubscriptionModeActive')}</option>
                                    </select>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionModeHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-display-detail">{__('lpaSubscriptionDisplayDetailLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="subscriptionDisplayDetail" data-default="{$lpaSubscription.defaultConfig.subscriptionDisplayDetail}" id="lpa-config-subscription-display-detail"{if $lpaSubscription.currentConfig.subscriptionDisplayDetail} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionDisplayDetailHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-display-cart">{__('lpaSubscriptionDisplayCartLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="subscriptionDisplayCart" data-default="{$lpaSubscription.defaultConfig.subscriptionDisplayCart}" id="lpa-config-subscription-display-cart"{if $lpaSubscription.currentConfig.subscriptionDisplayCart} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionDisplayCartHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-global-active">{__('lpaSubscriptionGlobalActiveLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="subscriptionGlobalActive" data-default="{$lpaSubscription.defaultConfig.subscriptionGlobalActive}" id="lpa-config-subscription-global-active"{if $lpaSubscription.currentConfig.subscriptionGlobalActive} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionGlobalActiveHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-global-interval">{__('lpaSubscriptionGlobalIntervalLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" type="text" name="subscriptionGlobalInterval" data-default="{$lpaSubscription.defaultConfig.subscriptionGlobalInterval}" id="lpa-config-subscription-global-interval"{if !empty($lpaSubscription.currentConfig.subscriptionGlobalInterval)} value="{$lpaSubscription.currentConfig.subscriptionGlobalInterval}"{/if}>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionGlobalIntervalHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-functional-attribute-interval">{__('lpaSubscriptionFunctionalAttributeIntervalLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" type="text" name="subscriptionFunctionalAttributeInterval" data-default="{$lpaSubscription.defaultConfig.subscriptionFunctionalAttributeInterval}" id="lpa-config-subscription-functional-attribute-interval"{if !empty($lpaSubscription.currentConfig.subscriptionFunctionalAttributeInterval)} value="{$lpaSubscription.currentConfig.subscriptionFunctionalAttributeInterval}"{/if}>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionFunctionalAttributeIntervalHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-order-attribute-flag">{__('lpaSubscriptionOrderAttributeFlagLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" type="text" name="subscriptionOrderAttributeFlag" data-default="{$lpaSubscription.defaultConfig.subscriptionOrderAttributeFlag}" id="lpa-config-subscription-order-attribute-flag"{if !empty($lpaSubscription.currentConfig.subscriptionOrderAttributeFlag)} value="{$lpaSubscription.currentConfig.subscriptionOrderAttributeFlag}"{/if}>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionOrderAttributeFlagHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-order-attribute-interval">{__('lpaSubscriptionOrderAttributeIntervalLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" type="text" name="subscriptionOrderAttributeInterval" data-default="{$lpaSubscription.defaultConfig.subscriptionOrderAttributeInterval}" id="lpa-config-subscription-order-attribute-interval"{if !empty($lpaSubscription.currentConfig.subscriptionOrderAttributeInterval)} value="{$lpaSubscription.currentConfig.subscriptionOrderAttributeInterval}"{/if}>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionOrderAttributeIntervalHint')}</small>
                                </div>
                            </div>
                            {if $lpaSubscription.currentConfig.subscriptionDiscountFeatureEnabled}
                                <div class="lpa-admin-option row mb-2">
                                    <div class="lpa-admin-option-title col-xs-3 col-3">
                                        <label for="lpa-config-subscription-discount-mode">{__('lpaSubscriptionDiscountModeLabel')}</label>
                                    </div>
                                    <div class="lpa-admin-option-input col-xs-9 col-9">
                                        <select name="subscriptionDiscountMode" class="form-control" data-default="{$lpaSubscription.defaultConfig.subscriptionDiscountMode}" id="lpa-config-subscription-discount-mode">
                                            <option value="inactive"{if empty($lpaSubscription.currentConfig.subscriptionDiscountMode) || $lpaSubscription.currentConfig.subscriptionDiscountMode === 'inactive'} selected{/if}>{__('lpaSubscriptionDiscountModeInactive')}</option>
                                            <option value="global"{if !empty($lpaSubscription.currentConfig.subscriptionDiscountMode) && $lpaSubscription.currentConfig.subscriptionDiscountMode === 'global'} selected{/if}>{__('lpaSubscriptionDiscountModeGlobal')}</option>
                                            <option value="attribute"{if !empty($lpaSubscription.currentConfig.subscriptionDiscountMode) && $lpaSubscription.currentConfig.subscriptionDiscountMode === 'attribute'} selected{/if}>{__('lpaSubscriptionDiscountModeAttribute')}</option>
                                        </select>
                                        <small class="form-text help-block text-muted">{__('lpaSubscriptionDiscountModeHint')}</small>
                                    </div>
                                </div>
                                <div class="lpa-admin-option row mb-2">
                                    <div class="lpa-admin-option-title col-xs-3 col-3">
                                        <label for="lpa-config-subscription-discount-global">{__('lpaSubscriptionDiscountGlobalLabel')}</label>
                                    </div>
                                    <div class="lpa-admin-option-input col-xs-9 col-9">
                                        <input class="form-control" type="number" step="1" min="0" name="subscriptionDiscountGlobal" data-default="{$lpaSubscription.defaultConfig.subscriptionDiscountGlobal}" id="lpa-config-subscription-discount-global"{if isset($lpaSubscription.currentConfig.subscriptionDiscountGlobal)} value="{$lpaSubscription.currentConfig.subscriptionDiscountGlobal}"{/if}>
                                        <small class="form-text help-block text-muted">{__('lpaSubscriptionDiscountGlobalHint')}</small>
                                    </div>
                                </div>
                                <div class="lpa-admin-option row mb-2">
                                    <div class="lpa-admin-option-title col-xs-3 col-3">
                                        <label for="lpa-config-subscription-discount-attribute">{__('lpaSubscriptionDiscountAttributeLabel')}</label>
                                    </div>
                                    <div class="lpa-admin-option-input col-xs-9 col-9">
                                        <input class="form-control" type="text" name="subscriptionDiscountAttribute" data-default="{$lpaSubscription.defaultConfig.subscriptionDiscountAttribute}" id="lpa-config-subscription-discount-attribute"{if isset($lpaSubscription.currentConfig.subscriptionDiscountAttribute)} value="{$lpaSubscription.currentConfig.subscriptionDiscountAttribute}"{/if}>
                                        <small class="form-text help-block text-muted">{__('lpaSubscriptionDiscountAttributeHint')}</small>
                                    </div>
                                </div>
                                {else}
                                <input type="hidden" name="subscriptionDiscountMode" data-default="{$lpaSubscription.defaultConfig.subscriptionDiscountMode}" id="lpa-config-subscription-discount-mode"{if isset($lpaSubscription.currentConfig.subscriptionDiscountMode)} value="{$lpaSubscription.currentConfig.subscriptionDiscountMode}"{/if}>
                                <input type="hidden" name="subscriptionDiscountGlobal" data-default="{$lpaSubscription.defaultConfig.subscriptionDiscountGlobal}" id="lpa-config-subscription-discount-global"{if isset($lpaSubscription.currentConfig.subscriptionDiscountGlobal)} value="{$lpaSubscription.currentConfig.subscriptionDiscountGlobal}"{/if}>
                                <input type="hidden" name="subscriptionDiscountAttribute" data-default="{$lpaSubscription.defaultConfig.subscriptionDiscountAttribute}" id="lpa-config-subscription-discount-attribute"{if isset($lpaSubscription.currentConfig.subscriptionDiscountAttribute)} value="{$lpaSubscription.currentConfig.subscriptionDiscountAttribute}"{/if}>
                            {/if}
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-reminder-mail-lead-time-days">{__('lpaSubscriptionReminderMailLeadTimeDaysLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" type="number" name="subscriptionReminderMailLeadTimeDays" data-default="{$lpaSubscription.defaultConfig.subscriptionReminderMailLeadTimeDays}" id="lpa-config-subscription-reminder-mail-lead-time-days"{if isset($lpaSubscription.currentConfig.subscriptionReminderMailLeadTimeDays)} value="{$lpaSubscription.currentConfig.subscriptionReminderMailLeadTimeDays}"{/if}>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionReminderMailLeadTimeDaysHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-normalize-order-time">{__('lpaSubscriptionNormalizeOrderTimeLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input type="checkbox" value="on" name="subscriptionNormalizeOrderTime" data-default="{$lpaSubscription.defaultConfig.subscriptionNormalizeOrderTime}" id="lpa-config-subscription-normalize-order-time"{if $lpaSubscription.currentConfig.subscriptionNormalizeOrderTime} checked="checked"{/if}> {__('lpaActive')}
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionNormalizeOrderTimeHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-normalize-order-time-to">{__('lpaSubscriptionNormalizeOrderTimeToLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" type="time" step="60" name="subscriptionNormalizeOrderTimeTo" data-default="{$lpaSubscription.defaultConfig.subscriptionNormalizeOrderTimeTo}" id="lpa-config-subscription-normalize-order-time-to"{if !empty($lpaSubscription.currentConfig.subscriptionNormalizeOrderTimeTo)} value="{$lpaSubscription.currentConfig.subscriptionNormalizeOrderTimeTo}"{/if}>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionNormalizeOrderTimeToHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-notification-mail-address">{__('lpaSubscriptionNotificationMailAddressLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" type="text" name="subscriptionNotificationMailAddress" data-default="{$lpaSubscription.defaultConfig.subscriptionNotificationMailAddress}" id="lpa-config-subscription-notification-mail-address"{if !empty($lpaSubscription.currentConfig.subscriptionNotificationMailAddress)} value="{$lpaSubscription.currentConfig.subscriptionNotificationMailAddress}"{/if}>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionNotificationMailAddressHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-customer-account-pq-selector">{__('lpaSubscriptionCustomerAccountPqSelectorLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <input class="form-control" type="text" name="subscriptionCustomerAccountPqSelector" data-default="{$lpaSubscription.defaultConfig.subscriptionCustomerAccountPqSelector}" id="lpa-config-subscription-customer-account-pq-selector"{if !empty($lpaSubscription.currentConfig.subscriptionCustomerAccountPqSelector)} value="{$lpaSubscription.currentConfig.subscriptionCustomerAccountPqSelector}"{/if}>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionCustomerAccountPqSelectorHint')}</small>
                                </div>
                            </div>
                            <div class="lpa-admin-option row mb-2">
                                <div class="lpa-admin-option-title col-xs-3 col-3">
                                    <label for="lpa-config-subscription-customer-account-pq-method">{__('lpaSubscriptionCustomerAccountPqMethodLabel')}</label>
                                </div>
                                <div class="lpa-admin-option-input col-xs-9 col-9">
                                    <select class="form-control" name="subscriptionCustomerAccountPqMethod" data-default="{$lpaConfig.defaultConfig.subscriptionCustomerAccountPqMethod}" id="lpa-config-subscription-customer-account-pq-method">
                                        <option value="prepend"{if $lpaConfig.currentConfig.subscriptionCustomerAccountPqMethod === 'prepend'} selected="selected"{/if}>{__('lpaPqMethodPrepend')}</option>
                                        <option value="append"{if $lpaConfig.currentConfig.subscriptionCustomerAccountPqMethod === 'append'} selected="selected"{/if}>{__('lpaPqMethodAppend')}</option>
                                        <option value="before"{if $lpaConfig.currentConfig.subscriptionCustomerAccountPqMethod === 'before'} selected="selected"{/if}>{__('lpaPqMethodBefore')}</option>
                                        <option value="after"{if $lpaConfig.currentConfig.subscriptionCustomerAccountPqMethod === 'after'} selected="selected"{/if}>{__('lpaPqMethodAfter')}</option>
                                        <option value="replaceWith"{if $lpaConfig.currentConfig.subscriptionCustomerAccountPqMethod === 'replaceWith'} selected="selected"{/if}>{__('lpaPqMethodReplaceWith')}</option>
                                    </select>
                                    <small class="form-text help-block text-muted">{__('lpaSubscriptionCustomerAccountPqMethodHint')}</small>
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
                                    <button class="btn btn-primary" name="saveSubscriptionsConfig" value="1"><i class="fa fa-save"></i>&nbsp;{__('save')}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
{include file="{$lpaAdminGlobal.adminTemplatePath}snippets/footer.tpl"}