{* image for productlist/item_box.tpl & item_list.tpl *}
{block 'productlist-item-box-image'}

    {function srcset}{strip}
        {if !empty($image)}
            {$image->cURLMini} {$imageData->xs->size->width}w,
            {$image->cURLKlein} {$imageData->sm->size->width}w,
            {$image->cURLNormal} {$imageData->md->size->width}w
        {/if}
    {/strip}{/function}

    {block name='productlist-image'}
        {$switchImage = isset($isMobile) && !$isMobile && !empty($Artikel->Bilder[1]) && $admorris_pro_templateSettings->hover_second_image === true}
        <div class="image-link-wrapper">
            <a class="image-wrapper {if $switchImage} image-wrapper--switch-image{/if}" href="{$Artikel->cURLFull}">
                {if isset($Artikel->Bilder[0]->cAltAttribut)}
                    {assign var='alt' value=$Artikel->Bilder[0]->cAltAttribut|strip_tags|truncate:60|escape:'html':'UTF-8':FALSE}
                {else}
                    {assign var='alt' value=$Artikel->cName}
                {/if}

                {if !empty($Artikel->AttributeAssoc.img_title)}
                    {assign var="img_title" value=$Artikel->AttributeAssoc.img_title}
                {/if}
                {block name='item-box-image'}
                    {counter assign=imgcounter print=0}
                    {* {include file="snippets/image.tpl" src=$Artikel->Bilder[0]->cURLNormal src2=$Artikel->Bilder[1]->cURLNormal|default:null alt=$alt} *}
                    {$image = $Artikel->Bilder[0]}
                    {$image2 = ''}

                    {$imageData = json_decode($image->galleryJSON)}
                    {* {dump($imageData)} *}
                    <div class="product-cell__image first-image">
                        {image
                            alt=$alt
                            progressiveLoading="{$image->cURLMini}"
                            src="{$image->cURLKlein}"
                            srcset="{srcset image=$image}"
                            lazy=true
                            sizes="auto"
                            data=["id"  => $imgcounter]
                            width=$imageData->md->size->width
                            height=$imageData->md->size->height
                        }
                    </div>

                    {if $switchImage}
                        {$image2 = $Artikel->Bilder[1]}
                        {$imageData2 = json_decode($image2->galleryJSON)}
                        <div class="product-cell__image second-image">
                            {image
                                alt=$alt
                                progressiveLoading=false
                                src="{$image2->cURLKlein}"
                                srcset="{srcset image=$image2}"
                                sizes="auto"
                                lazy=true
                                data=["id"  => $imgcounter|cat:"_2nd"]
                                width=$imageData2->md->size->width
                                height=$imageData2->md->size->height
                            }
                        </div>
                    {/if}
                {/block}

                {block name="item-box-image-overlay"}
                    {include 'snippets/overlay.tpl'}
                {/block}
            </a>
            {if $actions|default:true}
                <div class="product-cell__actions">
                    {include file='productlist/productlist_actions.tpl'}
                </div>
            {/if}
            {if isset($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE])}
                <div class="product-cell__purchase-info-container">
                    <div class="purchase-info alert alert-info last-no-mb" role="alert" aria-hidden="true">
                        {lang key='maximalPurchase' section='productDetails' assign='maximalPurchase'}
                        {$unitToShow = ""}
                        {if !empty($units)}{$unitToShow = $units}{/if}
                        <p>{$maximalPurchase|replace:"%d":$Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_MAXBESTELLMENGE]|replace:"%s":$unitToShow}</p>
                    </div>
                </div>
            {/if}
        </div>
    {/block}
{/block}
