{if !empty($admorris_pro_marketing_trustmark_google_id)}
<script {if !$adm_trustmark_google_load_scripts}data-{/if}src="https://apis.google.com/js/platform.js?onload=renderOptIn" {$admorris_pro_marketing_trustmark_google_cookie_header_intern} async defer></script>
<script{$admorris_pro_marketing_trustmark_google_cookie_header_intern}>
{literal}
  window.renderOptIn = function() {
    window.gapi.load('surveyoptin', function() {
      window.gapi.surveyoptin.render(
        {
          // REQUIRED FIELDS
          "merchant_id": {/literal}{$admorris_pro_marketing_trustmark_google_id},
          "order_id": "{$Bestellung->cBestellNr}",
          "email": "{$Bestellung->oKunde->cMail}",
          "delivery_country": "{$Bestellung->oKunde->cLand}",
          "estimated_delivery_date": "{$admorris_pro_marketing_trustmark_google_delay}",
          "opt_in_style": "{$admorris_pro_marketing_trustmark_google_collector_position}"
          {literal}
        });
    });
  }
</script>
{/literal}
{/if}