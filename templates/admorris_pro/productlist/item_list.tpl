{*custom*}
{* template to display products in product-lists *}

{block name='productlist-item-list'}
    {function srcset}{strip}
        {if !empty($image)}
            {$image->cURLMini} {$imageData->xs->size->width}w,
            {$image->cURLKlein} {$imageData->sm->size->width}w,
            {$image->cURLNormal} {$imageData->md->size->width}w
        {/if}
    {/strip}{/function}

    {block name='productlist-item-list-variables'}

        {if $Einstellungen.template.productlist.variation_select_productlist === 'N'}
            {assign var="hasOnlyListableVariations" value=0}
        {else}
            {hasOnlyListableVariations artikel=$Artikel maxVariationCount=$Einstellungen.template.productlist.variation_select_productlist maxWerteCount=$Einstellungen.template.productlist.variation_max_werte_productlist assign="hasOnlyListableVariations"}
        {/if}

        {$showVariations = ($hasOnlyListableVariations > 0 && !$Artikel->bHasKonfig && $Artikel->kEigenschaftKombi === 0)}


        {$sidebarActive = !$admorris_pro_templateSettings->floating_filter_sidebar}



        {* Grid Columns *}
        {* ============ *}
        {capture name="imageCol" assign="imageCol"}
            col-5 col-md-3
        {/capture}

        {capture name="detailCol" assign="detailCol"}
            col-7{if !$admPro->is_small_container()} col-lg-3{/if}
        {/capture}

        {capture name="actionCol" assign="actionCol"}
            col-12 {* col-md-5 *} {if !$admPro->is_small_container()} col-lg-6{else} top15{/if}
        {/capture}

        {* sub-columns of actionCol *}
        {* ======================== *}
        {capture name="variationCol" assign="variationCol"}
        {if $sidebarActive}col-12{else}col-12 col-lg-5{/if} d-none d-sm-block
        {/capture}

        {capture name="basketCol" assign="basketCol"}
        {if $sidebarActive} col-12 
        {else}
            {if $showVariations} col-12 col-lg-7{else}col-12{/if}
        {/if}
        {/capture}

    {/block}




    {* ======================== *}


    {block name='productlist-item-list-productbox-outer'}
        <div id="{$idPrefix|default:''}result-wrapper_buy_form_{$Artikel->kArtikel}" data-wrapper="true" class="product-cell product-list-item{* {if $Einstellungen.template.productlist.hover_productlist === 'Y'} hover-enabled{/if} *}{if isset($listStyle) && $listStyle === 'list'} active{/if}">
            {block name='productlist-item-list-productbox-inner'}
                <div class="product-body row product-list-item__row row-gap-4 {if $tplscope !== 'list'} text-center{/if}">
                    <div class="product-list-item__image-col {$imageCol}">
                        <div class="image-link-wrapper">
                            {block name="productlist-item-list-images"}{* Nova Block for compatibility *}
                                {include 'productlist/item_box_image.tpl'}
                            {/block}
                        </div>
                    </div>
                    <div class="product-list-item__info-col">
                        <div class="product-list-item__info-row gap">
                            <div class="{* {$detailCol}  *}product-list-item__product-detail-col product-detail gap">
                                {* <div class="flex-wrap-column"> *}
                                    {include 'productlist/item_list_details.tpl'}
                                {* </div> *}
                            </div>{* /col-lg-9 *}
                            <div class="product-list-item__actions-col{*  {$actionCol} *} product-detail">
                                <form id="{$idPrefix|default:''}buy_form_{$Artikel->kArtikel}" action="{$ShopURL}" method="post" class="form form-basket jtl-validate" 
                                    data-toggle="basket-add">
                                    {$jtl_token}
                                    {* <div class="product-detail-cell"> *}
                                    {block name="form-basket"}
                                        <div class="product-list-item__actions-row row-gap-5 expandable">

                                            {include 'productlist/item_list_actions.tpl'} 

                                        </div>
                                    {/block}
                                    {* </div> *}
                                </form>
                                
                                {if !($Artikel->nIstVater && $Artikel->kVaterArtikel === 0) && $Artikel->verfuegbarkeitsBenachrichtigung === 3}
                                    
                                    <form action="" method="post" class=" product-actions product-actions--list" role="group" 
                                        data-toggle="product-actions">
                                        {$jtl_token}
                                        {block name="product-actions"}
                                            <button type="button" id="{$idPrefix|default:''}n{$Artikel->kArtikel}" class="product-actions__button text-nowrap popup-dep notification btn" title="{lang key="requestNotification" section="global"}">
                                                {$admIcon->renderIcon('alert', 'icon-content icon-content--center')}&nbsp;&nbsp;<span class="product-actions__label icon-text--center">{lang key="requestNotification" section="global"}</span>
                                            </button>
                                        {/block}
                                        <input type="hidden" name="a" value="{if !empty({$Artikel->kVariKindArtikel})}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}" />
                                    </form>
                                {/if}
                            </div>{* /col-lg-3 *}
                        </div>
                    </div>
                </div>{* /product-body *}
            {/block}
        </div>{* /product-cell *}
    {/block}

    {block name='productlist-item-list-notification-form'}
        {* popup-content *}
        {if $Artikel->verfuegbarkeitsBenachrichtigung === 3}
            <div id="popupn{$Artikel->kArtikel}" class="d-none">
                {include file='productdetails/availability_notification_form.tpl' position="popup" tplscope='artikeldetails'}
            </div>
        {/if}
    {/block}
{/block}