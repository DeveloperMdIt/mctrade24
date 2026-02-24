
{if $admorris_pro_marketing_live_chat_typ === "widget"}
  {strip}
  <script {$admorris_pro_marketing_live_chat_cookie_header_intern} type="text/javascript">
  var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
  (function(){
  var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
  s1.async=true;
  s1.src='{$admorris_pro_marketing_live_chat_id}';
  s1.charset='UTF-8';
  s1.setAttribute('crossorigin','*');
  s0.parentNode.insertBefore(s1,s0);
  })();
  </script>
  {/strip}
{else}
  {$padding = $global_icon_size_multiplier * $admIconsUsed['chat']->sizeMultiplier / 4}

  <button type="button" id="live_chat_icon" class="chat-button chat-button--live animated fadeIn bg-color-brand-primary bg-primary hide" style="padding: {$padding}em">
      {* <span id="live_chat_logo" class="chat-icon"> *}
        {$admIcon->renderIcon('chat', "icon-content icon-content--center chat-icon", '', true)}
      {* </span> *}
  </button>
  <div id="live_chat_iframe">
      <button type="button" aria-label="Close chat" class="live_chat_iframe-close-button">&times;</button>
  </div>

  <script type="module" {$admorris_pro_marketing_live_chat_cookie_header_intern}>{strip}
  $(function(){
      var icon = $("#live_chat_icon").click(function(){
          $("#live_chat_iframe").fadeToggle(250);
      });

      $(".live_chat_iframe-close-button").click(function(){
          $("#live_chat_iframe").fadeToggle(250);
      });
        icon.removeClass('hide');

        var chatIframe = $('<iframe title="Chat" src="{$admorris_pro_marketing_live_chat_id}" height="100%" width="100%" frameborder="0" scrolling="no"></iframe>');
        $('#live_chat_iframe').append(chatIframe);

  });
  {/strip}
  </script>

{/if}