{col cols=12 lg=6 class="account-data-item account-data-item-ppc-vaulting"}
    {card no-body=true}
        {cardheader}
            {row class="align-items-center-util"}
            {col}
                <span class="h3">
                    {link class='text-decoration-none-util' href="#"}
                    {$vaultingTranslations.header}
                    {/link}
                </span>
            {/col}
            {col class="col-auto"}
                <div class="d-inline-flex flex-nowrap">
                    <span id="ppc-vaulting-visibility-label-enabled" class="{if $ppcVaultingActive !== true}d-none{/if}">
                        {lang key='active'}
                    </span>
                    <span id="ppc-vaulting-visibility-label-disabled" class="{if $ppcVaultingActive}d-none{/if}">
                        {lang key='inactive'}
                    </span>
                    <div class="custom-control custom-switch">
                        <input type='checkbox'
                               class='custom-control-input ppc-vaulting-switch'
                               id="ppc-vaulting-visibility"
                               data-customer-id="{$customerId}"
                               data-label="{$vaultingTranslations.header}"
                               {if $ppcVaultingActive}checked{/if}
                               aria-label=""
                        >
                        <label class="custom-control-label" for="ppc-vaulting-visibility"></label>
                    </div>
                </div>
            {/col}
            {/row}
        {/cardheader}
        {cardbody class="d-flex justify-content-center align-items-center flex-column"}
            <p>{$vaultingTranslations.description}</p>
        {/cardbody}
    {/card}
{/col}
<script>
    (function ($) {
        let ppcVaultinHandler = new PPCVaultingHandler($);
        $(window).on('load', function () {
            ppcVaultinHandler.init({
                vaultingSwitch: '#ppc-vaulting-visibility',
                asyncMsg: {json_encode($vaultingTranslations.errMsg)}
            });
        });
    })(jQuery);
</script>