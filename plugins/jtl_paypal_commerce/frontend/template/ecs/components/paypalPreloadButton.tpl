<button id="ppc-paypal-button-{$ppcNamespace}" type="button"
        class="mb-1 btn btn-lg btn-block btn-ppc ppc-paypal-button-custom {$ppcConfig['shape']} {$ppcConfig['color']}">
    <div class="spinner-border spinner-border-sm mr-2 d-none hidden"
         id="ppc-loading-spinner-express-{$ppcNamespace}" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    {if $ppcConfig['color'] !== 'blue' && $ppcConfig['color'] !== 'black'}
        <img class="ppc-paypal-button-custom-img" src="{$ppcFrontendUrl}img/paypal_color.svg" alt="PayPal" />
    {else}
        <img class="ppc-paypal-button-custom-img" src="{$ppcFrontendUrl}img/paypal_white.svg" alt="PayPal" />
    {/if}
    <span>{$ppcPreloadButtonLabelInactive}</span>
</button>
