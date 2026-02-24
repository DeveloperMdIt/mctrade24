{if $Artikel->FunktionsAttribute.extra_product1}
<div class="extra-product" style="margin-top:5px; white-space:normal">
    <b>
    {$oPlugin_admorris_pro->oPluginSprachvariableAssoc_arr.admorris_pro_marketing_extra_product_plus} {$Artikel->FunktionsAttribute.extra_product1|round:2 * $smarty.session.Waehrung->fFaktor} {$smarty.session.Waehrung->cNameHTML} {$oPlugin_admorris_pro->oPluginSprachvariableAssoc_arr.admorris_pro_marketing_extra_product1_name}
    </b>
  <span
  class="fa fa-info-circle"
  data-toggle="tooltip" title="{$oPlugin_admorris_pro->oPluginSprachvariableAssoc_arr.admorris_pro_marketing_extra_product1_infotext}"
  style="vertical-align: top;
  font-size: 12px;
  margin-bottom: 6px;"
></span>
</div>
{/if}
{if $Artikel->FunktionsAttribute.extra_product2}
<div class="extra-product" style="margin-top:5px; white-space:normal">
    <b>
    {$oPlugin_admorris_pro->oPluginSprachvariableAssoc_arr.admorris_pro_marketing_extra_product_plus} {$Artikel->FunktionsAttribute.extra_product2|round:2 * $smarty.session.Waehrung->fFaktor} {$smarty.session.Waehrung->cNameHTML} {$oPlugin_admorris_pro->oPluginSprachvariableAssoc_arr.admorris_pro_marketing_extra_product2_name}
    </b>
  <span
  class="fa fa-info-circle"
  data-toggle="tooltip" title="{$oPlugin_admorris_pro->oPluginSprachvariableAssoc_arr.admorris_pro_marketing_extra_product2_infotext}"
  style="vertical-align: top;
  font-size: 12px;
  margin-bottom: 6px;"
></span>
</div>
{/if}