{strip}
    {* This is the detail view for a subscription in the admin area *}
    <div class="row">
        <div class="col-12 col-xs-12">
            <div class="lpa-subscription-detail-wrapper">
                <div class="row">
                    <div class="col-12 col-xs-12">
                        <b>{__('lpaOrders')}</b>
                    </div>
                    <div class="col-12 col-xs-12">
                        <div class="lpa-subscription-table" style="font-size: 15px;">
                            <div class="lpa-subscription-table-head lpa-5-cols">
                                <div class="lpa-subscription-table-column">{__('lpaOrderNumber')}</div>
                                <div class="lpa-subscription-table-column">{__('lpaOrderStatusShop')}</div>
                                <div class="lpa-subscription-table-column">{__('lpaSubscriptionChargeId')}</div>
                                <div class="lpa-subscription-table-column">{__('lpaSubscriptionChargeStatus')}</div>
                                <div class="lpa-subscription-table-column">{__('lpaAmount')}</div>
                            </div>
                            {foreach $lpaSubscription->orders as $order}
                                <div class="lpa-subscription-order-item-row lpa-5-cols">
                                    <div class="lpa-subscription-table-column">{$order->cBestellNr|default:'-'}<span class="lpa-info-hint">({if $order->kBestellung == $lpaSubscription->shopOrderId}{__('lpaSubscriptionOrderInitial')}{else}{__('lpaSubscriptionOrderRecurring')}{/if})</span></div>
                                    <div class="lpa-subscription-table-column">
                                        {if isset($order->cStatus)}
                                            {if $order->cStatus === "-1"}
                                                {__('lpaOrderStatusCancelled')}
                                            {elseif $order->cStatus === "1"}
                                                {__('lpaOrderStatusOpen')}
                                            {elseif $order->cStatus === "2"}
                                                {__('lpaOrderStatusInProgress')}
                                            {elseif $order->cStatus === "3"}
                                                {__('lpaOrderStatusPaid')}
                                            {elseif $order->cStatus === "4"}
                                                {__('lpaOrderStatusShipped')}
                                            {elseif $order->cStatus === "5"}
                                                {__('lpaOrderStatusShippedPartially')}
                                            {else}
                                                {$order->cStatus}
                                            {/if}
                                        {else}
                                            -
                                        {/if}
                                    </div>
                                    <div class="lpa-subscription-table-column">
                                        {foreach $order->charges as $charge}
                                            <div>{$charge->chargeId}</div>
                                        {/foreach}
                                    </div>
                                    <div class="lpa-subscription-table-column">
                                        {foreach $order->charges as $charge}
                                            <div>{$charge->status}</div>
                                        {/foreach}
                                    </div>
                                    <div class="lpa-subscription-table-column">
                                        {foreach $order->charges as $charge}
                                            <div>{if isset($charge->chargeAmountAmount)}{$charge->chargeAmountAmount}{else} - {/if}{if isset($charge->chargeAmountCurrencyCode)} {$charge->chargeAmountCurrencyCode}{/if}</div>
                                        {/foreach}
                                    </div>
                                </div>
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