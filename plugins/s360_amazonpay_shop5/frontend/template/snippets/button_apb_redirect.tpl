<div class="lpa-button-content">
    <div id="{$lpaButton.id}" class="lpa-button-container lpa-button-apb-redirect-container"
         data-context="{$lpaButton.context}"
         data-merchant-id="{$lpaButton.sellerId}"
         data-ledger-currency="{$lpaButton.ledgerCurrency}"
         data-language="{$lpaButton.language}"
         data-product-type="{$lpaButton.productType}"
         data-placement="{$lpaButton.placement}"
         data-color="{$lpaButton.color}"
         data-sandbox="{if $lpaButton.sandbox}true{else}false{/if}"
         data-estimated-order-amount-amount="{$lpaButton.estimatedOrderAmountAmount}"
         data-estimated-order-amount-currency="{$lpaButton.estimatedOrderAmountCurrency}"
         data-publickeyid="{$lpaButton.publicKeyId}"
         data-payload='{stripcslashes($lpaButton.payload)}'
         data-signature="{$lpaButton.signature}"
    ></div>
</div>