{if $isNova}
    {block name='checkout-inc-shipping-address-fieldset-new-address' prepend}
    <div class="dhlwunschpaket-shipping-options">
        {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_packstation_active') === 'Y' && $packstationUsable === true}
        <label class="btn-block" for="packstation">
            {radio name="kLieferadresse" value="-1" id="packstation" data=['type'=>"jtlpack", 'jtlpack'=>"-2"] checked=(isset($jtlPack) && $jtlPack === -2)}
                <span class="control-label label-default">{$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label')}</span>
            {/radio}
        </label>
        {/if}
        {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_postfiliale_active') === 'Y' && $postfilialeUsable === true}
        <label class="btn-block" for="postfiliale">
            {radio name="kLieferadresse" value="-1" id="postfiliale" data=['type'=>"jtlpack", 'jtlpack'=>"-3"] checked=(isset($jtlPack) && $jtlPack === -3)}
                <span class="control-label label-default">{$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label2')}</span>
            {/radio}
        </label>
        {/if}
        {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_wunschnachbar_active') === 'Y' && $wunschnachbarUsable === true}
        <label class="btn-block" for="wunschnachbar">
            {radio name="kLieferadresse" value="-1" id="wunschnachbar" data=['type'=>"jtlpack", 'jtlpack'=>"-4"] checked=(isset($jtlPack) && $jtlPack === -4)}
                <span class="control-label label-default">{$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label3')}</span>
            {/radio}
        </label>
        {/if}
        <input type="hidden" name="jtlPack" id="jtlPack" value="{$jtlPack}">
    </div>
    {/block}

    {block name='checkout-inc-shipping-address-fieldset-new-address'}
        <label class="btn-block" for="delivery_new" data-toggle="collapse" data-target="#register_shipping_address:not(.show)">
            {radio name="kLieferadresse" value="-1" id="delivery_new" checked=($kLieferadresse == -1 && empty($jtlPack))}
                <span class="control-label label-default">{lang key='createNewShippingAdress' section='account data'}</span>
            {/radio}
        </label>
    {/block}

    {block name="checkout-inc-shipping-address-include-customer-shipping-address-first" prepend}
        <fieldset>
            {select name="kLieferadresse" id="kLieferadresse" class='custom-select form-group' required=true}
                <option value="-1" data-jtlpack="-1" {if isset($kLieferadresse) && $kLieferadresse === -1} selected="selected"{/if}>
                    {lang key='createNewShippingAdress' section='account data'}
                </option>
            {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_packstation_active') === 'Y' && $packstationUsable === true}
                <option value="-1" data-jtlpack="-2" {if isset($jtlPack) && $jtlPack === -2} selected="selected"{/if} id="packstation"
                        data-type="jtlpack">
                    {$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label')}
                </option>
            {/if}
            {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_postfiliale_active') === 'Y' && $postfilialeUsable === true}
                <option value="-1" data-jtlpack="-3" {if isset($jtlPack) && $jtlPack === -3} selected="selected"{/if} id="postfiliale"
                        data-type="jtlpack">
                    {$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label2')}
                </option>
            {/if}
            {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_wunschnachbar_active') === 'Y' && $wunschnachbarUsable === true}
                <option value="-1" data-jtlpack="-4" {if isset($jtlPack) && $jtlPack === -4} selected="selected"{/if} data-type="jtlpack">
                    {$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label3')}
                </option>
            {/if}
            {/select}
        </fieldset>
        <input type="hidden" name="jtlPack" id="jtlPack" value="{$jtlPack}">
    {/block}
{else}
    {block name="checkout-enter-shipping-address"}
        <div id="select_shipping_address" class="collapse collapse-non-validate{if $showShippingAddress} in{/if}" aria-expanded="{if $showShippingAddress}true{else}false{/if}">
            {block name='checkout-enter-shipping-address-body'}
                <fieldset>
                    <legend>{lang key='deviatingDeliveryAddress' section='account data'}</legend>
                    <ul class="list-group form-group">
                        {if !empty($smarty.session.Kunde->kKunde) && isset($Lieferadressen) && count($Lieferadressen) > 0}
                            {foreach $Lieferadressen as $adresse}
                                {if $adresse->kLieferadresse > 0}
                                    <li class="list-group-item">
                                        <div class="radio">
                                            <label class="btn-block" for="delivery{$adresse->kLieferadresse}" data-toggle="collapse" data-target="#register_shipping_address.in">
                                                <input class="radio-checkbox" type="radio" name="kLieferadresse" value="{$adresse->kLieferadresse}" id="delivery{$adresse->kLieferadresse}" {if $kLieferadresse == $adresse->kLieferadresse}checked{/if}>
                                                <span class="control-label label-default">{if $adresse->cFirma}{$adresse->cFirma},{/if} {$adresse->cVorname} {$adresse->cNachname}
                                , {$adresse->cStrasse} {$adresse->cHausnummer}, {$adresse->cPLZ} {$adresse->cOrt}
                                    , {$adresse->angezeigtesLand}</span></label>
                                        </div>
                                    </li>
                                {/if}
                            {/foreach}
                        {/if}
                        <li class="list-group-item">
                            <div class="radio">
                                <label class="btn-block" for="delivery_new" data-toggle="collapse" data-target="#register_shipping_address:not(.in)">
                                    <input class="radio-checkbox" type="radio" name="kLieferadresse" value="-1" id="delivery_new" {if $kLieferadresse == -1}checked{/if} required="required" aria-required="true">
                                    <span class="control-label label-default">{lang key='createNewShippingAdress' section='account data'}</span>
                                </label>
                            </div>
                        </li>
                        {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_packstation_active') === 'Y' && $packstationUsable === true}
                            <li class="list-group-item">
                                <div class="radio">
                                    <label class="btn-block" for="packstation" data-toggle="collapse" data-target="#register_shipping_address:not(.in)">
                                        <input class="radio-checkbox" type="radio" name="kLieferadresse" value="-1" data-jtlpack="-2" {if isset($jtlPack) && $jtlPack === -2} selected="selected"{/if} id="packstation" data-type="jtlpack">
                                        <span class="control-label label-default">{$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label')}</span>
                                    </label>
                                </div>
                            </li>
                        {/if}
                        {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_postfiliale_active') === 'Y' && $postfilialeUsable === true}
                            <li class="list-group-item">
                                <div class="radio">
                                    <label class="btn-block" for="postfiliale" data-toggle="collapse" data-target="#register_shipping_address:not(.in)">
                                        <input class="radio-checkbox" type="radio" name="kLieferadresse" value="-1" data-jtlpack="-3" {if isset($jtlPack) && $jtlPack === -3} selected="selected"{/if} id="postfiliale" data-type="jtlpack">
                                        <span class="control-label label-default">{$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label2')}</span>
                                    </label>
                                </div>
                            </li>
                        {/if}
                        {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_wunschnachbar_active') === 'Y' && $wunschnachbarUsable === true}
                            <li class="list-group-item">
                                <div class="radio">
                                    <label class="btn-block" for="nachbar" data-toggle="collapse" data-target="#register_shipping_address:not(.in)">
                                        <input class="radio-checkbox" type="radio" name="kLieferadresse" value="-1" data-jtlpack="-4" {if isset($jtlPack) && $jtlPack === -4} selected="selected"{/if} id="nachbar" data-type="jtlpack">
                                        <span class="control-label label-default">{$jtlPackPlugin->getLocalization()->getTranslation('ps_la_dhl_label3')}</span>
                                    </label>
                                </div>
                            </li>
                        {/if}
                    </ul>
                </fieldset>
                <fieldset id="register_shipping_address" class="collapse collapse-non-validate{if $kLieferadresse == -1}} in{/if}" aria-expanded="{if $kLieferadresse == -1}}true{else}false{/if}">
                    <legend>{lang key='createNewShippingAdress' section='account data'}</legend>
                    {include file='checkout/customer_shipping_address.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
                    {include file='checkout/customer_shipping_contact.tpl' prefix="register" fehlendeAngaben=$fehlendeAngabenShipping}
                </fieldset>
                <input type="hidden" name="jtlPack" id="jtlPack" value="{$jtlPack}">
            {/block}
        </div>
    {/block}
{/if}
