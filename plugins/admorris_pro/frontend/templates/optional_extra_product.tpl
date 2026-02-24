{block name="productdetails-info-optional-extra"}
    <div class="optional-extra-product">
        <div><input type="checkbox" name="optional-extra-product" id="optional-extra-product" class="optional-extra-product__checkbox" value="{$adm_optional_extra_product->cArtNr}"></div>
        <label class="optional-extra-product__label" for="optional-extra-product">
            {if $admorris_pro_marketing_extra_product_image == "1"}
                <div class="optional-extra-product__image">
                    {image src=$adm_optional_extra_product->cVorschaubild alt=$adm_optional_extra_product->cName}
                </div>
            {/if}
            <div class="optional-extra-product__text">
                <span class="optional-extra-product__name">{$adm_optional_extra_product->cName}</span>
                {if !isset($adm_optional_extra_product->FunktionsAttribute['extra_product_hide_info']) || $adm_optional_extra_product->FunktionsAttribute['extra_product_hide_info'] == 0 }
                    <p class="optional-extra-product__desc">{$adm_optional_extra_product->cKurzBeschreibung}</p>
                {/if}
            </div>
            <div class="optional-extra-product__right">
                <div class="optional-extra-product__price">{$adm_optional_extra_product->Preise->cVKLocalized[$NettoPreise]}
                </div>
                {$taxdata = $Artikel->taxData}
                <div class="price-note">
                    <span class="text-muted">
                        {if !empty($taxdata.text)}
                            {$taxdata.text}
                        {else}
                            {if $Einstellungen.global.global_ust_auszeichnung === 'auto'}
                                {if $taxdata.net}
                                    {lang key='excl' section='productDetails'}
                                {else}
                                    {lang key='incl' section='productDetails'}
                                {/if}
                                &nbsp;{$taxdata.tax}% {lang key='vat' section='productDetails'}
                            {elseif $Einstellungen.global.global_ust_auszeichnung === 'endpreis'}
                                {lang key='finalprice' section='productDetails'}
                            {/if}
                        {/if}
                    </span>
                </div>
            </div>
        </label>
    </div>
{/block}