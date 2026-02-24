{*custom*}
{block name='basket-cart-dropdown'}
    {$cartPositions = JTL\Session\Frontend::getCart()->PositionenArr}
    {block name='basket-cart-dropdown-max-cart-positions'}
        {$maxCartPositions = 15}
    {/block}
    {if !isset($headerLayout)} 
        {* when the cart dropdown is refreshed and rendered via AJAX *}
        {$headerLayout = $admPro->initHeaderLayout()}
    {/if}
    {if $admorris_pro_templateSettings->miniCartDisplay === "sidebar"}
        <button class="btn btn-primary cart-siderbar-close-button" onclick="closeCartSidebar()">
            {lang key="continueShopping" section="checkout"}
        </button>
    {/if}
    {if $cartPositions|count > 0}
        {$cartDropdownImages = $admPro->get_template_settings('cart_dropdown_images', false)}
        {block name='basket-cart-dropdown-cart-items-content'}
        <div class="cart-dropdown__wrapper cart-dropdown__wrapper--items">
            {block name='basket-cart-dropdown-cart-items-table'}
            <table class="table dropdown-cart-items hyphens">
                {* <thead>
                    <tr>
                        <th scope="col" class="sr-only">{lang key='product'}</th>
                        <th scope="col" class="sr-only">{lang key='price'}</th>
                    </tr>
                </thead> *}
                <tbody>
                {block name='basket-cart-dropdown-cart-items-body'}
                    {foreach $cartPositions as $oPosition}
                        {if $oPosition@iteration > $maxCartPositions}
                            {break}
                        {/if}
                    {if !$oPosition->istKonfigKind()}
                        {* {if $oPosition->nPosTyp == C_WARENKORBPOS_TYP_ARTIKEL} *}
                        {block name='basket-cart-dropdown-cart-item'}

                        <tr>
                            {if $cartDropdownImages !== 'false'}
                                <td class="dropdown-cart-items__image-cell{if $cartDropdownImages === 'small'} dropdown-cart-items__image-cell--small{/if}">
                                    {if !empty($oPosition->Artikel->Bilder) && $oPosition->Artikel->Bilder[0]->cPfadMini !== $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN}
                                        {if $cartDropdownImages === 'large'}
                                            {$class = 'dropdown-cart-items__image'}
                                        {else}
                                            {$class = 'img-sm'}
                                        {/if}
                                        {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|transByISO|escape:'html' class="dropdown-cart-items__item-image-link"}
                                            {include file='snippets/image.tpl'
                                                fluid=false
                                                item=$oPosition->Artikel
                                                square=false
                                                srcSize='xs'
                                                lazy=true
                                                class=$class}
                                        {/link}
                                    {/if}
                                </td>
                            {/if}
                                
                            <td class="dropdown-cart-items__item-name" colspan="{if $cartDropdownImages !== 'false'}3{else}4{/if}">
                                {* custom - modified for extraproduct salesbooster *}
                                {if empty($oPosition->Artikel->FunktionsAttribute["extra_product_not_buyable"]) && isset($oPosition->Artikel->cURL)}
                                    {link href=$oPosition->Artikel->cURLFull title=$oPosition->cName|transByISO|escape:'html' class="dropdown-cart-items__item-link"}
                                        {$oPosition->cName|transByISO}
                                    {/link}
                                {else}
                                    <span class="dropdown-cart-items__item-title">{$oPosition->cName|transByISO}</span>
                                {/if}
                                <br>
                                {$oPosition->nAnzahl|replace_delim}&nbsp;&times;&nbsp;
                                {if $oPosition->istKonfigVater() && $oPosition->cResponsibility === 'core'}
                                    {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {else}
                                    {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {/if}
                            </td>
                            {* <td class="item-price">
                                {if $oPosition->istKonfigVater()}
                                    {$oPosition->cKonfigpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {else}
                                    {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                {/if}
                            </td> *}
                        </tr>
                        {/block}
                        {* {else}
                            <tr>
                                <td></td>
                                <td class="item-name" colspan="2">
                                    {$oPosition->nAnzahl|replace_delim}&nbsp;&times;&nbsp;{$oPosition->cName|transByISO|escape:"htmlall"}
                                </td>
                                <td class="item-price text-nowrap text-right">
                                    {$oPosition->cEinzelpreisLocalized[$NettoPreise][$smarty.session.cWaehrungName]}
                                </td>
                            </tr>
                        {/if} *}
                    {/if}
                {/foreach}
                {/block}
                </tbody>
            </table>
            {/block}
        </div>
        {/block}
        <div id="cart-dropdown-bottom" class="cart-dropdown__wrapper cart-dropdown__wrapper--bottom">
            <table class="table dropdown-cart-items-footer hyphens">
                <tfoot>
                {if $NettoPreise}
                    <tr class="total total-net">
                        <td colspan="3">
                            {if empty($smarty.session.Versandart)}
                                {lang key='subtotal' section='account data'}
                            {else}
                                {lang key='totalSum'}
                            {/if} ({lang key='net' section='global'}):
                        </td>
                        <td class="text-nowrap text-right"><strong>{$WarensummeLocalized[$NettoPreise]}</strong></td>
                    </tr>
                {/if}
                {if $Einstellungen.global.global_steuerpos_anzeigen !== 'N' && isset($Steuerpositionen) && $Steuerpositionen|@count > 0}
                    {foreach $Steuerpositionen as $Steuerposition}
                        <tr class="text-muted-util tax">
                            <td colspan="3">{$Steuerposition->cName}</td>
                            <td class="text-nowrap text-right">{$Steuerposition->cPreisLocalized}</td>
                        </tr>
                    {/foreach}
                {/if}
                <tr class="total">
                    <td colspan="3">
                        {if empty($smarty.session.Versandart)}
                            {lang key='subtotal' section='account data'}
                        {else}
                            {lang key='totalSum'}
                        {/if}:
                    </td>
                    <td class="text-nowrap text-right total"><strong>{$WarensummeLocalized[0]}</strong></td>
                </tr>
                {* {if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])} *}
                {block name='basket-cart-dropdown-cart-item-favourable-shipping'}
                    {if $favourableShippingString !== ''}
                        <tr class="shipping-costs cart-dropdown-total-item">
                            <td colspan="4"><small>{$favourableShippingString}</small></td>
                        </tr>
                    {/if}
                {/block}

                {* {/if} *}
                </tfoot>
            </table>
    
            {block name='basket-cart-dropdown-coupon'}
                {$couponAvailable = admProCoupon::couponsAvailable()}
                {$Einstellungen = JTL\Shopsetting::getInstance()->getAll()}
                
                {if $Einstellungen.kaufabwicklung.warenkorb_kupon_anzeigen === 'Y' && $couponAvailable == 1}
                    {$jtl_token = admProForm::getTokenInput()}

                    <div class="mini-basket-alert"></div>
                    
                    <form class="mini-basket-coupon-form form-inline jtl-validate" id="mini-basket-coupon-form" method="post" action="{get_static_route id='warenkorb.php'}">
                        {$jtl_token}
                        <div class="form-group w-100{if !empty($invalidCouponCode)} has-error{/if}">
                            <p class="input-group w-100">
                                <input aria-label="{lang key='couponCode' section='account data'}" class="form-control" type="text" name="Kuponcode" id="mini-basket-code" maxlength="32" placeholder="{lang key='couponCode' section='account data'}" required/>
                                <span class="input-group-append">
                                    <input class="mini-basket-coupon__button btn btn-outline-secondary" type="submit" value="{lang key='couponSubmit' section='checkout'}" />
                                </span>
                            </p>
                        </div>
                    </form>
                {/if}
            {/block}

            <a href="{get_static_route id='bestellvorgang.php'}" class="btn btn-primary btn-block">{lang key="checkout" section="basketpreview"}</a>
            
            <a href="{get_static_route id='warenkorb.php'}" class="btn btn-link btn-block" title="{lang key='gotoBasket'}">{lang key='gotoBasket'}</a>

            <div id="plugin-placeholder-mini-basket"></div>
            <div class="cart-dropdown-buttons"></div>
        </div>

        {block name='basket-cart-dropdown-shipping-free-hint'}
            {if !empty($WarenkorbVersandkostenfreiHinweis)}
            <section id="cart-dropdown-shipping-info" aria-labelledby="cart-dropdown-shipping-info-heading" class="cart-dropdown__shipping-info">
                <h2 id="cart-dropdown-shipping-info-heading" class="sr-only">{lang key='shippingInfo' section='login'}</h2>
                {if !empty($oSpezialseiten_arr) && isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}
                    <a class="popup"
                        href="{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}"
                        {* data-toggle="tooltip"  data-placement="bottom" *} title="{lang key='shippingInfo' section='login'}">
                        <span title="{$WarenkorbVersandkostenfreiHinweis}">{$WarenkorbVersandkostenfreiHinweis|truncate:160:"&hellip;"}</span>
                        {$admIcon->renderIcon('info', 'icon-content icon-content--default')}
                    </a>
                {else}
                    {$admIcon->renderIcon('info', 'icon-content icon-content--default')}
                {/if}
            </section>
            {/if}
        {/block}

        {block name='basket-cart-dropdown-shipping-include-free-hint'}
            {include file='basket/freegift_hint.tpl'}
        {/block}
    
    {else}
        <div class="cart-dropdown__wrapper">
            <a class="cart-dropdown__empty-note" rel="nofollow" href="{get_static_route id='warenkorb.php'}" title="{lang section='checkout' key='emptybasket'}">{lang section='checkout' key='emptybasket'}</a>
            <a href="{get_static_route id='warenkorb.php'}" class="btn btn-secondary btn-block mb-5" title="{lang key='gotoBasket'}">{lang key='gotoBasket'}</a>
        </div>
    {/if}
{/block}