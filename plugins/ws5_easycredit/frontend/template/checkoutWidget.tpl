<div class="ec-payment-option-content"  style="display: none; margin-bottom: 30px;">
    <easycredit-checkout id="easycredit_checkout_component" amount="{$ecAmount}" webshop-id="{$ecWebshopId}" is-active="true" {if isset($ecAlert)}alert="{$ecAlert}"{/if}/>
    <input id="easycredit_installment_plan" type="hidden" name="easycredit_installment_plan" value="2" />
    <script type="text/javascript">
        {literal}
        $(function(){
            let ecSelectorInstallment = {/literal}'{$wsEcSelector}'{literal};
            let ecSelectorInvoice = {/literal}'{$wsEcSelectorInvoice}'{literal};

            if ($(ecSelectorInstallment + " input[name=\"Zahlungsart\"]").is(":checked")) {
                $(".ec-payment-option-content").show();
                $(".checkout-shipping-form button").attr("disabled", true);
            }

            $("input[type=\"radio\"]").on('change', function() {
                if ($(this).parents(ecSelectorInstallment).length === 0) {
                    $(".ec-payment-option-content").hide();
                }
                if (!$(ecSelectorInvoice + " input[name=\"Zahlungsart\"]").is(":checked") && !$(ecSelectorInstallment + " input[name=\"Zahlungsart\"]").is(":checked")) {
                    $(".checkout-shipping-form button").attr("disabled", false);
                }
            });

            $(ecSelectorInstallment).on('click', function() {
                if ($(this).find("input[name=\"Zahlungsart\"]").is(":checked")) {
                    $(".ec-payment-option-content").show();
                    $(".checkout-shipping-form button").attr("disabled", true);
                } else {
                    $(".ec-payment-option-content").hide();
                    if (!$(ecSelectorInvoice + " input[name=\"Zahlungsart\"]").is(":checked")) {
                        $(".checkout-shipping-form button").attr("disabled", false);
                    }
                }
            });

            $('#easycredit_checkout_component').on('submit', function(event) {
                let newURL = window.location.protocol + "//" + window.location.host + "/" + "/bestellvorgang.php?editZahlungsart=1";
                history.pushState(null, null, newURL);
                let installmentPlans = event.detail.numberOfInstallments;
                $('#easycredit_installment_plan').val(installmentPlans);
                $(ecSelectorInstallment).parents("form").submit();
            });

            let eCcomponent = document.querySelector('#easycredit_checkout_component');
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

