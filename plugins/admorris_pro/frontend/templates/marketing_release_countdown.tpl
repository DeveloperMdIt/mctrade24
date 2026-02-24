<hr>

<div class="special_offer_countdown_clock_wrapper {$admorrisProDisabledClass} {$admorris_pro_marketing_release_countdown_style}">
	<p class="title">{if $marketing_release_countdown_displaytitle}{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_release_countdown_title')}{/if}</p>
	<div class="special_offer_countdown_clock"></div>
  {* Fuer Kindartikel mit Countdown, die bei der Variationsauswahl nachgeladen werden,
    muss das flipclock script erst geladen werden *}

</div>

{inline_script}
<script>
$(function() {

  amFlipClockInit.init({
    selector:'.special_offer_countdown_clock', 
    date:'{$admorris_pro_marketing_release_countdown_date}', 
    now:'{$admorris_pro_marketing_release_countdown_now}', 
    format:'{$marketing_release_countdown_format}', 
    language:'{$meta_language}', 
    showSeconds:{$marketing_release_countdown_format_seconds}, 
  });

});
</script>
{/inline_script}