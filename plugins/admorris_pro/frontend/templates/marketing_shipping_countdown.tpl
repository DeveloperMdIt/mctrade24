<div class="am-shipping-countdown">
  {$shipping_countdown_text}
</div>
{inline_script}
<script>
// Load discount timer script
    {if $shipping_countdown === "Y"}
        
        
        $(function(){
          if(typeof $.fn.countdown !== 'undefined') {
            initShippingCountdown();
          } else {
            loadjs('{$oPlugin_admorris_pro->getPaths()->getFrontendURL()}js/jquery.countdown.min.js', initShippingCountdown);
          }

          function initShippingCountdown() {

            var countdownDate = new Date('{$versandschlussIso}');

            $('.shipping-countdown-days')
            .countdown(countdownDate, function(event) {
                $(this).text(
                event.strftime(' %-D {$admUtils::trans('marketing_shipping_countdown_days') }')
                );
            });

            $('.shipping-countdown-hours')
            .countdown(countdownDate, function(event) {
                $(this).text(
                event.strftime(' %-H {$admUtils::trans('marketing_shipping_countdown_hours')} ')
                );
            });

            $('.shipping-countdown-minutes')
            .countdown(countdownDate, function(event) {
                $(this).text(
                event.strftime(' %-M {$admUtils::trans('marketing_shipping_countdown_minutes')} ')
                );
            });

            $('.shipping-countdown-seconds')
            .countdown(countdownDate, function(event) {
                $(this).text(
                event.strftime(' %-S {$admUtils::trans('marketing_shipping_countdown_seconds')} ')
                );
            });
          }
          });

    {/if}
</script>
{/inline_script}