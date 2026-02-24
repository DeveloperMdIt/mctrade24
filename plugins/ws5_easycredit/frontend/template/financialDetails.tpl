<hr/>
<div class="row checkout-items-total-total">
	<div class="col ">
		<span class="price_label">{$interestValueTitle}</span>
	</div>                            
	<div class="col col-auto ml-auto text-right price-col">
		<strong class="price total-sum">{str_replace('.', ',', (str_replace(',', '.', $interestValue)|string_format:"%.2f"))}{* This ensures the number always has two decimal places *} {$currency}</strong>
	</div>
</div>
<hr/>
<div class="row checkout-items-total-total">
	<div class="col ">
		<span class="price_label"><strong>{$totalValueTitle}</strong></span>
	</div>                            
	<div class="col col-auto ml-auto text-right price-col">
		<strong class="price total-sum">{str_replace('.', ',', (str_replace(',', '.', $totalValue)|string_format:"%.2f"))}{* This ensures the number always has two decimal places *} {$currency}</strong>
	</div>
</div>	