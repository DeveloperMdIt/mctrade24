{strip}
<div class="recent-sales {$admorris_pro_marketing_recent_sales_animation} color-brand-primary text-primary">
  {$admIcon->renderIcon('recentSales', "icon-content icon-content--default icon-content--center")} <span class="icon-text--center"><strong>{$admorris_pro_marketing_recent_sales_total} {if $Artikel->cEinheit}{$Artikel->cEinheit} {/if}{$admorris_pro_marketing_recent_sales_text} </strong></span>
</div>
<style>
  .recent-sales {
      font-size: 14px;
  }

  .recent-sales-fade svg {
      animation: fading 1s infinite;
  }
  {literal}
    @keyframes fading{0%{opacity:0}50%{opacity:1}100%{opacity:0}}
    @-webkit-keyframes fading {0%{opacity:0}50%{opacity:1}100%{opacity:0}} 
  {/literal}

</style>

{/strip}