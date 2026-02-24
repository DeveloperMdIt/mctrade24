<div class="ec-payment-option-content">
	<select id="easycredit_installment_plan" name="easycredit_installment_plan" class="form-control custom-select">
		{foreach key=key item=plan from=$installmentPlans}
			<option value="{$plan->getNumberOfInstallments()}">
                    {$plan->getNumberOfInstallments()} Raten x {$plan->getInstallment()} &euro;
			</option>
		{/foreach}
	</select>

	<script type="text/javascript" data-cmp-ab="2" data-eucookie-id="ws5_easy_credit_frontend">
		{literal}

		$(function(){


			/*
			if ($(selector + " input[name=\"Zahlungsart\"]").is(":checked")) {
				$("#easycredit_consent_box").show();
				$(".ec-payment-option-content").show();
			}

			$("input[type=\"radio\"]").on('change', function() {
				if ($(this).parents(selector).length === 0) {
					$("#easycredit_consent_box").hide();
					$(".ec-payment-option-content").hide();
				}
			});

			$(selector).on('click', function() {
				if ($(this).find("input[name=\"Zahlungsart\"]").is(":checked")) {
					$("#easycredit_consent_box").show();
					$(".ec-payment-option-content").show();
				} else {
					$("#easycredit_consent_box").hide();
					$(".ec-payment-option-content").hide();
				}
			});

			$("#easycredit_checkout_component").on("submit", function() {
				console.log($('input[name="easycredit-duration"]').selected);
				//$(selector).parents("form").submit();
			});

			$(selector).parents("form").find("[type=\"submit\"]").on('click', function() {
				if ($(selector + " input[name=\"Zahlungsart\"]").is(":checked")) {
					let newURL = window.location.protocol + "//" + window.location.host + "/" + "/bestellvorgang.php?editZahlungsart=1";
					history.pushState(null, null, newURL);
					return true;
				}
			});

			 */
		});
		{/literal}
	</script>
</div>