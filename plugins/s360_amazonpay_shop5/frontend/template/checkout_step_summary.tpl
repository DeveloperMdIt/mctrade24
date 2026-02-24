{strip}
    <div class="row">
        <div class="col-12 col-xs-12">
            <div class="alert alert-warning" id="lpa-confirm-error" style="display:none;"></div>
        </div>
        <div class="col-12 col-xs-12 col-sm-6 mb-1  bottom2">
            {* Shipping address as selected in Amazon Pay *}
            <h3>{lang key="shippingAdress" section="checkout"}</h3>
            <p>{include file='checkout/inc_delivery_address.tpl' Lieferadresse=$smarty.session.Lieferadresse}</p>
        </div>
        <div class="col-12 col-xs-12 col-sm-6 mb-1 bottom2">
            {* Payment method as selected in Amazon Pay *}
            <h3>{lang key="paymentMethod" section="checkout"}</h3>
            <p>{$lpaCheckoutGlobal.paymentDescription}</p>
        </div>
        <div class="col-12 col-xs-12 mb-5 bottom10">
            <a href="{$lpaCheckoutGlobal.checkoutUrl}" title="{lang key="change" section="global"}">{lang key="change" section="global"}</a>
        </div>
    </div>
    <form class="form evo-validate" method="post" id="lpa-confirm-order-form">
        {$jtl_token}
        <div class="row">
            <div class="col-xs-12 col-12">
                {lang key='agb' assign='agb'}
                {if !empty($AGB->cAGBContentHtml)}
                    {modal id="agb-modal" title=$agb}{$AGB->cAGBContentHtml}{/modal}
                {elseif !empty($AGB->cAGBContentText)}
                    {modal id="agb-modal" title=$agb}{$AGB->cAGBContentText}{/modal}
                {/if}
                {if $Einstellungen.kaufabwicklung.bestellvorgang_wrb_anzeigen == 1}
                    {lang key='wrb' section='checkout' assign='wrb'}
                    {lang key='wrbform' assign='wrbform'}
                    {if !empty($AGB->cWRBContentHtml)}
                        {modal id="wrb-modal" title=$wrb}{$AGB->cWRBContentHtml}{/modal}
                    {elseif !empty($AGB->cWRBContentText)}
                        {modal id="wrb-modal" title=$wrb}{$AGB->cWRBContentText}{/modal}
                    {/if}
                    {if !empty($AGB->cWRBFormContentHtml)}
                        {modal id="wrb-form-modal" title=$wrbform}{$AGB->cWRBFormContentHtml}{/modal}
                    {elseif !empty($AGB->cWRBFormContentText)}
                        {modal id="wrb-form-modal" title=$wrbform}{$AGB->cWRBFormContentText}{/modal}
                    {/if}
                {/if}

                <div class="checkout-confirmation-legal-notice">
                    <p>{$AGB->agbWrbNotice}</p>
                </div>

                {if !isset($smarty.session.cPlausi_arr)}
                    {assign var=plausiArr value=array()}
                {else}
                    {assign var=plausiArr value=$smarty.session.cPlausi_arr}
                {/if}

                {if !isset($cPost_arr)}
                    {assign var=cPost_arr value=$smarty.post}
                {/if}
                {hasCheckBoxForLocation bReturn="bCheckBox" nAnzeigeOrt=2 cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                {if $bCheckBox || $lpaCheckoutGlobal.isImmediateCapture}
                    <hr>
                    {getCheckBoxForLocation nAnzeigeOrt=2 cPlausi_arr=$plausiArr cPost_arr=$cPost_arr assign='checkboxes'}
                    {if !empty($checkboxes)}
                        {foreach $checkboxes as $cb}
                            {if $cb->nPflicht == 1}
                                {* Fix for JTL incompatiblity: the RightOfWithdrawalOfDownloadItems checkbox is ALWAYS present and not properly checked by the core *}
                                {if !isset($cb->identifier) || $cb->identifier !== 'RightOfWithdrawalOfDownloadItems' || ($cb->identifier === 'RightOfWithdrawalOfDownloadItems' && (isset($lpaCheckoutGlobal.hasDownloads) && $lpaCheckoutGlobal.hasDownloads === true))}
                                    <div class="row">
                                        <div class="col-12 col-xs-12">
                                            <div class="form-group">
                                                <div class="checkbox custom-control custom-checkbox">
                                                    <input class="custom-control-input" type="checkbox" name="{$cb->cID}" value="Y" id="{if isset($cIDPrefix)}{$cIDPrefix}_{/if}{$cb->cID}"{if $cb->isActive} checked{/if}>
                                                    <label class="control-label custom-control-label" for="{if isset($cIDPrefix)}{$cIDPrefix}_{/if}{$cb->cID}">
                                                        {$cb->cName}
                                                        {if !empty($cb->cLinkURL)}
                                                            <span class="moreinfo"> (<a href="{$cb->cLinkURL}" class="popup checkbox-popup">{lang key='read' section='account data'}</a>)</span>
                                                        {/if}
                                                    </label>
                                                </div>
                                                {if !empty($cb->cBeschreibung)}
                                                    <p class="description text-muted small">
                                                        {$cb->cBeschreibung}
                                                    </p>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            {/if}
                        {/foreach}
                    {/if}
                    {if $lpaCheckoutGlobal.isImmediateCapture}
                        <div class="row">
                            <div class="col-12 col-xs-12">
                                <div id="lpa-immediate-capture">
                                    <div class="form-group">
                                        <div class="checkbox custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" name="confirmImmediateCapture" required value="Y" id="lpa-immediate-capture-checkbox"/>
                                            <label class="control-label custom-control-label" for="lpa-immediate-capture-checkbox">{$oPlugin->getLocalization()->getTranslation('checkout_immediate_capture_title')}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                    <hr>
                {/if}
            </div>
        </div>
        <div class="row">
            {block name='checkout-confirmation-comment'}
                <div class="col-12 col-xs-12 mb-5 bottom10">
                    {if (!isset($smarty.session.kommentar) || empty($smarty.session.kommentar)) && !$lpaCheckoutGlobal['showCommentField']}
                        <p class="lpa-checkout-comment">
                            <a href="#" class="btn btn-primary" title="{lang key='orderComments' section='shipping payment'}" onclick="$(this).hide();$('#panel-edit-comment').show();return false;">
                                <i class="fa fas fa-pen fa-pencil"></i>&nbsp;{lang key='orderComments' section='shipping payment'}
                            </a>
                        </p>
                    {/if}
                    <div class="card panel panel-default" id="panel-edit-comment"{if (!isset($smarty.session.kommentar) || empty($smarty.session.kommentar)) && !$lpaCheckoutGlobal['showCommentField']} style="display:none;"{/if}>
                        <div class="card-header panel-heading">
                            <h3 class="panel-title mb-0">{block name='checkout-confirmation-comment-title'}{lang key='comment' section='product rating'}{/block}</h3>
                        </div>
                        <div class="panel-body card-body">
                            {block name='checkout-confirmation-comment-body'}
                                {lang assign='orderCommentsTitle' key='orderComments' section='shipping payment'}
                                <textarea class="form-control border-0" autocomplete="nope" title="{$orderCommentsTitle|escape:'html'}" name="kommentar" cols="50" rows="3" id="comment" placeholder="{lang key='yourOrderComment' section='login'}">{if isset($smarty.session.kommentar)}{$smarty.session.kommentar}{/if}</textarea>
                            {/block}
                        </div>
                    </div>
                </div>
            {/block}
        </div>
        <div class="row">
            <div class="col-12 col-xs-12 order-submit">
                <div class="basket-final">
                    {include file='checkout/inc_order_items.tpl' tplscope="confirmation"}
                </div>
            </div>
        </div>
        {if isset($lpaCheckoutGlobal.subscriptionInterval)}
            <div class="row">
                <div class="col-12 col-xs-12">
                    <div class="alert alert-info">
                        {$oPlugin->getLocalization()->getTranslation('subscription_checkout_hint')} {$lpaCheckoutGlobal.subscriptionInterval->toDisplayString()}
                    </div>
                </div>
            </div>
        {/if}
        <div class="row">
            <div class="col-12 col-xs-12 col-sm-6 col-sm-push-6 lpa-submit order-1 order-sm-2 mt-3">
                <button class="btn btn-primary btn-lg submit submit_once" id="lpa-order-liable-pay-button" type="submit" style="display:none;">{lang key="orderLiableToPay" section="checkout"}</button>
            </div>
            <div class="col-12 col-xs-12 col-sm-6 col-sm-pull-6 lpa-cancel order-2 order-sm-1 mt-3">
                <a href="{$lpaCheckoutGlobal.checkoutUrl}?cancelAmazonPay" onclick="amazon.Pay.signout();" rel="nofollow" title="{$oPlugin->getLocalization()->getTranslation('cancel_checkout')}">{$oPlugin->getLocalization()->getTranslation('cancel_checkout')}</a>
            </div>
        </div>
    </form>
    {* JS parts *}
    <script type="text/javascript">
        window.lpaJqAsync = window.lpaJqAsync || [];
        lpaJqAsync.push(['ready', function () {
            $('#lpa-confirm-order-form').on('submit', function (e) {
                e.preventDefault();
                var $confirmErrorField = $('#lpa-confirm-error');
                var $form = $('#lpa-confirm-order-form');
                var $inputs = $form.find("input, select, button, textarea");
                var formData = $form.serializeArray();
                var $overlay = $('#lpa-checkout-overlay');

                $confirmErrorField.hide();
                {* disable inputs while submitting *}
                $inputs.prop("disabled", true);
                $overlay.show();
                $form.css('cursor', 'wait');
                var request = $.ajax({
                    url: '{$ShopURLSSL}{$lpaCheckoutGlobal.ioPath}',
                    method: 'POST',
                    dataType: 'json',
                    data: {literal}'io={"name":"lpaAjaxConfirmOrder","params":' + JSON.stringify(formData) + '}'{/literal}
                });
                request.done(function (data) {
                    if (data.result === 'error') {
                        console.log(data);
                        var code = data.code ? data.code : null;
                        var message = data.message ? data.message : null;
                        $("html, body").animate({
                            scrollTop: 0
                        }, "slow");
                        switch (code) {
                            case 'missingParameters':
                            case 'csrfTokenMissing':
                            case 'csrfTokenInvalid':
                            case 'requiredCheckboxMissing':
                                $confirmErrorField.text(message).show();
                                break;
                            case 'cartChecksumInvalid':
                            case 'noCheckoutSessionExists':
                            case 'constraintsExist':
                            case 'checkoutSessionNotOpen':
                            case 'noAmazonPayRedirectUrl':
                            {* in this case a checkout is not possible - we force a reload of the checkout (the checkout controller will recognize that change of the cart, too) *}
                                $confirmErrorField.text(message).show();
                                window.location.reload(false);
                                break;
                            default:
                            {* This may occur if somebody messes with the currency in a different tab or sth like that *}
                                $confirmErrorField.text('{html_entity_decode($oPlugin->getLocalization()->getTranslation('error_checkout_generic'))}').show();
                                console.log('Unexpected error - Code: ', code, ', Message: ', message);
                                break;
                        }
                    } else if (data.result === 'success' && data.amazonPayRedirectUrl) {
                        {* Success! We redirect the user to Amazon Pay *}
                        window.location.href = data.amazonPayRedirectUrl;
                    } else {
                        console.log('Unexpected result: Expected success and amazonPayReturnUrl, but got: ', data);
                    }
                });
                request.fail(function (jqXHR, textStatus, errorThrown) {
                    console.log('Failed: ' + jqXHR + "," + textStatus + "," + errorThrown);
                });
                request.always(function () {
                    $inputs.prop("disabled", false);
                    $overlay.hide();
                    $form.css('cursor', 'default');
                });
            });
            {* Now show the button to prevent early submits *}
            $('#lpa-order-liable-pay-button').show();
        }]);
    </script>
{/strip}