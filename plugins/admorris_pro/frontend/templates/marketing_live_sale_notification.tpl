<div id="admorris-live-sale-notification" class="animated fadeInUp {if !empty($admorris_pro_marketing_push_notification_prequest) && $admorris_pro_marketing_push_notification_prequest == 'Y'}admorris-live-sale-notification-move{/if}" style="display: none; ">
<div id="admorris-live-sale-notification-picture"></div>
<div id="admorris-live-sale-notification-text"></div>
</div>


<script type="module">
  import liveSaleNotification from '{$oPlugin_admorris_pro->getPaths()->getFrontendURL()}js/liveSaleNotification.js?v={$oPlugin_admorris_pro->getMeta()->getVersion()}';
  liveSaleNotification({
    data: {$smarty.session.admorris_pro_live_sale_notifications->data},
    duration: {$admorris_pro_marketing_live_sale_notification_duration},
    intervalDuration: {$admorris_pro_marketing_live_sale_notification_interval} + {$admorris_pro_marketing_live_sale_notification_duration},
    notificationDelay: {$admorris_pro_marketing_live_sale_notification_delay},
    langVars: {
      bought: ' {$admUtils::trans('marketing_live_sale_notification_firstrow_bought')}',
      someone: ' {$admUtils::trans('marketing_live_sale_notification_firstrow_someone')}',
      from: '  {$admUtils::trans('marketing_live_sale_notification_firstrow_from')}',
    },
  });
</script>

