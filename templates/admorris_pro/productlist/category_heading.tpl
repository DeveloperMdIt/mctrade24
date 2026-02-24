{*custom*}

{* included in layout/header.tpl with banner=true and in productlist/header.tpl
   parallax.js used for background image
   conditionally loaded in <head> 
   *}

{block 'productlist-category-heading'}

{$bannerClass = (isset($banner))?' productlist-heading--banner':''}
{if isset($banner)}
    {$backgroundImagePath = $admPro->getCategoryBannerImage()|trim}
    {$parallax = $Einstellungen.template.productlist.banner_parallax === 'Y'}
{/if}


{if isset($AktuelleKategorie->getCategoryFunctionAttribute('banner_alignment'))}
    {$alignment = " productlist-heading--align-`$AktuelleKategorie->getCategoryFunctionAttribute('banner_alignment')`"}
{else}
    {$alignment = ''}
    
{/if}

{* Banner Image Position *}
{if isset($AktuelleKategorie->getCategoryFunctionAttribute('banner_image_pos'))}
    {$bgPos = $AktuelleKategorie->getCategoryFunctionAttribute('banner_image_pos')}
    {if $parallax}
        {$bannerPos = "data-position='$bgPos'"}
    {else}
        {$bannerPos = "background-position: $bgPos;"}
        
    {/if}
{else}
    {$bannerPos = ''}
{/if}
{* Banner Text Color *}
{if !empty($AktuelleKategorie->getCategoryFunctionAttribute('banner_text_color'))}
    {$textColor = $AktuelleKategorie->getCategoryFunctionAttribute('banner_text_color')}
{/if}

<div class="productlist-heading text-center{$bannerClass}{$alignment}{if isset($banner) && !empty($parallax)} parallax-window{/if}" data-category-id="{$AktuelleKategorie->getID()}"{if isset($banner) && !empty($parallax)} data-parallax="scroll" data-image-src="{$backgroundImagePath}"{$bannerPos}{elseif isset($banner) && empty($parallax)}style="background-image:url('{$backgroundImagePath}');{$bannerPos}"
    {/if}>
    {if $oNavigationsinfo->getName()}<h1 class="productlist-heading__title"{if !empty($textColor)} style="color: {$textColor};"{/if}><span>{$oNavigationsinfo->getName()}</span></h1>{/if}
</div>

{/block}