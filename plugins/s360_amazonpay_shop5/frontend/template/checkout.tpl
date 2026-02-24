{strip}
    <div class="lpa-checkout-wrapper container">
        {include file=$lpaCheckoutGlobal.templatePathCheckoutSteps lpaCheckoutStep=$lpaCheckoutStep}
        <div class="row">
            <div class="col-12 col-xs-12">
                <h3>{lang key="billingAdress" section="checkout"}</h3>
                {if $lpaCheckoutGlobal.useBillingAddressFromAmazonPay}
                    <div id="billing-address" class="mb-5">
                        <div class="alert alert-info">
                            {$oPlugin->getLocalization()->getTranslation('billing_override_hint')}
                        </div>
                        <p>
                            {include file='checkout/inc_billing_address.tpl' Kunde=$lpaCheckoutGlobal.amazonPayBillingAddress}
                        </p>
                    </div>
                {else}
                    <div id="billing-address" class="mb-5">
                        <p>
                            {include file='checkout/inc_billing_address.tpl' Kunde=$smarty.session.Kunde}
                        </p>
                    </div>
                {/if}
            </div>
        </div>
        {if $lpaCheckoutStep === 'shippingPayment'}
            {include file=$lpaCheckoutGlobal.templatePathStepShippingPayment}
        {elseif $lpaCheckoutStep === 'summary'}
            {include file=$lpaCheckoutGlobal.templatePathStepSummary}
        {else}
            {* Should not happen - unknown step - display generic error message and do nothing else. *}
            <div class="alert alert-danger">{$oPlugin->getLocalization()->getTranslation('error_generic')}</div>
        {/if}
    </div>
{/strip}