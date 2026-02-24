{extends file="file:[admPro]marketing_popup/popup_base.tpl"}

{$popupType = 'iframe'}

{block name='admorris-pro-popup-modal-attributes'}aria-label="{$contentTitle}"{/block}


{block 'admorris-pro-popup-classname'}iframe-popup{/block}

{block name='admorris-pro-popup-additional-code'}
  <script>
    document.addEventListener("DOMContentLoaded",function(){
      (function() {
        var popupIframe = $("<iframe title='{$contentTitle}' src='{$contentLink}' frameborder='0' {if $styleScrollbar == '0'}scrolling='no'{/if}></iframe>");
        $('#admorris_pro_popup_{$id} .admorris-modal-body').prepend(popupIframe);
      })();
    });
  </script>
{/block}
