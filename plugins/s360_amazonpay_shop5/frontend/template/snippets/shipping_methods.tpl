{strip}
    <div class="row">
        <div class="col-xs-12 col-12">
            <div class="panel-wrap">
                <fieldset id="amazonpay-checkout-shipping-payment">
                    <legend>{lang section='global' key='shippingOptions'}</legend>
                    <div class="row bottom15 form-group">
                        {if !isset($Versandarten) || empty($Versandarten)}
                            <div class="col-xs-12 col-12">
                                <div class="alert alert-danger">{$oPlugin->getLocalization()->getTranslation('no_shipping_method')}</div>
                            </div>
                        {else}
                            {foreach $Versandarten as $versandart}
                                <div id="shipment_{$versandart->kVersandart}" class="col-xs-12 col-12">
                                    <div class="radio custom-control custom-radio">
                                        <input name="Versandart" value="{$versandart->kVersandart}" type="radio" class="custom-control-input radio-checkbox" id="del{$versandart->kVersandart}"
                                                {if $Versandarten|@count == 1
                                                || (isset($smarty.session.AktiveVersandart) && $smarty.session.AktiveVersandart  == $versandart->kVersandart)
                                                || !isset($smarty.session.AktiveVersandart) && $versandart@first} checked{/if}
                                                {if $versandart@first} required{/if}>
                                        <label for="del{$versandart->kVersandart}" class="btn-block custom-control-label">
                                            <div class="row">
                                                <div class="col-xs-6 col-6 col-sm-6">
                                                    <div class="row">
                                                        <div class="col-xs-12 col-12">
                                                            {if $versandart->cBild}
                                                                <img class="img-responsive-width img-sm mr-2" src="{$versandart->cBild}" alt="{$versandart->angezeigterName|trans}">
                                                            {/if}
                                                            <span class="content">
                                                                <span class="title">{$versandart->angezeigterName|trans}&nbsp;</span>
                                                            </span>
                                                        </div>
                                                        {if !empty($versandart->angezeigterHinweistext)}
                                                            <div class="col-xs-12 col-12 mt-1">
                                                                <span class="small text-muted">
                                                                    {$versandart->angezeigterHinweistext|trans}
                                                                </span>
                                                            </div>
                                                        {/if}
                                                    </div>
                                                </div>
                                                <div class="col-xs-3 col-3 col-sm-4">
                                                    {if isset($versandart->specificShippingcosts_arr)}
                                                        {foreach $versandart->specificShippingcosts_arr as $specificShippingcosts}
                                                            <div class="row">
                                                                <div class="col-xs-8 col-8 col-md-9 col-lg-9">
                                                                    <ul>
                                                                        <li>
                                                                            <small>{$specificShippingcosts->cName|trans}</small>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                                <div class="col-xs-4 col-4 col-md-3 col-lg-3 text-right">
                                                                    <small>
                                                                        {$specificShippingcosts->cPreisLocalized}
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        {/foreach}
                                                    {/if}
                                                    {if !empty($versandart->Zuschlag->fZuschlag)}
                                                        <span class="btn-block">
                                                            <small>{$versandart->Zuschlag->angezeigterName|trans}
                                                                (+{$versandart->Zuschlag->cPreisLocalized})
                                                            </small>
                                                        </span>
                                                    {/if}
                                                    {if !empty($versandart->cLieferdauer|trans) && $Einstellungen.global.global_versandermittlung_lieferdauer_anzeigen === 'Y'}
                                                        <span class="btn-block">
                                                            <small>{lang key='shippingTimeLP' section='global'}
                                                                : {$versandart->cLieferdauer|trans}</small>
                                                        </span>
                                                    {/if}
                                                </div>
                                                <div class="col-xs-3 col-3 col-sm-2">
                                                    <div class="shipping-cost text-right">{$versandart->cPreisLocalized}</div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            {/foreach}
                        {/if}
                    </div>
                </fieldset>
            </div>
            <div class="panel-wrap">
                <fieldset>
                    {if isset($Verpackungsarten) && $Verpackungsarten|@count > 0}
                        <legend>{lang section='checkout' key='additionalPackaging'}</legend>
                        <div class="row bottom15 form-group">
                            {foreach $Verpackungsarten as $oVerpackung}
                                <div id="packaging_{$oVerpackung->kVerpackung}" class="col-xs-12 col-12">
                                    <div class="checkbox custom-control custom-checkbox">
                                        <input name="kVerpackung[]" type="checkbox" class="radio-checkbox custom-control-input" value="{$oVerpackung->kVerpackung}" id="pac{$oVerpackung->kVerpackung}" {if isset($oVerpackung->bWarenkorbAktiv) && $oVerpackung->bWarenkorbAktiv === true || (isset($AktiveVerpackung[$oVerpackung->kVerpackung]) && $AktiveVerpackung[$oVerpackung->kVerpackung] === 1)}checked{/if}/>
                                        <label for="pac{$oVerpackung->kVerpackung}" class="btn-block control-label custom-control-label">
                                            <span class="content">
                                                <span class="title">{$oVerpackung->cName}</span>
                                            </span>
                                            <span class="pull-right float-right">
                                                {if $oVerpackung->nKostenfrei == 1}{lang key='ExemptFromCharges' section='global'}{else}{$oVerpackung->fBruttoLocalized}{/if}
                                            </span>
                                            <span class="btn-block">
                                                <small>{$oVerpackung->cBeschreibung}</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    {/if}
                </fieldset>
            </div>
        </div>
    </div>
{/strip}