{if $slide->kenBurns === "1"}
    <div class="keen-slider__slide-ken-burns" 
        style="--kenBurnsAnimation: ken_burns_{$slide->kenBurnsAnimation}; 
    --kenBurnsDuration: {if !empty($slide->kenBurnsDuration)} {$slide->kenBurnsDuration}ms {else} 2000ms {/if}"
    >
{/if}
    
{$backgroundColor = (!empty($slide->bgColor)) ? $slide->bgColor : $amSlider->sliderBackgroundColor}

{if $parallax}
<div class="rellax rellax--scaled" data-rellax-speed="-4" >
{/if}
    {* if background *}
    <div class="am-slide__background-item" style="background-color: {$backgroundColor}"></div>
    {$beforeInitSlider = $slideIndex === 0} {* no need for lazy laod on first slide *}
    {* if image *}
    {if !empty($slide->image) && $slide->mediaType === 'image'}
        {include file="slider/am_slide_image.tpl" slide=$slide beforeInitSlider=$beforeInitSlider 
        beforeinitSliderKenBurns=false}
    {/if}

    {* if video *}
    {if !empty($slide->video) && $slide->mediaType === 'video'}
        {if $slide->videoType === 'html5'}
            <div id="player-native-{$slide->id}-{$slide->video}" class="am-slide__video-item--html5"></div> 
        {/if}
        {if $slide->videoType === 'vimeo'}
            <div class="am-slide__video-item-embed-container">
                <div id="playerIframe-vimeo-{$slide->id}-{$slide->video}" class="am-slide__video-item-embed-container-iframe"></div>
            </div>
        {/if}
        {if $slide->videoType === 'youtube'}
            <div class="am-slide__video-item-embed-container">
                <div id="playerIframe-youtube-{$slide->id}-{$slide->video}" class="am-slide__video-item-embed-container-iframe"></div>
            </div>
        {/if}
    {/if}

{if $amSlider->parallax}
</div>
{/if}

{if $slide->kenBurns === "1"}
    </div>
{/if}

{* text and button layer *}
{include file="slider/am_slide_text.tpl"
    template=$slide->template 
    title=$slide->title
    mainText=$slide->content
    extraText=$slide->extraText
    buttonText=$slide->buttonText 
    buttonEnabled=$slide->buttonEnabled|filter_var:FILTER_VALIDATE_BOOLEAN
    seperatorEnabled=$slide->separatorLine|filter_var:FILTER_VALIDATE_BOOLEAN
    contentPosition=$slide->contentPosition
    textAlignment=$slide->textAlignment
    textColor=$slide->textColor
    btnHoverTextColor=$slide->buttonHoverTextColor
    link=$slide->link
    separatorLineLength=$slide->separatorLineLength
}