{$filteredAlerts = $admPro->filterAlertsByShowInTemplate($alertList->getAlertlist())}

{if $filteredAlerts->isNotEmpty()}
    <div id="alert-list">
        {foreach $filteredAlerts as $alert}
            {$alert->display()}
        {/foreach}
    </div>
{/if}
