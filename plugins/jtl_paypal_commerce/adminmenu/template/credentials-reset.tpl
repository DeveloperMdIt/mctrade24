{include file="snippets/alert_list.tpl"}
{if $resetCodeAvail}
    <p>{__('Geben Sie zum Zurücksetzen des PayPal-Kontos den Rücksetz-Code aus der EMail ein.')}</p>
    {include file="$basePath/template/credentials-reset-code.tpl" parts=8}
    <p>{__('Keine E-Mail erhalten!? Hier können Sie sich einen neuen Rücksetz-Code per E-Mail schicken lassen.')}</p>
{elseif $twoFAavail}
    <p>{__('Geben Sie zum Zurücksetzen des PayPal-Kontos die aktuelle Zahlenfolge der Zweifaktor-Authentifizierung ein.')}</p>
    {include file="$basePath/template/credentials-reset-code.tpl" parts=6}
    <p>{__('Alternativ können Sie sich einen Rücksetz-Code per E-Mail schicken lassen.')}</p>
{else}
    <p>{__('Für den Account ist keine Zweifaktor-Authentifizierung konfiguriert. Sie können sich einen Rücksetz-Code per E-Mail schicken lassen.')}</p>
{/if}
