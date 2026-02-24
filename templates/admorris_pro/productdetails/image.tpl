{*custom*}
{block name='productdetails-image-size'}
    {if $Einstellungen.bilder.container_verwenden === 'Y'}
        {$width = $Einstellungen.bilder.bilder_artikel_gross_breite}
        {$height = $Einstellungen.bilder.bilder_artikel_gross_hoehe}
    {else}
        {$width = null}
        {$height = null}
    {/if}
{/block}

{block name='productdetails-image'}
<div class="productdetails-gallery is-loading{if count($Artikel->Bilder) > 1} productdetails-gallery--multiple-images{/if} carousel vertical slide" data-ride="carousel" data-interval="0">
    {block name='product-image'}
    <div class="productdetails-gallery__main-image">
        <div id="gallery" class="product-images slick-smooth-loading" data-slick-type="gallery">
            {foreach $Artikel->Bilder as $image}
                {$easyzoomImage = $admPro->getWebpImage($image->cURLGross)}
                {strip}
                <div class="{if strpos($image->cURLGross, 'keinBild.gif') === false}easyzoom{/if}" data-src="{$easyzoomImage}" title="{$image->cAltAttribut|escape:'quotes'}">
                    <a href="{$easyzoomImage}" title="{$image->cAltAttribut|escape:'quotes'}">
                        {$progressivePlaceholder = $image@first}
                        {$lazy = !$image@first}
                        {$thumbsColWidth = (count($Artikel->Bilder) > 1 && $admorris_pro_themeVars->galleryThumbnailsOrientation === '2') ? ($Einstellungen.bilder.bilder_artikel_mini_breite + 20) : 0}
                        {$sizes = $admPro->product_gallery_sizes($thumbsColWidth)}
                        {image
                            src="{$image->cURLNormal}"
                            srcset="
                                {$image->cURLMini} {$image->imageSizes->xs->size->width}w,
                                {$image->cURLKlein} {$image->imageSizes->sm->size->width}w,
                                {$image->cURLNormal} {$image->imageSizes->md->size->width}w,
                                {$image->cURLGross} {$image->imageSizes->lg->size->width}w"
                            sizes=$sizes
                            alt="{$image->cAltAttribut|escape:'quotes'}"
                            class="gallery-img onload-gallery-image"
                            progressiveLoading=$image->cURLMini
                            progressivePlaceholder=$progressivePlaceholder
                            data=["list"=>"{$admPro->getWebpImage($image->galleryJSON|escape:'html')}", "index"=>$image@index]
                            nativeLazyLoading=$lazy
                            fetchpriority={($lazy) ? 'low' : 'high'}
                            width=$width
                            height=$height
                        }
                    </a>
                </div>
                {/strip}
            {/foreach}
        </div>
    </div>
    {/block}

    {block name='productdetails-image-thumb-size'}
        {if $Einstellungen.bilder.container_verwenden === 'Y'}
            {$width = $Einstellungen.bilder.bilder_artikel_mini_breite}
            {$height = $Einstellungen.bilder.bilder_artikel_mini_hoehe}
        {/if}
    {/block}


    {block name='productdetails-image-preview'}
        {$imageCount = $Artikel->Bilder|@count}
        {if $imageCount > 1}
            <div class="productdetails-gallery__thumbs">
                <ul id="gallery_preview_wrapper" class="productdetails-gallery__image-thumbs product-thumbnails-wrapper">
                    {foreach $Artikel->Bilder as $image}
                        <li>
                            {image
                                src="{$image->cURLMini}"
                                alt="{$image->cAltAttribut|escape:'html':'UTF-8':FALSE}"
                                lazy=false
                                fluid=false
                                width=$width
                                height=$height
                                class='productdetails-gallery__thumb-image'}
                        </li>
                    {/foreach}
                </ul>
            </div>

        {/if}
    {/block}
</div>
{/block}


{block name='productdetails-photoswipe'}
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="pswp__bg"></div>

    <div class="pswp__scroll-wrap">

        <div class="pswp__container">
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
            <div class="pswp__item"></div>
        </div>

        <div class="pswp__ui pswp__ui--hidden">

            <div class="pswp__top-bar">

                <div class="pswp__counter"></div>

                <span class="pswp__button pswp__button--close" title="Close (Esc)"></span>

                <span class="pswp__button pswp__button--share" title="Share"></span>

                <span class="pswp__button pswp__button--fs" title="Toggle fullscreen"></span>

                <span class="pswp__button pswp__button--zoom" title="Zoom in/out"></span>

                <div class="pswp__preloader">
                    <div class="pswp__preloader__icn">
                        <div class="pswp__preloader__cut">
                            <div class="pswp__preloader__donut"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                <div class="pswp__share-tooltip"></div>
            </div>

            <span class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)">
            </span>

            <span class="pswp__button pswp__button--arrow--right" title="Next (arrow right)">
            </span>

            <div class="pswp__caption">
                <div class="pswp__caption__center"></div>
            </div>

        </div>
    </div>
</div>
{/block}
