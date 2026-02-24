{if $admUtils::isTemplateActive()}
  {block name='item-box-wrapper' append}
    {* Enhanced Ecommerce Tracking *}
    {block 'adm-enhanced-ecommerce-tracking-data-item-box'}
      {if !isset($oPlugin_admorris_pro)}
        {$oPlugin_admorris_pro = \JTL\Shop::get('oplugin_admorris_pro')}
      {/if}
      {include "{$oPlugin_admorris_pro->getPaths()->getFrontendPath()}templates/enhanced_ecommerce_tracking_productlist_data.tpl"}
    {/block}
  {/block}
{/if}

{block name='productlist-item-list-image' prepend}
  {* EU energy label *}
  {$isProductSlider = str_starts_with($idPrefix|default:'', "product-slider-")}

  {if $isProductSlider && !empty($euEnergyLabels->show_slider)}
    {include file="../../templates/marketing_eu_energy_labels/slider.tpl" }
  {else if !$isProductSlider && !empty($euEnergyLabels->show_list)}
      {include file="../../templates/marketing_eu_energy_labels/slider.tpl" }
  {/if}
{/block}