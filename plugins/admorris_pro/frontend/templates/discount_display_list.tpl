
{if $am_discount->list_insert_type === 'overlay'}
  {$classModifier = 'am-discount--overlay'}
  {$sep = '<br>'}
{else}
  {$classModifier = 'am-discount--caption'}
{/if}

{$classModifier = $classModifier|cat:' am-discount--list'}

<div class="am-discount {$classModifier}{if !isset($bAjaxRequest) || !$bAjaxRequest} hidden{/if}">
  
  {include './discount_display_label.tpl'}
  
</div>
