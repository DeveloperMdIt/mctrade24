<div class="row dpflex-j-end">
    <div class="col-md-6 col-xl-5 ">
        <hr style="margin: 0.5rem 0;"/>
        <div class="dpflex-a-center dpflex-j-between">
            <span class="price_label">{$interestValueTitle}</span>
            <span class="price total-sum">{str_replace('.', ',', (str_replace(',', '.', $interestValue)|string_format:"%.2f"))}{* This ensures the number to always have two decimal places *} {$currency}</span>
        </div>
        <hr style="margin: 0.5rem 0;"/>
        <div class="dpflex-a-center dpflex-j-between">
            <span class="price_label"><strong>{$totalValueTitle}</strong></span>
            <strong class="price total-sum">{str_replace('.', ',', (str_replace(',', '.', $totalValue)|string_format:"%.2f"))}{* This ensures the number to always have two decimal places *} {$currency}</strong>
        </div>
    </div>
</div>