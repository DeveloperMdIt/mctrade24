<div id="admorris-cookie-wrapper" class="admorris-cookie-notice {if $admorris_pro_marketing_cookie_notice_style == '2'}admorris-cookie-notice-light {/if} admorris-cookie-notice-{$admorris_pro_marketing_cookie_notice_position} animated fadeIn hide">
    <p>
      {$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_marketing_cookie_notice_text')}&nbsp;&nbsp;<a href="{$admorris_pro_marketing_cookie_notice_link}">{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_marketing_cookie_notice_privacy_notice_link')}</a>
      <button type="button" id="cookie-OK" class="admorris-cookie-notice__button">{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_marketing_cookie_notice_button')}</button>
    </p>
</div> 


{inline_script}
<script>{strip}
(function() {

  var cookie_wrapper = document.getElementById('admorris-cookie-wrapper');
  var cookie_OK = document.getElementById('cookie-OK');

  

  function set_cookies() {
    localStorage.setItem('cookies_set', 1);
    cookie_wrapper.parentElement.removeChild(cookie_wrapper); {* IE fix *}
  }

  if (!localStorage.getItem('cookies_set')) {
    cookie_wrapper.classList.remove('hide');
    cookie_OK.addEventListener('click', set_cookies);
  }

})();
{/strip}
</script>
{/inline_script}

