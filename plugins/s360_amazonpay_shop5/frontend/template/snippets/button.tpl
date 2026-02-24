{strip}
    <div class="{$lpaButton.classes}">
        <div class="lpa-button-wrapper">
            {if $lpaButton.context === 'login'}
                {include file="{$lpaButton.frontendTemplatePath}snippets/button_login.tpl"}
            {elseif $lpaButton.context === 'apbRedirect'}
                {include file="{$lpaButton.frontendTemplatePath}snippets/button_apb_redirect.tpl"}
            {else}
                {include file="{$lpaButton.frontendTemplatePath}snippets/button_pay.tpl"}
            {/if}
        </div>
    </div>
{/strip}