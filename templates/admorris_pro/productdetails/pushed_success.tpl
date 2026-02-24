{**Custom**}
{block name='productdetails-pushed-success'}
{strip}
<div id="pushed-success" class="pushed-success notification-alert{if $card} card card-body{/if}">
    {if isset($zuletztInWarenkorbGelegterArtikel)}
        {assign var=pushedArtikel value=$zuletztInWarenkorbGelegterArtikel}
    {else}
        {assign var=pushedArtikel value=$Artikel}
    {/if}
    <div class="row justify-content-center">
        {assign var='showXSellingCart' value=isset($Xselling->Kauf) && count($Xselling->Kauf->Artikel) > 0}
        <div class="col-md-5 text-center">
            <h4 class="success-title" role="alert">{$cartNote}</h4>
            {block name='pushed-success-product-cell'}
            <div class="product-cell text-center{if isset($class)} {$class}{/if}">
                <div class="row row-gap row-gap-3 justify-content-center">
                    <div class="col-6">
                        {counter assign=imgcounter print=0}
                        {image
                            src="{$pushedArtikel->getImage(\JTL\Media\Image::SIZE_SM)}"
                            alt="{if isset($pushedArtikel->Bilder[0]->cAltAttribut)}{$pushedArtikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html':'UTF-8':FALSE}{else}{$pushedArtikel->cName}{/if}"
                            id="image{$pushedArtikel->kArtikel}_{$imgcounter}"
                            class="image pushed-success__image"
                            lazy=false
                        }
                    </div>
                    <div class="col-12">
                        <div class="caption">
                            <span class="title product-cell__title ">{$pushedArtikel->cName}</span>
                        </div>
                    </div>
                </div>
            </div>{* /product-cell *}
            {/block}
            <hr>
            <div class="pushed-success__button-group switcher justify-content-center" role="group">
                <a href="{get_static_route id='warenkorb.php'}" class="btn btn-secondary btn-basket">
                    {$admIcon->renderIcon('shoppingCart', 'icon-content icon-content--default')} {lang key='gotoBasket'}
                </a>
                {if isset($smarty.session.lastVisitedProductListURL)}
                    {$lastVisitedProductListURL = $smarty.session.lastVisitedProductListURL}
                {elseif isset($cCanonicalURL) && $cCanonicalURL !== null}
                    {$lastVisitedProductListURL = $cCanonicalURL}
                {elseif isset($pushedArtikel->cURLFull)}
                    {$lastVisitedProductListURL = $pushedArtikel->cURLFull}
                {/if}
                {$productID = $pushedArtikel->kArtikel}
                {if $pushedArtikel->kVaterArtikel > 0}
                    {$productID = $pushedArtikel->kVaterArtikel}
                {/if}
                {link href=$lastVisitedProductListURL|cat:'#buy_form_'|cat:$productID class="btn btn-primary btn-checkout continue-shopping"
                    data=["dismiss"=>"{if !$card}modal{/if}"]
                    aria=["label"=>"{lang section='aria' key='close'}"]}
                    {$admIcon->renderIcon('arrowCircleRight', 'icon-content icon-content--default')} {lang key='continueShopping' section='checkout'}
                {/link}
            </div>
{*
            <p class="continue-shopping">
                <a href="{get_static_route id='bestellvorgang.php'}">{lang key="checkout" section="basketpreview"}</a>
            </p>
*}
        </div>
        {block name='pushed-success-x-sell'}
        {if $showXSellingCart}
            <div class="col-7 recommendations d-none d-md-block">
                <h4 class="text-center">{lang key='customerWhoBoughtXBoughtAlsoY' section='productDetails'}</h4>
                {include file='snippets/product_slider.tpl' id='' productlist=$Xselling->Kauf->Artikel title='' showPanel=false sliderClass="pushed-success-slider"}
            </div>
        {/if}
        {/block}
    </div>
</div>
{/strip}
{/block}