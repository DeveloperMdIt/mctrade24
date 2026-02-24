{if $bServicesActive === true}
    {if $availableDhlServices['error'] === false}
        <fieldset id="jtl_pack_optionen">
            <legend>DHL-Wunschzustellung-Optionen</legend>
            <div id="wunschpaket_options">
                {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_wunschtag_active') === 'Y'}
                    <div class="mb-3">
                        {if isset($smarty.get.deliveryday_invalid)}
                            <div class="alert alert-danger alert-dismissible fade show">
                                {$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_invalid_delivery_date')}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        {/if}
                        <div id="div_jtl_pack_wunschtag">
                            <div>
                                <div class="button-wrap">
                                    <input class="hidden radio-label dhl-wunschtag" type="radio" value="0"
                                           id="jtl_pack_wunschtag_value_0" name="jtl_pack_wunschtag_value" checked/>
                                    <label class="button-label" for="jtl_pack_wunschtag_value_0">
                                        <p>{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_no_wunschtag_value')}</p>
                                    </label>
                                    <span id="possibledays">
                                        {if isset($availableDhlServices) && is_array($availableDhlServices) && ($availableDhlServices['error'] == false)}
                                            {if isset($availableDhlServices['dhl_service']) && $availableDhlServices['dhl_service']->preferredDay->available === true}
                                                {foreach $availableDhlServices['dhl_service']->preferredDay->validDays as $day}
                                                    <input class="hidden radio-label dhl-wunschtag wz-value"
                                                           value="{$day->start|date_format:'d.m.Y'}" type="radio"
                                                           id="jtl_pack_wunschtag_value_{$day->start|date_format:'d.m.Y'}"
                                                           name="jtl_pack_wunschtag_value" {if ($smarty.session.wunschtag_selected|default:'') == $day->start|date_format:'d.m.Y'} checked{/if} />
                                                    <label class="button-label"
                                                           for="jtl_pack_wunschtag_value_{$day->start|date_format:'d.m.Y'}"><p>{$day->start|date_format:'d.m.Y'}</p></label>
                                                {/foreach}
                                            {/if}
                                        {/if}
                                    </span>
                                </div>
                                <div>
                                    {$additionalCostsAdvice}
                                </div>
                            </div>
                        </div>
                    </div>
                {/if}

                {if $jtlPackPlugin->getConfig()->getValue('jtl_pack_wunschort_active') === 'Y'
                    && (!isset($smarty.session.Lieferadresse->kLieferadresse) || $smarty.session.Lieferadresse->kLieferadresse == 0 || is_null($smarty.session.Lieferadresse->kLieferadresse))}
                    <div class="clearfix"></div>
                    <br />
                    <div class="col-xs-12">
                        <div class="form-group float-label-control">
                            <label for="jtl_pack_wunschort"
                                   class="control-label">{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_wunschort')}
                                :</label>
                            <input type="text" name="jtl_pack_wunschort"
                                   value="{$smarty.session.wunschlocation|default:''}"
                                   id="jtl_pack_wunschort" class="form-control"
                                   placeholder="{$jtlPackPlugin->getLocalization()->getTranslation('jtl_pack_wunschort_value')}">
                        </div>
                    </div>
                {/if}
            </div>
        </fieldset>
        <hr class="my-3">
    {else}
        <fieldset id="jtl_pack_optionen">
            <legend>DHL-Wunschzustellung-Optionen</legend>
            <div id="wunschpaket_error" class="well">
                <h5>{$jtlPackPlugin->oPluginSprachvariableAssoc_arr['jtl_pack_error_header']}</h5>
                <br/>
                <p>{$jtlPackPlugin->oPluginSprachvariableAssoc_arr['jtl_pack_error_text']}</p>
            </div>
        </fieldset>
    {/if}
{/if}
