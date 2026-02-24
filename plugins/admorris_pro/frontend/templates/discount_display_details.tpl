{strip}
<div class="am-discount am-discount--details{if !isset($bAjaxRequest) || !$bAjaxRequest} hidden{/if}">
  {if strpos($am_discount->show_old_price, 'show_price') !== false}
    <span class="am-discount__old-price-info">
      {if $am_discount->discountFromUvp}
        {if strpos($am_discount->show_old_price, 'with_text') !== false}
          <abbr class="am-discount__old-price-text" title="{lang key="suggestedPriceExpl" section="productDetails"}">{$am_discount->lang->uvp}</abbr>{' '}
        {/if}
        <span class="am-discount__old-price">{$Artikel->cUVPLocalized}</span>

      {else}
        {if strpos($am_discount->show_old_price, 'with_text') !== false}
          <span class="am-discount__old-price-text">{$am_discount->lang->old_price}</span>{' '}
        {/if}
        <span class="am-discount__old-price">
        {if $org_article->Preise->Sonderpreis_aktiv}
          {$org_article->Preise->alterVKLocalized[$NettoPreise]}
        {else if $org_article->Preise->alterVKLocalized[$NettoPreise]}
          {$org_article->Preise->alterVKLocalized[$NettoPreise]}
        {else}
          {$org_article->Preise->cVKLocalized[$NettoPreise]}
        {/if}
        </span>
        
      {/if}
      
      
    </span>
  {/if}
  {include './discount_display_label.tpl'}
</div>


{/strip}