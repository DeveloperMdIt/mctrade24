{if !empty($dropdown)}
  {$iconWidth = 18}
  {$iconHeight = 19}
{else}
  {$iconWidth = 23}
  {$iconHeight = 24}
{/if}


{$shippingBarArr = $admorris_pro_marketing_shipping_cost_progress_bar_array}
<div id="shipping-cost-progress-bar" class="shipping-cost-progress-bar{if !empty($dropdown)} shipping-cost-progress-bar--mini{/if}">
    <div class="shipping-cost-progress-bar__heading">
        {if ($shippingBarArr['bisVersandkostenfrei'] > 0)}
            <span class="shipping-cost-progress-bar__title" style=>
                {$admUtils::trans('marketing_shipping_cost_progress_bar_title_under')}
                <strong
                    style="color: {$shippingBarArr['colorUnder']};">{$shippingBarArr['bisVersandkostenfreiLocalized']}</strong>
            </span>
        {else}
            <svg aria-hidden="true" class="shipping-cost-progress-bar-title__icon icon-content--center" width="{$iconWidth}" height="{$iconHeight}" viewBox="0 0 28 29" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M14 0.9375C16.4062 0.9375 18.7031 1.59375 20.7812 2.79688C22.8594 4 24.5 5.64062 25.7031 7.71875C26.9062 9.79688 27.5625 12.0938 27.5625 14.5C27.5625 16.9609 26.9062 19.2031 25.7031 21.2812C24.5 23.3594 22.8594 25.0547 20.7812 26.2578C18.7031 27.4609 16.4062 28.0625 14 28.0625C11.5391 28.0625 9.29688 27.4609 7.21875 26.2578C5.14062 25.0547 3.44531 23.3594 2.24219 21.2812C1.03906 19.2031 0.4375 16.9609 0.4375 14.5C0.4375 12.0938 1.03906 9.79688 2.24219 7.71875C3.44531 5.64062 5.14062 4 7.21875 2.79688C9.29688 1.59375 11.5391 0.9375 14 0.9375ZM14 3.5625C12.0312 3.5625 10.1719 4.05469 8.53125 5.03906C6.83594 6.02344 5.52344 7.39062 4.53906 9.03125C3.55469 10.7266 3.0625 12.5312 3.0625 14.5C3.0625 16.4688 3.55469 18.3281 4.53906 19.9688C5.52344 21.6641 6.83594 22.9766 8.53125 23.9609C10.1719 24.9453 12.0312 25.4375 14 25.4375C15.9688 25.4375 17.7734 24.9453 19.4688 23.9609C21.1094 22.9766 22.4766 21.6641 23.4609 19.9688C24.4453 18.3281 24.9375 16.4688 24.9375 14.5C24.9375 12.5312 24.4453 10.7266 23.4609 9.03125C22.4766 7.39062 21.1094 6.02344 19.4688 5.03906C17.7734 4.05469 15.9688 3.5625 14 3.5625ZM21.6562 10.6719C21.7656 10.8359 21.875 11 21.875 11.1641C21.875 11.3828 21.7656 11.4922 21.6562 11.6016L12.25 20.9531C12.0859 21.1172 11.9219 21.1719 11.7578 21.1719C11.5391 21.1719 11.4297 21.1172 11.3203 20.9531L6.34375 15.9766C6.17969 15.8672 6.125 15.7031 6.125 15.4844C6.125 15.3203 6.17969 15.1562 6.34375 15.0469L7.60156 13.7891C7.71094 13.6797 7.82031 13.625 8.03906 13.625C8.20312 13.625 8.36719 13.6797 8.53125 13.7891L11.7578 17.125L19.5234 9.41406C19.6328 9.30469 19.7422 9.25 19.9609 9.25C20.125 9.25 20.2891 9.35938 20.4531 9.46875L21.6562 10.6719Z"
                        fill="{$shippingBarArr['colorOver']}" />
                </svg>
            <span class="shipping-cost-progress-bar__title icon-text--center" style="color: {$shippingBarArr['colorOver']};">
                {$admUtils::trans('marketing_shipping_cost_progress_bar_title_over')}
            </span>
        {/if}
    </div>
    <div class="shipping-cost-progress-bar__wrapper">
        {if $shippingBarArr['showDescriptors']}
            <span class="shipping-cost-progress-bar__descriptor">{$shippingBarArr['leftDescriptor']}</span>
        {/if}
        <div class="shipping-cost-progress-bar__outer">
            <div class="shipping-cost-progress-bar__inner"
                style="width: {$shippingBarArr['progressBarValue']}%; background-color:
                {if $shippingBarArr['progressBarValue'] < 100}{$shippingBarArr['colorUnder']}{else}{$shippingBarArr['colorOver']}{/if} ">
            </div>
        </div>
        {if $shippingBarArr['showDescriptors']}
            <span class="shipping-cost-progress-bar__descriptor">{$shippingBarArr['rightDescriptor']}</span>
        {/if}
    </div>
    <div class="shipping-cost-progress-bar__footer">
        <span class="shipping-cost-progress-bar__text ">
            {if ($shippingBarArr['bisVersandkostenfrei'] > 0)}
                {$admUtils::trans('marketing_shipping_cost_progress_bar_text_under')}
                {$shippingBarArr['versandLand']}.
            {else}
                {$admUtils::trans('marketing_shipping_cost_progress_bar_text_over')}
                {$shippingBarArr['versandLand']}.
            {/if}
        </span>
    </div>

</div>

