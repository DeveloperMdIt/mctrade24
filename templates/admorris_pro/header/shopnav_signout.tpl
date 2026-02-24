{*custom*}
{block 'header-shopnav-signout'}
    {$labelSetting = $headerLayout->getItemSetting('signout', 'label', $layoutType)}

    {if !$labelSetting}
        {$labelSetting = 'icon'}
    {/if}

    {$showIcon = ($labelSetting|in_array:['icon', 'icon_text'])?true:false}
    {$showLabel = ($labelSetting|in_array:['text', 'icon_text'])?true:false}
    {$iconSpacing = ($labelSetting === 'icon_text')?' ':''}

    {strip}
    {if isset($smarty.session.Kunde) && isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde > 0}

    <a href="{get_static_route id='jtl.php'}?logout=1" title="{lang key='logOut'}" class="shopnav__link">
        {if $showIcon}
            {$admIcon->renderIcon('signOut', 'icon-content icon-content--center shopnav__icon')}
        {/if}
        {$iconSpacing}
        {if $showLabel}
            <span class="shopnav__label icon-text--center">{lang key='logOut'}</span>
        {/if}
    </a>
    {/if}


    {/strip}
{/block}