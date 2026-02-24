<!-- Observer and callback for when the price changes without a page reload. Especially usefull for products with a "Konfigurator" -->
<script type="module">
    // Select the node that will be observed for mutations
    const targetNode = document.querySelector('.product-offer .price_wrapper meta[itemprop="price"]');

    // Callback function to execute when mutations are observed
    const callback = function (mutationsList) {
        for (let mutation of mutationsList) {
            if (mutation.type === "attributes" && mutation.attributeName === "content") {
                // Update "amount"-Attribute of <easycredit-widget>, if price changed and has a valid value
                const newPrice = targetNode?.getAttribute("content")
                if (typeof newPrice === "string" && newPrice.length !== 0) {
                    document.querySelector("easycredit-widget").setAttribute("amount", newPrice);
                }
            }
        }
    };

    // Create an observer instance linked to the callback function and start observing the target node for configured mutations
    new MutationObserver(callback).observe(targetNode, { attributes: true, childList: false, subtree: false });
</script>
<easycredit-widget amount="{$ecAmount}" webshop-id="{$ecWebshopId}" extended="{$widgetExtended}" display-type="{$widgetVariant}" style="margin: 20px 0;" payment-types="{$widgetPaymentTypes}"/>
{if $showBothWidgets}
    <easycredit-widget amount="{$ecAmount}" webshop-id="{$ecWebshopIdInvoice}" extended="{$widgetExtended}" display-type="{$widgetVariant}" style="margin: 20px 0;" payment-types="{$widgetPaymentTypes}"/>
{/if}
