{block name="productdetails-structured-data"}
    <script type="application/ld+json">
        {
            {block name="rich-snippets-product-inner"}
                "@context": "http://schema.org",
                "@type": {if !empty($Artikel->cISBN) && ($Einstellungen.artikeldetails.isbn_display === 'L' || $Einstellungen.artikeldetails.isbn_display === 'DL')}["Product","Book"]{else}"Product"{/if},
                "name": "{$Artikel->cName|escape:'html'}",
                "image": [
                    {foreach $Artikel->Bilder as $image}
                        {if !$image@first},{/if}
                        "{$image->cURLGross}"
                    {/foreach}
                ],
                "url": "{$cCanonicalURL}",
                {block name='rich-snippets-description'}
                "description": "{if !empty($Artikel->cKurzBeschreibung)}{$Artikel->cKurzBeschreibung|adm_trim_html:4800|escape:'html'}{else}{$Artikel->cBeschreibung|adm_trim_html:4800|escape:'html'}{/if}",
                {/block}
                {block name='rich-snippets-sku'}
                    "sku": "{$Artikel->cArtNr}",
                {/block}
                {block name='rich-snippets-barcode'}
                    {if !empty($Artikel->cBarcode) && ($Einstellungen.artikeldetails.gtin_display === 'lists' || $Einstellungen.artikeldetails.gtin_display === 'always')}
                        "{if $Artikel->cBarcode|count_characters === 8}gtin8{else}gtin13{/if}": "{$Artikel->cBarcode}",
                    {/if}
                    {if !empty($Artikel->cISBN) && ($Einstellungen.artikeldetails.isbn_display === 'L' || $Einstellungen.artikeldetails.isbn_display === 'DL')}
                        "isbn": "{$Artikel->cISBN}",
                    {/if}
                {/block}
                {block name='rich-snippets-brand'}
                    "brand": {
                        "@type": "Brand",
                        "name": "{$Artikel->cHersteller|escape:'html'}"
                        {if $Einstellungen.artikeldetails.artikel_weitere_artikel_hersteller_anzeigen === 'Y'}
                            ,
                            "url": "{if !empty($Artikel->cHerstellerHomepage)}{$Artikel->cHerstellerHomepage}{else}{$Artikel->cHerstellerURL}{/if}"
                        {/if}
                        {if ($Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'B' || $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'BT') && !empty($Artikel->cHerstellerBildURLKlein)}
                            ,
                            "image": "{$Artikel->cHerstellerBildURLKlein}"
                        {/if}
                    },
                {/block}
                {block name='rich-snippets-offer'}
                    "offers": {
                        "@type": "Offer",
                        {if $Artikel->Preise->oPriceRange->isRange()}
                            "minPrice": "{$Artikel->Preise->oPriceRange->getMinLocalized($NettoPreise)|formatForMicrodata}",
                            "maxPrice": "{$Artikel->Preise->oPriceRange->getMaxLocalized($NettoPreise)|formatForMicrodata}",
                        {/if}
                        "price": "{$Artikel->Preise->cVKLocalized[$NettoPreise]|formatForMicrodata}",
                        "priceCurrency": "{JTL\Session\Frontend::getCurrency()->getName()}",
                        {block name='rich-snippets-availability'}
                            "availability": "{if $Artikel->nErscheinendesProdukt && $Artikel->Erscheinungsdatum_de !== '00.00.0000' && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'}https://schema.org/PreOrder{elseif $Artikel->cLagerBeachten === 'N' || $Artikel->fLagerbestand > 0 || $Artikel->cLagerKleinerNull === 'Y'}https://schema.org/InStock{elseif $Artikel->cLagerBeachten === 'Y' && $Artikel->cLagerKleinerNull === 'N' && $Artikel->fLagerbestand <= 0}https://schema.org/OutOfStock{/if}",
                        {/block}
                        "businessFunction": "http://purl.org/goodrelations/v1#Sell",
                        "url": "{$cCanonicalURL}"
                        {if $Artikel->Preise->Sonderpreis_aktiv && $Artikel->dSonderpreisStart_en !== null && $Artikel->dSonderpreisEnde_en !== null}
                            ,
                            "validFrom": "{$Artikel->dSonderpreisStart_en}",
                            "validThrough": "{$Artikel->dSonderpreisEnde_en}",
                            "priceValidUntil": "{$Artikel->dSonderpreisEnde_en}"
                        {/if}
                    }
                {/block}
                {if $Artikel->Bewertungen->oBewertungGesamt->nAnzahl > 0}
                    {block name="rich-snippets-review"}
                        ,
                        "review": [
                                {foreach $Artikel->HilfreichsteBewertung->oBewertung_arr as $oBewertung}
                                    {if !$oBewertung@first},{/if}
                                    {
                                        "@type": "Review",
                                        "datePublished": "{$oBewertung->dDatum}",
                                        "description": "{$oBewertung->cText|escape:'html'}",
                                        "name": "{$oBewertung->cTitel|escape:'html'}",
                                        "thumbnailURL": "{$Artikel->cVorschaubildURL}",
                                        "reviewRating": {
                                            "@type": "Rating",
                                            "ratingValue": "{$oBewertung->nSterne}",
                                            "bestRating": "5",
                                            "worstRating": "1"
                                        },
                                        "author": {
                                            "@type": "Person",
                                            "name": "{$oBewertung->cName|escape:'html'}"
                                        }
                                    }
                                {/foreach}
                            ]
                        {/block}
                        {block name="rich-snippets-aggregateRating"}
                            ,
                        "aggregateRating": {
                            "@type": "AggregateRating",
                            "ratingValue": "{$Artikel->Bewertungen->oBewertungGesamt->fDurchschnitt}",
                            "reviewCount": "{$Artikel->Bewertungen->oBewertungGesamt->nAnzahl}"
                        }
                    {/block}
                {/if}
            {/block}
        }
    </script>
{/block}