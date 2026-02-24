{* Wiederbestellen *}
{$admPro_reorderItems = (!empty($Bestellungen)) ? $am_Reorder->lastOrderedItems($Bestellungen):null}
{if $admPro_reorderItems}
  {if $admUtils::isTemplateActive()}
    {$containerSize = $am_globalContainerSize}
    {if $containerSize === 's'}
      {$grid = 'col-xs-6'}
    {elseif $containerSize === 'm' || $containerSize === 'l'}
      {$grid = 'col-xs-6 col-sm-4'}

    {else}
      {$grid = 'col-xs-6 col-sm-4 col-lg-3'}
    {/if}
  {else}
    {$grid = 'col-xs-6 col-lg-4'}
  {/if}

  <div id="reorder" class="reorder-list">
    <h2 class="reorder-heading">{$admUtils::trans('marketing_reorder_heading')}</h2>
    <div {* class="panel-body" *} id="result-wrapper">
      <input type="hidden" id="product-list-type" value="gallery">
      {include file='snippets/pagination.tpl' oPagination=$admPro_reorderItems cThisUrl='jtl.php' parts=['pagi', 'label']}
      <div class="productlist__row">
        {if $admUtils::isTemplateActive()}
          {$loadingAnimation = $admorris_pro_templateSettings->image_preload_animation === 'Y'}

          {if $loadingAnimation}
            <div class="productlist__loader">
              {include 'components/loading_animation.tpl'}
            </div>
          {/if}
        {else}
          {$loadingAnimation = false}
        {/if}
        <div class="productlist__results-wrapper{if $loadingAnimation} is-loading{/if}">

          <div class="row gallery{if !$admUtils::isTemplateActive()} row-eq-height row-eq-img-height{/if}"
            id="product-list">

            {$wrapperHoverClass = (isset($Einstellungen.template.productlist.hover_productlist) && $Einstellungen.template.productlist.hover_productlist === 'Y')?' product-wrapper--hover-enabled':' product-wrapper--hover-disabled'}


            {foreach $admPro_reorderItems->getPageItems() as $Artikel}
              {if $Artikel->kArtikel}
                <div class="product-wrapper {$grid}{$wrapperHoverClass}">
                  {$productlistClass = ($admUtils::isTemplateActive())?'product-cell--gallery':'thumbnail'}

                  {include file='productlist/item_box.tpl' tplscope='gallery' class=$productlistClass notificationsOverride='basket'}

                </div>

              {/if}
            {/foreach}
          </div>
        </div>
      </div>

      {include file='snippets/pagination.tpl' oPagination=$admPro_reorderItems cThisUrl='jtl.php' parts=['pagi', 'label']}
    </div>
  </div>

  {* When a variation combi is selected & ajax pushToBasket is not activated by default, 
  * the submit listener needs to be added again when the variation is loaded 
  *}
  {if $admUtils::isTemplateActive()}
    <script>
      $(function() {
        $(document).on('variationSetVal', function(e, wrapper, variation) {
          var basketForm = $(wrapper.selector).find('.form-basket');
          if (basketForm.data('toggle') == '') {
            basketForm.on('submit', function(event) {
              event.preventDefault();
              event.stopPropagation();

              var $form = basketForm;
              var data = $form.serializeObject();
              data['a'] = variation;

              $.evo.basket().addToBasket($form, data);
            })
          }
        });
      });
    </script>

  {/if}
{/if}