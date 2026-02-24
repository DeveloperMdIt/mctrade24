{if isset($admorris_pro_marketing_extra_product) && isset($admorris_pro_marketing_extra_product->product)  }
    {$adm_optional_extra_product = $admorris_pro_marketing_extra_product->product}
    {block name='productdetails-action-wrapper' prepend}
      {include file="../../templates/optional_extra_product.tpl"}
    {/block}
{/if}   