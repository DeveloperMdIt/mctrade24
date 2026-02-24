{block name='snippets-product-pagination'}
    {if $Einstellungen.artikeldetails.artikeldetails_navi_blaettern === 'Y' && isset($NavigationBlaettern)}
        {if isset($NavigationBlaettern->vorherigerArtikel) && $NavigationBlaettern->vorherigerArtikel->kArtikel}
            {button variant="link"
                href=$NavigationBlaettern->vorherigerArtikel->cURLFull
                title={sanitizeTitle title=$NavigationBlaettern->vorherigerArtikel->cName}
                aria=["label"=>"{lang section='productDetails' key='previousProduct'}: {$NavigationBlaettern->vorherigerArtikel->cName}"]}
                {$admIcon->renderIcon('chevronLeft', 'icon-content icon-content--default icon-content--center icon-arrow--right')}
            {/button}
        {/if}
        {if isset($NavigationBlaettern->naechsterArtikel) && $NavigationBlaettern->naechsterArtikel->kArtikel}
            {button variant="link"
                href=$NavigationBlaettern->naechsterArtikel->cURLFull
                title={sanitizeTitle title=$NavigationBlaettern->naechsterArtikel->cName}
                aria=["label"=>"{lang section='productDetails' key='nextProduct'}: {$NavigationBlaettern->naechsterArtikel->cName}"]}{$admIcon->renderIcon('chevronRight', 'icon-content icon-content--default icon-content--center')}
            {/button}
        {/if}
    {/if}
{/block}