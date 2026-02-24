{strip}
    {* This is the detail view for an order in the admin area *}
    {$isSubscription = (mb_stripos($lpaOrder->chargePermissionId, 'b') === 0 || mb_stripos($lpaOrder->chargePermissionId, 'c') === 0)}
    <div class="row">
        <div class="col-12 col-xs-12">
            <div class="lpa-order-detail-wrapper">
                <div class="row">
                    <div class="col-12 col-xs-12">
                        <h4>{__('lpaDetailsFor')} {$lpaOrder->chargePermissionId}</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-xs-12">
                        <form class="lpa-order-actions">
                            <div class="row">
                                {if $lpaOrder->status === 'Chargeable'}
                                    <div class="col-12 col-xs-12 col-md-6">
                                        <div class="input-group">
                                            <input class="form-control" type="number" value="{$lpaOrder->chargeAmountLimitAmount}" name="amount" placeholder="{__('lpaAmount')}"/>
                                            <div class="input-group-append input-group-btn">
                                                <button type="button" class="btn btn-default" disabled="disabled">{$lpaOrder->chargeAmountLimitCurrencyCode}</button>
                                                <button type="button" class="btn btn-primary" onclick="lpaOrderManagement.createCharge('{$lpaOrder->chargePermissionId}', $(this).closest('form').find('[name=amount]').val(),'{$lpaOrder->chargeAmountLimitCurrencyCode}');">{__('lpaCreateChargeCTA')}</button>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                                {if $lpaOrder->status !== 'Closed'}
                                    <div class="col-12 col-xs-12 col-md-6">
                                        <button type="button" class="btn btn-danger" onclick="lpaOrderManagement.cancelChargePermission('{$lpaOrder->chargePermissionId}');">{__('lpaCancelChargePermissionCTA')}</button>
                                        <button type="button" class="btn btn-primary" onclick="lpaOrderManagement.closeChargePermission('{$lpaOrder->chargePermissionId}');">{__('lpaCloseChargePermissionCTA')}</button>
                                    </div>
                                {/if}
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-xs-12">
                        <b>{__('lpaCharges')}</b>
                    </div>
                    <div class="col-12 col-xs-12">
                        <div class="lpa-charges-table" style="font-size: 15px;">
                            <div class="lpa-order-table-head lpa-{if $isSubscription}8{else}7{/if}-cols">
                                <div class="lpa-order-table-column">{__('lpaChargeId')}</div>
                                {if $isSubscription}<div class="lpa-order-table-column">{__('lpaOrderNumber')}</div>{/if}
                                <div class="lpa-order-table-column">{__('lpaChargeStatus')}</div>
                                <div class="lpa-order-table-column">{__('lpaAmount')}</div>
                                <div class="lpa-order-table-column">{__('lpaAmountCaptured')}</div>
                                <div class="lpa-order-table-column">{__('lpaAmountRefunded')}</div>
                                <div class="lpa-order-table-column">{__('lpaExpirationDate')}</div>
                                <div class="lpa-order-table-column">{__('lpaAction')}</div>
                            </div>
                            {foreach from=$lpaOrder->charges item='charge' name='charge'}
                                <div class="lpa-charge-item-row lpa-7-cols">
                                    <div class="lpa-order-table-column">{$charge->chargeId}</div>
                                    {if $isSubscription}
                                        <div class="lpa-order-table-column lpa-shop-order-number">
                                            {if isset($charge->order->cBestellNr)}
                                                {$charge->order->cBestellNr} ({if $charge->order->cBestellNr === $lpaOrder->shopOrderNumber}{__('lpaSubscriptionOrderInitial')}{else}{__('lpaSubscriptionOrderRecurring')}{/if})
                                            {else} - {/if}
                                        </div>
                                    {/if}
                                    <div class="lpa-order-table-column lpa-charge-status lpa-status-{mb_strtolower($charge->status)}{if isset($charge->statusReason) && !empty($charge->statusReason)} lpa-status-reason-{mb_strtolower($charge->statusReason)}{/if}">
                                        {if isset($charge->status)}{$charge->status}{if isset($charge->statusReason) && !empty($charge->statusReason)} ({$charge->statusReason}){/if}{else} - {/if}
                                    </div>
                                    <div class="lpa-order-table-column lpa-charge-total">{if isset($charge->chargeAmountAmount)}{$charge->chargeAmountAmount}{else} - {/if}{if isset($charge->chargeAmountCurrencyCode)} {$charge->chargeAmountCurrencyCode}{/if}</div>
                                    <div class="lpa-order-table-column lpa-charge-captured">{if isset($charge->captureAmountAmount)}{$charge->captureAmountAmount}{else} - {/if}{if isset($charge->captureAmountCurrencyCode)} {$charge->captureAmountCurrencyCode}{/if}</div>
                                    <div class="lpa-order-table-column lpa-charge-refunded">{if isset($charge->refundedAmountAmount)}{$charge->refundedAmountAmount}{else} - {/if}{if isset($charge->refundedAmountCurrencyCode)} {$charge->refundedAmountCurrencyCode}{/if}</div>
                                    <div class="lpa-order-table-column {if $charge->status === 'AuthorizationInitiated' || $charge->status === 'Authorized'}{if isset($charge->expirationTimestamp) && (strtotime($charge->expirationTimestamp) - $smarty.now) < 604800} lpa-status-danger{elseif isset($charge->expirationTimestamp) && (strtotime($charge->expirationTimestamp) - $smarty.now) < 2419200} lpa-status-warning{/if}{/if}">
                                        {if isset($charge->expirationTimestamp)}
                                            {$expirationTimestampTime=strtotime($charge->expirationTimestamp)}
                                            {$expirationTimestampTime|date_format:"d.m.Y"}
                                        {else} - {/if}
                                    </div>
                                    <div class="lpa-order-table-column">
                                        <form class="lpa-authorization-actions">
                                            <div class="row">
                                                {if $charge->status === 'Authorized'}
                                                    <div class="col-12 col-xs-12">
                                                        <div class="input-group input-group-sm" style="margin-bottom: 0;">
                                                            <input class="form-control" type="number" value="{$charge->chargeAmountAmount}" name="amount" placeholder="{__('lpaAmount')}"/>
                                                            <div class="input-group-append input-group-btn">
                                                                <button type="button" class="btn btn-default" disabled="disabled">{$charge->chargeAmountCurrencyCode}</button>
                                                                <button type="button" class="btn btn-primary" onclick="lpaOrderManagement.captureCharge('{$charge->chargeId}', $(this).closest('form').find('[name=amount]').val(),'{$charge->chargeAmountCurrencyCode}');">{__('lpaCaptureChargeCTA')}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {/if}
                                                {if $charge->status === 'Authorized' || $charge->status === 'AuthorizationInitiated'}
                                                    <div class="col-12 col-xs-12 mt-1 text-right">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="lpaOrderManagement.cancelCharge('{$charge->chargeId}');">{__('lpaCancelChargeCTA')}</button>
                                                    </div>
                                                {/if}
                                                {if $charge->status === 'Captured'}
                                                    <div class="col-12 col-xs-12 mt-1 text-right">
                                                        <div class="input-group input-group-sm" style="margin-bottom: 0;">
                                                            <input class="form-control" type="number" value="{$charge->captureAmountAmount}" name="amount" placeholder="{__('lpaAmount')}"/>
                                                            <div class="input-group-append input-group-btn">
                                                                <button type="button" class="btn btn-default" disabled="disabled">{$charge->captureAmountCurrencyCode}</button>
                                                                <button type="button" class="btn btn-primary" onclick="lpaOrderManagement.createRefund('{$charge->chargeId}', $(this).closest('form').find('[name=amount]').val(),'{$charge->captureAmountCurrencyCode}');">{__('lpaCreateRefundCTA')}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                {/if}
                                                {if $charge->status !== 'Authorized' && $charge->status !== 'AuthorizationInitiated' && $charge->status !== 'Captured'}
                                                    <div class="col-12 col-xs-12">
                                                        <i>{__('lpaNoActionPossible')}</i>
                                                    </div>
                                                {/if}
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                {if !empty($charge->refunds)}
                                    <div class="lpa-refund-table" style="padding-left: 50px; padding-top: 10px;">
                                        <b>{__('lpaRefundsFor')} {$charge->chargeId}:</b>
                                        <div class="lpa-order-table-head lpa-3-cols">
                                            <div class="lpa-order-table-column">{__('lpaRefundId')}</div>
                                            <div class="lpa-order-table-column">{__('lpaRefundStatus')}</div>
                                            <div class="lpa-order-table-column">{__('lpaAmount')}</div>
                                        </div>
                                        {foreach from=$charge->refunds item='refund' name='refunds'}
                                            <div class="lpa-refund-item-row lpa-3-cols">
                                                <div class="lpa-order-table-column">{$refund->refundId}</div>
                                                <div class="lpa-order-table-column lpa-refund-status lpa-status-{mb_strtolower($refund->status)}{if isset($refund->statusReason) && !empty($refund->statusReason)} lpa-status-reason-{mb_strtolower($refund->statusReason)}{/if}">{if isset($refund->status)}{$refund->status}{if isset($refund->statusReason) && !empty($refund->statusReason)} ({$refund->statusReason}){/if}{else} - {/if}</div>
                                                <div class="lpa-order-table-column lpa-refund-total">{if isset($refund->refundAmountAmount)}{$refund->refundAmountAmount}{else} - {/if}{if isset($refund->refundAmountCurrencyCode)} {$refund->refundAmountCurrencyCode}{/if}</div>
                                            </div>
                                        {/foreach}
                                    </div>
                                {/if}
                                {foreachelse}
                                - {__('lpaNone')} -
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{/strip}