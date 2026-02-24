<div class="lpa-button-content row">
    <div class="lpa-button-content-cols {$lpaButton.cssColumns}">
        {if !empty($lpaButton.subscriptionIntervals)}
            <div class="lpa-button-subscription" id="{$lpaButton.id}-subscription">
                <label for="{$lpaButton.id}-subscription-select">
                    <span class="lpa-button-subscription-label-title">{$lpaButton.subscriptionLabelTitle}</span>
                    <span class="lpa-button-subscription-label-text">{$lpaButton.subscriptionLabelText}</span>
                    {if !empty($lpaButton.subscriptionDiscountText)}<span class="lpa-button-subscription-discount-text">{$lpaButton.subscriptionDiscountText}</span>{/if}
                </label>
                <select class="form-control form-control-sm" id="{$lpaButton.id}-subscription-select">
                    <option value="none"{if $lpaButton.selectedSubscriptionInterval === null} selected="selected"{/if}>{$lpaButton.subscriptionNoneText}</option>
                    {foreach $lpaButton.subscriptionIntervals as $subscriptionInterval}
                        <option value="{$subscriptionInterval->toString()}"{if $lpaButton.selectedSubscriptionInterval !== null && $lpaButton.selectedSubscriptionInterval->toString() === $subscriptionInterval->toString()} selected="selected"{/if}>{$subscriptionInterval->toDisplayString()}</option>
                    {/foreach}
                </select>
            </div>
        {/if}
        <div id="{$lpaButton.id}" class="lpa-button-container lpa-button-pay-container lpa-button-group-{$lpaButton.id}" style="min-height:{$lpaButton.height}px;height:{$lpaButton.height}px;"
             data-context="{$lpaButton.context}"
             data-product-id="{if $lpaButton.context === 'payCategory'}{$lpaButton.productId}{/if}"
             data-msg-required-field-missing="{$lpaButton.requiredFieldMissingMessage}"
             data-shop-url="{$ShopURLSSL}"
             data-io-path="{$lpaButton.ioPath}"
             data-merchant-id="{$lpaButton.sellerId}"
             data-ledger-currency="{$lpaButton.ledgerCurrency}"
             data-language="{$lpaButton.language}"
             data-product-type="{$lpaButton.productType}"
             data-placement="{$lpaButton.placement}"
             data-color="{$lpaButton.color}"
             data-sandbox="{if $lpaButton.sandbox}true{else}false{/if}"
             data-estimated-order-amount-amount="{$lpaButton.estimatedOrderAmountAmount}"
             data-estimated-order-amount-currency="{$lpaButton.estimatedOrderAmountCurrency}"
        ></div>
        {if !empty($lpaButton.subscriptionIntervals)}
            {foreach $lpaButton.subscriptionIntervals as $subscriptionInterval}
                <div id="{$lpaButton.id}-subscription-{$subscriptionInterval->toString()}" class="lpa-button-container lpa-button-pay-container lpa-button-group-{$lpaButton.id}" style="height:{$lpaButton.height}px;"
                     data-context="{$lpaButton.context}"
                     data-product-id="{if $lpaButton.context === 'payCategory'}{$lpaButton.productId}{/if}"
                     data-msg-required-field-missing="{$lpaButton.requiredFieldMissingMessage}"
                     data-shop-url="{$ShopURLSSL}"
                     data-io-path="{$lpaButton.ioPath}"
                     data-merchant-id="{$lpaButton.sellerId}"
                     data-ledger-currency="{$lpaButton.ledgerCurrency}"
                     data-language="{$lpaButton.language}"
                     data-product-type="{$lpaButton.productType}"
                     data-placement="{$lpaButton.placement}"
                     data-color="{$lpaButton.color}"
                     data-sandbox="{if $lpaButton.sandbox}true{else}false{/if}"
                     data-subscription-interval="{$subscriptionInterval->toString()}"
                     data-estimated-order-amount-amount="{$lpaButton.estimatedOrderAmountAmount}"
                     data-estimated-order-amount-currency="{$lpaButton.estimatedOrderAmountCurrency}"
                ></div>
            {/foreach}
        {/if}
        <div class="lpa-pay-button-express-feedback col-12 mt-1"></div>
    </div>
</div>