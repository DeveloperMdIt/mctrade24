<div class="ec-payment-option-content-invoice"  style="display: none; margin-bottom: 30px;">
    <easycredit-checkout id="easycredit_rechnung_component" amount="{$ecAmount}" webshop-id="{$ecWebshopIdInvoice}" is-active="true" payment-type="BILL" {if isset($ecAlert)}alert="{$ecAlert}"{/if}/>
    <script type="text/javascript">
        {literal}
        $(function(){
            let ecSelectorInvoice = {/literal}'{$wsEcSelectorInvoice}'{literal};
            let ecSelectorInstallment = {/literal}'{$wsEcSelector}'{literal};

            if ($(ecSelectorInvoice + " input[name=\"Zahlungsart\"]").is(":checked")) {
                $(".ec-payment-option-content-invoice").show();
                $(".checkout-shipping-form button").attr("disabled", true);
            }

            $("input[type=\"radio\"]").on('change', function() {
                if ($(this).parents(ecSelectorInvoice).length === 0) {
                    $(".ec-payment-option-content-invoice").hide();
                }
                if (!$(ecSelectorInvoice + " input[name=\"Zahlungsart\"]").is(":checked") && !$(ecSelectorInstallment + " input[name=\"Zahlungsart\"]").is(":checked")) {
                    $(".checkout-shipping-form button").attr("disabled", false);
                }
            });

            $(ecSelectorInvoice).on('click', function() {
                if ($(this).find("input[name=\"Zahlungsart\"]").is(":checked")) {
                    $(".ec-payment-option-content-invoice").show();
                    $(".checkout-shipping-form button").attr("disabled", true);
                } else {
                    $(".ec-payment-option-content-invoice").hide();
                    if (!$(ecSelectorInstallment + " input[name=\"Zahlungsart\"]").is(":checked")) {
                        $(".checkout-shipping-form button").attr("disabled", false);
                    }
                }
            });

            $('#easycredit_rechnung_component').on('submit', function(event) {
                let newURL = window.location.protocol + "//" + window.location.host + "/" + "/bestellvorgang.php?editZahlungsart=1";
                history.pushState(null, null, newURL);
                $(ecSelectorInvoice).parents("form").submit();
            });

            let eCcomponent = document.querySelector('#easycredit_rechnung_component');
            let eCshadowRoot = eCcomponent.shadowRoot;
            let eCstyle = document.createElement('style');
            eCstyle.textContent = `
                /* Eigenes CSS hier einfügen */

                `;
            if ({/literal}{$wsEcCheckoutFullWidth}{literal}) {
                eCstyle.textContent += ` .ec-checkout-container .ec-checkout { width: 100%; }`;
            }

            eCshadowRoot.prepend(eCstyle);
        });
        {/literal}
    </script>
</div>
