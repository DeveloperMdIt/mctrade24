<div id="srt-customer-data" style="display:none;">
    <span id="srt-customer-email">{$sv_checkout_Kunde->cMail}</span>
    <span id="srt-customer-reference">{$sv_bestellung->cBestellNr}</span>
</div>
<div id="SHOPVOTECheckoutProducts" style="display: none;" translate="no">
    {foreach from=$sv_bestellung->Positionen item=pos}
        {if $pos->nPosTyp == 1}
            <span class="SVCheckoutProductItem">
			<span class="sv-i-product-url">{$pos->Artikel->cURLFull}</span>
			<span class="sv-i-product-image-url">{$sv_shop_url}/{$pos->Artikel->cVorschaubild}</span>
			<span class="sv-i-product-name">{$pos->Artikel->cName}</span>
			<span class="sv-i-product-sku">{$pos->Artikel->cArtNr}</span>
			<span class="sv-i-product-gtin">{$pos->Artikel->cBarcode}</span>
			<span class="sv-i-product-brand">{$pos->Artikel->cHerstellerMetaTitle}</span>
		</span>
        {/if}
    {/foreach}
</div>

<script src="https://feedback.shopvote.de/srt-v4.min.js"></script>
<script type="text/javascript">
    var myToken = "{$lfsShopVotePlugin->getConfig()->getValue('sv_api_key')}";
    var mySrc = ('https:' === document.location.protocol ? 'https' : 'http');
    loadSRT(myToken, mySrc);
</script>