{*custom*}
{block 'header-language-selector'}
{$labelSetting = $headerLayout->getItemSetting('language', 'label', $layoutType)}
{$dropdownSetting = $headerLayout->getItemSetting('language', 'dropdown', $layoutType)}


{$showIcon = ($labelSetting|in_array:['icon', 'icon_text'])?true:false}
{$showLabel = ($labelSetting|in_array:['text', 'icon_text'])?true:false}
{$iconSpacing = ($labelSetting === 'icon_text')?' ':''}


{if !isset($layoutType)}
    {$layoutType = 'desktopLayout'}
{/if}

{if isset($smarty.session.Sprachen) && $smarty.session.Sprachen|@count > 1}
    {if !$dropdownSetting}
        <ul id="language-selector-{$layoutType}" class="language-selector language-selector--inline inline-separator-list">
            {block name="top-bar-user-settings-language"}
            {foreach $smarty.session.Sprachen as $Sprache}
                <li>
                    <a href="{$Sprache->getUrl()}" hreflang="{$Sprache->getIso639()}" class="link_lang nav-link {$Sprache->getIso()}{if $Sprache->getId() === JTL\Shop::getLanguageID()} active{/if}">{if $lang === 'ger'}{$Sprache->cNameDeutsch}{else}{$Sprache->cNameEnglisch}{/if}</a>
                </li>
            {/foreach}
            {/block}
        </ul>
    {else}
        <ul id="language-dropdown-{$layoutType}" class="language-selector language-selector--dropdown header-nav nav">
            <li class="nav-item language-dropdown dropdown">
                <button type="button" class="nav-link dropdown-toggle shopnav__link button-reset" data-toggle="dropdown" itemprop="inLanguage" itemscope itemtype="http://schema.org/Language" title="{lang key='selectLang'}" aria-label="{lang key='language'}" aria-expanded="false" data-display="static">
                    {if $showIcon}
                        {$admIcon->renderIcon('languageSelection', "icon-content icon-content--default")}
                    {/if}
                    {$iconSpacing}
                    {if $showLabel}
                        <span class="shopnav__label">
                            {foreach $smarty.session.Sprachen as $Sprache}
                                {if $Sprache->getId() === JTL\Shop::getLanguageID()}
                                    <span class="lang-{$lang} icon-text--center" itemprop="name"> {if $lang === 'ger'}{$Sprache->cNameDeutsch}{else}{$Sprache->cNameEnglisch}{/if}</span>
                                {/if}
                            {/foreach}
                        </span>
                    {/if}
                    {$admIcon->renderIcon('caretDown', 'icon-content icon-content--default')}
                </button>
                {$dropdownClasses = ''}
                {if $layoutType !== 'offcanvasLayout'}
                    {$dropdownClasses = ' dropdown-menu-right dropdown-menu--animated'}
                {/if}
                <div id="language-dropdown-list-{$layoutType}" class="dropdown-menu{$dropdownClasses}">
                    {foreach $smarty.session.Sprachen as $Sprache}
                        {if $Sprache->getId() != $smarty.session.kSprache}
                            <a class="dropdown-item" href="{$Sprache->getUrl()}" class="link_lang {$Sprache->getIso()}" hreflang="{$Sprache->getIso639()}">{$Sprache->displayLanguage}</a>
                        {/if}
                    {/foreach}
                </div>
            </li>
        </ul>
    {/if}
    {* /language-dropdown *}
{/if}
{/block}