{if !empty($admorris_pro_marketing_trustmark_google_id)}
<div id="google-customer-reviews-badge"></div>
<script {if !$adm_trustmark_google_load_scripts}data-{/if}src="https://apis.google.com/js/platform.js?onload=renderBadge" {$admorris_pro_marketing_trustmark_google_cookie_header_intern} async defer></script>
<script{$admorris_pro_marketing_trustmark_google_cookie_header_intern}>{literal}
  window.renderBadge = function() {
    var ratingBadgeContainer = document.createElement("div");
    document.body.appendChild(ratingBadgeContainer);
    window.gapi.load('ratingbadge', function() {
      window.gapi.ratingbadge.render(ratingBadgeContainer, {"merchant_id": {/literal}{$admorris_pro_marketing_trustmark_google_id}, "position": "{$admorris_pro_marketing_trustmark_google_position}"{literal}});
    });
  }
  
    
    window.___gcfg = {
      lang: '{/literal}{$meta_language}{literal}'
    };
  </script>
  {/literal}
{/if}