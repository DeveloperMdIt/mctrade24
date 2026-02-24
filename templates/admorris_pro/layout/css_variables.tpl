{if $nSeitenTyp === $smarty.const.PAGE_ARTIKEL}
    {if $Einstellungen.bilder.container_verwenden === 'Y'}
        {$imageXsWidth = $Einstellungen.bilder.bilder_artikel_mini_breite}
    {else}
        {$imageXsWidth = $Artikel->getImageWidth('xs')}
    {/if}
{/if}
<style>
    :root {
        {if $nSeitenTyp === $smarty.const.PAGE_ARTIKEL}
            --article-image-xs-width: {$imageXsWidth}px;
        {/if}
        --container-size: {$admPro->container_size_px()}px;
        {$productSliderDisplayCount = $admPro->setProductSliderDisplayAmount()}
        --product-slider-display-count-xs: {$productSliderDisplayCount.xs};
        --product-slider-display-count-sm: {$productSliderDisplayCount.sm};
        --product-slider-display-count-md: {$productSliderDisplayCount.md};
        --product-slider-display-count-lg: {$productSliderDisplayCount.lg};
        --product-slider-display-count-xl: {$productSliderDisplayCount.xl};
        
        {block 'layout-css-variables-root'}{/block}
    }
</style>

