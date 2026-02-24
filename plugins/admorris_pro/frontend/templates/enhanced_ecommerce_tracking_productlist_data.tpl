{* included via the template folder in item_box & item_list *}
{if isset($adm_tracking_payload)}
  {$notFather = (!$Artikel->nIstVater || $Artikel->kVaterArtikel != 0)}

  {$canAddToCart = ($Artikel->inWarenkorbLegbar === 1
    || ($Artikel->nErscheinendesProdukt === 1 && $Einstellungen.global.global_erscheinende_kaeuflich === 'Y'))
    && (($Artikel->nIstVater === 0 && $Artikel->Variationen|@count === 0)
        || $hasOnlyListableVariations === 1)
    && !$Artikel->bHasKonfig
    && $Einstellungen.template.productlist.buy_productlist === 'Y'
    && !isset($Artikel->FunktionsAttribute[$smarty.const.FKT_ATTRIBUT_VOUCHER_FLEX])
    && $notFather
  }

  {* !$Artikel->nIstVater || $Artikel->kVaterArtikel != 0 *}

  {* 1000flies list matrix - custom child template *}
  {$listMatrix = isset($Artikel->FunktionsAttribute.listenansicht) && !empty($Artikel->Variationen) && count($Artikel->Variationen) == 1}
  {* Testing without attribute *}
  {$listMatrix = !empty($Artikel->Variationen) && count($Artikel->Variationen) == 1}

  {$isMatrixProduct = !( $Artikel->nIstVater == 0 || (isset($Artikel->FunktionsAttribute.listenansicht) && strtolower($Artikel->FunktionsAttribute.listenansicht) == "hidden")) && !empty($Artikel->Variationen) && count($Artikel->Variationen) == 1}

  {if $canAddToCart || $listMatrix}
    {block 'adm-enhanced-ecommerce-tracking-data-json'}
      {if $isMatrixProduct && isset($Artikel->oVariationKombiKinderAssoc_arr)}
        {$payload = $Artikel->oVariationKombiKinderAssoc_arr}
      {else}
        {$payload = $Artikel}
      {/if}
      {$payload_encoded = json_encode($adm_tracking_payload::createArticleData($payload))}
      <script id="adm-enhanced-ecommerce-tracking-data-{$Artikel->kArtikel}" type="application/json">
       {$payload_encoded}
      </script>
    {/block}
  {/if}
{/if}