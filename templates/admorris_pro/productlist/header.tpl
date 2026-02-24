{**Custom**}
{block name='productlist-header'}
{if !isset($oNavigationsinfo)
    || (!$oNavigationsinfo->getManufacturer() && !$oNavigationsinfo->getCharacteristicValue() && !$oNavigationsinfo->getCategory())}
    {opcMountPoint id='opc_before_heading'}
    <h1 class="productlist-search-heading">{$Suchergebnisse->getSearchTermWrite()}</h1>
{/if}


{if $Suchergebnisse->getSearchUnsuccessful() == true}
    {opcMountPoint id='opc_before_no_results'}
    <div class="alert alert-info">{lang key='noResults' section='productOverview'}</div>
    <form id="suche2" action="{$ShopURL}" method="get" class="form">
        <label for="searchkey">{lang key='searchText'}</label>
                    
        <div class="flex-button-group flex-button-group--wrap form-group">
            <input type="text" class="form-control" name="suchausdruck" value="{if $Suchergebnisse->getSearchTerm()}{$Suchergebnisse->getSearchTerm()|escape:'htmlall'}{/if}" id="searchkey" />
            <input type="submit" value="{lang key='searchAgain' section='productOverview'}" class="submit btn btn-primary" />
        </div>
    </form>
{/if}

{include file='snippets/extension.tpl'}
    
{block name='productlist-header-navinfo'}
    {if $oNavigationsinfo->getName()}
        {* <div class="title">
            <h1>{$oNavigationsinfo->getName()}</h1>
        </div> *}
        {opcMountPoint id='opc_before_heading'}
        {if empty($AktuelleKategorie->getCategoryFunctionAttribute('category_banner_image'))}
            {include 'productlist/category_heading.tpl'}
        {/if}
    {/if}
    {* {if (!empty($oNavigationsinfo->cBildURL) && $oNavigationsinfo->cBildURL !== 'gfx/keinBild.gif' && $oNavigationsinfo->cBildURL !== 'gfx/keinBild_kl.gif')
        || }
        
    {/if} *}

    {$showTitle = true}
    {$showImage = true}
    {$navData = null}
    {if $oNavigationsinfo->getCategory() !== null}

        {$showImage = in_array($Einstellungen['navigationsfilter']['kategorie_bild_anzeigen'], ['B', 'BT'])}
        {$navData = $oNavigationsinfo->getCategory()}
    {elseif $oNavigationsinfo->getManufacturer() !== null}
        {$showImage = in_array($Einstellungen['navigationsfilter']['hersteller_bild_anzeigen'], ['B', 'BT'])}
        {$navData = $oNavigationsinfo->getManufacturer()}
    {elseif $oNavigationsinfo->getCharacteristicValue() !== null}
        {$showImage = in_array($Einstellungen['navigationsfilter']['merkmalwert_bild_anzeigen'], ['B', 'BT'])}
        {$navData = $oNavigationsinfo->getCharacteristicValue()}
    {/if}

    {$navinfoImg = $oNavigationsinfo->getImageURL() !== $smarty.const.BILD_KEIN_KATEGORIEBILD_VORHANDEN
    && $oNavigationsinfo->getImageURL() !== 'gfx/keinBild_kl.gif'
    && $oNavigationsinfo->getImageURL() !== $imageBaseURL|cat:$smarty.const.BILD_KEIN_KATEGORIEBILD_VORHANDEN
    && $showImage}

    

    {$catDesc = $Einstellungen.navigationsfilter.kategorie_beschreibung_anzeigen === 'Y'
        && $oNavigationsinfo->getCategory() !== null
        && $oNavigationsinfo->getCategory()->getDescription()|strlen > 0}
    {$manufacturerDesc = $Einstellungen.navigationsfilter.hersteller_beschreibung_anzeigen === 'Y'
        && $oNavigationsinfo->getManufacturer() !== null
        && $oNavigationsinfo->getManufacturer()->getDescription()|strlen > 0}
    {$attributeDesc = $Einstellungen.navigationsfilter.merkmalwert_beschreibung_anzeigen === 'Y'
        && $oNavigationsinfo->getCharacteristicValue() !== null
        && $oNavigationsinfo->getCharacteristicValue()->getDescription()|strlen > 0}



    {$showDescription = $catDesc || $manufacturerDesc || $attributeDesc}


    {if $showDescription || $navinfoImg}
        <div class="category-header cluster justify-content-center" style="--cluster-spacing: var(--space-2xl-3xl);">
            {if $navinfoImg}
                    {include file='snippets/image.tpl'
                        class='productlist-header-description-image category-header__image align-self-start w-auto'
                        item=$navData
                        fluid=true
                        lazy=false
                        sizes='min(100vw, 500px)'
                        alt="{if $oNavigationsinfo->getCategory() !== null && !empty($navData->getImageAlt())}{$navData->getImageAlt()}{else}{$navData->getDescription()|default:''|strip_tags|truncate:50}{/if}"
                    }
            {/if}
            
            {if $showDescription}
                <div class="category-header__description desc clearfix">
                    <div class="item_desc custom_content">
                    {if $catDesc}
                        {$oNavigationsinfo->getCategory()->getDescription()}
                    {/if}
                    {if $manufacturerDesc}
                        {$oNavigationsinfo->getManufacturer()->getDescription()}
                    {/if}
                    {if $attributeDesc}
                        {$oNavigationsinfo->getCharacteristicValue()->getDescription()}
                    {/if}
                    </div>
                </div>
            {/if}


        </div>
    {/if}
{/block}

{if $nSeitenTyp === $smarty.const.PAGE_ARTIKELLISTE && $Suchergebnisse->getProducts()->count() === 0}
    {include 'layout/breadcrumb.tpl'}
{/if}


{* custom - subcategories moved to own template file *}
{include 'productlist/subcategories.tpl'}

{* show separator only if there is a description or subcategories *}
{if $Einstellungen.navigationsfilter.artikeluebersicht_bild_anzeigen !== 'N' && $oUnterKategorien_arr|@count > 0 || $showDescription || $navinfoImg}

    <hr class="v-spacing">

{/if}

{block name='productlist-header-include-selection-wizard'}
    {include file='selectionwizard/index.tpl' container=false}
{/block}

{if count($Suchergebnisse->getProducts()) > 0}
    {opcMountPoint id='opc_before_result_options'}
    <div id="improve_search" class="clearfix">
        {include file='productlist/result_options.tpl'}
    </div>
{/if}

{if $Suchergebnisse->getProducts()|@count <= 0 && isset($KategorieInhalt)}
    {if isset($KategorieInhalt->TopArtikel->elemente) && $KategorieInhalt->TopArtikel->elemente|@count > 0}
        {opcMountPoint id='opc_before_category_top'}
        {lang key='topOffer' section='global' assign='slidertitle'}
        {include file='snippets/product_slider.tpl' id='slider-top-products' productlist=$KategorieInhalt->TopArtikel->elemente title=$slidertitle}
    {/if}

    {if isset($KategorieInhalt->BestsellerArtikel->elemente) && $KategorieInhalt->BestsellerArtikel->elemente|@count > 0}
        {opcMountPoint id='opc_before_category_bestseller'}
        {lang key='bestsellers' section='global' assign='slidertitle'}
        {include file='snippets/product_slider.tpl' id='slider-bestseller-products' productlist=$KategorieInhalt->BestsellerArtikel->elemente title=$slidertitle}
    {/if}
{/if}

{* {if $Suchergebnisse->getProductCount() > 0}
    <div class="row list-pageinfo top10">
        <div class="col-4 page-current">
            <strong>{lang key='page' section='productOverview'} {$Suchergebnisse->getPages()->getCurrentPage()}</strong> {lang key='of' section='productOverview'} {$Suchergebnisse->getPages()->getTotalPages()}
        </div>
        <div class="col-8 page-total text-right">
            {lang key='products'} {$Suchergebnisse->getOffsetStart()} - {$Suchergebnisse->getOffsetEnd()} {lang key='of' section='productOverview'} {$Suchergebnisse->getProductCount()}
        </div>
    </div>
{/if} *}

{* <hr> *}
{/block}