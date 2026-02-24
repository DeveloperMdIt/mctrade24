{block name='snippets-alert'}
    {alert
        variant={$alert->getCssType()}
        data=["fade-out"=>{$alert->getFadeOut()}, "key"=>{$alert->getKey()}]
        id="{if $alert->getId()}{$alert->getId()}{/if}"
        class="alert-wrapper{if $alert->getDismissable()} alert-dismissible{/if}"
        role="alert"
        aria=["live"=>"assertive", "atomic"=>"true"]
    }
        {* admorris Pro custom - renderIcon added *}
        {if $alert->getIcon()}{$mappedIcon = $admIcon->mapIcon($alert->getIcon())}{$admIcon->renderIcon($mappedIcon,'icon-content')}{/if}
        {* custom - changed to button element *}
        {if $alert->getDismissable()}
            <button type="button" class="close" data-dismiss="alert" aria-label="{lang key="close" section="account data"}">
                <span aria-hidden="true">&times;</span>
            </button>
        {/if}
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