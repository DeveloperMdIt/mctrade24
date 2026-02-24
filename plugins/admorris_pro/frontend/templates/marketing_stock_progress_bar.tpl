<div id="stock-progress-bar">

    <div class="stock-progress-bar-title">
    {$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_marketing_stock_progress_bar_titel_pre')} <span class="stock-progress-bar-stock color-brand-primary text-primary">{$Artikel->fLagerbestand}{if $Artikel->cEinheit} {$Artikel->cEinheit}{/if}</span> {$oPlugin_admorris_pro->getLocalization()->getTranslation('admorris_pro_marketing_stock_progress_bar_titel_sub')}
    </div>
     <div class="stock-progress-bar-outer">
        <div class="stock-progress-bar-inner bg-color-brand-primary bg-primary" style="width: {$admorris_pro_marketing_stock_progress_bar_value}%">

        </div>

    </div>

</div>

{if $isAjax}
  <script>
  if (!loadjs.isDefined('stock-progess-bar')) {
    loadjs('css!{$oPlugin_admorris_pro->getPaths()->getFrontendUrl()}css/stock-progress-bar.css');
  }
  </script>
{/if}