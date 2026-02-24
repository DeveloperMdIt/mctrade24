{* preload first image of slider, slider styles and slider scripts *}

{if isset($amSlider) && count($amSlider->slide_arr) > 0}

    {* check if screen size is mobile or desktop at init *}
    {$firstSlide = null}
    {if $admPro->checkIfMobileScreen() === 1} 
        {if !empty($amSlider->mobileSlides)}
            {$firstSlide = $amSlider->mobileSlides[0]}
        {/if}
    {else}
        {if !empty($amSlider->desktopSlides)}
            {$firstSlide = $amSlider->desktopSlides[0]}
        {/if}
    {/if}
    
    {if !empty($firstSlide->image) && $firstSlide->mediaType === 'image'}
        {$srcSets = $admImage::getOPCImageSrcSet($firstSlide->image)}

        {* helper function getOPCImageSrcSetString is somehow faster than accessing slide->srcSet prop ???? - google page speed seems to like it *}
        {$scaling = 0}
        {if $amSlider->parallax === "1"}
            {$scaling = 0.2}
        {/if}

        {if !empty($srcSets->webp[0] && \JTL\Media\Image::hasWebPSupport())}
            {$srcSetString = $admImage::getOPCImageSrcSetString($srcSets->webp, $scaling)}
            <link rel="preload" as="image" href="{$firstSlide->absoluteImage}" imagesrcset="{$srcSetString}" imagesizes="100vw" fetchpriority="high">
        {else}
            {$srcSetString = $admImage::getOPCImageSrcSetString($srcSets->default, $scaling)}
            <link rel="preload" as="image" href="{$firstSlide->absoluteImage}" imagesrcset="{$srcSetString}" imagesizes="100vw" fetchpriority="high">
        {/if}
    {/if}

    {* <link rel="preload" href="{$amTemplateDirFull}styles/keen-slider.min.css" as="style">
    <link rel="preload" href="{$amTemplateDirFull}js/keen-slider.min.js" as="script">
    <link rel="modulepreload" href="{$amTemplateDir}js/admorris/keenSlider.js?v={$templateVersion}" as="script" crossorigin> *}
    <link rel="modulepreload" href="{$amTemplateDirFull}js/admorris/{$admPro->getJsFilenameFromManifest('proSlider.js')}" as="script">

    {if $amSlider->parallax}
        <link rel="preload" href="{$amTemplateDirFull}slider/js/rellax.min.js" as="script">
    {/if}
{/if}