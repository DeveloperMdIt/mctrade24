  {capture icon assign="icon"}
    {* {if $admUtils::isTemplateActive()} *}
      {$admIcon->renderIcon('whatsApp', "icon-content icon-content--default icon-content--center chat-icon whatsapp_logo", '', true)}
      {* <div class="chat-icon whatsapp_logo fa fa-whatsapp" aria-hidden="true"></div> *}
    {* {else}
      <img class="chat-icon-img" src="{$oPlugin_admorris_pro->getPaths()->getFrontendURL()}icons/whatsapp-icon.svg" alt="">
    {/if} *}
  {/capture}
  {$padding = $global_icon_size_multiplier * $admIconsUsed['whatsApp']->sizeMultiplier / 4}
  
 <div class="whatsapp-chat whatsapp_chat_desktop">
    <a aria-label="Whatsapp" class="chat-button chat-button--whatsapp animated fadeIn hide {$admorris_pro_marketing_whatsapp_chat_class} " href="https://web.whatsapp.com/send?l={$meta_language}&phone={$admorris_pro_marketing_whatsapp_chat_number}&text={$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_whatsapp_chat_text')}" target="_blank" rel="noopener" style="padding: {$padding}em">  
      {$icon}
    </a>
</div>

<div class="whatsapp-chat whatsapp_chat_mobile">
    <a aria-label="Whatsapp" class="chat-button chat-button--whatsapp animated fadeIn hide {$admorris_pro_marketing_whatsapp_chat_class}" href="https://wa.me/{$admorris_pro_marketing_whatsapp_chat_number}?text={$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_whatsapp_chat_text')}" target="_blank" rel="noopener" style="padding: {$padding}em">
      {$icon}
    </a>
</div>

{inline_script}
<script{$admorris_pro_marketing_whatsapp_cookie_header_intern}>{strip}
$(function(){
  $('.chat-button').removeClass('hide')
});
{/strip}
</script>
{/inline_script}