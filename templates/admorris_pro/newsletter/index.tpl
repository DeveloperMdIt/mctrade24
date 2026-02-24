{block name='newsletter-index'}
    {block name='newsletter-index-include-header'}
    {include file='layout/header.tpl'}
{/block}

{block name='newsletter-index-content'}
    {block name='newsletter-index-heading'}
        {if !empty($Link->getTitle())}
            {opcMountPoint id='opc_before_newsletter_heading'}
            <div>
                <h1>{$Link->getTitle()}</h1>
            </div>
        {/if}
    {/block}
    {include file='snippets/extension.tpl'}
    {assign var=cPost_arr value=$cPost_arr|default:[]}
    {block name='newsletter-index-link-content'}
        {if !empty($Link->getContent())}
            {opcMountPoint id='opc_before_newsletter_content'}
            <div class="bottom15">
                {$Link->getContent()}
            </div>
        {/if}
    {/block}
    {if $cOption === 'eintragen'}
        {if empty($bBereitsAbonnent)}
            {block name='newsletter-index-newsletter-subscribe-form'}
            {opcMountPoint id='opc_before_newsletter_subscribe'}
            <div id="newsletter-subscribe" class="panel-wrap">
                <div class="card ">
                    <div class="card-header">
                        {block name='newsletter-subscribe-title'}{lang key='newsletterSubscribe' section='newsletter'}{/block}
                    </div>
                    <div class="card-body">
                        {block name='newsletter-subscribe-body'}
                        <p>{lang key='newsletterSubscribeDesc' section='newsletter'}</p>

                        <form method="post" action="{get_static_route id='newsletter.php'}" role="form" class="jtl-validate">
                            <fieldset>
                                {if !empty($oPlausi->cPost_arr.cVorname)}
                                    {assign var='inputVal_firstname' value=$oPlausi->cPost_arr.cVorname}
                                {elseif !empty($oKunde->cVorname)}
                                    {assign var='inputVal_firstname' value=$oKunde->cVorname}
                                {/if}
                                {include file='snippets/form_group_simple.tpl'
                                    options=[
                                        'text', 'newsletterfirstname', 'cVorname',
                                        {$inputVal_firstname|default:null}, {lang key='newsletterfirstname' section='newsletter'},
                                        false, null, 'given-name'
                                    ]
                                }
                                {if !empty($oPlausi->cPost_arr.cNachname)}
                                    {assign var='inputVal_lastName' value=$oPlausi->cPost_arr.cNachname}
                                {elseif !empty($oKunde->cNachname)}
                                    {assign var='inputVal_lastName' value=$oKunde->cNachname}
                                {/if}
                                {include file='snippets/form_group_simple.tpl'
                                    options=[
                                        'text', 'lastName', 'cNachname',
                                        {$inputVal_lastName|default:null}, {lang key='newsletterlastname' section='newsletter'},
                                        false, null, 'family-name'
                                    ]
                                }
                                {if !empty($oPlausi->cPost_arr.cEmail)}
                                    {assign var='inputVal_email' value=$oPlausi->cPost_arr.cEmail}
                                {elseif !empty($oKunde->cMail)}
                                    {assign var='inputVal_email' value=$oKunde->cMail}
                                {/if}
                                {block name='newsletter-index-form-email'}
                                {include file='snippets/form_group_simple.tpl'
                                    options=[
                                        'email', 'email', 'cEmail',
                                        {$inputVal_email|default:null}, {lang key='newsletteremail' section='newsletter'},
                                        true, null, 'email'
                                    ]
                                }
                                {/block}
                                {assign var=plausiArr value=$oPlausi->nPlausi_arr|default:[]}
                                {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
                                    isset($Einstellungen.newsletter.newsletter_sicherheitscode) && $Einstellungen.newsletter.newsletter_sicherheitscode !== 'N' && empty($smarty.session.Kunde->kKunde)}
                                    <hr>
                                    <div class="form-group float-label-control{if !empty($plausiArr.captcha) && $plausiArr.captcha === true}} has-error{/if}">
                                    {captchaMarkup getBody=true}
                                    </div>
                                {/if}
                                {hasCheckBoxForLocation nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr bReturn="bHasCheckbox"}
                                {if $bHasCheckbox}
                                    <hr>
                                    {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                                    <hr>
                                {/if}

                                <div class="form-group">
                                    {$jtl_token}
                                        <input type="hidden" name="abonnieren" value="1" />
                                        <button type="submit" class="btn btn-primary submit">
                                            <span>{lang key='newsletterSendSubscribe' section='newsletter'}</span>
                                        </button>
                                        {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ])}
                                            <p class="info small">
                                                {lang key='newsletterInformedConsent' section='newsletter' printf=$oSpezialseiten_arr[$smarty.const.LINKTYP_DATENSCHUTZ]->getURL()}
                                            </p>
                                        {/if}
                                </div>
                            </fieldset>
                        </form>
                        {/block}
                    </div>
                </div>
            </div>
            {/block}
        {/if}

        {block name='newsletter-unsubscribe'}
        {opcMountPoint id='opc_before_newsletter_unsubscribe'}
        <div id="newsletter-unsubscribe" class="panel-wrap top15">
            <div class="card ">
                <div class="card-header">
                    {block name='newsletter-unsubscribe-title'}{lang key='newsletterUnsubscribe' section='newsletter'}{/block}
                </div>
                <div class="card-body">
                    {block name='newsletter-unsubscribe-body'}
                    <p>{lang key='newsletterUnsubscribeDesc' section='newsletter'}</p>

                    <form method="post" action="{get_static_route id='newsletter.php'}" name="newsletterabmelden" class="jtl-validate">
                        <fieldset>
                            {include file='snippets/form_group_simple.tpl'
                                options=[
                                    'email', 'checkOut', 'cEmail',
                                    {$oKunde->cMail|default:null}, {lang key='newsletteremail' section='newsletter'},
                                    true, $oFehlendeAngaben->cUnsubscribeEmail|default:null, 'email'
                                ]
                            }
                            {$jtl_token}
                            <input type="hidden" name="abmelden" value="1" />
                            <button type="submit" class="submit btn btn-secondary">
                                <span>{lang key='newsletterSendUnsubscribe' section='newsletter'}</span>
                            </button>
                        </fieldset>
                    </form>
                    {/block}
                </div>
            </div>
        </div>
        {/block}
    {elseif $cOption === 'anzeigen'}
        {if isset($oNewsletterHistory) && $oNewsletterHistory->kNewsletterHistory > 0}
            {block name='newsletter-history'}
            <h2>{lang key='newsletterhistory' section='global'}</h2>
            <div id="newsletterContent">
                <div class="newsletter">
                    <p class="newsletterSubject">
                        <strong>{lang key='newsletterdraftsubject' section='newsletter'}:</strong> {$oNewsletterHistory->cBetreff}
                    </p>
                    <p class="newsletterReference smallfont">
                        {lang key='newsletterdraftdate' section='newsletter'}: {$oNewsletterHistory->Datum}
                    </p>
                </div>

                <fieldset id="newsletterHtml">
                    <legend>{lang key='newsletterHtml' section='newsletter'}</legend>
                    {$oNewsletterHistory->cHTMLStatic|replace:'src="http://':'src="//'}
                </fieldset>
            </div>
            {/block}
        {else}
            <div class="alert alert-danger">{lang key='noEntriesAvailable' section='global'}</div>
        {/if}
    {/if}
{/block}

{block name='footer'}
    {include file='layout/footer.tpl'}
{/block}
{/block}