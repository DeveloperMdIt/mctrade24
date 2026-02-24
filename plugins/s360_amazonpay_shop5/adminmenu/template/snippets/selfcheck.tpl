<div class="lpa-self-check-results">
    {foreach $lpaSelfCheck as $selfCheck}
        <span class="lpa-self-check-result d-inline-block text-{$selfCheck.status}">{__($selfCheck.message)}</span>
    {/foreach}
</div>