{* sizes attribute if there is an image *}
{if !empty($styleSizeWidth)}
  {$sizes = "min(90vw, {$styleSizeWidth})"}
{else}
  {$sizes = "90vw"}
{/if}


<div class="admorris-popup-container {block 'admorris-pro-popup-classname'}{/block}" style="color: {$styleColor}">
  <!-- Modal -->
  <div class="modal {$stylePosition}" 
    id="admorris_pro_popup_{$id}" 
    tabindex="-1" 
    role="dialog" 
    aria-modal="true"
    {block name='admorris-pro-popup-modal-attributes'}{/block}
    style="background-color: {$styleBackdropColor}; --admPro-popup-align: {$align}; --admPro-popup-justify: {$justify}; --admPro-popup-width: {$styleSizeWidth|default:'100%'}; --admPro-popup-height: {$styleSizeHeight|default:'100%'}"
  >
    <div class="modal-dialog" role="document" style="pointer-events: all; {if $popupType|default:'' === 'iframe' && !empty($styleSizeHeight)}max-height: {$styleSizeHeight};{/if}">
    
      <!-- Modal content-->
              
      <div class="admorris-modal-body animated {$styleAnimation} {if $styleShadow == '1'}admorris-modal-shadow{/if}" style="background-color: {$styleBackgroundColor};{if isset($imageAspectRatio)}aspect-ratio: {$imageAspectRatio};{/if}">

        {block name='admorris-pro-popup-content-image'}
          {if $popupHasImage}
            <div class="admorris-popup-modal-image">
              {if !empty($contentLink) && $showImageLink|default:true}
                <a href="{$contentLink}" onclick="amPopupTrigger.triggerSetCookie();location.href='{$contentButtonLink}'">
                  {call bgImage}
                </a>
              {else}
                {call bgImage}
              {/if}
            </div>
          {/if}
        {/block}

        {block name='admorris-pro-popup-content'}{/block}


        {if $styleCloseButton == '1'}
            <button type="button" class="admorris-close-button" aria-label="{$admUtils::trans('close_popup')}" data-dismiss="modal">&times;</button>
        {/if}

      </div>
    
    </div>
  </div>
</div>

{block name='admorris-pro-popup-additional-code'}{/block}

{include file="file:[admPro]marketing_popup/init_popup.tpl"}

{function bgImage}
  {if !empty($styleBackgroundImage)}
    {responsiveImage 
      src="{$styleBackgroundImage}"
      alt="{$contentText3}"
      class="popup-img admorris_pro_popup_background_image"
      fluid=true
      webp=true
      opc=true
      nativeLazyLoading=true
      sizes=$sizes
    }
  {/if}
{/function}
