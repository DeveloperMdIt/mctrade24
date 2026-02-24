<div id="paylater-legal-info" class="custom-control collapse fade{if $AktiveZahlungsart === $puiPaymentId} show{/if}">
    <input type="hidden" name="puiLegalInfoShown" value="1">
    <div class="card" role="document">
        <div class="card-body">
            <strong>{$legalInformationHeader}</strong>
            <p>{$legalInformation}</p>
        </div>
    </div>
</div>
<script>
    if (typeof puiPaymentId === 'undefined') {
        let puiPaymentId = '{$puiPaymentId}';
        {literal}
        $(document).ready(function () {
            $('#fieldset-payment').on('change', 'input[name="Zahlungsart"]', function (e) {
                if (e.target.id === 'payment' + puiPaymentId) {
                    $('#paylater-legal-info').collapse('show');
                } else {
                    $('#paylater-legal-info').collapse('hide');
                }
            });
        });
        {/literal}
    }
</script>
