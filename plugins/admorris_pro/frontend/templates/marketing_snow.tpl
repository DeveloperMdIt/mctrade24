{inline_script}
<script>
$(function() {

  var freqMult = 5;
  var w = document.documentElement.clientWidth;

  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    return;
  }

  if (w > 720) {
    freqMult = 1;
  } else if (w > 540) {
    freqMult = 3;
  }


  $("{$admorris_pro_expert_settings_snow_css_selector}").flurry({
    character: {$admorris_pro_marketing_snow_character},
    color: ["{$admorris_pro_marketing_snow_color1}", "{$admorris_pro_marketing_snow_color2}", "{$admorris_pro_marketing_snow_color3}", "{$admorris_pro_marketing_snow_color4}", "{$admorris_pro_marketing_snow_color5}"],
    height: {$admorris_pro_marketing_snow_height},
    frequency: {$admorris_pro_marketing_snow_frequency} * freqMult,
    speed: {$admorris_pro_marketing_snow_speed},
    large: {$admorris_pro_marketing_snow_large},
    small: {$admorris_pro_marketing_snow_small},
    wind: {$admorris_pro_marketing_snow_wind},
    windVariance: {$admorris_pro_marketing_snow_wind_variance},
    rotation: {$admorris_pro_marketing_snow_rotation},
    rotationVariance: {$admorris_pro_marketing_snow_rotation_variance},
    startRotation: {$admorris_pro_marketing_snow_start_rotation},
    startOpacity: {$admorris_pro_marketing_snow_start_opacity},
    endOpacity: {$admorris_pro_marketing_snow_end_opacity},
    opacityEasing: "{$admorris_pro_marketing_snow_opacity_easing}",
    blur: {$admorris_pro_marketing_snow_blur},
    overflow: "{$admorris_pro_marketing_snow_overflow}",
    zIndex: {$admorris_pro_marketing_snow_zIndex}
  });
  {if $admorris_pro_marketing_snow_duration != 0}
  setTimeout(function (){
    $("{$admorris_pro_expert_settings_snow_css_selector}").flurry('destroy');
  }, {$admorris_pro_marketing_snow_duration});
  {/if}
});

</script>
{/inline_script}
