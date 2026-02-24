{block name='boxes-box-config'}
<div class="box autoscroll box-config d-none" id="sidebox{$oBox->getID()}">
    <div class="product-filter-headline">{lang key='yourConfiguration'}</div>
    <div class="box-content-wrapper">
        <div id="box_config_list">
            <!-- ul itemlist -->
        </div>
        <div id="box_config_price">
            <span class="price_div">{lang key='priceAsConfigured' section='productDetails'}</span>
            <span class="price updateable"><!-- price --></span>
            {if $Artikel->cLocalizedVPE[$NettoPreise]}
                <small class="price_base updateable">{$Artikel->cLocalizedVPE[$NettoPreise]}</small>
                <br />
            {/if}
            <div class="vat_info">
                {include file='snippets/shipping_tax_info.tpl' taxdata=$Artikel->taxData}
            </div>
        </div>
    </div>
</div>
{/block}
