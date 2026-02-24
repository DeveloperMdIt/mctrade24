{*custom*}
{* manufacturer, category, artNr, & MHD added to attributes table *}
{* using a table instead of list-group *}

{block name='productdetails-attributes'}
    {$inQuickView = !empty($smarty.get.quickView)}

    {assign var="showManufacturer" value=false}
    {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'N' && isset($Artikel->cHersteller)}
        {assign var="showManufacturer" value=true}
    {/if}
    {assign var="showCategory" value=false}
    {if $Einstellungen.artikeldetails.artikeldetails_kategorie_anzeigen === 'Y'}
        {assign var="showCategory" value=true}
    {/if}
    {assign var="showArtNr" value=false}
    {if isset($Artikel->cArtNr)}
        {assign var="showArtNr" value=true}
    {/if}
    {assign var="showMHD" value=false}
    {if $Einstellungen.artikeldetails.show_shelf_life_expiration_date === 'Y'
        && isset($Artikel->dMHD)
        && isset($Artikel->dMHD_de)}
        {assign var="showMHD" value=true}
    {/if}


    {if $showAttributesTable}
        {strip}
            {block name='productdetails-attributes-table'}
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col" class="sr-only">{lang section="productDetails" key='itemInformation'}</th>
                                <th scope="col" class="sr-only">{lang section="productDetails" key='itemValue'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {if $showManufacturer}
                                <tr>
                                    <td class="attr-label">{lang key="manufacturer" section="productDetails"}:</td>
                                    <td class="attr-value">
                                        <a href="{$Artikel->cHerstellerURL}">
                                            {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'Y' && (!empty($Artikel->cBildpfad_thersteller) || $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen === 'B') && isset($Artikel->cHerstellerBildKlein)}
                                                <img src="{$Artikel->cHerstellerBildURLKlein}" alt="{$Artikel->cHersteller}" class="img-sm">
                                            {/if}
                                            {if $Einstellungen.artikeldetails.artikeldetails_hersteller_anzeigen !== 'B'}
                                                <span>{$Artikel->cHersteller}</span>
                                            {/if}
                                        </a>
                                    </td>
                                </tr>
                            {/if}
                            {assign var=i_kat value=($Brotnavi|@count)-2}
                            {if $showCategory && isset($Brotnavi[$i_kat])}
                                <tr>
                                    <td class="attr-label">{lang key="category" section="global"}:</td>
                                    <td class="attr-value">
                                        <a href="{$Brotnavi[$i_kat]->getURL()}">
                                            {$Brotnavi[$i_kat]->getName()}
                                        </a>
                                    </td>
                                </tr>
                            {/if}
                            {if $showArtNr}
                                <tr>
                                    <td class="attr-label">{lang key="sortProductno" section="global"}:</td>
                                    <td class="attr-value">{$Artikel->cArtNr}</td>
                                </tr>
                            {/if}
                            {if !empty($Artikel->cBarcode) && ($Einstellungen.artikeldetails.gtin_display === 'lists' || $Einstellungen.artikeldetails.gtin_display === 'always')}
                                <tr>
                                    <td class="attr-label">{lang key='ean'}: </td>
                                    <td class="attr-value"><span>{$Artikel->cBarcode}</span>
                                    </td>
                                </tr>
                            {/if}
                            {if !empty($Artikel->cISBN) && ($Einstellungen.artikeldetails.isbn_display === 'L' || $Einstellungen.artikeldetails.isbn_display === 'DL')}
                                <tr>
                                    <td class="attr-label">{lang key='isbn'}: </td>
                                    <td class="attr-value"><span>{$Artikel->cISBN}</span></td>
                                </tr>
                            {/if}
                            {if !empty($Artikel->cUNNummer) && !empty($Artikel->cGefahrnr) && ($Einstellungen.artikeldetails.adr_hazard_display === 'L' || $Einstellungen.artikeldetails.adr_hazard_display === 'DL')}
                                <tr>
                                    <td class="attr-label">
                                        {lang key='adrHazardSign'}:
                                    </td>
                                    <td class="attr-value">
                                        <table class="adr-table value">
                                            <tr>
                                                <td>{$Artikel->cGefahrnr}</td>
                                            </tr>
                                            <tr>
                                                <td>{$Artikel->cUNNummer}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            {/if}
                            {if $showMHD}
                                <tr title="{lang key='productMHDTool' section='global'}">
                                    <td class="attr-label">{lang key="productMHD" section="global"}:</td>
                                    <td class="attr-value" itemprop="best-before">{$Artikel->dMHD_de}</td>
                                </tr>

                            {/if}

                            {if $Einstellungen.artikeldetails.merkmale_anzeigen === 'Y'}
                                {block name='productdetails-attributes-characteristics'}
                                    {foreach $Artikel->oMerkmale_arr as $oMerkmal}
                                        <tr class="attr-characteristic">
                                            <td class="attr-label">
                                                {$oMerkmal->getName()|escape:'html'}&zwj;:
                                            </td>
                                            <td class="attr-value">
                                                {strip}
                                                    <div class="characteristic-values cluster">
                                                        {foreach $oMerkmal->getCharacteristicValues() as $oMerkmalWert}
                                                            {if $oMerkmal->getType() === 'TEXT' || $oMerkmal->getType() === 'SELECTBOX' || $oMerkmal->getType() === ''}
                                                                <span class="value"><a href="{$oMerkmalWert->getURL()}"
                                                                        class="badge badge-primary">{$oMerkmalWert->getValue()|escape:'html':'UTF-8':FALSE}</a>
                                                                </span>
                                                            {else}
                                                                <span class="value">
                                                                    {block name='productdetails-attributes-image'}
                                                                        <a {if !$inQuickView}href="{$oMerkmalWert->getURL()}"{/if}
                                                                            class="text-decoration-none-util"
                                                                            data-toggle="tooltip" data-placement="top" data-boundary="window"
                                                                            title="{$oMerkmalWert->getValue()|escape:'html'}"
                                                                            aria-label="{$oMerkmalWert->getValue()|escape:'html'}"
                                                                        >
                                                                            {$img = $oMerkmalWert->getImage(\JTL\Media\Image::SIZE_XS)}
                                                                            {if $img !== null && strpos($img, $smarty.const.BILD_KEIN_MERKMALBILD_VORHANDEN) === false
                                                                            && strpos($img, $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN) === false}
                                                                                {include file='snippets/image.tpl'
                                                                                    item=$oMerkmalWert
                                                                                    square=false
                                                                                    srcSize='xs'
                                                                                    sizes='40px'
                                                                                    width='40'
                                                                                    height='40'
                                                                                    class='img-aspect-ratio'
                                                                                    alt=$oMerkmalWert->getValue()}
                                                                            {else}
                                                                                <span class="badge badge-primary">{$oMerkmalWert->getValue()|escape:'html'}</span>
                                                                            {/if}
                                                                        </a>
                                                                    {/block}
                                                                </span>
                                                            {/if}
                                                        {/foreach}
                                                    </div>
                                                {/strip}
                                            </td>
                                        </tr>
                                    {/foreach}
                                {/block}
                            {/if}

                            {if $showShippingWeight}
                                {block name="productdetails-attributes-shipping-weight"}
                                    <tr class="attr-weight">
                                        <td class="attr-label">{lang key="shippingWeight" section="global"}&zwj;: </td>
                                        <td class="attr-value weight-unit">{$Artikel->cGewicht} {lang key="weightUnit" section="global"}
                                        </td>
                                    </tr>
                                {/block}
                            {/if}

                            {if $showProductWeight}
                                {block name="productdetails-attributes-product-weight"}
                                    <tr class="attr-weight" itemprop="weight" itemscope itemtype="http://schema.org/QuantitativeValue">
                                        <td class="attr-label">{lang key="productWeight" section="global"}&zwj;: </td>
                                        <td class="attr-value weight-unit weight-unit-article">
                                            <span itemprop="value">{$Artikel->cArtikelgewicht}</span> <span
                                                itemprop="unitText">{lang key="weightUnit" section="global"}</span>
                                        </td>
                                    </tr>
                                {/block}
                            {/if}

                            {if isset($Artikel->cMasseinheitName) && isset($Artikel->fMassMenge) && $Artikel->fMassMenge > 0 && $Artikel->cTeilbar !== 'Y' && ($Artikel->fAbnahmeintervall == 0 || $Artikel->fAbnahmeintervall == 1) && isset($Artikel->cMassMenge)}
                                {block name="productdetails-attributes-unit"}
                                    <tr class="attr-contents">
                                        <td class="attr-label">{lang key="contents" section="productDetails"}&zwj;: </td>
                                        <td class="attr-value">{$Artikel->cMassMenge} {$Artikel->cMasseinheitName}</td>
                                    </tr>
                                {/block}
                            {/if}

                            {if $dimension && $Einstellungen.artikeldetails.artikeldetails_abmessungen_anzeigen === 'Y'}
                                {block name="productdetails-attributes-dimensions"}
                                    {assign var=dimensionArr value=$Artikel->getDimensionLocalized()}
                                    {if $dimensionArr|count > 0}
                                        <tr class="attr-dimensions">
                                            <td class="attr-label">{lang key="dimensions" section="productDetails"}
                                                ({foreach $dimensionArr as $dimkey => $dim}
                                                    {$dimkey}{if $dim@last}{else} &times; {/if}
                                                {/foreach})&zwj;:
                                            </td>
                                            <td class="attr-value">
                                                {foreach $dimensionArr as $dim}
                                                    {$dim}{if $dim@last} cm {else} &times; {/if}
                                                {/foreach}
                                            </td>
                                        </tr>
                                    {/if}
                                {/block}
                            {/if}

                            {assign var=funcAttrVal value=$Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_ATTRIBUTEANHAENGEN]|default:0}
                            {if $Einstellungen.artikeldetails.artikeldetails_attribute_anhaengen === 'Y' || $funcAttrVal == 1}
                                {block name="productdetails-attributes-shop-attributes"}
                                    {foreach $Artikel->Attribute as $Attribut}
                                        <tr class="attr-custom">
                                            <td class="attr-label">{$Attribut->cName}&zwj;: </td>
                                            <td class="attr-value">{$Attribut->cWert}</td>
                                        </tr>
                                    {/foreach}
                                {/block}
                            {/if}
                        </tbody>{* /attr-group *}
                    </table>
                </div>
            {/block}
        {/strip}
    {/if}

{/block}