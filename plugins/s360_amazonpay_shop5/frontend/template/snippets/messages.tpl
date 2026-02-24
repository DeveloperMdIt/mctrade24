{strip}
    {if isset($lpa.messages) && count($lpa.messages) > 0}
        <div class="lpa-messages">
        {foreach $lpa.messages as $message}
            <div class="alert alert-{$message.type} lpa-message" role="alert">{$message.content}</div>
        {/foreach}
        </div>
    {/if}
{/strip}