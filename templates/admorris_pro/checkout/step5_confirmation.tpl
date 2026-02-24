{block name='checkout-step5-confirmation'}
    <div id="order-confirm" class="checkout-confirmation">
        {block name='checkout-step5-confirmation-alert'}
            {if !empty($smarty.get.mailBlocked)}
                {alert variant="danger"}{lang key='kwkEmailblocked' section='errorMessages'}{/alert}
            {/if}
            {if !empty($smarty.get.fillOut)}
                {alert variant="danger"}{lang key='mandatoryFieldNotification' section='errorMessages'}{/alert}
            {/if}
        {/block}

        {row class="checkout-confirmation-above-productlist"}
            {col cols=12 md=6 id="billing-address"}
                {block name='checkout-step5-confirmation-delivery-billing-address'}
                    {card no-body=true class="checkout-confirmation-billing-address card-gray"}
                        {cardheader}
                            {block name='checkout-step5-confirmation-delivery-billing-address-header'}
                                <span class="h4 d-flex align-items-center justify-content-between">
                                    {row class='align-items-center w-100'}
                                        {col}
                                            <span class="checkout-confirmation-heading">{lang section="account data" key='billingAndDeliveryAddress'}</span>
                                        {/col}
                                        {col class='col-auto d-flex'}
                                            {button variant="link"
                                                size="sm"
                                                href="{get_static_route id='bestellvorgang.php'}?editRechnungsadresse=1"
                                                aria=['label'=>{lang key='change'}]
                                            }
                                                <span class="checkout-confirmation-change">{lang key='change'}</span>
                                                {$admIcon->renderIcon('pencil', 'icon-content float-right')}
                                            {/button}
                                        {/col}
                                    {/row}
                                    <button type="button" class="border-0 p-0 bg-transparent" data-toggle="collapse" data-target="#checkout-step5-confirmation-delivery-billing-address-collapse" aria-expanded="true">
                                        <span class="sr-only">Toggle {lang section="account data" key='billingAndDeliveryAddress'}</span>
                                    </button>
                                </span>
                            {/block}
                        {/cardheader}
                        {collapse id="checkout-step5-confirmation-delivery-billing-address-collapse" visible=true}
                            {cardbody}
                                {block name='checkout-step5-confirmation-delivery-billing-address-body'}
                                <div class="switcher">
                                    <div class="checkout-confirmation__billing-address">
                                        {block name='checkout-step5-confirmation-include-inc-billing-address'}
                                            <p><strong class="title">{lang key='billingAdress' section='account data'}</strong></p>
                                            <p>{include file='checkout/inc_billing_address.tpl'}</p>
                                        {/block}
                                    </div>
                                    <div class="checkour-confirmation__delivery-address">
                                        {block name='checkout-step5-confirmation-include-inc-delivery-address'}
                                            <p><strong class="title">{lang key='shippingAdress' section='account data'}</strong></p>
                                            <p>{include file='checkout/inc_delivery_address.tpl'}</p>
                                        {/block}
                                    </div>
                                </div>
                                {/block}
                            {/cardbody}
                        {/collapse}
                    {/card}
                {/block}
            {/col}
            {col cols=12 md=6 id="shipping-method"}
                {block name='checkout-step5-confirmation-shipping-billing-method'}
                    {card no-body=true class="checkout-confirmation-shipping card-gray"}
                        {cardheader}
                            {block name='checkout-step5-confirmation-shipping-billing-method-header'}
                                <span class="h4 d-flex align-items-center justify-content-between">
                                    {row class='align-items-center w-100'}
                                        {col}
                                            <span class="checkout-confirmation-heading">{lang section="account data" key='shippingAndPaymentOptions'}</span>
                                        {/col}
                                        {col class='col-auto d-flex'}
                                            {button variant="link"
                                                size="sm"
                                                href="{get_static_route id='bestellvorgang.php'}?editVersandart=1"
                                                aria=['label'=>{lang key='change'}]
                                            }
                                                <span class="checkout-confirmation-change">{lang key='change'}</span>
                                                {$admIcon->renderIcon('pencil', 'icon-content float-right')}
                                            {/button}
                                        {/col}
                                    {/row}
                                    <button type="button" class="border-0 p-0 bg-transparent" data-toggle="collapse" data-target="#checkout-step5-confirmation-shipping-billing-method-collapse" aria-expanded="true">
                                        <span class="sr-only">Toggle {lang section="account data" key='shippingAndPaymentOptions'}</span>
                                    </button>
                                </span>
                            {/block}
                        {/cardheader}
                        {collapse id="checkout-step5-confirmation-shipping-billing-method-collapse" visible=true}
                            {cardbody}
                                {block name='checkout-step5-confirmation-shipping-billing-method-body'}
                                    <div class="switcher">
                                        <div class="checkout-confirmation__shipping-method">
                                            {block name='checkout-step5-confirmation-shipping-method'}
                                                <p><strong class="title">{lang key='shippingOptions'}</strong></p>
                                                <p>{$smarty.session.Versandart->angezeigterName|transByISO}</p>
                                                {$cEstimatedDelivery = JTL\Session\Frontend::getCart()->getEstimatedDeliveryTime()}
                                                {if $cEstimatedDelivery|strlen > 0}
                                                    <p class="small text-muted">
                                                        <strong>{lang key='shippingTime'}</strong>: {$cEstimatedDelivery}
                                                    </p>
                                                {/if}
                                            {/block}
                                        </div>
                                        <div class="checkout-confirmation__payment-method">
                                            {block name='checkout-step5-confirmation-payment-method'}
                                                <p><strong class="title">{lang key='paymentOptions'}</strong></p>
                                                <p>{$smarty.session.Zahlungsart->angezeigterName|transByISO}</p>
                                                {if isset($smarty.session.Zahlungsart->cHinweisText) && !empty($smarty.session.Zahlungsart->cHinweisText)}{* this should be localized *}
                                                    <p class="small text-muted">{$smarty.session.Zahlungsart->cHinweisText}</p>
                                                {/if}
                                            {/block}
                                        </div>
                                    </div>
                                {/block}
                            {/cardbody}
                        {/collapse}
                    {/card}
                {/block}
            {/col}

            {if $KuponMoeglich}
                {col cols=12 md=6}
                    {block name='checkout-step5-confirmation-coupon'}
                        {card no-body=true id="panel-edit-coupon" class="min-h-card card-gray"}
                            {cardheader}
                                {block name='checkout-step5-confirmation-coupon-header'}
                                    <span class="h4 checkout-confirmation-heading d-flex align-items-center justify-content-between">
                                        {lang key='useCoupon' section='checkout'}
                                        <button type="button" class="border-0 p-0 bg-transparent" data-toggle="collapse" data-target="#checkout-step5-confirmation-include-coupon-collapse" aria-expanded="false">
                                            <span class="sr-only">Toggle {lang key='useCoupon' section='checkout'}</span>
                                        </button>
                                    </span>
                                {/block}
                            {/cardheader}
                            {collapse id="checkout-step5-confirmation-include-coupon-collapse" visible=false}
                                {cardbody}
                                    {block name='checkout-step5-confirmation-include-coupon-form'}
                                        {include file='checkout/coupon_form.tpl'}
                                    {/block}
                                {/cardbody}
                            {/collapse}
                        {/card}
                    {/block}
                {/col}
            {/if}

            {if $GuthabenMoeglich}
                {block name='checkout-step5-confirmation-credit'}
                    {col cols=12 md=6}
                        {card id="panel-edit-credit" no-body=true class="min-h-card card-gray"}
                            {cardheader}
                                {block name='checkout-step5-confirmation-credit-header'}
                                    <span class="h4 checkout-confirmation-heading d-flex align-items-center justify-content-between">
                                        {lang key='credit' section='account data'}
                                        <button type="button" class="border-0 p-0 bg-transparent" data-toggle="collapse" data-target="#checkout-step5-confirmation-include-credit-collapse" aria-expanded="false">
                                            <span class="sr-only">Toggle {lang key='credit' section='account data'}</span>
                                        </button>
                                    </span>
                                {/block}
                            {/cardheader}
                            {collapse id="checkout-step5-confirmation-include-credit-collapse" visible=false}
                                {cardbody}
                                    {block name='checkout-step5-confirmation-include-credit-form'}
                                        {include file='checkout/credit_form.tpl'}
                                    {/block}
                                {/cardbody}
                            {/collapse}
                        {/card}
                    {/col}
                {/block}
            {/if}
            {col cols=12 md=6}
                {block name='checkout-step5-confirmation-comment'}
                    {card no-body=true id="panel-edit-comment" class="min-h-card card-gray"}
                        {cardheader}
                            {block name='checkout-step5-confirmation-comment-header'}
                                <span class="h4 checkout-confirmation-heading d-flex align-items-center justify-content-between">
                                    {lang key='comment' section='product rating'}
                                    <button type="button" class="border-0 p-0 bg-transparent" data-toggle="collapse" data-target="#checkout-step5-confirmation-comment-collapse" aria-expanded="false">
                                        <span class="sr-only">Toggle {lang key='comment' section='product rating'}</span>
                                    </button>
                                </span>
                            {/block}
                        {/cardheader}
                        {collapse id="checkout-step5-confirmation-comment-collapse" visible=false}
                            {cardbody}
                                {block name='checkout-step5-confirmation-comment-body'}
                                    {lang assign='orderCommentsTitle' key='orderComments' section='shipping payment'}
                                    {textarea title=$orderCommentsTitle|escape:'html'
                                        name="kommentar"
                                        cols="50"
                                        rows="3"
                                        id="comment"
                                        placeholder=$orderCommentsTitle|escape:'html'
                                        aria=["label"=>$orderCommentsTitle|escape:'html']
                                        class="checkout-confirmation-comment"
                                    }
                                        {if isset($smarty.session.kommentar)}{$smarty.session.kommentar}{/if}
                                    {/textarea}
                                {/block}
                            {/cardbody}
                        {/collapse}
                    {/card}
                {/block}
            {/col}
        {/row}


        {block name="checkout-step5-confirmation-pre-form-hr"}
            <hr class="checkout-confirmation-pre-form-hr">
        {/block}

        {block name='checkout-step5-confirmation-form'}
            {form method="post" name="agbform" id="complete_order" action="{get_static_route id='bestellabschluss.php'}" class="jtl-validate"}
                {block name='checkout-step5-confirmation-form-content'}
                    {lang key='agb' assign='agb'}
                    {if !empty($AGB->cAGBContentHtml)}
                        {block name='checkout-step5-confirmation-modal-agb-html'}
                            {modal id="agb-modal" title=$agb}{$AGB->cAGBContentHtml}{/modal}
                        {/block}
                    {elseif !empty($AGB->cAGBContentText)}
                        {block name='checkout-step5-confirmation-modal-agb-text'}
                            {modal id="agb-modal" title=$agb}{$AGB->cAGBContentText}{/modal}
                        {/block}
                    {/if}
                    {if $Einstellungen.kaufabwicklung.bestellvorgang_wrb_anzeigen == 1}
                        {lang key='wrb' section='checkout' assign='wrb'}
                        {lang key='wrbform' assign='wrbform'}
                        {if !empty($AGB->cWRBContentHtml)}
                            {block name='checkout-step5-confirmation-modal-wrb-html'}
                                {modal id="wrb-modal" title=$wrb}{$AGB->cWRBContentHtml}{/modal}
                            {/block}
                        {elseif !empty($AGB->cWRBContentText)}
                            {block name='checkout-step5-confirmation-modal-wrb-text'}
                                {modal id="wrb-modal" title=$wrb}{$AGB->cWRBContentText}{/modal}
                            {/block}
                        {/if}
                        {if !empty($AGB->cWRBFormContentHtml)}
                            {block name='checkout-step5-confirmation-modal-wrb-form-html'}
                                {modal id="wrb-form-modal" title=$wrbform}{$AGB->cWRBFormContentHtml}{/modal}
                            {/block}
                        {elseif !empty($AGB->cWRBFormContentText)}
                            {block name='checkout-step5-confirmation-modal-wrb-form-text'}
                                {modal id="wrb-form-modal" title=$wrbform}{$AGB->cWRBFormContentText}{/modal}
                            {/block}
                        {/if}
                    {/if}
                    {block name='checkout-step5-confirmation-check'}{* custom admorris pro block *}
                        <div class="checkout-confirmation-check">
                            {block name='checkout-step5-confirmation-alert-agb'}
                                <div class="checkout-confirmation-legal-notice">
                                    <p>{$AGB->agbWrbNotice}</p>
                                </div>
                            {/block}

                            {if !isset($smarty.session.cPlausi_arr)}
                                {assign var=plausiArr value=[]}
                            {else}
                                {assign var=plausiArr value=$smarty.session.cPlausi_arr}
                            {/if}

                            {hasCheckBoxForLocation bReturn="bCheckBox" nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                            {if $bCheckBox}
                                {block name='checkout-step5-confirmation-include-checkbox'}
                                    <hr>
                                    {include file='snippets/checkbox.tpl' nAnzeigeOrt=$nAnzeigeOrt cPlausi_arr=$plausiArr cPost_arr=$cPost_arr}
                                    <hr>
                                {/block}
                            {/if}
                        </div>
                    {/block}
                    {row}
                        {col cols=12 class="order-submit"}
                            {block name='checkout-step5-confirmation-confirm-order'}
                            <div class="checkout-confirmation-items basket-final">
                                <div id="panel-submit-order">
                                    {input type="hidden" name="abschluss" value="1"}
                                    {input type="hidden" id="comment-hidden" name="kommentar" value=""}
                                    {block name='checkout-step5-confirmation-order-items'}
                                        {card no-body=true class='card-gray card-products checkout-confirmation-items__card'}
                                            {cardheader}
                                                {block name='checkout-step5-confirmation-order-items-header'}
                                                {/block}
                                            {/cardheader}
                                            {cardbody}
                                                {block name='checkout-step5-confirmation-include-inc-order-items'}
                                                    {include file='checkout/inc_order_items.tpl' tplscope='confirmation'}
                                                {/block}
                                            {/cardbody}
                                        {/card}
                                    {/block}
                                    {block name='checkout-step5-confirmation-order-items-actions'}
                                        {row class="checkout-button-row"}
                                            {col cols=12 md=6 lg=4 class='ml-auto order-1 order-md-2'}
                                                {button type="submit" variant="primary" id="complete-order-button" block=true class="submit_once button-row-mb"}
                                                    {lang key='orderLiableToPay' section='checkout'}
                                                {/button}
                                            {/col}
                                            {col cols=12 md=6 lg=3 class='order-2 order-md-1'}
                                                {link href="{get_static_route id='warenkorb.php'}" class="btn btn-outline-primary btn-block"}
                                                    {lang key='modifyBasket' section='checkout'}
                                                {/link}
                                            {/col}
                                        {/row}
                                    {/block}
                                </div>
                            </div>
                            {/block}
                        {/col}
                    {/row}
                {/block}
            {/form}
        {/block}
    </div>
{/block}
