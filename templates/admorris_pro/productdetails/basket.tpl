{function name="quantity_grp" }
    {block name='productdetails-basket-quantity-grp'}
        <div id="quantity-grp{if !empty($tplscope) && $tplscope === "fixed"}-fixed{/if}">
            <button name="inWarenkorb" type="submit" value="{lang key='addToCart'}"
                class="submit btn btn-primary{if $Artikel->inWarenkorbLegbar != 1} disabled btn--disabled-dashed{/if}"
                {if $Artikel->inWarenkorbLegbar != 1} disabled{/if}>
                <span class="add-to-basket__label">
                    {if $Artikel->inWarenkorbLegbar != 1}{lang key="soldout"}{else}{if $isMobile}{$admIcon->renderIcon('shoppingCart', 'icon-content')}{else}{lang key='addToCart'}{/if}{/if}
                </span>
            </button>
        </div>
    {/block}
{/function}

{function name="wrap_in_paragraph" item_to_wrap=null wrap=null}
    {block name='productdetails-basket-wrap-in-paragraph'}
        {if $wrap}
            <p>{$item_to_wrap}</p>
        {else}
            {$item_to_wrap}
        {/if}
    {/block}
{/function}

{function name="purchase_info"}
    {block name='productdetails-basket-purchase-info'}
        {if $tplscope === "fixed"}
            {$wrap = false}
        {else}
            {$wrap = true}
        {/if}
        {if $Artikel->inWarenkorbLegbar == 1
            && ($Artikel->fMindestbestellmenge > 1
                || ($Artikel->fMindestbestellmenge > 0 && $Artikel->cTeilbar === 'Y')
                || ($Artikel->fAbnahmeintervall > 0 && $Einstellungen.artikeldetails.artikeldetails_artikelintervall_anzeigen === 'Y')
                || $Artikel->cTeilbar === 'Y'
                || $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|default:0 > 0)}
            {if $tplscope !== "fixed"}
                <div class="clearfix"></div>
                <div class="purchase-info alert alert-info top10 last-no-mb" role="alert">
            {/if}

            {assign var='units' value=$Artikel->cEinheit}
            {if empty($Artikel->cEinheit) || $Artikel->cEinheit|@count_characters == 0}
                {lang key='units' section="productDetails" assign='units'}
            {/if}

            {if $Artikel->fMindestbestellmenge > 1 || ($Artikel->fMindestbestellmenge > 0 && $Artikel->cTeilbar === 'Y')}
                {lang key='minimumPurchase' section='productDetails' assign='minimumPurchase'}
                {wrap_in_paragraph item_to_wrap=$minimumPurchase|replace:"%d":$Artikel->fMindestbestellmenge|replace:"%s":$units wrap=$wrap}

            {/if}

            {if $Artikel->fAbnahmeintervall > 0 && $Einstellungen.artikeldetails.artikeldetails_artikelintervall_anzeigen === 'Y'}
                {lang key='takeHeedOfInterval' section='productDetails' assign='takeHeedOfInterval'}
                {wrap_in_paragraph item_to_wrap=$takeHeedOfInterval|replace:"%d":$Artikel->fAbnahmeintervall|replace:"%s":$units wrap=$wrap}
            {/if}

            {if $Artikel->cTeilbar === 'Y'}
                {wrap_in_paragraph item_to_wrap={lang key='integralQuantities' section='productDetails'} wrap=$wrap}
            {/if}

            {if !empty($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]) && $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE] > 0}
                {lang key='maximalPurchase' section='productDetails' assign='maximalPurchase'}
                {wrap_in_paragraph item_to_wrap=$maximalPurchase|replace:"%d":$Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|replace:"%s":$units wrap=$wrap}

            {/if}
            {if $tplscope !== "fixed"}
                </div>
            {/if}
        {/if}
    {/block}
{/function}

{function name="show_notifications"}
    {block name='productdetails-basket-show-notifications'}
        {if $showNotificationButton}
            {assign var=kArtikel value=$Artikel->kArtikel}
            {if $Artikel->kArtikelVariKombi > 0}
                {assign var=kArtikel value=$Artikel->kArtikelVariKombi}
            {/if}
            <div class="notification">
                <p class="notification__info">{$admIcon->renderIcon('info', 'icon-content icon-content--default')}
                    {lang key="notifyMeWhenProductAvailableAgain" section="global"}</p>

                <button type="button" id="n{$kArtikel}" class="btn btn-primary popup-dep notification__button"
                    title="{lang key='requestNotification'}">
                    {$admIcon->renderIcon('alert', 'icon-content icon-content--default icon-content--center')} <span
                        class="icon-text--center">{lang key='requestNotification'}</span>
                </button>
            </div>
        {/if}
    {/block}
{/function}

{function name="quantity_spinner"}
    {block name='productdetails-basket-quantity'}
        {* {$max = $Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|default:''}
        {$min = $admPro->getMinValueArticle($Artikel)}

        <div class="js-spinner quantity-input quantity-input--details">

            <span class="js-spinner-button{if !empty($fixed)} d-none d-md-inline{/if}" data-spinner-button="down"></span>

            <div class="js-spinner-input{if $Artikel->cEinheit} js-spinner--unit-addon{/if}">
                <input type="number" min="{$min}" {if !empty($max)}max="{$max}" {/if}{if $Artikel->fAbnahmeintervall > 0}
                    step="{$Artikel->fAbnahmeintervall}" {/if} id="quantity{if $tplscope === "fixed"}-fixed{/if}"
                    class="{if empty($fixed)}quantity {/if}form-control" name="anzahl{if !empty($fixed)}-fixed{/if}"
                    aria-label="{lang key='quantity'}" value="{if $min > 0}{$min}{else}1{/if}">
                {if $Artikel->cEinheit}
                    <div class="js-spinner__unit-addon unit">{$Artikel->cEinheit}</div>
                {/if}
            </div>

            <span class="js-spinner-button{if !empty($fixed)} d-none d-md-inline{/if}" data-spinner-button="up"></span>
        </div> *}
        {if $Artikel->inWarenkorbLegbar == 1}
            {$wrapperClassName = 'quantity-input quantity-input--details'}
            {capture inputName assign=inputName}anzahl{if !empty($fixed)}-fixed{/if}{/capture}

            {quantityInput 
                id="quantity{if $tplscope === "fixed"}-fixed{/if}"
                article=$Artikel 
                wrapperClass=$wrapperClassName 
                name=$inputName 
                buttonClass=(!empty($fixed))? 'd-none d-md-inline' : '' 
                idPrefix=(!empty($fixed))? 'fixed-' : ''
            }
        {/if}

    {/block}
{/function}

{if empty($tplscope)}{$tplscope = null}{/if}
{block name='productdetails-basket'}
    {$showNotificationButton = ($verfuegbarkeitsBenachrichtigung == 2 || $verfuegbarkeitsBenachrichtigung == 3) && $Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull !== 'Y'}

    {if empty($tplscope) || $tplscope !== "fixed"}

        {block name='add-to-cart'}
            {if $Artikel->nIstVater && $Artikel->kVaterArtikel == 0}
                <p class="alert alert-info choose-variations">{lang key='chooseVariations' section='messages'}</p>
            {else}
                {if !$showMatrix}
                    {block name='basket-form-inline'}
                        {if $Artikel->Preise->fVKNetto == 0 && isset($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_VOUCHER_FLEX])}
                            {block name='productdetails-basket-voucher-flex'}
                                <div class="jtl-voucher-input input-group form-counter">
                                    {input type="number"
                                        step=".01"
                                        value="{if isset($voucherPrice)}{$voucherPrice}{/if}"
                                        name="{$smarty.const.FKT_ATTRIBUT_VOUCHER_FLEX}Value"
                                        required=true
                                        placeholder="{lang key='voucherFlexPlaceholder' section='productDetails' printf=$smarty.session.Waehrung->getName()}"}
                                    <div class="input-group-append"><span class="input-group-text">{$smarty.session.Waehrung->getName()}</span></div>
                                </div>
                                {if isset($kEditKonfig)}
                                    <input type="hidden" name="kEditKonfig" value="{$kEditKonfig}" />
                                {/if}
                                {input type="hidden" id="quantity" class="quantity" name="anzahl" value="1"}
                            {/block}
                        {/if}

                        {if (!$Artikel->nErscheinendesProdukt || $Einstellungen.global.global_erscheinende_kaeuflich === 'Y') && ($Artikel->inWarenkorbLegbar == 1 || $Artikel->fLagerbestand == 0)}
                            {quantity_spinner}

                            <span class="product-actions__spacer"></span>

                            <div class="add-to-basket" id="add-to-cart">
                                {quantity_grp data=$tplscope}
                            </div>
                        {/if}
                    {/block}
                {/if}
            {/if}
            {show_notifications}
            {purchase_info}
        {/block}
    {else}
        {block name='add-to-cart-fixed'}
            <div class="productdetails-fixed-form {if $admorris_pro_templateSettings->fixedAddToBasketPosition == "top" } productdetails-fixed-form--top {else} productdetails-fixed-form--bottom{/if}">
                <div class="productdetails-fixed-form__container admPro-container container--{$admPro->container_size()}">
                    <div class="productdetails-fixed-form__left">
                        {image
                            src="{$Artikel->Bilder[0]->cURLMini}"
                            alt="{$Artikel->Bilder[0]->cAltAttribut|escape:'html'}"
                            class="productdetails-fixed-form__image"
                        }
                        <div class="productdetails-fixed-form__text">
                            <h2>{$Artikel->cName|truncate:63:"..."}</h2>
                        </div>
                    </div>
                    <div class="productdetails-fixed-form__right">
                        <div class="productdetails-fixed-form__price">
                            {include file='productdetails/price.tpl' Artikel=$Artikel  tplscope='detail-fixed' outputID=true}
                        </div>

                        <div class="productdetails-basket-action__wrapper">
                            {if empty($Artikel->FunktionsAttribute.unverkaeuflich) && ($Artikel->inWarenkorbLegbar == 0 || $Artikel->nIstVater == 1)}
                                {if !empty($Artikel->Variationen) && ($Artikel->Variationen|count) > 0 && $Artikel->kVaterArtikel == 0}
                                    <button class="btn btn-outline-secondary" data-toggle="tooltip" data-trigger="hover"
                                        data-placement="{if $admorris_pro_templateSettings->fixedAddToBasketPosition === "top" }bottom{else}top{/if}"
                                        title="{lang key='chooseVariations' section='messages'}">
                                        {$admIcon->renderIcon('chevronUpCircle', 'icon-content')}
                                    </button>
                                {/if}
                            {/if}

                            {block name='productdetails-basket-fixed'}
                                {if (!$Artikel->nIstVater && $Artikel->kVaterArtikel == 0) || ($isAjax) || ($Artikel->nIstVater != 0 && $Artikel->kVaterArtikel != 0)}
                                    {if !$showMatrix}
                                        {block name='basket-form-inline-fixed'}
                                            {if (!$Artikel->nErscheinendesProdukt || $Einstellungen.global.global_erscheinende_kaeuflich === 'Y') && ($Artikel->inWarenkorbLegbar == 1 || $Artikel->fLagerbestand == 0)}
                                                {quantity_spinner fixed=true}
                                                <div class="add-to-basket" id="add-to-cart-fixed">
                                                    {quantity_grp data=$tplscope}
                                                </div>
                                            {/if}
                                        {/block}
                                    {/if}
                                {/if}
                            {/block}
                        </div>
                    </div>
                </div>
            </div>
        {/block}
    {/if}
{/block}