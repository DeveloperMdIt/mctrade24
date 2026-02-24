{$style = "{$instance->getStyleString()};{if $instance->getProperty('min-height')}min-height:{$instance->getProperty('min-height')}px;{/if} position:relative;"}
{$class = 'opc-Container '|cat:$instance->getAnimationClass()|cat:' '|cat:$instance->getStyleClasses()}
{$data  = $instance->getAnimationData()}
{$fluid = $instance->getProperty('boxed') === false}

{if $instance->getProperty('background-flag') === 'still' && !empty($instance->getProperty('still-src'))}
    {$name = basename($instance->getProperty('still-src'))}
    {$imgAttribs = $instance->getImageAttributes($instance->getProperty('still-src'))}
    {$style = "{$style} background-image:url('{$imgAttribs.src}');"}
{elseif $instance->getProperty('background-flag') === 'image' && !empty($instance->getProperty('src'))}
    {$name = basename($instance->getProperty('src'))}
    {$class = "{$class} parallax-window"}
    {$imgAttribs = $instance->getImageAttributes()}
    {if $isPreview}
        {$style = "{$style} background-image:url('{$imgAttribs.src}');"}
        {$style = "{$style} background-size:cover;"}
    {else}
        {$data = array_merge($data, [
            'parallax'  => 'scroll',
            'z-index'   => '1',
            'image-src' => $imgAttribs.src
        ])}
    {/if}
{elseif $instance->getProperty('background-flag') === 'video'}
    {$style          = "{$style} overflow:hidden;"}
    {$imgAttribs     = $instance->getImageAttributes($instance->getProperty('video-poster'))}
    {$videoPosterUrl = $imgAttribs.src}
    {$name           = basename($instance->getProperty('video-src'))}
    {$videoSrcUrl    = "{\JTL\Shop::getURL()}/{$smarty.const.PFAD_MEDIA_VIDEO}{$name}"}
{/if}

{function containerContent}
    {if $instance->getProperty('background-flag') === 'video' && !empty($instance->getProperty('video-src'))}
        <video autoplay loop playsinline muted poster="{$videoPosterUrl}" id="{$instance->getUid()}_video"
               style="display: inherit; width: 100%; position: absolute; left: 0; top: 0; opacity: 0.7;">
            {if !$isPreview}
                <source src="{$videoSrcUrl}" type="video/mp4">
            {/if}
        </video>
        {if !$isPreview}
            <button tabindex="0"
                    class="btn btn-sm opc-container-pause btn-pause"
                    onclick="togglePausePlayBgVideo()"
                    title="{lang key='pause' section='aria'}">
                <i class="fas fa-pause"></i>
            </button>
            {inline_script}<script>
                function togglePausePlayBgVideo()
                {
                    let button = $(event.currentTarget);
                    let fa = button.find('i');
                    let video  = $('#{$instance->getUid()}_video');

                    if (fa.hasClass('fa-pause')) {
                        video[0].pause();
                        fa.removeClass('fa-pause').addClass('fa-play');
                    } else {
                        video[0].play();
                        fa.addClass('fa-pause').removeClass('fa-play');
                    }
                }
            </script>{/inline_script}
        {/if}
    {/if}
    <div {if $isPreview}class='opc-area' data-area-id='container'{/if} style="position: relative;">
        {if $isPreview}
            {$instance->getSubareaPreviewHtml('container')}
        {else}
            {$instance->getSubareaFinalHtml('container')}
        {/if}
    </div>
{/function}

{if $inContainer && !$isPreview}
    <div style="{$style}" class="{$class}"
         {foreach $data as $key => $value}
             data-{$key}="{$value}"
         {/foreach}>
        {call containerContent}
    </div>
{else}
    {container style=$style class=$class data=$data fluid=$fluid}
        {call containerContent}
    {/container}
{/if}
