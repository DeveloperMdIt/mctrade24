<nav class="nav nav-wizard stepper row mb-3">
    <div class="{if $lpaCheckoutStep === 'shippingPayment'}active step-active step-current {/if}col-6 col-xs-6 nav-item step">
        {if $lpaCheckoutStep === 'summary'}
            <a href="{$lpaCheckoutGlobal.checkoutUrl}" rel="nofollow" title="{lang section='account data' key='shippingAndPaymentOptions'}" class="text-decoration-none">
                <div class="step-content">
                    <span class="badge badge-pill badge-primary mr-3 ml-md-auto">
                        <span class="badge-count">1</span>
                    </span>
                    <span class="step-text d-none d-md-inline-block mr-auto">
                        {lang section='account data' key='shippingAndPaymentOptions'}
                    </span>
                    <span class="fas fa-check ml-0 ml-md-3 mr-auto text-primary"></span>
                </div>
            </a>
        {else}
            <div class="step-content">
                <span class="badge badge-pill badge-primary mr-3 ml-md-auto">
                    <span class="badge-count">1</span>
                </span>
                <span class="step-text mr-auto">
                    {lang section='account data' key='shippingAndPaymentOptions'}
                </span>
            </div>
        {/if}
    </div>
    <div class="{if $lpaCheckoutStep === 'summary'}active active step-active step-current {/if}col-6 col-xs-6 nav-item step">
        <div class="step-content">
            <span class="badge badge-pill badge-{if $lpaCheckoutStep === 'summary'}primary mr-3{else}secondary{/if} mr-md-3 ml-md-auto">
                <span class="badge-count">2</span>
            </span>
            <span class="step-text {if !isset($step3_active) || !$step3_active}d-none d-md-inline-block{/if} mr-auto">
                {lang section='checkout' key='summary'}
            </span>
        </div>
    </div>
</nav>