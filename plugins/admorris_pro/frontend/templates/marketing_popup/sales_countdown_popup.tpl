{extends file="file:[admPro]marketing_popup/popup_base.tpl"}

{$popupHasImage = true}
{$showImageLink = false}

{block 'admorris-pro-popup-classname'}sale-countdown-popup{/block}

{block name='admorris-pro-popup-content'}
  <div class="admorris-sales-countdown">
      <div class="sales-title">{$contentTitle}</div>
      <div class="sales-text" style="color: {$styleButtonColor};">{$contentText1}</div>
      <div class="special_offer_countdown_clock_wrapper {$countdownStyle}">
        <div class="sales-pre-countdown-text">
          {if $saleStartTimestamp > $admorris_pro_now}
            {$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_marketing_sale_countdown_start')}
          {else}
            {$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_marketing_sale_countdown_end')}
          {/if}
        </div>
        <div class="sales_countdown_clock"></div>
      </div>
      {if $saleStartTimestamp < $admorris_pro_now}
        <button onclick="amPopupTrigger.triggerSetCookie();location.href='{$contentButtonLink}'" type="button"
          class="btn submit" style="color: {$styleButtonTextColor}; background-color: {$styleButtonColor};">
          <span>{$contentButtonText}</span>
        </button>
      {/if}
      <div class="sales-legaltext">{$contentText2}</div>

  </div>
{/block}

{block name='admorris-pro-popup-additional-code'}
  {if $saleStartTimestamp > $admorris_pro_now}
    {$countdownDate = $saleStart}
  {else}
    {$countdownDate = $saleEnd}
  {/if}
  
  
  <script type="module">
    $(function() {
      amFlipClockInit.init({
        selector: '.sales_countdown_clock',
        date:'{$countdownDate}', 
        now:'{$currentDate}', 
        format:'{$countdownFormat}', 
        language:'{$meta_language}', 
        showSeconds: true,
      });
  
    });
  </script>
{/block}
