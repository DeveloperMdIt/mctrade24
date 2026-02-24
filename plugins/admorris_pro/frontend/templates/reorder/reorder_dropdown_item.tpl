<li class="reorder-dropdown__item">
  {if !($am_reorderDropdownImageSetting === 'false')}
    {$imageWrapperModifierClass = ($am_reorderDropdownImageSetting === 'small')?' reorder-dropdown__image-wrapper--small':''}
    <div class="reorder-dropdown__image-wrapper{$imageWrapperModifierClass}">
      {if $reorderArticle->Bilder[0]->cPfadNormal !== BILD_KEIN_ARTIKELBILD_VORHANDEN}
        {if $am_reorderDropdownImageSetting === 'small'}
          <img src="{$reorderArticle->Bilder[0]->cPfadKlein}" alt="" class="reorder-dropdown__image" />
        {else}
          <img src="{$reorderArticle->Bilder[0]->cPfadMini}" alt="" class="reorder-dropdown__image" />
        {/if}
        {* {if $cartDropdownImages === 'large'}
          <img src="{$oPosition->Artikel->Bilder[0]->cPfadKlein}" alt="" class="dropdown-cart-items__image" />
        {else} *}
                      
        {* {/if} *}
      
      {/if}
    </div>
  {/if}
  <div class="reorder-dropdown__info">
    <a  class="reorder-dropdown__article-link" href="{$reorderArticle->cURL}"><strong>{$reorderArticle->cName}</strong></a><br>
    <div class="reorder-dropdown__price">
        {* {$reorderArticle->Preise->cVKLocalized[0]} *}
        {include 'productdetails/price.tpl' Artikel=$reorderArticle tplscope='reorder'}
    </div>
  </div>
  <form id="{$i}-reorder_buy_form_{$reorderArticle->kArtikel}" action="{$ShopURL}/" method="post" class="form form-basket" data-toggle="basket-add">
    {$jtl_token}
    {* <input type="submit" name="inWarenkorb" value="1" class="hidden"> *}
    {if $reorderArticle->kArtikelVariKombi > 0}
        <input type="hidden" name="aK" value="{$reorderArticle->kArtikelVariKombi}" />
    {/if}
            
    {if isset($reorderArticle->kVariKindArtikel)}
        <input type="hidden" name="VariKindArtikel" value="{$reorderArticle->kVariKindArtikel}" />
        {if !empty($reorderArticle->variCombiProperty_arr)}
          {foreach $reorderArticle->variCombiProperty_arr as $variCombiProperty}
            <input type="hidden" name="eigenschaftwert[{$variCombiProperty->kEigenschaft}]" value="{$variCombiProperty->kEigenschaftWert}">
          {/foreach}
        {/if}
    {/if}
    {if $reorderArticle->kVaterArtikel > 0 && !empty($reorderArticle->cVariationKombi)}
      {$variKombiArray = $am_Reorder::varikombiStringToArray($reorderArticle->cVariationKombi)}
      {foreach $variKombiArray as $value}
          <input type="hidden" name="eigenschaftwert[{$value[0]}]" value="{$value[1]}">
      {/foreach}
  {/if}
    <input type="hidden" name="anzahl" class="quantity" value="{if $reorderArticle->fAbnahmeintervall > 0}{if $reorderArticle->fMindestbestellmenge > $reorderArticle->fAbnahmeintervall}{$reorderArticle->fMindestbestellmenge}{else}{$reorderArticle->fAbnahmeintervall}{/if}{else}1{/if}" />
    {if $reorderArticle->kArtikelVariKombi > 0}
        <input type="hidden" name="aK" value="{$reorderArticle->kArtikelVariKombi}" />
    {/if}
    {if isset($reorderArticle->kVariKindArtikel)}
        <input type="hidden" name="VariKindArtikel" value="{$reorderArticle->kVariKindArtikel}" />
        <input type="hidden" name="a" value="{$reorderArticle->kVariKindArtikel}" />

    {else}
        <input type="hidden" name="a" value="{$reorderArticle->kArtikel}" />
      
    {/if}
    <input type="hidden" name="wke" value="1" />
    <input type="hidden" name="Sortierung" value="">
    
    <button aria-label="In den Warenkorb" name="inWarenkorb" type="submit" value="In den Warenkorb" class="reorder-dropdown__add-to-cart-btn text-primary" id="{$i}-submit{$reorderArticle->kArtikel}"
      title="{lang key="addToCart" section="global"}">
      {$admIcon->renderIcon('reorder', 'shopnav__icon icon-reorder icon-content')}
      {* {fetch file="`$oPlugin_admorris_pro->getPaths()->getFrontendPath()`icons/reorder-icon.svg"} *}
      <span class="sr-only">{lang key="addToCart" section="global"}</span>
    </button>
  </form>
</li>