{block name='productlist-item-list-productbox-inner' append}
  {* Enhanced Ecommerce Tracking *}
  {block 'adm-enhanced-ecommerce-tracking-data-item-list'}
    {if !isset($oPlugin_admorris_pro)}
      {$oPlugin_admorris_pro = \JTL\Shop::get('oplugin_admorris_pro')}
    {/if}
    {include "{$oPlugin_admorris_pro->getPaths()->getFrontendPath()}templates/enhanced_ecommerce_tracking_productlist_data.tpl"}
  {/block}
{/block}

{if !empty($euEnergyLabels->show_list)}
  {* Nova block *}
  {block name='productlist-item-list-images' prepend}
    {include file="../../templates/marketing_eu_energy_labels/slider.tpl" }
  {/block}
{/if}
