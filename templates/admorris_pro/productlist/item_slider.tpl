{*custom*}
{* template to display products in slider *}
{block name='productlist-item-slider'}
    {$alignment = $admorris_pro_templateSettings->productCellTextAlignment}
    {$hoverEnabledClass = (!empty($hoverEnabled)) ? ' hover-enabled' : ''}

    <div class="product-cell{if isset($class)} {$class}{/if} {$hoverEnabledClass}">
        <div class="image-link-wrapper">
            {block name='productlist-item-slider-image'}{* Nova Block for compatibility *}
            {block name='item-slider-image-wrapper'}
                <a class="image-wrapper" href="{$Artikel->cURLFull}">
                    {if isset($Artikel->Bilder[0]->cAltAttribut)}
                        {assign var="alt" value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html':'UTF-8':FALSE}
                    {else}
                        {assign var="alt" value=$Artikel->cName}
                    {/if}
                    {$img_title = ''}
                    {if isset($Artikel->AttributeAssoc.img_title)}
                        {assign var="img_title" value=$Artikel->AttributeAssoc.img_title}
                    {/if}

                    {* {$imageSize->height} *}
                    {block name='item-slider-image'}
                        {$firstImage = $Artikel->Bilder[0]->cPfadNormal}
                        {$secondImage = $Artikel->Bilder[1]->cPfadNormal|default:''}

                        {*
                {if isset($bAjaxRequest) && $bAjaxRequest}
                    {$lazy = false}

                    <img  data-first="{$firstImage}" data-second="{$secondImage}" id="{$Artikel->kArtikel}" src="{$Artikel->Bilder[0]->cPfadNormal}" alt="{$alt}"{if !empty($img_title)} title="{$img_title}"{/if} />
                {else}

                {/if}
                *}

                        {$lazy = true}
                        {$imageSize = $admPro->getImageSize($Artikel->Bilder[0]->cPfadGross)}
                        {$width = $imageSize->size->width|default:''}
                        {$height = $imageSize->size->height|default:''}
                        {$progressiveLoading = $Artikel->Bilder[0]->cPfadMini}

                        {include file='snippets/image.tpl' item=$Artikel square=false srcSize='sm' class='product-image' title=$img_title lazy=$lazy width=$width height=$height progressiveLoading=$progressiveLoading}
                    {/block}
                    {*include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cPfadKlein alt=$alt*}
                    {block name='item-slider-overlay'}
                        {include 'snippets/overlay.tpl'}

                        {if isset($am_discountDisplay) && $am_discountDisplay->list_insert_type === 'overlay'}
                            {$am_discountDisplay->productSlider($Artikel)}
                        {/if}
                    {/block}
                </a>
            {/block}
            {/block}
        </div>
        {block name='item-slider-caption'}
            {if empty($noCaptionSlider)}
                <div class="product-cell__caption caption{if $alignment === 'center'} text-center{/if}">
                    {block name='item-slider-title-wrapper'}
                        <div class="product-cell__title-wrapper">
                            {block name='item-slider-title'}
                                <div class="product-cell__title word-break h4">
                                    {if isset($showPartsList) && $showPartsList === true && isset($Artikel->fAnzahl_stueckliste)}
                                        <span class="article-bundle-info">
                                            <span class="bundle-amount">{$Artikel->fAnzahl_stueckliste}</span> <span
                                                class="bundle-times">x</span>
                                        </span>
                                    {/if}
                                    <a href="{$Artikel->cURLFull}">
                                    {$Artikel->cKurzbezeichnung}
                                    </a>
                                </div>
                            {/block}
                        </div>
                    {/block}

                    {block name='item-slider-rating'}
                        {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $Artikel->fDurchschnittsBewertung > 0}
                            <div class="item-box__rating item-box__rating--slider">
                                {include file='productdetails/rating.tpl' stars=$Artikel->fDurchschnittsBewertung}
                            </div>
                        {/if}
                    {/block}
                    {block name='item-slider-price'}
                        <div>
                            {include file='productdetails/price.tpl' Artikel=$Artikel tplscope=$tplscope}
                        </div>
                    {/block}
                </div>
            {/if}
        {/block}
    </div>{* /product-cell *}
{/block}