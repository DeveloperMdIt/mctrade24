<!-- doofinder add to cart script -->
<script> 
    class DoofinderAddToCartError extends Error {
        constructor(reason, status = "") {
            const message = "Error adding an item to the cart. Reason: " + reason + ". Status code: " + status;
            super(message);
            this.name = "DoofinderAddToCartError";
        }
    } 
    
    function addToCart(product_id, amount, statusPromise){
        product_id = parseInt(product_id);
        let properties = {
            jtl_token : {'"'}{$token}{'"'}
        };
        $.evo.io().call('pushToBasket', [product_id, amount, properties], this, function(error, data) {

            if(error) {
                statusPromise.reject(new DoofinderAddToCartError(error));
            }

            let response = data.response;

            if(response){
                switch (response.nType) {
                    case 0:
                        statusPromise.reject(new DoofinderAddToCartError(response.cHints.join(' , ')));
                        break;
                    case 1:
                        statusPromise.resolve("forwarding..");
                        window.location.href = response.cLocation;
                        break;
                    case 2:
                        statusPromise.resolve("The item has been successfully added to the cart.");
                        $.evo.basket().updateCart();
                        $.evo.basket().pushedToBasket(response);
                        break;
                }
            }
        })
    }
     
    document.addEventListener("doofinder.cart.add", function(event) {
        const { item_id, amount, grouping_id, link, statusPromise } = event.detail;

        $.ajax({
            url: "{$addToCartUrl}",
            type: "POST",
            data: {
                action: 'checkForVariations',
                id: item_id,
                link: link
            },
            success: function(response) {
                if (response == 'true') {
                    window.location.href = link;
                } else {
                    addToCart(item_id, amount, statusPromise);
                }
            },
        });   
    });
</script>