{block name='productdetails-config-options'}
    <div class="cfg-group-list-wrapper stack">

    {foreach $Artikel->oKonfig_arr as $configGroup}
        {if $configGroup->getItemCount() > 0}
            {$configLocalization = $configGroup->getSprache()}
            {$configGroupHasImage = (strpos($configGroup->getImage(\JTL\Media\Image::SIZE_MD), $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN)) === false}
            {$kKonfiggruppe = $configGroup->getKonfiggruppe()}
            <div id="cfg-grp-{$configGroup->getID()}" class="cfg-group js-cfg-group stack card card-body {if $configGroup@first}visited{/if}"
                 data-id="{$configGroup->getID()}">
                {block name='productdetails-config-options-heading'}
                    <h2 class="hr-sect h3">
                        {$configLocalization->getName()}
                    </h2>
                {/block}
                {block name='productdetails-config-options-collapse'}
                    
                    {block name='productdetails-config-container-group-description-alert'}
                        {alert variant="danger" class="js-cfg-group-error d-none" data=["id"=>"{$kKonfiggruppe}"]}{/alert}
                    {/block}

                    {if !empty($aKonfigerror_arr[$kKonfiggruppe])}
                        {alert variant="danger"}
                            {$aKonfigerror_arr[$kKonfiggruppe]}
                        {/alert}
                    {/if}

                    {block name='productdetails-config-container-group-description'}
                        {if $configLocalization->hatBeschreibung() || $configGroupHasImage}
                            {row class="group-description"}
                                {if $configLocalization->hatBeschreibung()}
                                    {col cols=10 order=1}
                                        {$configLocalization->getBeschreibung()}
                                    {/col}
                                {/if}
                                {if $configGroupHasImage}
                                    {if $admorris_pro_templateSettings->configuratorConfigGroupImage}
                                        {col cols=2}
                                            {include file='snippets/image.tpl' item=$configGroup square=false class="group-img"}
                                        {/col}
                                    {/if}
                                {/if}
                            {/row}
                        {/if}
                    {/block}

                    {block name='productdetails-config-options-group-info'}
                    <div class="cfg-group-info">
                        {if !empty($configGroup->getMin()) || !empty($configGroup->getMax())}
                            {badge variant="info" class="js-group-badge-checked"}
                            {if $configGroup->getMin() === 1 && $configGroup->getMax() === 1}
                                {lang key='configChooseOneComponent' section='productDetails'}
                            {elseif $configGroup->getMin() === $configGroup->getMax()}
                                {lang key='configChooseNumberComponents' section='productDetails' printf=$configGroup->getMin()}
                            {elseif !empty($configGroup->getMin()) && $configGroup->getMax() < $configGroup->getItemCount()}
                                {lang key='configChooseMinMaxComponents' section='productDetails' printf=$configGroup->getMin()|cat:':::'|cat:$configGroup->getMax()}
                            {elseif !empty($configGroup->getMin())}
                                {lang key='configChooseMinComponents' section='productDetails' printf=$configGroup->getMin()}
                            {elseif $configGroup->getMax() < $configGroup->getItemCount()}
                                {lang key='configChooseMaxComponents' section='productDetails' printf=$configGroup->getMax()}
                            {else}
                                {lang key='optional'}
                            {/if}
                            {/badge}
                        {elseif $configGroup->getMin() == 0}
                            {badge variant="info" class="js-group-badge-checked"}{lang key='optional'}{/badge}
                        {/if}
                    </div>
                {/block}

                    {block name='productdetails-config-container-group-items'}
                        {* {row class="form-group"} *}
                        <ul class="config-item-list list-unstyled">

                        {$viewType = $configGroup->getAnzeigeTyp()}
                        {if $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_CHECKBOX
                        || $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_RADIO
                        || $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_DROPDOWN_MULTI}
                            {block name='productdetails-config-container-group-item-type-swatch'}
                                {foreach $configGroup->oItem_arr as $oItem}
                                    {* {col cols=12} *}
                                        {$bSelectable = 0}
                                        {if $oItem->isInStock()}
                                            {$bSelectable = 1}
                                        {/if}
                                        {$kKonfigitem = $oItem->getKonfigitem()}
                                        {$checkboxActive = (isset($nKonfigitem_arr)
                                            && in_array($oItem->getKonfigitem(), $nKonfigitem_arr))
                                            || (!empty($aKonfigerror_arr)
                                            && isset($smarty.post.item)
                                            && isset($smarty.post.item[$kKonfiggruppe])
                                            && in_array($oItem->getKonfigitem(), $smarty.post.item[$kKonfiggruppe]))
                                            || ($oItem->getSelektiert()
                                            && !isset($kEditKonfig)
                                            && (!isset($aKonfigerror_arr)
                                            || !$aKonfigerror_arr))}
                                            {$cKurzBeschreibung = $oItem->getKurzBeschreibung()}
                                            {$cBeschreibung = $oItem->getBeschreibung()}
                                            {if !empty($cKurzBeschreibung)}
                                                {$cBeschreibung = $cKurzBeschreibung}
                                            {/if}
                                        {block name='productdetails-config-container-group-item-type-swatch-option'}
                                            {if $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_RADIO}
                                                {block name='productdetails-config-container-group-item-type-swatch-option-radio'}
                                                    {capture 'itemInputRadio' assign="itemInput"}
                                                        {radio name="item[{$kKonfiggruppe}][]"
                                                            value=$oItem->getKonfigitem()
                                                            disabled=empty($bSelectable)
                                                            data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                            checked=$checkboxActive
                                                            id="item{$oItem->getKonfigitem()}"
                                                            class="cfg-swatch"
                                                            required=$oItem@first && $configGroup->getMin() > 0
                                                        }
                                                            {$oItem->getName()}
                                                        {/radio}
                                                    {/capture}
                                                {/block}
                                            {else}
                                                {block name='productdetails-config-container-group-item-type-swatch-option-checkbox'}
                                                    {capture 'itemInputCheckbox' assign="itemInput"}
                                                        {checkbox name="item[{$kKonfiggruppe}][]"
                                                            value=$oItem->getKonfigitem()
                                                            disabled=empty($bSelectable)
                                                            data=["selected"=>{isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}]
                                                            checked=$checkboxActive
                                                            id="item{$oItem->getKonfigitem()}"
                                                            class="cfg-swatch"
                                                        }
                                                            {$oItem->getName()}
                                                        {/checkbox}
                                                    {/capture}
                                                {/block}
                                            {/if}
                                            {block name='productdetails-config-container-group-item-type-swatch-option-item-description-with-input'}
                                                <li data-id="{$oItem->getKonfigitem()}" class="config-item config-item--custom-input js-delegate-config-click-event{if $oItem->getEmpfohlen()} bg-recommendation{/if}{if empty($bSelectable)} disabled{/if}{if $checkboxActive} active{/if}">
                                                    {if isset($aKonfigitemerror_arr[$kKonfigitem]) && $aKonfigitemerror_arr[$kKonfigitem]}
                                                        <p class="box_error alert alert-danger">{$aKonfigitemerror_arr[$kKonfigitem]}</p>
                                                    {/if}
                                                    {* {badge class="badge-circle circle-small"}<i class="fas fa-check"></i>{/badge} *}
                                                    {include file='productdetails/config_item_description.tpl' itemInputFild=$itemInput}
                                                </li>
                                            {/block}
                                        {/block}
                                    {* {/col} *}
                                {/foreach}
                            {/block}
                        {elseif $viewType === $smarty.const.KONFIG_ANZEIGE_TYP_DROPDOWN}
                            {block name='productdetails-config-container-group-item-type-dropdown'}
                                <li class="config-item config-option-dropdown" data-id="{$kKonfiggruppe}">
                                {* {col cols=12 data=["id"=>$kKonfiggruppe] class="config-option-dropdown"} *}
                                {block name='productdetails-config-container-group-item-type-dropdown-select'}
                                    {formgroup}
                                        {select name="item[{$kKonfiggruppe}][]"
                                        data=["ref"=>$kKonfiggruppe]
                                        required=$configGroup->getMin() > 0
                                        aria=["label"=>$configLocalization->getName()]
                                        class='custom-select'
                                        }
                                            <option value="">{lang key='pleaseChoose'}</option>
                                        {foreach $configGroup->oItem_arr as $oItem}
                                            {$bSelectable = 0}
                                            {if $oItem->isInStock()}
                                                {$bSelectable = 1}
                                            {/if}
                                            <option value="{$oItem->getKonfigitem()}"
                                                    id="item{$oItem->getKonfigitem()}"
                                                    {if empty($bSelectable)} disabled{/if}
                                                    {if isset($nKonfigitem_arr)} data-selected="{if in_array($oItem->getKonfigitem(), $nKonfigitem_arr)}true{else}false{/if}"
                                                    {else}{if $oItem->getSelektiert() && empty($aKonfigerror_arr)}selected="selected"{/if}{/if}>
                                                {$oItem->getName()}{if empty($bSelectable)} - {lang section='productDetails' key='productOutOfStock'}{/if}
                                                {if JTL\Session\Frontend::getCustomerGroup()->mayViewPrices()}
                                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                                    {if $oItem->hasRabatt() && $oItem->showRabatt()}({$oItem->getRabattLocalized()} {lang key='discount'})&nbsp;{elseif $oItem->hasZuschlag() && $oItem->showZuschlag()}({$oItem->getZuschlagLocalized()} {lang key='additionalCharge'})&nbsp;{/if}
                                                    {$oItem->getPreisLocalized()}
                                                {/if}
                                            </option>
                                        {/foreach}
                                        {/select}
                                    {/formgroup}
                                {/block}
                                {* {/col} *}
                                {* {col class="cfg-dropdown-item-description"} *}
                                <div class="cfg-dropdown-item-description">
                                {foreach $configGroup->oItem_arr as $oItem}
                                    {$bSelectable = 0}
                                    {if $oItem->isInStock()}
                                        {$bSelectable = 1}
                                    {/if}
                                    {$cKurzBeschreibung = $oItem->getKurzBeschreibung()}
                                    {$cBeschreibung = $oItem->getBeschreibung()}
                                    {if !empty($cKurzBeschreibung)}
                                        {$cBeschreibung = $cKurzBeschreibung}
                                    {/if}
                                    {block name='productdetails-config-container-group-item-type-dropdown-collapse'}
                                        {collapse visible=isset($nKonfigitem_arr) && in_array($oItem->getKonfigitem(), $nKonfigitem_arr)
                                        id="drpdwn_qnt_{$oItem->getKonfigitem()}"
                                        class="cfg-drpdwn-item{if $oItem->getSelektiert() && empty($aKonfigerror_arr)} show{/if}"}
                                            {include file='productdetails/config_item_description.tpl'}
                                        {/collapse}
                                    {/block}
                                {/foreach}
                                </div>
                                {* {/col} *}
                                </li>
                            {/block}
                        {/if}
                        {* {/row} *}
                        </ul>

                    {/block}
                    {* {block name='productdetails-config-bottom'}
                        {if $Einstellungen.template.productdetails.config_layout !== 'list'}
                        <div class="sticky-bottom">
                            {if $configGroup@last}
                                {nav}
                                    {navitem id="cfg-tab-summary-finish"
                                        href="#cfg-tab-pane-summary"
                                        role="tab"
                                        router-data=["toggle"=>"pill"]
                                        router-aria=["controls"=>"cfg-tab-pane-summary", "selected"=>"false"]
                                        router-class="btn btn-secondary btn-sm"
                                        disabled=true
                                    }
                                        {lang key='finishConfiguration' section='productDetails'}
                                    {/navitem}
                                {/nav}
                            {/if}
                        </div>
                        {/if}
                    {/block} *}

                {/block}
            </div>
        {/if}
    {/foreach}

    </div>
{/block}
