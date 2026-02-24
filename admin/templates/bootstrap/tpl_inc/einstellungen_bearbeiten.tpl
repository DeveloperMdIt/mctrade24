{if isset($cSearch) && $cSearch|strlen  > 0}
    {assign var=title value=$cSearch}
{/if}
{include file='tpl_inc/seite_header.tpl' cTitel=$title cBeschreibung='' cDokuURL=$cPrefURL}
{$search = isset($cSuche) && !empty($cSuche)}
{if $search}
    <script>
        $(function() {
            var $element = $('.input-group.highlight');
            if ($element.length > 0) {
                var height = $element.height(),
                    offset = $element.offset().top,
                    wndHeight = $(window).height();
                if (height < wndHeight) {
                    offset = offset - ((wndHeight / 2) - (height / 2));
                }

                $('html, body').stop().animate({ scrollTop: offset }, 400);
            }
        });
    </script>
{/if}
<script>
    $(highlightTargetFormGroup);
    window.addEventListener('hashchange', highlightTargetFormGroup);
</script>
<div id="content">
    <div id="settings">
        {if $testResult|default:null !== null}
            <div class="card">
                <div class="card-body">
                    <pre>{$testResult}</pre>
                </div>
            </div>
        {/if}
        {if isset($sections) && count($sections) > 0}
            <form name="einstellen" method="post" action="{$action|default:''}" class="settings navbar-form">
                {$jtl_token}
                <input type="hidden" name="einstellungen_bearbeiten" value="1" />
                {if $search}
                    <input type="hidden" name="cSuche" value="{$cSuche}" />
                    <input type="hidden" name="einstellungen_suchen" value="1" />
                {/if}
                <input type="hidden" name="kSektion" value="{$kEinstellungenSektion}" />
                {include file='tpl_inc/config_sections.tpl'}
                <div class="save-wrapper">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            {foreach $sections as $section}
                                {if $section->getID() === $smarty.const.CONF_EMAILS}
                                    <script>
                                    $(function() {
                                        let $emailMethodInput = $('#email_methode');
                                        let $configTextBtn    = $('#configTest');
                                        let $tenantGroup      = $('#oauth_tenant_id').closest('.form-group');
                                        let $smtpUserGroup    = $('#email_smtp_user').closest('.form-group');

                                        let $oauthGroups = $(
                                                `#oauth_client_id, #oauth_client_secret, #oauth_tenant_id,
                                                #oauth_access_token, #oauth_refresh_token, #oauth_authorize_btn`
                                        ).closest('.form-group');

                                        let $smtpGroups = $(
                                                `#email_smtp_hostname, #email_smtp_port, #email_smtp_auth,
                                                #email_smtp_user, #email_smtp_pass, #email_smtp_verschluesselung`
                                        ).closest('.form-group');

                                        let $sendmailPath = $('#email_sendmail_pfad').closest('.form-group');

                                        updateMailConfigUI();

                                        $emailMethodInput.on('change', function () {
                                            updateMailConfigUI();
                                        });

                                        function updateMailConfigUI()
                                        {
                                            let method = $emailMethodInput.val();
                                            $oauthGroups.hide();
                                            $configTextBtn.hide();
                                            $smtpGroups.hide();
                                            $sendmailPath.hide();
                                            if (method === 'outlook') {
                                                $oauthGroups.show();
                                                $configTextBtn.show();
                                                $smtpUserGroup.show();
                                            } else if (method === 'gmail') {
                                                $oauthGroups.show();
                                                $configTextBtn.show();
                                                $tenantGroup.hide();
                                                $smtpUserGroup.show();
                                            } else if (method === 'smtp') {
                                                $configTextBtn.show();
                                                $smtpGroups.show();
                                            } else if (method === 'sendmail') {
                                                $sendmailPath.show();
                                            }
                                        }
                                    });
                                    </script>
                                    <button type="submit" name="test_emails" value="1"
                                            class="btn btn-secondary btn-block" id="configTest">
                                        {__('saveWithconfigTest')}
                                    </button>
                                {/if}
                            {/foreach}
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            {include file='snippets/buttons/saveButton.tpl' value="{__('savePreferences')}"
                                scrollFunction=true}
                        </div>
                    </div>
                </div>
            </form>
        {else}
            <div class="alert alert-info">{__('noSearchResult')}</div>
        {/if}
    </div>
</div>
