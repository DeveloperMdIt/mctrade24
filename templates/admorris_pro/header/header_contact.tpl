{*custom*}
{$contactPhone = $admorris_pro_templateSettings->contact_phone}
{$contactEmail = $admorris_pro_templateSettings->contact_email}

{if $layoutType === 'offcanvasLayout'}
    {$listClass = 'nav nav--offcanvas'}
{else}
    {$listClass = 'inline-separator-list'}
{/if}

<ul class="header-contact {$listClass}">
    {if $contactEmail}
        {$icon = $admIcon->renderIcon('envelope', 'header-contact__icon icon-content icon-content--default icon-content--center')}
        <li class="header__contact-item">{obfuscate}<a class="header-contact__email-link nav-link" href="mailto:{$contactEmail}">{$icon} <span class="header-contact__text icon-text--center">{$contactEmail}<span></a>{/obfuscate}</li>
    {/if}

    {if $contactPhone}
        {$icon = $admIcon->renderIcon('phone', 'header-contact__icon icon-content icon-content--default icon-content--center')}
        <li class="header__contact-item"><a class="header-contact__phone-link nav-link" href="tel:{str_replace('(0)', '', str_replace(' ', '', $contactPhone))}">{$icon} <span class="header-contact__text icon-text--center">{$contactPhone}</span></a></li>
    {/if}
</ul>