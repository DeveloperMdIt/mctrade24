<hr>
<div class="special_offer_countdown_clock_wrapper {$admorrisProDisabledClass} {$admorris_pro_marketing_special_offer_countdown_style}">
	<div class="title">{if $marketing_special_offer_countdown_displaytitle == 'Y'}{$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_special_offer_countdown_title')}{/if}</div>
	<div class="special_offer_countdown_clock"></div>

{inline_script}
<script type="text/javascript">
$(function() {
  amFlipClockInit.init({
    selector:'.special_offer_countdown_clock', 
    date:'{$admorris_pro_marketing_special_offer_sonderpreisBis}', 
    now:'{$admorris_pro_marketing_special_offer_now}', 
    format:'{$marketing_special_offer_countdown_format}', 
    language:'{$meta_language}', 
    showSeconds:{$marketing_special_offer_countdown_format_seconds},
    addDay: true,
  });

});
</script>
{/inline_script}

</div>