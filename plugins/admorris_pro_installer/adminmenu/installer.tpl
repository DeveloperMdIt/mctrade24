{* admorris_pro installer template *}
{assign var=state value=$installerState}
<div class="admpro-installer">
  {if $state->license && !$state->license->isOk()}
    {if $state->license->needsVersionMatch()}
      <div class="info error">Keine passende Version gefunden / No matching version found.</div>
    {elseif $state->license->isInvalid()}
      <div class="info error">Lizenz ungültig / License invalid.</div>
    {elseif $state->license->status == 'error'}
      <div class="info error">Lizenzprüfung Fehler / License check error.</div>
    {/if}
  {elseif $state->error}
    <div class="info error">{$state->error|escape}</div>
  {elseif $state->success}
    <div class="info success">{$state->success|escape}</div>
  {/if}

  {* Dynamic AJAX driven UI will populate actions; keep meta area *}

  <div class="meta small text-muted">
    {if $state->downloadLink}Download: {$state->downloadLink|escape}{/if}
    {if $state->downloadSize}<br/>Size: {$state->downloadSize} bytes{/if}
    {if $state->shopVersion}<br/>Shop: {$state->shopVersion|escape}{/if}
  </div>
</div>
<style>
.admpro-installer .info { margin:2rem auto; font-size:1.2rem; text-align:center; }
.admpro-installer .error { color:#b30000; }
.admpro-installer .success { color:#0a7d00; }
.admpro-installer .btn { margin:0.5rem; }
</style>
<script>
window.admProInstallerState = {$installerStateJson nofilter};
// Provide global ioUrl if not already defined (JTL usually sets this); fallback to adminUrl
window.admProInstallerIoUrl = "{JTL\Shop::getURL()}/admin/io";
// admin base url and plugin version for runtime imports
window.admProInstallerAdminUrl = "{$adminUrl|escape}";
window.admProInstallerVersion = "{$pluginVersion|escape}";
</script>
<script type="importmap">
{
  "imports": {
    "admpro/ioFetch": "{$adminUrl|escape}js/ioFetch.js?v={$pluginVersion|escape}"
  }
}
</script>
<script type="module" src="{$adminUrl|escape}js/installer.js?v={$pluginVersion|escape}"></script>