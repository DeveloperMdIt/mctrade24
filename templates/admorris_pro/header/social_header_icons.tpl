{*custom*}
{block 'header-social-icons'}
    {$socialmediaArr = $admPro->getSocialmedia()}

    <ul class="header__social-icons list-unstyled">
        {foreach $socialmediaArr as $item}
        
            {if !empty($item.link)}
                {if ($item.name === "googleplus") } 
                    {$item.name = "googlePlus"}
                {/if}
                <li>
                    <a href="{$item.link}" class="btn-social btn-{$item.name}" title="{$item.title}" aria-label="{$item.title}" target="_blank" rel="noopener">{$admIcon->renderIcon($item.name, 'icon-content icon-content--default')}</a>
                </li>
            {/if}    
        {/foreach} 
    </ul>
{/block}