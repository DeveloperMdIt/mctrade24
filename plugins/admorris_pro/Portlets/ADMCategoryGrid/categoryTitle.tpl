{$categoryTextClasses = ' text-'|cat:{$instance->getProperty('textPosition')}}
{if $instance->getProperty('categoryCardType') == 'textOutside'}
  {if $instance->getProperty('noGutters')}
      {$categoryTextClasses = $categoryTextClasses|cat:' mt-2 mb-3'}
  {else}
      {$categoryTextClasses = $categoryTextClasses|cat:' mt-3'}
  {/if}
{/if}
<div class="adm-category-card-text-wrapper w-100{$categoryTextClasses}">
    {$headerArgs = 'class="m-0 '|cat:{$instance->getProperty(headerSize)}|cat:'" style="color: '|cat:{$instance->getProperty(customHeadlineColor)}|cat:';font-weight: '|cat:{$instance->getProperty(customHeadlineWeight)}|cat:'; '|cat:'"'}
    {$headerTag = $instance->getProperty('headerType')}

    {if empty($headerTag)}
        <p {$headerArgs}>{$categories[$catIdx + $i]->getName()}</p>
    {else}
        <{$headerTag} {$headerArgs}>{$categories[$catIdx + $i]->getName()}</{$headerTag}>
    {/if}
</div>