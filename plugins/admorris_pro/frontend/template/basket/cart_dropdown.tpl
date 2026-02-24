{if isset($admorris_pro_marketing_shipping_cost_progress_bar_array['miniCart']) && $admorris_pro_marketing_shipping_cost_progress_bar_array['miniCart'] == true}
  {if !isset($oPlugin_admorris_pro)}
    {$oPlugin_admorris_pro = \JTL\Shop::get('oplugin_admorris_pro')}
  {/if}
  {block name='basket-cart-dropdown-shipping-free-hint'}
    <link 
      type="text/css"
      href="{$oPlugin_admorris_pro->getPaths()->getFrontendURL()}css/shipping-cost-progress-bar.css?v={$oPlugin_admorris_pro->getMeta()->getVersion()}"
      rel="stylesheet"
    >
    {include file="../../templates/marketing_shipping_cost_progress_bar/marketing_shipping_cost_progress_bar_cart.tpl" dropdown=true}
  {/block}
{/if}