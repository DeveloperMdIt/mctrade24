{block name='snippets-wishlist'}
    {$alignment = $admorris_pro_templateSettings->productCellTextAlignment}
    {$hoverEnabled = $Einstellungen.template.productlist.hover_productlist === 'Y'}
    {$hoverEnabledClass = (!empty($hoverEnabled)) ? 'hover-enabled' : 'hover-disabled'}
    {get_static_route id='wunschliste.php' assign='wishlistURL'}
    {block name='snippets-wishlist-header'}
        {include file='layout/header.tpl'}
    {/block}

    {block name='snippets-wishlist-content'}
        {block name='snippets-wishlist-include-extension'}
            {include file='snippets/extension.tpl'}
        {/block}

        {container fluid=$Link->getIsFluid() class="snippets-wishlist {if $Einstellungen.template.theme.left_sidebar === 'Y' && $boxesLeftActive}container-plus-sidebar{/if}"}
        {if $step === 'wunschliste versenden' && $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
            {block name='snippets-wishlist-content-heading-email'}
                <h1>{lang key='wishlistViaEmail' section='login'}</h1>
            {/block}
            {block name='snippets-wishlist-content-form-outer'}
                {row}
                    {col cols=12}
                    {block name='snippets-wishlist-form-subheading'}
                        <div class="subheadline">{$CWunschliste->getName()}</div>
                    {/block}
                    {block name='snippets-wishlist-form'}
                        {form method="post" action=$wishlistURL name="Wunschliste"}
                        {block name='snippets-wishlist-form-inner'}
                            {block name='snippets-wishlist-form-inputs-hidden'}
                                {input type="hidden" name="wlvm" value="1"}
                                {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                {input type="hidden" name="send" value="1"}
                            {/block}
                            {block name='snippets-wishlist-form-textarea'}
                                {formgroup
                                    label-for="wishlist-email"
                                    label="{lang key='wishlistEmails' section='login'}{if $Einstellungen.global.global_wunschliste_max_email > 0} | {lang key='wishlistEmailCount' section='login'}: {$Einstellungen.global.global_wunschliste_max_email}{/if}"}
                                    {textarea id="wishlist-email" name="email" rows="5" style="width:100%"}{/textarea}
                                {/formgroup}
                            {/block}
                            {block name='snippets-wishlist-form-submit'}
                                {row}
                                    {col md=4 xl=3 class='ml-auto-util'}
                                        {button name='action' block=true type='submit' value='sendViaMail' variant='primary'}
                                            {lang key='wishlistSend' section='login'}
                                        {/button}
                                    {/col}
                                {/row}
                            {/block}
                        {/block}
                        {/form}
                    {/block}
                    {/col}
                {/row}
            {/block}
        {else}
            {block name='snippets-wishlist-content-heading'}
                <h1>
                    {if $isCurrenctCustomer === false && $CWunschliste->getCustomer() !== null}
                        {$CWunschliste->getName()} {lang key='from' section='product rating' alt_section='login,productDetails,productOverview,global,'} {$CWunschliste->getCustomer()->cVorname}
                    {else}
                        {lang key='myWishlists'}
                    {/if}
                </h1>
            {/block}

            {row class="wishlist-actions"}
                {if $isCurrenctCustomer === true}
                    {block name='snippets-wishlist-actions'}
                        {col class="col-auto"}
                            {dropdown variant="link no-caret" class="wishlist-options" text="<i class='fas fa-ellipsis-v'></i>" aria=["label"=>"{lang key='wishlistOptions' section='aria'}"]}
                                {dropdownitem tag='div'}
                                {block name='snippets-wishlist-actions-rename'}
                                    {button type="submit" variant="link" class="w-100-util no-caret" data=["toggle" => "collapse", "target"=>"#edit-wishlist-name"]}
                                        {lang key='rename'}
                                    {/button}
                                {/block}
                                {/dropdownitem}
                                {dropdownitem tag='div'}
                                {block name='snippets-wishlist-actions-remove-products'}
                                    {form
                                        method="post"
                                        action="{$wishlistURL}{if $CWunschliste->isDefault() !== true}?wl={$CWunschliste->getID()}{/if}"
                                        name="Wunschliste"
                                    }
                                        {input type="hidden" name="wla" value="1"}
                                        {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                        {input type="hidden" name="action" value="removeAll"}
                                        {button type="submit" variant="link" class="stretched-link"}
                                            {lang key='wlRemoveAllProducts' section='wishlist'}
                                        {/button}
                                    {/form}
                                {/block}
                                {/dropdownitem}
                                {dropdownitem tag='div'}
                                {block name='snippets-wishlist-actions-add-all-cart'}
                                    {form
                                        method="post"
                                        action="{$wishlistURL}{if $CWunschliste->isDefault() !== true}?wl={$CWunschliste->getID()}{/if}"
                                        name="Wunschliste"
                                    }
                                        {input type="hidden" name="wla" value="1"}
                                        {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                        {input type="hidden" name="action" value="addAllToCart"}
                                        {button type="submit" variant="link" class="stretched-link"}
                                            {lang key='wishlistAddAllToCart' section='login'}
                                        {/button}
                                    {/form}
                                {/block}
                                {/dropdownitem}
                                {dropdownitem tag='div'}
                                {block name='snippets-wishlist-actions-delete-wl'}
                                    {form method="post" action=$wishlistURL slide=true}
                                        {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                        {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->getID()}
                                        {input type="hidden" name="action" value="delete"}
                                        {button type="submit" variant="link" class="stretched-link"}
                                            {lang key='wlDelete' section='wishlist'}
                                        {/button}
                                    {/form}
                                {/block}
                                {/dropdownitem}
                                {if $CWunschliste->isDefault() !== true}
                                    {dropdownitem tag='div'}
                                    {block name='snippets-wishlist-actions-set-active'}
                                        {form method="post" action=$wishlistURL slide=true}
                                            {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                            {input type="hidden" name="kWunschlisteTarget" value=$CWunschliste->getID()}
                                            {input type="hidden" name="action" value="setAsDefault"}
                                            {button type="submit"
                                                variant="link"
                                                class="stretched-link"
                                                title="{lang key='setAsStandardWishlist' section='wishlist'}"
                                                data=["toggle" => "tooltip", "placement" => "bottom"]
                                            }
                                                {lang key='activate'}
                                            {/button}
                                        {/form}
                                    {/block}
                                    {/dropdownitem}
                                {/if}
                                {dropdownitem tag='div'}
                                {block name='snippets-wishlist-actions-add-new'}
                                    {button type="submit"
                                        variant="link"
                                        class="stretched-link no-caret"
                                        data=["toggle" => "collapse", "target"=>"#create-new-wishlist"]
                                    }
                                        {lang key='wishlistAddNew' section='login'}
                                    {/button}
                                {/block}
                                {/dropdownitem}
                            {/dropdown}
                        {/col}
                    {/block}
                    {block name='snippets-wishlist-wishlists'}
                        {col class="col-md-auto"}
                            {dropdown id='wlName'
                                variant='outline-secondary'
                                text=$CWunschliste->getName()
                                toggle-class='w-100-util'
                                class="wishlist-dropdown-name"}
                            {foreach $oWunschliste_arr as $wishlist}
                                {dropdownitem href="{$wishlistURL}{if $wishlist->isDefault() !== true}?wl={$wishlist->getID()}{/if}" rel="nofollow" }
                                    {$wishlist->getName()}
                                {/dropdownitem}
                            {/foreach}
                            {/dropdown}
                        {/col}
                    {/block}
                {/if}
                {block name='snippets-wishlist-search'}
                    {col cols=12 class="col-md wishlist-search-wrapper"}
                    {if $hasItems === true || !empty($wlsearch)}
                        <div id="wishlist-search">
                            {form method="post" action=$wishlistURL name="WunschlisteSuche"}
                            {block name='snippets-wishlist-search-form-inputs-hidden'}
                                {if $CWunschliste->isPublic() && !empty($cURLID)}
                                    {input type="hidden" name="wlid" value=$cURLID}
                                {/if}
                                {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                {input type="hidden" name="action" value="search"}
                            {/block}
                            {block name='snippets-wishlist-search-form-inputs'}
                                {inputgroup}
                                    {input name="cSuche" size="35" type="text" value=$wlsearch placeholder="{lang key='wishlistSearch' section='login'}" aria=["label"=>"{lang key='wishlistSearch' section='login'}"]}
                                    {inputgroupaddon append=true}
                                    {block name='snippets-wishlist-search-form-submit'}
                                        {button name="action"
                                            value="search"
                                            type="submit"
                                            variant="outline-primary"
                                            aria=["label"=>{lang key='wishlistSearchBTN' section='login'}]
                                            class="wishlist-search-button"}
                                            <i class="fa fa-search"></i>
                                            <span class="wishlist-search-button-text">{lang key='wishlistSearchBTN' section='login'}</span>
                                        {/button}
                                    {/block}
                                    {/inputgroupaddon}
                                    {if !empty($wlsearch)}
                                        {block name='snippets-wishlist-search-form-remove-search'}
                                            {inputgroupaddon append=true}
                                                {button type="submit" name="cSuche" value="" variant="outline-primary"}
                                                    <i class="fa fa-undo"></i> {lang key='wishlistRemoveSearch' section='login'}
                                                {/button}
                                            {/inputgroupaddon}
                                        {/block}
                                    {/if}
                                {/inputgroup}
                            {/block}
                            {/form}
                        </div>
                    {/if}
                    {/col}
                {/block}
            {/row}
            {if $isCurrenctCustomer === true}
                {block name='snippets-wishlist-visibility'}
                    {block name='snippets-wishlist-visibility-hr-top'}
                        <hr>
                    {/block}
                    {row class='wishlist-privacy-count'}
                    {block name='snippets-wishlist-visibility-form'}
                        {col class='col-xl wishlist-privacy'}
                            <div class="d-inline-flex flex-nowrap">
                                <div class="custom-control custom-switch">
                                    <input type='checkbox'
                                        class='custom-control-input wl-visibility-switch'
                                        id="wl-visibility-{$CWunschliste->getID()}"
                                        data-wl-id="{$CWunschliste->getID()}"
                                        {if $CWunschliste->isPublic()}checked{/if}
                                        aria-label="{if $CWunschliste->isPublic()}{lang key='wishlistNoticePublic' section='login'}{else}{lang key='wishlistNoticePrivate' section='login'}{/if}"
                                    >
                                    <label class="custom-control-label" for="wl-visibility-{$CWunschliste->getID()}">
                                        <span data-switch-label-state="public-{$CWunschliste->getID()}" class="{if $CWunschliste->isPublic() !== true}d-none{/if}">
                                            {lang key='wishlistNoticePublic' section='login'}
                                        </span>
                                        <span data-switch-label-state="private-{$CWunschliste->getID()}" class="{if $CWunschliste->isPublic()}d-none{/if}">
                                            {lang key='wishlistNoticePrivate' section='login'}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        {/col}
                    {/block}
                    {block name='snippets-wishlist-visibility-count'}
                        {col class='col-auto wishlist-count'}
                            {count($CWunschliste->getItems())} {lang key='products'}
                        {/col}
                    {/block}
                    {/row}
                {/block}
                {block name='snippets-wishlist-link'}
                    {row class="wishlist-url {if $CWunschliste->isPublic() !== true}d-none{/if}" id='wishlist-url-wrapper'}
                        {col cols=12}
                            {form method="post" action=$wishlistURL}
                                {block name='snippets-wishlist-link-inputs-hidden'}
                                    {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                {/block}
                                {block name='snippets-wishlist-link-inputs'}
                                    {inputgroup}
                                    {block name='snippets-wishlist-link-name'}
                                        {input type="text"
                                            id="wishlist-url"
                                            name="wishlist-url"
                                            readonly=true
                                            aria=["label"=>"{lang key='wishlist'}-URL"]
                                            data=["static-route" => "{$wishlistURL}?wlid="]
                                            value="{$wishlistURL}?wlid={$CWunschliste->getURL()}"}
                                    {/block}
                                    {if $Einstellungen.global.global_wunschliste_freunde_aktiv === 'Y'}
                                        {block name='snippets-wishlist-link-envelop'}
                                            {inputgroupaddon append=true}
                                                {button type="submit"
                                                    variant="link"
                                                    name="action"
                                                    class="btn-outline-secondary"
                                                    value="sendViaMail"
                                                    disabled=(!$hasItems)
                                                    title="{lang key='wishlistViaEmail' section='login'}"
                                                    aria=["label"=>{lang key='wishlistViaEmail' section='login'}]
                                                }
                                                    <i class="far fa-envelope"></i>
                                                {/button}
                                            {/inputgroupaddon}
                                        {/block}
                                    {/if}
                                    {/inputgroup}
                                {/block}
                            {/form}
                        {/col}
                    {/row}
                {/block}

                {block name='snippets-wishlist-form-rename'}
                    {block name='snippets-wishlist-form-rename-hr-top'}
                        <hr>
                    {/block}
                    {row}
                        {col cols=12}
                            {collapse id="edit-wishlist-name" visible=false class="wishlist-collapse"}
                                {form
                                    method="post"
                                    action="{$wishlistURL}{if $CWunschliste->isDefault() !== true}?wl={$CWunschliste->getID()}{/if}"
                                    name="Wunschliste"
                                }
                                {block name='snippets-wishlist-form-content-rename'}
                                    {block name='snippets-wishlist-form-content-rename-inputs-hidden'}
                                        {input type="hidden" name="wla" value="1"}
                                        {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                        {input type="hidden" name="action" value="update"}
                                    {/block}
                                    {block name='snippets-wishlist-form-content-rename-submit'}
                                        {inputgroup}
                                            {inputgroupaddon prepend=true}
                                                {inputgrouptext}
                                                    {lang key='name' section='global'}
                                                {/inputgrouptext}
                                            {/inputgroupaddon}
                                        {input id="wishlist-name" type="text" placeholder="name" name="WunschlisteName" value=$CWunschliste->getName()}
                                            {inputgroupaddon append=true}
                                                {input type="submit" value="{lang key='rename'}"}
                                            {/inputgroupaddon}
                                        {/inputgroup}
                                    {/block}
                                {/block}
                                {/form}
                            {/collapse}
                        {/col}
                    {/row}
                {/block}
                {block name='snippets-wishlist-form-new'}
                    {row}
                        {col cols=12}
                            {collapse id="create-new-wishlist" visible=($newWL === 1) class="wishlist-collapse"}
                                {form method="post" action=$wishlistURL slide=true}
                                    {block name='snippets-wishlist-form-content-new'}
                                        {block name='snippets-wishlist-form-content-new-inputs-hidden'}
                                            {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                                        {/block}
                                        {block name='snippets-wishlist-form-content-new-submit'}
                                            {inputgroup}
                                                {input name="cWunschlisteName" type="text"
                                                    class="input-sm"
                                                    placeholder="{lang key='wishlistAddNew' section='login'}"
                                                    size="35"}
                                                {inputgroupaddon append=true}
                                                    {button type="submit" size="sm" name="action" value="createNew" variant="outline-primary"}
                                                        <i class="fa fa-save"></i> {lang key='wishlistSaveNew' section='login'}
                                                    {/button}
                                                {/inputgroupaddon}
                                            {/inputgroup}
                                        {/block}
                                    {/block}
                                {/form}
                            {/collapse}
                        {/col}
                    {/row}
                {/block}
            {/if}

            {block name='snippets-wishlist-form-basket'}
                {include file='snippets/pagination.tpl'
                    cThisUrl="wunschliste.php"
                    oPagination=$pagination
                    cParam_arr=['wl' => {$CWunschliste->getID()}, 'wlid' => $cURLID]}
                {form method="post"
                    action="{$wishlistURL}{if $CWunschliste->isDefault() !== true}?wl={$CWunschliste->getID()}{/if}"
                    name="Wunschliste"
                    class="basket_wrapper{if $hasItems === true} has-items{/if}"
                    id="wl-items-form"}
                {block name='snippets-wishlist-form-basket-content'}
                    {block name='snippets-wishlist-form-basket-inputs-hidden'}
                        {input type="hidden" name="wla" value="1"}
                        {input type="hidden" name="kWunschliste" value=$CWunschliste->getID()}
                        {if $CWunschliste->isPublic() && !empty($cURLID)}
                            {input type="hidden" name="wlid" value=$cURLID}
                        {/if}
                        {if !empty($wlsearch)}
                            {input type="hidden" name="wlsearch" value="1"}
                            {input type="hidden" name="cSuche" value=$wlsearch}
                        {/if}
                    {/block}
                    {if !empty($CWunschliste->getItems())}
                        {block name='snippets-wishlist-form-basket-products'}
                            {row class='product-list gallery'}
                            {foreach $wishlistItems as $wlPosition}
                                {col cols=12 sm=6 md=4 xl=3 class="wishlist-item"}
                                    <div id="result-wrapper_buy_form_{$wlPosition->getID()}" data-wrapper="true" class="productbox productbox-column productbox-hover product-cell {$hoverEnabledClass}{if isset($listStyle) && $listStyle === 'gallery'} active {/if}{if isset($class)} {$class}{/if}{block name='item-box-product-cell-class'}{/block}"{if $idPrefix|default:false} data-id-prefix="{$idPrefix}"{/if}>
                                        <div class="product-cell__wrapper pos-abs">
                                            {row}
                                                {col cols=12}
                                                    <div class="productbox-image">
                                                        {if $isCurrenctCustomer === true}
                                                            {block name='snippets-wishlist-form-basket-remove'}
                                                            <div class="productbox-quick-actions productbox-onhover">
                                                                {button
                                                                    type="submit"
                                                                    variant="link"
                                                                    name="remove" value=$wlPosition->getID()
                                                                    aria=["label"=>"{lang key='wishlistremoveItem' section='login'}"]
                                                                    title="{lang key='wishlistremoveItem' section='login'}"
                                                                    class="wishlist-pos-delete"
                                                                    data=["toggle"=>"tooltip"]
                                                                }
                                                                    <i class="fas fa-times"></i>
                                                                {/button}
                                                            </div>
                                                            {/block}
                                                        {/if}
                                                        {block name='snippets-wishlist-form-basket-image'}
                                                            {include file='productlist/item_box_image.tpl' Artikel=$wlPosition->getProduct() actions=false}
                                                        {/block}
                                                    </div>
                                                {/col}
                                                {col cols=12 class="{if $alignment === 'center'}text-center{/if} product-cell__body"}
                                                    {block name='snippets-wishlist-form-basket-name'}
                                                        {link href=$wlPosition->getProduct()->getURL() class="product-cell__title title h4"}
                                                            {$wlPosition->getProductName()}
                                                        {/link}
                                                    {/block}
                                                    {block name='snippets-wishlist-form-basket-price'}
                                                        {if $wlPosition->getProduct()->getOption('nShowOnlyOnSEORequest', 0) === 1}
                                                            <p class="caption">{lang key='productOutOfStock' section='productDetails'}</p>
                                                        {elseif $wlPosition->getProduct()->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N'}
                                                            <p class="caption">{lang key='priceOnApplication' section='global'}</p>
                                                        {else}
                                                            {block name='snippets-wishlist-form-basket-include-price'}
                                                                {include file='productdetails/price.tpl' Artikel=$wlPosition->getProduct() tplscope='detail'}
                                                            {/block}
                                                        {/if}
                                                    {/block}
                                                    <div class="expandable">
                                                    {block name='snippets-wishlist-form-basket-characteristics'}
                                                        {if $Einstellungen.artikeluebersicht.artikeluebersicht_kurzbeschreibung_anzeigen === 'Y' && $wlPosition->getProduct()->cKurzBeschreibung}
                                                            <div class="product-cell__shortdescription">
                                                                {$wlPosition->getProduct()->cKurzBeschreibung}
                                                            </div>
                                                        {/if}
                                                        <div class="product-characteristics productbox-onhover">
                                                            {block name='snippets-wishlist-form-basket-characteristics-include-item-details'}
                                                                {block name='item-box-article-number'}
                                                                    {if $admorris_pro_templateSettings->article_number_gallery === true}
                                                                        <div class="product-cell__article-number">
                                                                        {formrow tag='dl' class="formrow-small"}
                                                                            {col tag='dt' cols=6}{lang key="productNo" section="global"}:{/col}
                                                                            {col tag='dd' cols=6}{$wlPosition->getProduct()->cArtNr}{/col}
                                                                        {/formrow}
                                                                        </div>
                                                                    {/if}
                                                                {/block}
                                                                {block name='item-box-rating'}
                                                                    {if $Einstellungen.bewertung.bewertung_anzeigen === 'Y' && $wlPosition->getProduct()->fDurchschnittsBewertung > 0}
                                                                        <div class="product-cell__rating">
                                                                            {include file='productdetails/rating.tpl' stars=$wlPosition->getProduct()->fDurchschnittsBewertung}
                                                                        </div>
                                                                    {/if}
                                                                {/block}
                                                            {/block}
                                                            {block name='snippets-wishlist-form-basket-characteristics-selected'}
                                                                {formrow tag='dl' class="formrow-small"}

                                                                    {foreach $wlPosition->getProperties() as $CWunschlistePosEigenschaft}
                                                                        {if $CWunschlistePosEigenschaft->getFreeTextValue()}
                                                                            {col tag='dt' cols=6}{$CWunschlistePosEigenschaft->getPropertyName()}:{/col}
                                                                            {col tag='dd' cols=6}{$CWunschlistePosEigenschaft->getFreeTextValue()}{/col}
                                                                        {else}
                                                                            {col tag='dt' cols=6}{$CWunschlistePosEigenschaft->getPropertyName()}:{/col}
                                                                            {col tag='dd' cols=6}{$CWunschlistePosEigenschaft->getPropertyValueName()}{/col}
                                                                        {/if}
                                                                    {/foreach}
                                                                {/formrow}
                                                            {/block}
                                                        </div>
                                                    {/block}
                                                    {block name='snippets-wishlist-form-basket-main'}
                                                        <div class="productbox-onhover productbox-options">
                                                            {block name='snippets-wishlist-form-basket-textarea'}
                                                                {textarea
                                                                    placeholder={lang key='yourNote'}
                                                                    readonly=($isCurrenctCustomer !== true)
                                                                    rows="5"
                                                                    name="Kommentar_{$wlPosition->getID()}"
                                                                    class="js-update-wl auto-expand"
                                                                    aria=["label"=>"{lang key='wishlistComment' section='login'} {$wlPosition->getProductName()}"]
                                                                }{$wlPosition->getComment()}{/textarea}
                                                            {/block}
                                                            {block name='snippets-wishlist-form-basket-delivery-status'}
                                                                {block name='snippets-wishlist-item-list-include-delivery-status'}
                                                                <div class="product-cell__delivery-status delivery-status{if $alignment === 'center'} text-center{/if}">
                                                                    {assign var=anzeige value=$Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandsanzeige}
                                                                        {if $wlPosition->getProduct()->inWarenkorbLegbar === $smarty.const.INWKNICHTLEGBAR_UNVERKAEUFLICH}
                                                                            <span class="status"><small>{lang key='productUnsaleable' section='productDetails'}</small></span>
                                                                        {elseif $wlPosition->getProduct()->nErscheinendesProdukt}
                                                                        <div class="availablefrom">
                                                                            <small>{lang key='productAvailableFrom'}: {$wlPosition->getProduct()->Erscheinungsdatum_de}</small>
                                                                        </div>
                                                                        {if $Einstellungen.global.global_erscheinende_kaeuflich === 'Y' && $wlPosition->getProduct()->inWarenkorbLegbar === 1}
                                                                            <div class="attr attr-preorder"><small class="value">{lang key='preorderPossible'}</small></div>
                                                                        {/if}
                                                                    {elseif $anzeige !== 'nichts'
                                                                        && $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N'
                                                                        && $wlPosition->getProduct()->getBackorderString() !== ''
                                                                        && ($wlPosition->getProduct()->cLagerKleinerNull === 'N' || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                                                                        <div class="signal_image status-1"><small>{$wlPosition->getProduct()->getBackorderString()}</small></div>
                                                                {elseif $anzeige !== 'nichts'
                                                                    && $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen !== 'N'
                                                                    && $wlPosition->getProduct()->cLagerBeachten === 'Y'
                                                                    && $wlPosition->getProduct()->fLagerbestand <= 0
                                                                    && $wlPosition->getProduct()->fLieferantenlagerbestand > 0
                                                                    && $wlPosition->getProduct()->fLieferzeit > 0
                                                                    && ($wlPosition->getProduct()->cLagerKleinerNull === 'N' || $Einstellungen.artikeluebersicht.artikeluebersicht_lagerbestandanzeige_anzeigen === 'U')}
                                                                    <div class="signal_image status-1"><small>{lang key='supplierStockNotice' printf=$wlPosition->getProduct()->fLieferzeit}</small></div>
                                                                    {elseif $anzeige === 'verfuegbarkeit' || $anzeige === 'genau'}
                                                                        <div class="signal_image status-{$wlPosition->getProduct()->Lageranzeige->nStatus}"><small>{$wlPosition->getProduct()->Lageranzeige->cLagerhinweis[$anzeige]}</small></div>
                                                                    {elseif $anzeige === 'ampel'}
                                                                        <div class="signal_image status-{$wlPosition->getProduct()->Lageranzeige->nStatus}"><small>{$wlPosition->getProduct()->Lageranzeige->AmpelText}</small></div>
                                                                    {/if}
                                                                    {if $wlPosition->getProduct()->cEstimatedDelivery}
                                                                        <div class="estimated_delivery{if $alignment === 'center'} text-center{/if}">
                                                                                <small>{lang key='shippingTime'}: {$wlPosition->getProduct()->cEstimatedDelivery}</small>
                                                                        </div>
                                                                    {/if}
                                                                </div>
                                                                {/block}
                                                            {/block}
                                                            {block name='wishlist-item-box-below-productlist-delivery-status'}
                                                                {if $Einstellungen.artikeluebersicht.artikeluebersicht_kurzbeschreibung_anzeigen === 'Y' && $wlPosition->getProduct()->cKurzBeschreibung}
                                                                    <div class="product-cell__shortdescription">
                                                                        {$wlPosition->getProduct()->cKurzBeschreibung}
                                                                    </div>
                                                                {/if}
                                                            {/block}
                                                            {if !($wlPosition->getProduct()->Preise->fVKNetto == 0 && $Einstellungen.global.global_preis0 === 'N')}
                                                                {block name='snippets-wishlist-form-basket-input-group-details'}
                                                                    <div class="form-row productbox-actions text-center">
                                                                        {col cols=12}
                                                                        {block name='snippets-wishlist-form-basket-quantity'}
                                                                            {$wrapperClassName='product-cell__quantity'}
                                                                            {quantityInput name="Anzahl_{$wlPosition->getID()}" article=$wlPosition->getProduct() wrapperClass=$wrapperClassName|default:'' idPrefix=$idPrefix|default:''}
                                                                        {/block}
                                                                        {/col}
                                                                        {col cols=12 class="wishlist-item-buttons"}
                                                                            {if $wlPosition->getProduct()->bHasKonfig}
                                                                                {block name='snippets-wishlist-form-basket-has-config'}
                                                                                    {link href=$wlPosition->getProduct()->getURL()
                                                                                        class="btn btn-primary btn-block"
                                                                                        title="{lang key='product' section='global'} {lang key='configure' section='global'}"}
                                                                                        <span class="fa fa-cogs"></span> {lang key='configure'}
                                                                                    {/link}
                                                                                {/block}
                                                                            {else}
                                                                                {block name='snippets-wishlist-form-basket-add-to-cart'}
                                                                                    {button type="submit"
                                                                                        name="addToCart"
                                                                                        value=$wlPosition->getID()
                                                                                        variant="primary"
                                                                                        block=true
                                                                                        title="{lang key='wishlistaddToCart' section='login'}"}
                                                                                        {lang key='addToCart'}
                                                                                    {/button}
                                                                                {/block}
                                                                            {/if}
                                                                        {/col}
                                                                    </div>
                                                                {/block}
                                                            {/if}
                                                        </div>
                                                    {/block}
                                                    </div>
                                                {/col}
                                            {/row}
                                        </div>
                                    </div>
                                {/col}
                            {/foreach}
                            {/row}
                        {/block}
                        {block name='snippets-wishlist-form-basket-submit'}
                            <div class="wishlist-all-to-cart sticky-bottom">
                            {row}
                                {col cols=12 md="auto"}
                                    {if $isCurrenctCustomer === true}
                                        {button type="submit"
                                            title="{lang key='addCurrentProductsToCart' section='wishlist'}"
                                            name="action"
                                            value="addAllToCart"
                                            block=true
                                            variant="primary"
                                        }
                                            <i class="fa fa-shopping-cart"></i>
                                            {if !empty($wlsearch)}
                                                {lang key='addCurrentProductsToCart' section='wishlist'}
                                            {else}
                                                {lang key='wishlistAddAllToCart' section='login'}
                                            {/if}
                                        {/button}
                                    {/if}
                                {/col}
                            {/row}
                            </div>
                        {/block}
                    {else}
                        {block name='snippets-wishlist-alert'}
                            {alert variant="info"}{lang key='noEntriesAvailable' section='global'}{/alert}
                        {/block}
                    {/if}
                {/block}
                {/form}
            {/block}
        {/if}
        {/container}
    {/block}

    {block name='snippets-wishlist-include-footer'}
        {include file='layout/footer.tpl'}
    {/block}
{/block}
