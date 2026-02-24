<div id="listActionAPMSettings-Container">
    {$jtl_token}
    <input type="hidden" name="id" value="{$id}" />
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="listActionAPMSettings-sortNo">{__('sortNo')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
            <input class="form-control" type="text" name="sortNr" id="listActionAPMSettings-sortNo" value="{$sortNr}" tabindex="1">
        </div>
    </div>
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="listActionAPMSettings-picture">{__('pictureURL')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
            <input class="form-control" type="text" name="pictureURL" id="listActionAPMSettings-picture" value="{$pictureURL}" tabindex="1">
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">{getHelpDesc cDesc=__('pictureDesc')}</div>
    </div>
    {foreach $availableLanguages as $language}
        {assign var=cISO value=$language->getIso()|upper}
        <div class="form-group form-row align-items-center">
            <label class="col col-sm-4 col-form-label text-sm-right" for="paymentDesc_{$cISO}">{__('noticeTextShop')} ({$language->getLocalizedName()}):</label>
            <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
                <textarea class="form-control" id="paymentDesc_{$cISO}" name="paymentDesc_{$cISO}">{if isset($paymentDesc[$cISO])}{$paymentDesc[$cISO]}{/if}</textarea>
            </div>
        </div>
    {/foreach}
</div>
