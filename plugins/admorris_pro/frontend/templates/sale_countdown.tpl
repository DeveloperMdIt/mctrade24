<div class="sale-countdown sale-countdown--{$sale->name}">
  <div class="sale-countdown__col center-vertically">
    <div>
      <span class="sale-countdown__text v-align-middle">{$discount_percent}</span> <strong class="sale-countdown__percent v-align-middle"> {$sale->discount}</strong>
    </div>
  </div>
  <div class="sale-countdown__col center-vertically">
    <div>
    {if $sale->bannerLink}
      <a class="sale-countdown__banner-text" href="{$sale->bannerLink}">{$sale->bannerText}</a>
    {else}
      <span class="sale-countdown__banner-text">{$sale->bannerText}</span> 
    {/if}
    </div>
  </div>
  <div class="sale-countdown__col center-vertically">
    <div>
      <span class="sale-countdown__text v-align-middle">
      {if $sale->isSaleOn()}
        {$end_countdown}
      {else}
        {$start_countdown}
      {/if}
      </span> <strong class="sale-countdown__timer v-align-middle" id="sale-countdown-clock"></strong>
    </div>
  </div>

</div>



{if $sale->isSaleOn()}
  {$date = $sale->endDateIso}
{else}
  {$date = $sale->startDateIso}
{/if}


<script type="module">
$(function() {

  if ($('.header-wrapper').hasClass('header-wrapper--overlay'))
  {
    // Either place sale countdown below or above menu
    // only needed if slider is set to fullscreen mode
    const sliderWrapper = document.getElementById('keen-slider__wrapper');
    if(sliderWrapper && sliderWrapper.classList.contains('fullscreen-container')) {
      const saleCountDown = document.querySelector('.sale-countdown');
      const header = document.querySelector('.header');

      // set additional styling
      saleCountDown.style.position = 'absolute';
      saleCountDown.style.zIndex = '15';
      saleCountDown.style.width = '100%';

      handleSaleCountDownPositionFullScreenSlider(saleCountDown, header);

      // add resize observer
      const resizeObserver = new ResizeObserver(entries => {
        handleSaleCountDownPositionFullScreenSlider(saleCountDown, header);
      });

      resizeObserver.observe(saleCountDown);

      // handle margin of saleCountDown / header
      function handleSaleCountDownPositionFullScreenSlider(saleCountDown, header) {
        const heightSaleCountDown = saleCountDown.offsetHeight.toFixed(2);
        const heightHeader = header.offsetHeight.toFixed(2);

        // check if salescountdown is before or after header
        if (saleCountDown.previousSibling === header) saleCountDown.style.marginTop = heightHeader + 'px';
        else header.style.marginTop = heightSaleCountDown + 'px';
      }
    }
  }

  var countdownEl = $('#sale-countdown-clock');

  function countdownUpdate(event) {
    var format = '%H:%M:%S';
      if(event.offset.totalDays > 0) {
        format = '%-D {$countdown_days} ' + format;
      }
      $(this).html(event.strftime(format));
  }
  countdownEl.countdown(new Date('{$date}'))
    .on('update.countdown', countdownUpdate)
  {if !$sale->isOn}
    .on('finish.countdown', function(event) {
      $(this).prev().html('{$end_countdown}');
      countdownEl.countdown('{$sale->endDate}')
    })
  {/if}
  .countdown('start');

});
</script>