<div id="discount_timer_widget" style="display: none;">
    <form method="post" action="{get_static_route id='warenkorb.php'}">
        {$jtl_token}
            <div class="form-group{if !empty($invalidCouponCode) || !empty($cKuponfehler)} has-error{/if}">
                <div class='widget-couponbox'>
                    <div class='widget-couponbox-inner'>
                        <input type="hidden" name="Kuponcode" maxlength="10" value="{$coupon_code}" readonly hidden/>
                        <input class="discount_timer_countdown_widget_input" type="submit" value="{$coupon_code}" aria-label="{lang key='couponCode' section='account data'}"/>
                    </div>   
                    <div class='widget-couponbox-inner-left'>
                        <span id="clock"></span>
                    </div> 
                </div>
            </div>
    </form>
</div>

{inline_script}
<script type="text/javascript">
  var discountTimerWidgetCookie = localStorage.getItem('discount_timer_modal_{$name}');
      discount_timerWidget = $('#discount_timer_widget');
      // console.log(discountTimerWidgetCookie);
      // console.log('{$coupon_code}');
  if (discountTimerWidgetCookie == '{$coupon_code}'){

      // window.alert(discountTimerWidgetCookie + '= {$coupon_code}');
      
      $("#discount_timer_widget").fadeIn(1800);
      // $("#discount_timer_widget").show(); 
  }


  $('#clock').countdown('{$validity}', function(event) {
    $(this).html(event.strftime('%M:%S'));
  });
</script>
{/inline_script}