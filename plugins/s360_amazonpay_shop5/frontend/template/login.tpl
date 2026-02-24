{strip}
    <div class="container lpa-login-wrapper">
        {* On this site, the login with Amazon Pay account creation is handled *}
        {if $lpaDisplayMode === 'create'}
            {* create account form - see $lpaCreate *}
            {if !empty($fehlendeAngaben)}
                <div class="alert alert-danger">{lang key='mandatoryFieldNotification' section='errorMessages'}</div>
            {/if}
            {if isset($fehlendeAngaben.email_vorhanden) && $fehlendeAngaben.email_vorhanden == 1}
                <div class="alert alert-danger">{lang key='emailAlreadyExists' section='account data'}</div>
            {/if}
            {if isset($fehlendeAngaben.formular_zeit) && $fehlendeAngaben.formular_zeit == 1}
                <div class="alert alert-danger">{lang key='formToFast' section='account data'}</div>
            {/if}
            <div id="new_customer" class="row">
                <div class="col-12 col-xs-12">
                    <form method="post" class="evo-validate label-slide">
                        {$jtl_token}
                        <input type="hidden" name="createAccount" value="1">
                        {if isset($lpaCreate.explanationText) && !empty($lpaCreate.explanationText)}
                            <div class="alert alert-success lpa-explanation">{$lpaCreate.explanationText}</div>
                        {/if}
                        <div class="alert alert-danger lpa-error-message" id="lpa-error-message-packstation" style="display:none;">{$oPlugin->getLocalization()->getTranslation('packstation_not_allowed')}</div>
                        <div class="alert alert-danger lpa-error-message" id="lpa-error-message-generic" style="display:none;">{$oPlugin->getLocalization()->getTranslation('address_selection_error')}</div>

                        {* Include address form *}
                        {include file="{$lpaCreate.frontendTemplatePath}login_address_form.tpl" nAnzeigeOrt=$smarty.const.CHECKBOX_ORT_REGISTRIERUNG}

                        {if $lpaCreate.accountCreationMode === 'optional'}
                            {if $lpaCreate.askForPassword}
                                <script type="text/javascript">
                                    window.lpaTogglePasswordFields = function () {
                                        var requireFields = $('[name="createFullAccount"]').is(':checked');
                                        if (requireFields) {
                                            $('#create_account_data input[type="password"]').prop('required', true);
                                            $('#create_account_data').show();
                                        } else {
                                            $('#create_account_data').hide();
                                            $('#create_account_data input[type="password"]').prop('required', false);
                                        }
                                    };
                                </script>
                            {/if}
                            <fieldset>
                                <div class="row">
                                    <div class="col-12 col-xs-12 mb-3">
                                        <div class="checkbox custom-control custom-checkbox custom-control-inline">
                                            <input type="checkbox" class="custom-control-input" name="createFullAccount" value="Y" id="lpa-create-account-toggle"{if $lpaCreate.askForPassword} onchange="window.lpaTogglePasswordFields();"{/if}{if isset($cPost_var['createFullAccount']) &&  $cPost_var['createFullAccount'] === 'Y'} checked="checked"{/if}>
                                            <label class="control-label custom-control-label" for="lpa-create-account-toggle">{lang key="createNewAccount" section="checkout"}</label>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        {/if}
                        {if $lpaCreate.askForPassword && $lpaCreate.accountCreationMode !== 'never'}
                            <fieldset>
                                <div id="create_account_data" class="row"{if $lpaCreate.accountCreationMode === 'optional' && (!isset($cPost_var, $cPost_var['createFullAccount']) || $cPost_var['createFullAccount'] !== 'Y')} style="display:none;"{/if}>
                                    <div class="col-6 col-xs-6">
                                        <div class="form-group float-label-control{if isset($fehlendeAngaben.pass_zu_kurz) || isset($fehlendeAngaben.pass_ungleich)} has-error{/if}">
                                            <label for="password" class="control-label">{lang key='password' section='account data'}</label>
                                            <input type="password" name="pass" maxlength="20" id="password" class="form-control" placeholder="{lang key='password' section='account data'}" {if $lpaCreate.accountCreationMode === 'optional' && (!isset($cPost_var, $cPost_var['createFullAccount']) || $cPost_var['createFullAccount'] !== 'Y')}{else}required{/if} autocomplete="off" aria-autocomplete="none">
                                            {if isset($fehlendeAngaben.pass_zu_kurz)}
                                                <div class="form-error-msg text-danger"><i class="fa fa-warning"></i> {$warning_passwortlaenge}</div>
                                            {/if}
                                        </div>
                                    </div>
                                    <div class="col-6 col-xs-6">
                                        <div class="form-group float-label-control{if isset($fehlendeAngaben.pass_ungleich)} has-error{/if}">
                                            <label for="password2" class="control-label">{lang key='passwordRepeat' section='account data'}</label>
                                            <input type="password" name="pass2" maxlength="20" id="password2" class="form-control" placeholder="{lang key='passwordRepeat' section='account data'}" {if $lpaCreate.accountCreationMode === 'optional' && (!isset($cPost_var, $cPost_var['createFullAccount']) || $cPost_var['createFullAccount'] !== 'Y')}{else}required{/if} data-must-equal-to="#create_account_data input[name='pass']"
                                                   data-custom-message="{lang key='passwordsMustBeEqual' section='account data'}" autocomplete="off" aria-autocomplete="none">
                                            {if isset($fehlendeAngaben.pass_ungleich)}
                                                <div class="form-error-msg text-danger"><i class="fa fa-warning"></i> {lang key='passwordsMustBeEqual' section='account data'}</div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                        {/if}
                        <hr>
                        <input type="submit" class="btn btn-primary btn-lg pull-right submit submit_once" value="{lang key='sendCustomerData' section='account data'}">
                    </form>
                </div>
            </div>
        {elseif $lpaDisplayMode === 'error'}
            <div class="alert alert-danger">
                {$oPlugin->getLocalization()->getTranslation('generic_error')}
            </div>
        {/if}
    </div>
{/strip}