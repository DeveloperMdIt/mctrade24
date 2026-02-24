{if $isNova}
    {block name="checkout-customer-shipping-address" prepend}
        {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_search_enabled') === '1'}
            {formrow class="form-group"}
            {col cols=12 md=6}
                <button type="button" class="storelocator" id="storelocator" style="display: none;" tabindex="99999">
                    {$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_searchbtn')}
                </button>
            {/col}
            {/formrow}
        {/if}
    {/block}
    {if $Einstellungen.kunden.lieferadresse_abfragen_adresszusatz === 'N'}
        {block name='checkout-customer-shipping-address-country-wrap' prepend}
            {block name='checkout-customer-shipping-address-addition'}
                {col cols=12 md=6}
                {block name='checkout-customer-shipping-address-street-additional'}
                    {include file='snippets/form_group_simple.tpl'
                        options=[
                        "text", "{$prefix}-{$name}-street2", "{$prefix}[{$name}][adresszusatz]",
                        {$Lieferadresse->cAdressZusatz|default:null}, {lang key='street2' section='account data'},
                        $Einstellungen.kunden.lieferadresse_abfragen_adresszusatz, null, "shipping address-line3"
                        ]
                    }
                {/block}
                {/col}
            {/block}
            <div class="w-100-util"></div>
        {/block}
    {/if}
    {block name="checkout-customer-shipping-address-city"}
        {formgroup
            class="{if !empty($fehlendeAngaben.ort)} has-error{/if}"
            label="{lang key='city' section='account data'}"
            label-for="{$prefix}-{$name}-city"
        }
            {input type="text"
                name="{$prefix}[{$name}][ort]"
                value="{if isset($Lieferadresse->cOrt)}{$Lieferadresse->cOrt}{/if}"
                id="{$prefix}-{$name}-city"
                placeholder="{lang key='city' section='account data'}"
                required=true
                autocomplete="shipping address-level2"
                aria=["label"=>{lang key='city' section='account data'}]
            }
            {if isset($fehlendeAngaben.ort)}
                <div class="form-error-msg text-danger"><i class="fa fa-exclamation-triangle"></i>
                    {if $fehlendeAngaben.ort==3}
                        {lang key='cityNotNumeric' section='account data'}
                    {else}
                        {lang key='fillOut'}
                    {/if}
                </div>
            {/if}
        {/formgroup}
    {/block}
{else}
    {block name="checkout-customer-shipping-address" prepend}
        {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_search_enabled') === '1'}
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <button type="button" class="storelocator" id="storelocator" style="display: none;" tabindex="99999" disabled>
                        {$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_searchbtn')}
                    </button>
                </div>
            </div>
        {/if}
    {/block}

    {block name="checkout-customer-shipping-address-city"}
        {formgroup
            class="{if !empty($fehlendeAngaben.ort)} has-error{/if}"
            label=""
            label-for="{$prefix}-{$name}-city"
        }
            {input type="text"
                name="{$prefix}[{$name}][ort]"
                value="{if isset($Lieferadresse->cOrt)}{$Lieferadresse->cOrt}{/if}"
                id="{$prefix}-{$name}-city"
                placeholder="{lang key='city' section='account data'}"
                required=true
                autocomplete="shipping address-level2"
                aria=["label"=>{lang key='city' section='account data'}]
            }
            {if isset($fehlendeAngaben.ort)}
                <div class="form-error-msg text-danger"><i class="fa fa-exclamation-triangle"></i>
                    {if $fehlendeAngaben.ort==3}
                        {lang key='cityNotNumeric' section='account data'}
                    {else}
                        {lang key='fillOut'}
                    {/if}
                </div>
            {/if}
        {/formgroup}
    {/block}
{/if}
