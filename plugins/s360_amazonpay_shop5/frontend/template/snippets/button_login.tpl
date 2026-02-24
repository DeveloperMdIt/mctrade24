<div class="lpa-button-content row">
    <div class="lpa-button-content-cols {$lpaButton.cssColumns}">
        <div id="{$lpaButton.id}" class="lpa-button-container lpa-button-login-container" style="min-height:{$lpaButton.height}px;height:{$lpaButton.height}px;"
             data-merchant-id="{$lpaButton.sellerId}"
             data-ledger-currency="{$lpaButton.ledgerCurrency}"
             data-language="{$lpaButton.language}"
             data-product-type="{$lpaButton.productType}"
             data-placement="{$lpaButton.placement}"
             data-color="{$lpaButton.color}"
             data-sandbox="{if $lpaButton.sandbox}true{else}false{/if}"
             data-publickeyid="{$lpaButton.publicKeyId}"
             data-payload='{stripcslashes($lpaButton.payload)}'
             data-signature="{$lpaButton.signature}"
        ></div>
    </div>
</div>