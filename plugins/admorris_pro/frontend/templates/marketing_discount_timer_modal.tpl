<div class="admorris-discount-timer-container {$style}">
  <div class='modal fade' id='discount_timer_modal' role="dialog" aria-modal="true" aria-labelledby="discount_timer_title" aria-describedby="discount_timer_description">
    <div class='modal-dialog'>
      <div class='modal-content' id='discount_timer_modal_content'>
        <div class='modal-header'>
          <!-- <button type="button" class="close" data-dismiss="modal" aria-hidden="true">�</button> -->
        </div>
        <div class='modal-body'>

          <h2 class='modal-title' id="discount_timer_title">
            <strong>{$headline}</strong>
          </h2>
          <br>

          <div class='modal-subline' id="discount_timer_description">
            <p>{$subline}</p>
          </div>

          <div class="discount_timer_countdown_clock"></div>

          <div>
            <form method="post" action="{get_static_route id='warenkorb.php'}">
              {$jtl_token}
              <div class="form-group{if !empty($invalidCouponCode) || !empty($cKuponfehler)} has-error{/if}">

                <div class='modal-couponbox'>
                  <div class='modal-couponbox-inner'>
                    <input class="discount_timer_countdown_input"
                      aria-label="{lang key='couponCode' section='account data'}" type="text" name="Kuponcode"
                      maxlength="10" value="{$coupon_code}" readonly />
                    {* <div class="copy-btn">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path
                          d="M502.6 70.63l-61.25-61.25C435.4 3.371 427.2 0 418.7 0H255.1c-35.35 0-64 28.66-64 64l.0195 256C192 355.4 220.7 384 256 384h192c35.2 0 64-28.8 64-64V93.25C512 84.77 508.6 76.63 502.6 70.63zM464 320c0 8.836-7.164 16-16 16H255.1c-8.838 0-16-7.164-16-16L239.1 64.13c0-8.836 7.164-16 16-16h128L384 96c0 17.67 14.33 32 32 32h47.1V320zM272 448c0 8.836-7.164 16-16 16H63.1c-8.838 0-16-7.164-16-16L47.98 192.1c0-8.836 7.164-16 16-16H160V128H63.99c-35.35 0-64 28.65-64 64l.0098 256C.002 483.3 28.66 512 64 512h192c35.2 0 64-28.8 64-64v-32h-47.1L272 448z" />
                      </svg>
                    </div> *}
                  </div>

                  <div class='modal-couponbox-inner-left'>
                    {$discount}{$discounttyp}{if !empty($additional_info)} <sup>*</sup>{/if}
                  </div>
                </div>

                {if !empty($additional_info)}
                  <p><sup>*</sup> {$additional_info}</p>
                {/if}

                <input class="btn btn-primary btn-block modal-call-to-action" type="submit"
                  value="{$call_to_action_button}" />
                
                
                {if $minimal_order_value > 0}
                  <div class="modal-couponbox-min-value mt-3">
                    <small>{lang|sprintf:$minimal_order_value:JTL\Session\Frontend::getCurrency()->getName() key="minValueInfo" section="productDetails"}</small>
                  </div>
                {/if}
              </div>
            </form>
          </div>
          <!-- <a href="{get_static_route id='bestellvorgang.php'}" class="btn btn-primary btn-block modal-call-to-action">{$call_to_action_button}</a> -->
          <br>
          <button type="button" class="btn btn-link btn-block modal-cancel-button"
            data-dismiss="modal">{$cancel_button}</button>
          <br>
        </div>
        <div class="modal-bg">
          <svg aria-hidden="true" viewBox="0 0 451 902" fill="none" xmlns="http://www.w3.org/2000/svg"
            style="position: absolute; top: 0; left: 0;">
            <path opacity="0.125" d="M0 82C203.8 82 369 247.2 369 451C369 654.8 203.8 820 0 820"
              stroke="url(#paint2_linear)" stroke-width="164" stroke-miterlimit="10"></path>
            <defs>
              <linearGradient id="paint2_linear" x1="323.205" y1="785.242" x2="-97.6164" y2="56.3589"
                gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="white" stop-opacity="0"></stop>
                <stop offset="1" stop-color="#377dff"></stop>
              </linearGradient>
            </defs>
          </svg>
        </div>

      </div>
    </div>
  </div>
</div>


<script type="module">
  {strip}
    $(function() {

      var clock;

      var discountTimerModalCookie = localStorage.getItem('discount_timer_modal_{$name}'),
      discount_timerModal = $('#discount_timer_modal');

      if (!discountTimerModalCookie) {

        amFlipClockInit.init({
          selector: '.discount_timer_countdown_clock',
          date: '{$validityIso}',
          now: '{$now}',
          format: 'MinuteCounter',
          language: '{$meta_language}',
          showSeconds: true,
        });


        localStorage.setItem('discount_timer_modal_{$name}', '{$coupon_code}');

        $("div.admorris-popup-container").remove();

        setTimeout(function() {
          $.fn.modal ? showModal() : window.addEventListener('load', showModal);
        }, 10);
      } else {
        $("#discount_timer_modal_content").remove();
      }

      function showModal() {
        discount_timerModal.modal({
          show: true,
          backdrop: true,
          keyboard: false
        });
      }
    });

  {/strip}
</script>