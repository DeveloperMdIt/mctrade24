{block name='blog-image-banner'}
    {strip}
    <div id="blog-banner" class="blog-banner{$admBlogSettings->getBannerCssClasses($admorris_pro_templateSettings)}"
    {if $admBlogSettings->bannerParallax} 
        data-parallax="scroll" data-image-src="{$admBlogSettings->currentBannerImage}"
    {else}
        style="background-image: url('{$ShopURL}/{$admBlogSettings->currentBannerImage}');"
    {/if} itemscope>
        <div class="blog-banner__wrapper">
            {if \JTL\Shop::$AktuelleSeite !== 'NEWSDETAIL' && !$admBlogSettings->category}
                <h1 class="blog-banner__title">
                    {lang key='news' section='news'}
                </h1>
            {elseif $admBlogSettings->category}
                <h1 class="blog-banner__title">
                    {$admBlogSettings->category->cName}
                </h1>
            {else}
                <h1 class="blog-banner__title" itemprop="headline">
                    {$oNewsArchiv->getTitle()}
                </h1>
                {include 'blog/details_meta.tpl'}
            {/if}
        </div>
    </div>

    {if $admBlogSettings->getBannerType() === 'image-ratio'}
        {$image = $admImage::imageSize($admBlogSettings->currentBannerImage)}
        <style>
            .blog-banner::before {
                content: "";
                width: 1px;
                margin-left: -1px;
                float: left;
                height: 0;
                padding-top: {$image->height / $image->width * 100}%;
            }

            .blog-banner::after {
                content: "";
                display: table;
                clear: both;
            }
        </style>
    {/if}
    {/strip}
{/block}