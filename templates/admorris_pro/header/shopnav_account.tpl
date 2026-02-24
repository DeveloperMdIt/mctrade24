{*custom*}
{block name='layout-header-shop-nav-account'}
    {if !isset($layoutType)}
        {$layoutType = 'desktopLayout'}
    {/if}
    {$labelSetting = $headerLayout->getItemSetting('account', 'label', $layoutType)}

    {if !$labelSetting}
        {$labelSetting = 'icon'}
    {/if}

    {$showIcon = ($labelSetting|in_array:['icon', 'icon_text'])?true:false}
    {$showLabel = ($labelSetting|in_array:['text', 'icon_text'])?true:false}
    {$iconSpacing = ($labelSetting === 'icon_text')?' ':''}

    {strip}


    {if $layoutType !== 'desktopLayout'}
        <a href="{get_static_route id='jtl.php'}" title="{lang key='myAccount'}" class="nav-link shopnav__link">
            {if $showIcon}
            {$admIcon->renderIcon('user', 'icon-content icon-content--default icon-content--center shopnav__icon')}
            {/if}
            {$iconSpacing}
            
            <span class="shopnav__label icon-text--center{if !$showLabel} sr-only{/if}">
                {if empty($smarty.session.Kunde->kKunde)}
                    {lang key='login'}
                {else}
                    {lang key='myAccount'}
                {/if}
            </span>
            
        </a>
    {else}
        <div class="nav header-shop-nav">
            
            <div class="dropdown nav-item">
                {if empty($smarty.session.Kunde->kKunde)}
                    <button class="btn nav-link shopnav__link dropdown-toggle" data-toggle="dropdown" data-display="static" aria-expanded="false" title="{lang key='login'}">
                        {if $showIcon}
                                {$admIcon->renderIcon('user', 'icon-content icon-content--default icon-content--center  shopnav__icon')}
                        {/if}
                        {$iconSpacing}
                        <span class="shopnav__label icon-text--center{if !$showLabel} sr-only{/if}">{lang key='login'}</span>
                    </button>
                    <div id="login-dropdown" class="login-dropdown dropdown-menu dropdown-menu-right dropdown-menu--animated">

                        {form action="{get_static_route id='jtl.php' secure=true}" method="post" class="jtl-validate" slide=false}
                            {block name='layout-header-shop-nav-account-form-content'}
                                <fieldset id="quick-login">
                                    {block name='header-shop-nav-account-quick-login'}
                                        <legend class="login-dropdown__legend h5">
                                            {lang key='loginForRegisteredCustomers' section='checkout'}
                                        </legend>
                                    {/block}
                                    {block name='layout-header-nav-account-form-email'}
                                        {formgroup label-for="email_quick" label={lang key='emailadress'}}
                                            {input type="email" name="email" id="email_quick" size-class="sm"
                                                placeholder=" " required=true
                                                autocomplete="email"}
                                        {/formgroup}
                                    {/block}
                                    {block name='layout-header-nav-account-form-password'}
                                        {formgroup label-for="password_quick" label={lang key='password'} class="account-icon-dropdown-pass"}
                                            {input type="password" name="passwort" id="password_quick" size-class="sm"
                                                required=true placeholder=" "
                                                autocomplete="current-password"}
                                        {/formgroup}
                                    {/block}
                                    {block name='layout-header-nav-account-form-captcha'}
                                        {if isset($showLoginCaptcha) && $showLoginCaptcha}
                                            {formgroup class="simple-captcha-wrapper"}
                                                {captchaMarkup getBody=true}
                                            {/formgroup}
                                        {/if}
                                    {/block}
                                    {block name='layout-header-shop-nav-account-form-submit'}
                                        {input type="hidden" name="login" value="1"}
                                        {if !empty($oRedirect->cURL)}
                                            {foreach $oRedirect->oParameter_arr as $oParameter}
                                                {input type="hidden" name=$oParameter->Name value=$oParameter->Wert}
                                            {/foreach}
                                            {input type="hidden" name="r" value=$oRedirect->nRedirect}
                                            {input type="hidden" name="cURL" value=$oRedirect->cURL}
                                        {/if}
                                        {button type="submit" id="submit-btn" block=true variant="primary"}{lang key='login'}{/button}
                                    {/block}
                                </fieldset>
                            {/block}
                        {/form}

                        {block name='layout-header-nav-account-link-forgot-password'}
                            {link href="{get_static_route id='pass.php'}" rel="nofollow" title="{lang key='forgotPassword'}" class="password-forgotten-link d-block"}
                                {lang key='forgotPassword'}
                            {/link}
                        {/block}
                        {block name='layout-header-nav-account-link-register'}
                            {link href="{get_static_route id='registrieren.php'}" rel="nofollow" title="{lang key='registerNow'}" class="btn btn-link btn-block"}
                                {$admIcon->renderIcon('signIn', 'icon-content icon-content--default icon-content--center')}&nbsp;&nbsp;<span class="icon-text--center">{lang key='registerNow'}</span>
                            {/link}
                        {/block}
                    </div>
                {else}
                    <button class="btn nav-link dropdown-toggle shopnav__link" data-toggle="dropdown" data-display="static" aria-expanded="false">
                        {if $showIcon}{$admIcon->renderIcon('user', 'icon-content icon-content--default icon-content--center shopnav__icon')}{/if}{$iconSpacing}
                        {if $showLabel}<span class="{if $showIcon}d-none d-xl-inline{/if} icon-text--center">
                            {* {lang key='hello'} {if $smarty.session.Kunde->cAnrede === 'w'}{$Anrede_w}{elseif $smarty.session.Kunde->cAnrede === 'm'}{$Anrede_m}{/if} {$smarty.session.Kunde->cNachname} *}
                            <span id="myAccountLabel" class="sr-only">{lang key="myAccount"}</span>
                            {lang key='hello'} {$smarty.session.Kunde->cVorname}
                        </span>{/if}
                        {* <span class="caret"></span> *}
                    </button>
                    <nav aria-labelledby="myAccountLabel" class="account-dropdown dropdown-menu dropdown-menu-right dropdown-menu--animated">
                    {block name='layout-header-shop-nav-account-logged-in'}
                        {get_static_route id='jtl.php' secure=true assign='secureAccountURL'}
                            {dropdownitem href=$secureAccountURL title="{lang key='myAccount'}"}
                                {lang key='myAccount'}
                            {/dropdownitem}
                            {dropdownitem href="{$secureAccountURL}?bestellungen=1" title="{lang key='myAccount'}"}
                                {lang key='myOrders'}
                            {/dropdownitem}
                            {dropdownitem href="{$secureAccountURL}?editRechnungsadresse=1" title="{lang key='myAccount'}"}
                                {lang key='myPersonalData'}
                            {/dropdownitem}
                            {dropdownitem href="{$secureAccountURL}?editLieferadresse=1" title="{lang key='myAccount'}"}
                                {lang key='myShippingAddresses'}
                            {/dropdownitem}
                        {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
                                {dropdownitem href="{get_static_route id='wunschliste.php'}" title="{lang key='myAccount'}"}
                                    {lang key='myWishlists'}
                                {/dropdownitem}
                        {/if}
                        {dropdowndivider}
                        {dropdownitem href="{$secureAccountURL}?logout=1" title="{lang key='logOut'}" class="account-icon-dropdown-logout"}
                            {lang key='logOut'}
                        {/dropdownitem}
                    {/block}
                    </nav>
                {/if}
            </div>
        </div>

    {/if}


    {/strip}

{/block}
