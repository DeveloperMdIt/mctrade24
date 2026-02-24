{* INFO: caused multiple page reloads in some cases and is deactivated because of that *}
{* {if $Einstellungen.consentmanager.consent_manager_active === 'Y' && !isset($smarty.session.consents)}
    {block name="footer-js" append}
        <script type="module">
            document.dispatchEvent(new CustomEvent('adm.consent.updated', {}));
        </script>
    {/block}
{/if} *}