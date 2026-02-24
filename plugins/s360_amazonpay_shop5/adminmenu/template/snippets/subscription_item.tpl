{strip}
    {$isSandbox = (mb_stripos($lpaSubscription->chargePermissionId, 's') === 0 || mb_stripos($lpaSubscription->chargePermissionId, 'c') === 0)}
    <div class="lpa-subscription-item lpa-8-cols" data-subscription-id="{$lpaSubscription->id}" data-shop-order-id="{$lpaSubscription->shopOrderId}" data-customer-id="{$lpaSubscription->jtlCustomerId}">
        <div class="lpa-subscription-table-column lpa-subscription-id">{if isset($lpaSubscription->id)}{$lpaSubscription->id}{else} - {/if}</div>
        <div class="lpa-subscription-table-column lpa-subscription-customer-mail">{if !empty($lpaSubscription->jtlCustomerId) && !empty($lpaSubscription->customer->cMail)}{$lpaSubscription->customer->cMail}{else} - {/if}</div>
        <div class="lpa-subscription-table-column lpa-shop-initial-order-number">{if isset($lpaSubscription->shopOrderNumber)}{$lpaSubscription->shopOrderNumber}{else} - {/if}</div>
        <div class="lpa-subscription-table-column lpa-subscription-interval">{if isset($lpaSubscription->interval)}{$lpaSubscription->interval}{else} - {/if}</div>
        <div class="lpa-subscription-table-column lpa-subscription-status">{if isset($lpaSubscription->status)}{$lpaSubscription->status}{else} - {/if}{if !empty($lpaSubscription->statusReason)} ({$lpaSubscription->statusReason}){/if}</div>
        <div class="lpa-subscription-table-column lpa-amazon-charge-permission-id{if $isSandbox} lpa-sandbox{/if}">{if isset($lpaSubscription->chargePermissionId)}{$lpaSubscription->chargePermissionId}{else} - {/if}</div>
        <div class="lpa-subscription-table-column lpa-subscription-next">{if $lpaSubscription->status === 'active'}{date("H:i:s - d.m.Y", $lpaSubscription->nextOrderTimestamp)}{else} - {/if}</div>
        <div class="lpa-subscription-table-column lpa-subscription-action">
            <button type="button" class="btn btn-sm btn-default" title="{__('lpaSubscriptionDetailsCTA')}" onclick="window.lpaSubscriptionManagement.getSubscriptionDetail('{$lpaSubscription->id}');"><i class="fa fas fa-pen fa-pencil" aria-hidden="true"></i></button>
            <div class="btn-group" style="margin-left: 5px;">
                {if $lpaSubscription->status !== 'canceled'}<button type="button" class="btn btn-sm btn-danger" title="{__('lpaSubscriptionCancel')}" onclick="window.lpaSubscriptionManagement.cancelSubscription('{$lpaSubscription->id}', '{$lpaSubscription->shopOrderNumber}');"><i class="fa fas fa-ban" aria-hidden="true"></i></button>{/if}
                {if $lpaSubscription->status !== 'paused' && $lpaSubscription->status !== 'canceled'}<button type="button" class="btn btn-sm btn-warning" title="{__('lpaSubscriptionPause')}" onclick="window.lpaSubscriptionManagement.pauseSubscription('{$lpaSubscription->id}')"><i class="fa fas fa-pause"></i></button>{/if}
                {if $lpaSubscription->status === 'paused'}<button type="button" class="btn btn-sm btn-success" title="{__('lpaSubscriptionResume')}" onclick="window.lpaSubscriptionManagement.resumeSubscription('{$lpaSubscription->id}')"><i class="fa fas fa-play"></i></button>{/if}
                {if $lpaSubscription->status === 'active'}<button type="button" class="btn btn-sm btn-success" title="{__('lpaSubscriptionCreateOrder')}" onclick="window.lpaSubscriptionManagement.createOrderForSubscription('{$lpaSubscription->id}');"><i class="fa fas fa-cart-plus" aria-hidden="true"></i></button>{/if}
            </div>
        </div>
    </div>
    <div class="lpa-subscription-item-detail-row" >
        <div class="lpa-subscription-item-detail-content">
            <div class="lpa-subscription-item-detail" data-subscription-id="{$lpaSubscription->id}" style="display:none;">
                {include file="{$lpaAdminGlobal.adminTemplatePath}snippets/subscription_detail.tpl" lpaSubscription=$lpaSubscription}
            </div>
        </div>
    </div>
{/strip}