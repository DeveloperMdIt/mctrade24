{block name='snippets-alert'}
    {alert
        variant={$alert->getCssType()}
        data=["fade-out"=>{$alert->getFadeOut()}, "key"=>{$alert->getKey()}]
        id="{if $alert->getId()}{$alert->getId()}{/if}"
        class="alert-wrapper"
        role="alert"
        aria=["live"=>"assertive", "atomic"=>"true"]
    }
        {if $alert->getIcon()}
            <i class="fas fa-{if $alert->getIcon() === 'warning'}exclamation-triangle{else}{$alert->getIcon()}{/if}"></i>
        {/if}
        {if $alert->getDismissable()}<div class="close">&times;</div>{/if}
        {if !empty($alert->getLinkHref()) && empty($alert->getLinkText())}
            {link href=$alert->getLinkHref()}{$alert->getMessage()}{/link}
        {elseif !empty($alert->getLinkHref()) && !empty($alert->getLinkText())}
            {$alert->getMessage()}
            {link href=$alert->getLinkHref()}{$alert->getLinkText()}{/link}
        {else}
            {$alert->getMessage()}
        {/if}
    {/alert}
{/block}
