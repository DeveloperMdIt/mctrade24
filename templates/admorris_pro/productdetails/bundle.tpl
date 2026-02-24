{*custom*}
{block name='productdetails-bundle'}
{if !empty($Products)}
    <form action="{if !empty($ProductMain->cURL)}{$ProductMain->cURL}{else}index.php{/if}" method="post" id="form_bundles" data-toggle="snackbar-add" class="jtl-validate">
        <div class="card bundle__card">
            {$jtl_token}
            <div class="bundle__name d-none" data-image="{$ProductMain->cVorschaubild}">{$ProductMain->cName}</div>
            <input type="hidden" name="a" value="{$ProductMain->kArtikel}" />
            <input type="hidden" name="addproductbundle" value="1" />
            <input type="hidden" name="aBundle" value="{$ProductKey}" />
            {block name="productdetails-bundle"}{* for additional hidden inputs use block prepend *}
            <div class="card-header">
                <h3 class="card-title">{lang key="buyProductBundle" section="productDetails"}</h3>
            </div>
            <div class="card-body row">
                <div class="col-12 col-lg-8">
                    <div class="bundle-list row no-gutters">
                        {foreach $Products as $bundleProduct}
                        <div class="bundle-item col col-md-3 col-6">
                            <div class="bundle-item-head">
                                <div class="bundle-item-image">
                                    <a href="{$bundleProduct->cURLFull}">
                                        <img src="{if $bundleProduct->Bilder[0]->cURLKlein}{$bundleProduct->Bilder[0]->cURLKlein}{else}{$smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN}{/if}"
                                            alt="{$bundleProduct->cName}"
                                            title="{$bundleProduct->cName}" 
                                            class="img-fluid"/>
                                    </a>
                                </div>
                                <div class="bundle-item-plus{if $bundleProduct@last} invisible{/if}">
                                    <span class="ci-plus vcenter"></span>
                                </div>
                            </div>
                            <div class="bundle-item-title">
                                <a href="{$bundleProduct->cURL}">{$bundleProduct->cName}</a>
                                <br>
                                <strong class="price price-xs">{$bundleProduct->Preise->cVKLocalized[0]}</strong>
                            </div>
                        </div>
                        {/foreach}
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    {if $smarty.session.Kundengruppe->mayViewPrices()}
                        <div class="bundle-price">
                            <strong class="bundle-price__for large-text">{lang key="priceForAll" section="productDetails"}:</strong> 
                            <strong class="bundle-price__price price">{$ProduktBundle->cPriceLocalized[$NettoPreise]}</strong>
                            {if $ProduktBundle->fPriceDiff > 0}
                                <br />
                                <span class="bundle-price__label badge badge-warning">{lang key='youSave' section='productDetails'}: {$ProduktBundle->cPriceDiffLocalized[$NettoPreise]}</span>
                            {/if}
                            {if $ProductMain->cLocalizedVPE}
                                <strong class="badge">{lang key='basePrice'}: </strong>
                                <span class="value">{$ProductMain->cLocalizedVPE[$NettoPreise]}</span>
                            {/if}
                            <button name="inWarenkorb" type="submit" value="{lang key='addAllToCart'}" class="submit btn btn-primary">{lang key='addAllToCart'}</button>
                        </div>
                    {/if}
                </div>
            </div>
            {/block}
        </div>
    </form>
{/if}
{/block}