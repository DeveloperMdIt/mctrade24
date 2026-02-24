{if !empty($Artikel->FunktionsAttribute.admorris_pro_eu_energy_label)}
  <div class="eu-energy-label-wrapper eu-energy-label-wrapper--detail">
    {include file="{$smarty.current_dir}/label.tpl" }
    {if !empty($Artikel->FunktionsAttribute.admorris_pro_eu_energy_label_data_sheet)}
        <a class="eu-energy-link" target="_blank"href="{$ShopURL}/media/image/storage/opc/energy_labels/documents/{$Artikel->FunktionsAttribute.admorris_pro_eu_energy_label_data_sheet}">
          {$admUtils::trans('marketing_energy_data_sheet')}
        </a>
    {/if}
  </div>
{/if}