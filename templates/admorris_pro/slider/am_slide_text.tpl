
{if ($textAlignment === 'left')} {$flexAlignment = 'flex-start'} {/if}
{if ($textAlignment === 'right')} {$flexAlignment = 'flex-end' } {/if}
{if ($textAlignment === 'center')} {$flexAlignment = 'center' } {/if}

{if ($contentPosition) === 'left'} {$contentAlignment = '0 40% 0 0'} {/if}
{if ($contentPosition) === 'center'} {$contentAlignment = '0 20%'} {/if}
{if ($contentPosition) === 'right'} {$contentAlignment = '0 0 0 40%'} {/if}

{if !$buttonEnabled && !empty($link)}
    <a 
        href="{$admPro->handleSlideLink($link)}"
        class="slide-content am-slide__content-link--overlay active"
        {if $admPro->openSlideLinkInNewTab($admPro->handleSlideLink($link))}
            target="_blank"
        {/if}>
    </a>
{/if}

<div class="am-slide__content-container {if $slideIndex === 0}am-slide__content-container--initial{/if} am-slider__content-wrapper" 
    style="--amSlideTextColor: {$textColor}; --amSlideButtonHoverColor: {$btnHoverTextColor}; 
    --amSlideTextAlignment: {$textAlignment}; --amSlideFlexAlignment: {$flexAlignment};
    --amSlideContentAlignment: {$contentAlignment}"
    data-template="{$template}">
    {if !empty($title)}
        <div class="slide-content am-slide__content-title am-slider__title">
            <div class="overlay am-slide__content-title--overlay"></div>
            <div class="animated am-slide__content-title--animated">
                {$title}
            </div>
        </div>
    {/if}

    {if $seperatorEnabled === true}
        <div class="slide-content am-slide__content-separator" style="width: min({$separatorLineLength}px, 100%);">
            <div class="animated am-slide__content-separator--animated"></div>
        </div>
    {/if}

    {if !empty($mainText)}
        <div class="slide-content am-slide__content am-slide__content-text am-slider__content">
            <div class="overlay am-slide__content-text--overlay"></div>
            <div class="animated am-slide__content-text--animated ">
                {$mainText}
            </div>
        </div>
    {/if}

    {if !empty($extraText)}
        <div class="slide-content am-slide__content-extra am-slider__extra-text">
            <div class="overlay am-slide__content-extra--overlay"></div>
            <div class="animated am-slide__content-extra--animated">
                {$extraText}
            </div>
        </div>
    {/if}

    {if $buttonEnabled && !empty($link)}
        <div class="slide-content am-slide__content-button am-slider__button position-relative">
            <div class="overlay am-slide__content-button--overlay"></div>
                <a href="{$admPro->handleSlideLink($link)}"
                    {if $admPro->openSlideLinkInNewTab($admPro->handleSlideLink($link))}target="_blank"{/if}
                    class="animated am-slide__content-button--animated am-slider__button-link text-center">
                {$buttonText}
            </a>
        </div>
    {/if}
</div>