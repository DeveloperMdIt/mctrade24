{*custom*}

{$allCurrencies = JTL\Session\Frontend::getCurrencies()}
{$currentCurrency = JTL\Session\Frontend::getCurrency()}

{$labelSetting = $headerLayout->getItemSetting('currency', 'label', $layoutType)}

{$showIcon = ($labelSetting|in_array:['icon', 'icon_text'])?true:false}
{$showLabel = ($labelSetting|in_array:['text', 'icon_text'])?true:false}
{$iconSpacing = ($labelSetting === 'icon_text')?' ':''}

{if $allCurrencies|count > 1}
    <ul class="header-nav nav">
        {block name='top-bar-user-settings-currency'}
        <li class="nav-item currency-dropdown dropdown">
            <button type="button" class="nav-link dropdown-toggle shopnav__link button-reset" data-toggle="dropdown" title="{lang key='selectCurrency'}" data-display="static" aria-expanded="false" aria-label="{lang key='currency'}: {$currentCurrency->getName()}" aria-controls="currency-dropdown-{$layoutType}">
                {if $showIcon}
                    {if $currentCurrency->getCode() === 'EUR'}
                        {$admIcon->renderIcon('euro', 'icon-content icon-content--default icon-content--center shopnav__icon', $currentCurrency->getName())}
                    {elseif $currentCurrency->getCode() === 'USD'}
                        {$admIcon->renderIcon('dollar', 'icon-content icon-content--default icon-content--center shopnav__icon', $currentCurrency->getName())}
                    {elseif $currentCurrency->getCode() === 'GBP'}
                        {$admIcon->renderIcon('pound', 'icon-content icon-content--default icon-content--center shopnav__icon', $currentCurrency->getName())}
                    {else}
                        {$currentCurrency->cName}
                    {/if}
                {/if}
                {$iconSpacing}
                {if $showLabel}
                    <span class="shopnav__label icon-text--center">{lang key='currency'}</span>
                {/if}
                {$admIcon->renderIcon('caretDown', 'icon-content icon-content--default')}
            </button>
            {$dropdownClasses = ''}
            {if $layoutType !== 'offcanvasLayout'}
                {$dropdownClasses = ' dropdown-menu-right dropdown-menu--animated'}
            {/if}
            <ul id="currency-dropdown-{$layoutType}" class="dropdown-menu{$dropdownClasses}">
            {foreach $allCurrencies as $currency}
                <li>
                    <a href="{$currency->getURL()}" class="dropdown-item" rel="nofollow">{$currency->getName()}</a>
                </li>
            {/foreach}
            </ul>
        </li>
        {/block}
    </ul>
{/if}