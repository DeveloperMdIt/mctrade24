{block name='clerk_sales_tracking'}
    {if !empty($Bestellung)}

        {block name='clerk_sales_tracking_api'}
            <span
                    class="clerk"
                    data-api="log/sale"
                    data-sale="{$Bestellung->cBestellNr}"
                    data-email="{$s360_clerk_sales_tracking.email}"
                    data-products='{json_encode($s360_clerk_sales_tracking.positions) nofilter}'>
            </span>
        {/block}
    {/if}
{/block}
