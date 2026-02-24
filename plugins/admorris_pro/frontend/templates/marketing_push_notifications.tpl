<div id="permission_div" class="animated fadeIn">
  <button id="permission_bell" class="permission-bell show bg-color-brand-primary bg-primary">
    {$admIcon->renderIcon('alert', 'icon-content', '', true)}
  </button>
      <div id="speech-bubble" class="speech-bubble">
          <div id="push_ok">
              <span>{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_push_notification_prequest_titel')}</span>
              <div>
                  <button id="allow-pushnotifications-button" class="permission_button" {* onclick="requestPermission();$('#speech-bubble').fadeOut(300);" *}>{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_push_notification_prequest_button_yes')}</button>  
                  <button class="permission_button permission_button_no" onclick="$('#speech-bubble').fadeOut(300);">{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_push_notification_prequest_button_no')}</button>   
              </div>
          </div>
              <div style="clear: both;"></div>
          <div id="push_blocked">
              <span>{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_push_notification_prequest_blocked')}</span>
              <br>
                <a href="{$admorris_pro_pluginpfad}assets/marketing_push_notifications/push_instruction.png" target="_blank" title="{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_push_notification_prequest_blocked_click')}"><img src="{$admorris_pro_pluginpfad}assets/marketing_push_notifications/push_instruction.png"></a>
          </div>
      </div>
</div>

<script type="module">
window.addEventListener('load', function () { 
  

  {$kKunde = (!empty($smarty.session.Kunde)) ? $smarty.session.Kunde->getID() : 0}

  amNotificationRequest.init({
    lang: "{$smarty.session.kSprache}",
    kKunde: "{$kKunde}",
    kArtikel: "{(isset($Artikel))?$Artikel->getId():''}",
    WarenkorbWarensumme: "{$WarenkorbWarensumme[1]}",
    // Genehmigung direkt abfragen oder ueber vorherige eigene Abfrage
    permissionRequest: {if $admorris_pro_marketing_push_notification_prequest == 'N'}'direct'{else}'popover'{/if},
  });

  
});


</script>

