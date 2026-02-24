{strip}
{if $am_discount->old_price_style === 'strike_through_color'}
  {$strikeTroughColor = $am_discount->background_color}
{else}
  {$strikeTroughColor = 'currentColor'}
  
{/if}

<style>
{if $am_discount->list_insert_type === 'overlay'}
  .am-discount--overlay {
    {if strpos($am_discount->label_position, 'top') !== false}
      top: {$am_discount->label_vertical_position}px;
    {else}
      bottom: {$am_discount->label_vertical_position}px;
    {/if}
    {if strpos($am_discount->label_position, 'right') !== false}
      right: 0;
      text-align: right;
    {else}
      left: 0;
      text-align: left;
    {/if}
  }
{/if}

.am-discount {
  font-size: {$am_discount->details_fontsize};
}
.am-discount--list {
  font-size: {$am_discount->list_fontsize};
}

.am-discount__label {
  color: {$am_discount->text_color};
  background-color: {$am_discount->background_color};
}

  .am-discount__old-price:before {
  {if $am_discount->old_price_style !== 'false'}
    border-top-width: 2px;    
    border-top-color: {$strikeTroughColor}; 
  {else}
    border: 0;
  {/if}
  }
</style>
{inline_script}
<script>
  $(function() {
    $('.am-discount').removeClass('hidden');
  });
</script>
{/inline_script}
{/strip}