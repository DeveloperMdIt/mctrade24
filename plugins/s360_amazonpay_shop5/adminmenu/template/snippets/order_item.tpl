{strip}
    {$isSandbox = (mb_stripos($lpaOrder->chargePermissionId, 's') === 0 || mb_stripos($lpaOrder->chargePermissionId, 'c') === 0)}
    {$isSubscription = (mb_stripos($lpaOrder->chargePermissionId, 'b') === 0 || mb_stripos($lpaOrder->chargePermissionId, 'c') === 0)}
    <div class="lpa-order-item" data-order-reference-id="{$lpaOrder->chargePermissionId}" data-shop-order-id="{$lpaOrder->shopOrderId}">
        <div class="lpa-order-table-column lpa-shop-order-number">{if isset($lpaOrder->cBestellNr)}{$lpaOrder->cBestellNr}{elseif isset($lpaOrder->shopOrderNumber)}{$lpaOrder->shopOrderNumber}{else} - {/if}{if $isSubscription} ({__('lpaSubscriptionOrderInitial')}){/if}</div>
        <div class="lpa-order-table-column lpa-shop-order-status">
            {if isset($lpaOrder->cStatus)}
                {if $lpaOrder->cStatus === "-1"}
                    {__('lpaOrderStatusCancelled')}
                {elseif $lpaOrder->cStatus === "1"}
                    {__('lpaOrderStatusOpen')}
                {elseif $lpaOrder->cStatus === "2"}
                    {__('lpaOrderStatusInProgress')}
                {elseif $lpaOrder->cStatus === "3"}
                    {__('lpaOrderStatusPaid')}
                {elseif $lpaOrder->cStatus === "4"}
                    {__('lpaOrderStatusShipped')}
                {elseif $lpaOrder->cStatus === "5"}
                    {__('lpaOrderStatusShippedPartially')}
                {else}
                    {$lpaOrder->cStatus}
                {/if}
            {else}
                -
            {/if}
        </div>
        <div class="lpa-order-table-column lpa-amazon-charge-permission-id{if isset($lpaOrder->chargePermissionId) && $isSandbox} lpa-sandbox{/if}">{if isset($lpaOrder->chargePermissionId)}{$lpaOrder->chargePermissionId}{else} - {/if}</div>
        <div class="lpa-order-table-column lpa-amazon-order-status lpa-status-{mb_strtolower($lpaOrder->status)}{if isset($lpaOrder->statusReason) && !empty($lpaOrder->statusReason)} lpa-status-reason-{mb_strtolower($lpaOrder->statusReason)}{/if}">{if isset($lpaOrder->status)}{$lpaOrder->status}{if isset($lpaOrder->statusReason) && !empty($lpaOrder->statusReason)} ({$lpaOrder->statusReason}){/if}{else} - {/if}</div>
        <div class="lpa-order-table-column lpa-order-total">{if isset($lpaOrder->chargeAmountLimitAmount)}{if $isSubscription} {__('lpaSubscriptionMaxAmountPrefix')} {/if}{$lpaOrder->chargeAmountLimitAmount}{if isset($lpaOrder->chargeAmountLimitCurrencyCode)} {$lpaOrder->chargeAmountLimitCurrencyCode}{/if}{if $isSubscription} {__('lpaSubscriptionMaxAmountPostfix')}{/if}{else} - {/if}</div>
        <div class="lpa-order-table-column lpa-expiration-date">{if isset($lpaOrder->expirationTimestamp)}{$expirationTimestampTime=strtotime($lpaOrder->expirationTimestamp)}{$expirationTimestampTime|date_format:"d.m.Y"}{else} - {/if}</div>
        <div class="lpa-order-table-column lpa-order-action">
            <div class="btn-group">
                <button type="button" class="btn btn-xs btn-primary" title="{__('lpaOrderRefreshCTA')}" onclick="window.lpaOrderManagement.refreshChargePermission('{$lpaOrder->chargePermissionId}');"><i class="fa fas fa-sync fa-refresh" aria-hidden="true"></i></button>
                <button type="button" class="btn btn-xs btn-default" title="{__('lpaOrderDetailsCTA')}" onclick="window.lpaOrderManagement.getChargePermission('{$lpaOrder->chargePermissionId}');"><i class="fa fas fa-pen fa-pencil" aria-hidden="true"></i></button>
            </div>
        </div>
    </div>
    <div class="lpa-order-item-detail-row" >
        <div class="lpa-order-item-detail-content">
            <div class="lpa-order-item-detail" data-charge-permission-id="{$lpaOrder->chargePermissionId}" style="display:none;"></div>
        </div>
    </div>
{/strip}