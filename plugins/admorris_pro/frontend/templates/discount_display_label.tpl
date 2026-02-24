{if !isset($sep)}
  {$sep = ' '}
{/if}

{strip}
<div class="am-discount__label">
  {if $am_discount->textPos === 'prefix_text'}
    <span class="am-discount__label-text">{$am_discount->lang->prefixed_text}</span>{$sep}
  {/if}
  <span>
  {if $am_discount->textPos === 'no_text'}
    -
  {/if}
  {$am_discount->discount}
  </span>
  {if $am_discount->textPos === 'postfix_text'}
    {$sep}<span class="am-discount__label-text">{$am_discount->lang->postfixed_text}</span> 
  {/if}
</div>
{/strip}