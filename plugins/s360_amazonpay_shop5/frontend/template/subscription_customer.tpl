<div class="container lpa-conditional-container">
    <script type="text/javascript">
        var lpaSubscriptionCancelConfirm = function lpaSubscriptionCancelConfirm(chargePermissionId) {
            return window.confirm('{$lpaSubscription.translations['subscription_customer_confirm_cancel']|escape:'quotes'} ' + chargePermissionId);
        };
    </script>
    <div class="row">
        <div class="col-12 col-xs-12">
            {$lpaSubscription.translations['subscription_customer_description']}
        </div>
        {if !$lpaSubscription.subscriptionsActive}
            <div class="col-12 col-xs-12">
                <div class="alert alert-warning">
                    {$lpaSubscription.translations['subscription_customer_subscriptions_disabled']}
                </div>
            </div>
        {/if}
        <div class="col-12 col-xs-12">
            <table class="lpa-subscriptions-table table">
                {foreach $lpaSubscription.subscriptions as $subscription}
                    {if $subscription@first}
                        <thead class="lpa-subscriptions-table-row lpa-subscriptions-table-head">
                        <tr>
                            <th class="lpa-subscriptions-table-column">{$lpaSubscription.translations['subscription_customer_charge_permission_id']}</th>
                            <th class="lpa-subscriptions-table-column">{$lpaSubscription.translations['subscription_customer_interval']}</th>
                            <th class="lpa-subscriptions-table-column">{$lpaSubscription.translations['subscription_customer_orders']}</th>
                            <th class="lpa-subscriptions-table-column">{$lpaSubscription.translations['subscription_customer_status']}</th>
                            <th class="lpa-subscriptions-table-column">{$lpaSubscription.translations['subscription_customer_next_order_time']}</th>
                            <th class="lpa-subscriptions-table-column">{$lpaSubscription.translations['subscription_customer_action']}</th>
                        </tr>
                        </thead>
                    {/if}
                    <tr class="lpa-subscriptions-table-row" data-subscription-id="{$subscription->getId()}">
                        <td class="lpa-subscriptions-table-column">{$subscription->getChargePermissionId()}</td>
                        <td class="lpa-subscriptions-table-column">
                            {if $subscription->getInterval() !== null}
                                {$subscription->getInterval()->toDisplayString()}
                            {/if}
                        </td>
                        <td class="lpa-subscriptions-table-column">
                            {if isset($lpaSubscription.orders[$subscription->getId()])}
                                {foreach $lpaSubscription.orders[$subscription->getId()] as $order}
                                    <div class="lpa-subscriptions-order{if $order->shopOrderId === $order->initialShopOrderId} lpa-subscriptions-initial-order{else} lpa-subscriptions-recurring-order{/if}" data-order-id="{$order->kBestellung}">
                                        <a href="{get_static_route id='jtl.php'}?bestellung={$order->kBestellung}" target="_blank" title="{lang key='showOrder' section='login'}: {lang key='orderNo' section='login'} {$order->cBestellNr}">{$order->cBestellNr}</a> {if $subscription->getShopOrderId() == $order->kBestellung}({$lpaSubscription.translations['subscription_customer_order_initial']}){else}({$lpaSubscription.translations['subscription_customer_order_recurring']}){/if}
                                    </div>
                                {/foreach}
                            {/if}
                        </td>
                        <td class="lpa-subscriptions-table-column">
                            {if $subscription->getStatus() === 'active'}
                                <span class="text-success">
                                    {$lpaSubscription.translations['subscription_customer_status_active']}
                                </span>
                            {elseif $subscription->getStatus() === 'canceled'}
                                <span class="text-info">
                                        {$lpaSubscription.translations['subscription_customer_status_canceled']}
                                    </span>
                            {elseif $subscription->getStatus() === 'paused'}
                                <span class="text-warning">
                                    {$lpaSubscription.translations['subscription_customer_status_paused']}
                                </span>
                            {else}
                                <span class="text-info">
                                    {$lpaSubscription.translations['subscription_customer_status_unknown']}
                                </span>
                            {/if}
                        </td>
                        <td class="lpa-subscriptions-table-column">
                            {if $subscription->getStatus() === 'active'}{date("H:i:s - d.m.Y", $subscription->getNextOrderTimestamp())}{else}-{/if}
                        </td>
                        <td class="lpa-subscriptions-table-column">
                            {if $subscription->getStatus() !== 'canceled'}
                                <form method="post" onsubmit="return lpaSubscriptionCancelConfirm('{$subscription->getChargePermissionId()}');">
                                    {$jtl_token}
                                    <input type="hidden" name="subscriptionId" value="{$subscription->getId()}"/>
                                    <input type="hidden" name="action" value="cancelSubscription"/>
                                    <button class="btn btn-danger lpa-subscription-cancel" type="submit">{$lpaSubscription.translations['subscription_customer_action_cancel']}</button>
                                </form>
                                {else}
                                -
                            {/if}
                        </td>
                    </tr>
                    {foreachelse}
                    <i>{$lpaSubscription.translations['subscription_customer_no_subscriptions']}</i>
                {/foreach}
            </table>
        </div>
    </div>
</div>