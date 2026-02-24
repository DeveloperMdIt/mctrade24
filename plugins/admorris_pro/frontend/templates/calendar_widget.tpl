{* Admorris Advent Kalender *}
<div class="am-advent-calendar-wrapper hide">
    <style>{strip}
        {if !empty($z_index)}
            .am-advent-calendar-wrapper {
                z-index: {$z_index};
            }
            .am-advent-calendar-modal {
                z-index: {max(1040, $z_index + 50)};
            }
            .am-snow {
                z-index: {max(1020, $z_index + 20)};
            }

            .am-advent-calendar-spinner-wrapper {
              z-index: {$z_index + 1};
            }
        {/if}

        .am-advent-calendar {
            background-color: {$brand_color};
        }

        .am-advent-calendar-spinner-wrapper  {
          background-color: {$brand_color};
        }

        .am-advent-calendar-modal__dialog {
            background-color: {$brand_color};
        }

        .am-advent-calendar-modal__close.close {
            color: {$brand_color};
        }

        {if !empty($toggle_offset)}
            .am-advent-calendar__toggle {
                top: {$toggle_offset}%
            }
        {/if}
    {/strip}</style>
    <div class="am-advent-calendar transition-preload">
        <button type="button" class="am-advent-calendar__toggle am-calendar-btn" aria-label="Adventkalender öffnen" aria-controls="am-advent-calendar-windows" aria-expanded="false">
            <img src="{$imgUrl}/Vogel_large_1x.png" alt="Vogel"
                  srcset="{$imgUrl}/Vogel_large_1x.png 152w,
                          {$imgUrl}/Vogel_large_2x.png 304w,
                          {$imgUrl}/Vogel_large_3x.png 456w,
                          {$imgUrl}/Vogel_medium_1x.png 101w,
                          {$imgUrl}/Vogel_medium_2x.png 202w,
                          {$imgUrl}/Vogel_small_1x.png 76w,
                          {$imgUrl}/Vogel_small_3x.png 228w"
                  sizes="(min-width: 1200px) 152px,
                        (min-width: 468px) 101px,
                        76px">
        </button>
        <div id="am-advent-calendar-windows" class="am-advent-calendar__grid clearfix" aria-hidden="true" tabindex="-1">
          {* <div class="am-advent-calendar-spinner-wrapper">
            <div class="am-advent-calendar-spinner"></div>
          </div> *}
            {for $i=1 to 24}
              {$imageIndex = str_pad($i, 2, '0', STR_PAD_LEFT)}
              <button type="button" class="am-advent-calendar__window am-calendar-btn" data-day="{$i}">
                  <img class="am-advent-calendar__window-img" 
                    hidden
                    width="76" height="76" loading="lazy"
                    src="{$imgUrl}/Fenster/Fenster_{$imageIndex}.png"
                    srcset="{$imgUrl}/Fenster/Fenster_{$imageIndex}_large_1x.png 1x,
                            {$imgUrl}/Fenster/Fenster_{$imageIndex}_large_2x.png 2x,
                            {$imgUrl}/Fenster/Fenster_{$imageIndex}_large_3x.png 3x">
                  <img class="am-advent-calendar__window-open-img"
                    hidden
                    width="76" height="76" loading="lazy"
                    src="{$imgUrl}/Fenster_offen/Fenster_offen_large_1x.png"
                    srcset="{$imgUrl}/Fenster_offen/Fenster_offen_large_1x.png 1x,
                            {$imgUrl}/Fenster_offen/Fenster_offen_large_2x.png 2x,
                            {$imgUrl}/Fenster_offen/Fenster_offen_large_3x.png 3x">
                  <span class="sr-only">Fenster {$i}</span>
              </button>
            {/for}
            

        </div>
    </div>

</div>
{if !empty($calendarContent)}
  <div class="am-advent-calendar-content">
    
    {foreach $calendarContent as $content}
      <div class="am-advent-calendar-modal am-advent-calendar-modal--{$content->headingImage.style}-header modal fade" data-show="false" id="calendar_modal_{$content->day}" data-day="{$content->day}" tabindex="-1" role="dialog">
          <div class="am-advent-calendar-modal__dialog modal-dialog" role="document">
              <div class="am-advent-calendar-modal__content">
                  <div class="am-advent-calendar-modal__header ">
                      <button type="button" class="am-advent-calendar-modal__close close"  aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      <img width="770" height="210" class="am-advent-calendar-modal__header-img" 
                        src="{$content->headingImage.url}1x.png" loading="lazy"
                        srcset="{$content->headingImage.url}1x.png 1x,
                                {$content->headingImage.url}2x.png 2x,
                                {$content->headingImage.url}3x.png 3x"
                        alt="Modal Heading Image">
                  </div>
                  <div class="am-advent-calendar-modal__body">
                      {$content->content}
                  </div>
              </div>
          </div>
        </div>
    {/foreach}
    <canvas class="am-snow"></canvas>
  </div>
{/if}