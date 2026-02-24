{* check if mobile screen -> use mobile slides, else desktop slides *}
{$slider = null} 
{if $admPro->checkIfMobileScreen() === 1} 
    {$slider = $amSlider->mobileSlides}
{else}
    {$slider = $amSlider->desktopSlides}
{/if}

{if !empty($slider)}
    {* <link rel="stylesheet" href="{$amTemplateDirFull}styles/keen-slider.min.css" /> *}

    {$fullscreen = (in_array($amSlider->fullscreen, ['fullscreen', 'fullscreen_framed'])) ? true : false}

    {$sizeRatio = json_decode($amSlider->sizeRatio)}


    {if $amSlider->fullscreen === 'image_ratio'}
        {if $amSlider->slide_arr[0]->image}
            {$firstSlideSize = $admImage::imageSize($amSlider->slide_arr[0]->image)}
            {$imageRatio = "{$firstSlideSize->width} / {$firstSlideSize->height}"}
        {else}
            {* if no image is set in first slide => 16:9*}
            {$imageRatio = '16 / 9'} 
        {/if}
    {/if}

    {$backgroundColor = $amSlider->sliderBackgroundColor}
    {* use background of first slide, else general slider bg color *}
    {* {$backgroundColor = (!empty($slider[0]->bgColor)) ? $slider[0]->bgColor : $amSlider->sliderBackgroundColor} *}

    {* check if slider has youtube api / vimeo sdk -> load them async in script*}
    {$sliderHasYoutubeVideo = $admPro->checkIfAmSliderHasVideo($slider, 'youtube')}
    {$sliderHasVimeoVideo = $admPro->checkIfAmSliderHasVideo($slider, 'vimeo')}

    {* handle responsive breakpoints *}
    {if $amSlider->fullscreen === 'default'}
        <style>
            .keen-slider__wrapper {
                aspect-ratio: {$sizeRatio->gridwidth[0]} / {$sizeRatio->gridheight[0]};
            }

            {foreach $sizeRatio->gridwidth as $key => $value}
                @media screen and (max-width: {$sizeRatio->gridwidth[$key]}px) {
                    .keen-slider__wrapper {
                        aspect-ratio: {$sizeRatio->gridwidth[$key]} / {$sizeRatio->gridheight[$key]};
                    }
                }
            {/foreach}
        </style>
    {/if}

    <div id="keen-slider__wrapper" class="keen-slider__wrapper container--{if $amSlider->sliderContainerSize === 'Global'}{$admorris_pro_templateSettings->global_container}{else}{$amSlider->sliderContainerSize}{/if}{if $fullscreen} fullscreen-container{/if}{if $admorris_pro_templateSettings->header_overlay} overlay{/if}"
        {if isset($imageRatio)} style="aspect-ratio: {$imageRatio};" {/if} style="
            background-color: {$backgroundColor};
        {if $amSlider->fullscreen == 'fullscreen_framed'}
            border:20px solid {$amSlider->sliderBackgroundColor};
        {/if}
        ">
        {if $amSlider->parallax}
            <div class="keen-slider__rellax-wrapper--scaled">
        {/if}
            {* show preview of first slide before slider has been initialized -> better LCP *}
            {$firstSlide = $slider[0]}
            {* render text content before slider has been initialized *}
            {include file="slider/am_slide_text.tpl"
                slideIndex=0
                template=$firstSlide->template 
                title=$firstSlide->title
                mainText=$firstSlide->content
                extraText=$firstSlide->extraText
                buttonText=$firstSlide->buttonText 
                buttonEnabled=$firstSlide->buttonEnabled|filter_var:FILTER_VALIDATE_BOOLEAN
                seperatorEnabled=$firstSlide->separatorLine|filter_var:FILTER_VALIDATE_BOOLEAN
                contentPosition=$firstSlide->contentPosition
                textAlignment=$firstSlide->textAlignment
                textColor=$firstSlide->textColor
                btnHoverTextColor=$firstSlide->buttonHoverTextColor
                link=$firstSlide->link
                separatorLineLength=$firstSlide->separatorLineLength
            }        
            {if $amSlider->parallax}
                <div class="rellax--scaled rellax--scaled-preview">
            {/if}

                {if $firstSlide->kenBurns === "1"}
                    <div class="keen-slider__slide-ken-burns">
                {/if}
                    {$kenBurnsZoomOut = false}
                    {if $firstSlide->kenBurns === "1" && $firstSlide->kenBurnsAnimation === "zoom_out"}
                        {$kenBurnsZoomOut = true}
                    {/if}

                    {if !empty($firstSlide->image) && $firstSlide->mediaType === "image"}
                        {include file="slider/am_slide_image.tpl"
                            slide=$firstSlide
                            beforeInitSlider=true
                            beforeinitSliderKenBurns=$kenBurnsZoomOut
                        }
                    {/if}


                {if $firstSlide->kenBurns === "1"}
                    </div>
                {/if}
            {if $amSlider->parallax}
                </div>
            {/if}
            <div id="keen-slider" class="keen-slider">
                {foreach from=$slider item=slide key=key}
                    {if !empty($slide->transition)}
                        {$transition = $slide->transition}
                    {else}
                        {$transition = $amSlider->transition}
                    {/if}
                    <div class="keen-slider__slide--custom{if $key===0} keen-slider__slide--initial{/if}" style="--transitionName:{$transition}">
                        {include file="slider/am_slide.tpl" slide=$slide slider=$amSlider parallax=$amSlider->parallax slideIndex=$key}
                    </div>
                {/foreach}
            </div>
        {if $amSlider->parallax}
            </div>
        {/if}

        {if (count($slider) > 1)}
            {if !empty($amSlider->navArrowsSvg)}
                <div id="keen-slider__arrows" class="keen-slider__arrows
                    {if $admorris_pro_templateSettings->header_overlay} overlay{/if} 
                    keen-slider__nav
                    {if $amSlider->hideNavOnLeave} --hidden{/if} 
                    {if $amSlider->hideNavOnMobile} --hidden-on-mobile{/if}"
                    style="fill: {$slider[0]->textColor}">
                    <button type="button" aria-label="Previous Slide" id="keen-slider__arrows--left" class="keen-slider__arrows--left button-reset">
                        {$amSlider->navArrowsSvg}
                    </button>
                    <button type="button" aria-label="Next Slide" id="keen-slider__arrows--right" class="keen-slider__arrows--right button-reset">
                        {$amSlider->navArrowsSvg}
                    </button>
                </div>
            {/if}
            {if !empty($amSlider->navBulletsSvg)}
                <div id="keen-slider__dots__wrapper" class="keen-slider__dots__wrapper keen-slider__nav
                    {if $amSlider->hideNavOnLeave} --hidden{/if}
                    {if $amSlider->hideNavOnMobile} --hidden-on-mobile{/if}"
                    style="fill: {$slider[0]->textColor}">
                    <div id="keen-slider__dots" class="keen-slider__dots">
                        {foreach $slider as $slide}
                            <button type="button" aria-label="Slide #{$slide@iteration}" class="keen-slider__dots--dot button-reset">{$amSlider->navBulletsSvg}</button>
                        {/foreach}
                    </div>
                </div>
            {/if}
        {/if}
    </div>


    {if $amSlider->parallax}
        <script src="{$amTemplateDirFull}slider/js/rellax.min.js"></script>

        <script>
            const rellaxContainers = Array.from(document.querySelectorAll('.rellax'));
            // rellax sets transform prop on scroll and css rules for transfrom are overwritten
            // -> we append additional transforming in callback
            var rellax = new Rellax('.rellax');
        </script>
    {/if}

    {$firstImageSrcSet = json_encode(null)}
    {if !empty($firstSlide->srcSets)}
        {$firstImageSrcSet = json_encode($firstSlide->srcSets)}
    {/if}

    <script>
        window.admorris_slider_options = {
                delay: "{$amSlider->delay}",
                autoplay: Boolean({$amSlider->autoplay}),
                loop: Boolean({$amSlider->looping}),
                pauseOnHover: Boolean({$amSlider->pauseOnHover}),
                hideNavOnLeave: Boolean({$amSlider->hideNavOnLeave}),
                parallax: Boolean({$amSlider->parallax}),
                aspectRatio: "{$amSlider->fullscreen}",
                hasYoutubeVideo: {$sliderHasYoutubeVideo},
                hasVimeoVideo: {$sliderHasVimeoVideo},
                firstImgSrcSet: {$firstImageSrcSet},
                mobileSlides: {json_encode($amSlider->mobileSlides)},
                desktopSlides: {json_encode($amSlider->desktopSlides)}
        }
    </script>

{/if}
