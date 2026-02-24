{$data = $instance->getAnimationData()}
{$categories = $portlet->getPickedCategoriesArr($instance)}
{$classes = $instance->getAnimationClass()|cat:' '|cat:$instance->getStyleClasses()}
{$areaClass = ''}

{if $instance->getProperty('noGutters')}
    {$classes = $classes|cat:' no-gutters'}
{else}
    {$areaClass = 'pb-4'}
{/if}

{$vertical = 'd-flex '|cat:' align-items-'|cat:$instance->getProperty('verticalXS')|cat:' align-items-sm-'|cat:$instance->getProperty('verticalS')|cat:' align-items-md-'|cat:$instance->getProperty('verticalM')|cat:' align-items-lg-'|cat:$instance->getProperty('verticalL')|cat:' align-items-xl-'|cat:$instance->getProperty('verticalXL')}
{$classes = $classes|cat:' '|cat:$vertical}
{$columns = $portlet->getLayouts($instance)}
{$amountOfColumns = count($columns)}
{$amountOfCategories = count($categories)}

{if $amountOfColumns !== 0 && $amountOfCategories !== 0}
    <div id="{$instance->getProperty('blockId')}" class="adm-category-grid-wrapper adm-portlet" style="{$instance->getStyleString()}">
        {for $catIdx=0 to $amountOfCategories step=$amountOfColumns}
            {row data=$data|default:[] class=$classes}
                {foreach $columns as $i => $colLayout}
                    {if empty($categories[$catIdx + $i])}
                        {continue}
                    {/if}
                    {if $catIdx + $i < $amountOfCategories}
                        {$areaId="col-$i"}
                        {col class=$areaClass|default:null
                            cols=$colLayout.xs|default:false
                            md=$colLayout.sm|default:false
                            lg=$colLayout.md|default:false
                            xl=$colLayout.lg|default:false
                        }
                            {$imageSize = $instance->getProperty('imageSize')}
                            <div class="adm-category-grid-item {if $imageSize != 0}mx-{$imageSize}{/if}">
                                {$imgAlt = $categories[$catIdx + $i]->getImageAlt()}
                                {$noImage = true}
                                {if $categories[$catIdx + $i]->hasImage()}
                                    {$imgSrc = $categories[$catIdx + $i]->getImageURL()}
                                    {$noImage = false}
                                {/if}
                                {$imgAttribs = ''}
                                {if $noImage}
                                    {$imgAttribs = $instance->getImageAttributes($instance->getProperty('fallbackImage'))}
                                    {$imgSrc = $imgAttribs.src}
                                {/if}
                                {if $instance->getProperty('imageAspectRatio') == 'custom'}
                                    {$imgAspectRatio = $instance->getProperty('customAspectRatio')}
                                {else}
                                    {$imgAspectRatio = $instance->getProperty('imageAspectRatio')}
                                {/if}
                                <div class="adm-category-grid-link-wrapper position-relative overflow-hidden"
                                    style="{$instance->getStyleString()}">
                                    {link href=$categories[$catIdx + $i]->getURL() class="d-block"}
                                        {image
                                            fluid=true lazy=true webp=true
                                            src=$imgSrc
                                            alt="{if empty($imgAlt->cWert)}{$categories[$catIdx + $i]->getName()}{else}{$imgAlt->cWert}{/if}"
                                            sizes="{if $noImage}{$imgAttribs.srcsizes}{else}(min-width: 992px) 25vw, 33vw{/if}"
                                            srcset="
                                                {if $noImage}
                                                    {$imgAttribs.srcset}
                                                {else}
                                                    {$categories[$catIdx + $i]->getImage(\JTL\Media\Image::SIZE_XS)}
                                                    {$categories[$catIdx + $i]->getImageWidth(\JTL\Media\Image::SIZE_XS)}w,
                                                    {$categories[$catIdx + $i]->getImage(\JTL\Media\Image::SIZE_MD)}
                                                    {$categories[$catIdx + $i]->getImageWidth(\JTL\Media\Image::SIZE_MD)}w,
                                                    {$categories[$catIdx + $i]->getImage(\JTL\Media\Image::SIZE_XL)}
                                                    {$categories[$catIdx + $i]->getImageWidth(\JTL\Media\Image::SIZE_XL)}w
                                                {/if}"|strip
                                            class="w-100"
                                            style="object-fit: {$instance->getProperty('imageType')}; aspect-ratio: {str_replace("\\", "", $imgAspectRatio)};{if $instance->getProperty('imageHeight')} height: {$instance->getProperty('imageHeight')}px;{/if}"
                                        }
                                        <div class="adm-category-card-image-overlay w-100 h-100 position-absolute"
                                            style=" top: 0; background-color:{$instance->getProperty('overlayColor')};"></div>
                                        {if $instance->getProperty('categoryCardType') == 'textInside'}
                                            <div class="position-absolute w-100 h-100 d-flex align-items-center justify-content-center p-3"
                                                style="top: 0; left: 0">
                                                {include file='./categoryTitle.tpl' inCard=true}
                                            </div>
                                        {/if}
                                    {/link}
                                </div>
                                {if $instance->getProperty('categoryCardType') == 'textOutside'}
                                    {include file='./categoryTitle.tpl' inCard=false}
                                {/if}
                            </div>
                        {/col}
                    {/if}
                {/foreach}
            {/row}
        {/for}
    </div>
{else}
    <div class="alert alert-info">
        Bitte wählen Sie Kategorien aus.
    </div>
{/if}