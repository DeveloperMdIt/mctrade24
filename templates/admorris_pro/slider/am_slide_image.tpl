{* set scale for preview slide to match kenBurns before init *}
{$scalePreviewImage = ""}
{if $beforeinitSliderKenBurns === true}
    {$scalePreviewImage = "transform: scale(1.3);"}
{/if}

{$opc = true}
{$src = $slide->image}
{* Images from shop4 were put in mediafiles/Bilder, but the url just started with 'Bilder'. That is why absouteImage has to be used for those *}
{if strpos($slide->image, 'Bilder') === 0}
    {$opc = false}
    {$src = $slide->absoluteImage}
{/if}

{* if preview / current slide has parallax we use scaling -> alters srcset string (width param) so image with bigger resolution will be choosen for smaller viewport*}
{* this wa we don't loose that much information, e.g. image won't seem blurry *}
{$scaling = 0}

{if $amSlider->parallax === "1"}
    {$scaling = 0.2}
{/if}

{* Check ken burns effect ans make sizes larger *}

{responsiveImage
    src="{$src}"
    alt="Slider Image"
    lazy=false opc=$opc progressivePlaceholder=$beforeInitSlider
    class="lazy-load-placeholder am-slide__image-item"
    sizes="{if $beforeInitSlider}100vw{else}auto{/if}"
    style="; object-position: {$slide->imagePosH} {$slide->imagePosV}; background-position: {$slide->imagePosH} {$slide->imagePosV}; background-color: {$backgroundColor};
        {if ($beforeInitSlider === true)} position: absolute;{/if}
        {$scalePreviewImage}"
    scaling=$scaling
    alt="{$slide->altTag}"
}
