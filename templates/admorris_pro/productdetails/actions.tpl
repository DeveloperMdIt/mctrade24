{*custom*}
{block name='productdetails-actions'}
{strip}
{if !isset($smarty.get.quickView) || $smarty.get.quickView != 1}
    <div class="product-actions product-actions--details d-print-none" role="group">
        {assign var=kArtikel value=$Artikel->kArtikel}

        {if $Artikel->kArtikelVariKombi > 0}
            {assign var=kArtikel value=$Artikel->kArtikelVariKombi}
        {/if}
        {if $Einstellungen.global.global_wunschliste_anzeigen === 'Y'}
            <button name="Wunschliste" type="submit" class="product-actions__button btn btn-link wishlist" title="{lang key='addToWishlist' section='productDetails'}">
                {$admIcon->renderIcon('heart', 'icon-content icon-content--default icon-content--center icon-content--productdetails')}
                <span class="product-actions__label icon-text--center">&nbsp;&nbsp;{lang key='wishlist'}</span>
            </button>
        {/if}
        {if $Einstellungen.artikeldetails.artikeldetails_vergleichsliste_anzeigen === 'Y' && (!isset($Einstellungen.vergleichsliste.vergleichsliste_anzeigen) || $Einstellungen.vergleichsliste.vergleichsliste_anzeigen === 'Y')}
            <button name="Vergleichsliste" type="submit" class="product-actions__button btn btn-link compare" title="{lang key='addToCompare' section='productDetails'}">
                {$admIcon->renderIcon('tasks', 'icon-content icon-content--default icon-content--center icon-content--productdetails')}
                <span class="product-actions__label icon-text--center">&nbsp;&nbsp;{lang key='compare'}</span>
            </button>
        {/if}
        {if $Einstellungen.artikeldetails.artikeldetails_fragezumprodukt_anzeigen === 'P'}
            <button type="button" id="z{$kArtikel}" class="product-actions__button btn btn-link popup-dep question" title="{lang key='productQuestion' section='productDetails'}">
                {$admIcon->renderIcon('questionMark', 'icon-content icon-content--default icon-content--center icon-content--productdetails')}
                <span class="product-actions__label icon-text--center">&nbsp;&nbsp;{lang key='productQuestion' section='productDetails'}</span>
            </button>
        {/if}
    </div>
{/if}
{/strip}
{/block}