{$mobileSelector = $oPlugin_admorris_pro->getConfig()->getValue('admorris_pro_expert_settings_reorder_mobile_css_selector')}

{if isset($am_reorderLabelSetting)}
  {$showIcon = (in_array($am_reorderLabelSetting, ['icon', 'icon_text']))?true:false}
  {$showLabel = (in_array($am_reorderLabelSetting, ['text', 'icon_text']))?true:false}
  {$iconSpacing = ($am_reorderLabelSetting === 'icon_text')?' ':''}
{else}
  {$showIcon = true}
  {if $am_reorderDropdownLayoutType === 'desktopLayout'}
    {$showLabel = false}
  {else}
    {$showLabel = str_contains($mobileSelector, '#mainNavigation')}
  {/if}
  {$iconSpacing = ''}

{/if}


{$label = $admUtils::trans('marketing_reorder_dropdown_label')}
{$allArticlesLabel = $admUtils::trans('marketing_reorder_all_articles')}


{$caret = (isset($navbarDropdownCaret))?$navbarDropdownCaret:'caret'}



{if $am_reorderDropdownLayoutType === 'desktopLayout'}

  {capture reorderDesktopMenu assign=reorderDropdownDesktop}
    <li class="reorder-menu dropdown items{if !$isAdmorrisProActive && !empty($mobileSelector)} d-none d-lg-block{/if}">
      <button class="reorder__dropdown-button shopnav__link nav-link" data-toggle="dropdown" title="{$label}">
        {if $showIcon}
          {$admIcon->renderIcon('reorder', 'shopnav__icon icon-reorder icon-content icon-content--default icon-content--center')}
          {* {fetch file="`$oPlugin_admorris_pro->getPaths()->getFrontendPath()`icons/reorder-icon.svg"} *}
          {if !$showLabel}
            <span class="sr-only">{$label}</span>
          {/if}
          {* <svg class="shopnav__icon icon-reorder" viewBox="0 0 30 32"><use xlink:href="{$oPlugin_admorris_pro->getPaths()->getFrontendURL()}icons/reorder-icon.svg#icon-reorder"></svg> *}
        {/if} 
        {$iconSpacing}
        {if $showLabel}

          <span class="shopnav__label icon-text--center">{$label}</span>
        {/if}
        {* {if !$isAdmorrisProActive}
          <span class="caret"></span>
        {/if} *}

      </button>

      {if $am_reorderDropdownLayoutType === 'desktopLayout'}
        <div class="reorder-dropdown dropdown-menu dropdown-menu-right dropdown-menu--animated">
          <ul class="list-unstyled reorder-dropdown__list scrollbox">
            {foreach $am_reorderDropdownArticles as $reorderArticle}
              {* {if array_key_exists($reorderArticle->kArtikel, $am_reorderVariationValues)}
                {foreach $am_reorderVariationValues[$reorderArticle->kArtikel] as $posVariationValues}
                  {include $itemTemplate}
                {/foreach}
              {else} *}
                {* marketing_reorder_dropdown_item.tpl *}
                {include $am_reorderItemTemplate i=$reorderArticle@iteration}
              {* {/if} *}
            {/foreach}
          
          </ul>
          <a href="{get_static_route id='registrieren.php'}#reorder" class="reorder-dropdown__overview-btn btn btn-block text-primary">
            {$allArticlesLabel}
          </a>
        </div>
      {/if}
    </li>
  {/capture}

  {strip}
  {if $isAdmorrisProActive}
    <ul class="header-shop-nav nav navbar-nav horizontal">
      {$reorderDropdownDesktop}
    </ul>
  {else}
    {$reorderDropdownDesktop}
  {/if}
    

  {/strip}

{else}
  {capture reorderMobileMenu assign=reorderMobile}
    <a href="{get_static_route id='registrieren.php'}#reorder" class="reorder__dropdown-button shopnav__link nav-link" title="{$label}">
      <span>
        {if $showIcon}
          {$admIcon->renderIcon('reorder', 'shopnav__icon icon-reorder icon-content icon-content--default icon-content--center')}
        {/if}
        {$iconSpacing}
        {if $showLabel}
          <span class="shopnav__label icon-text--center">{$label}</span>
        {/if}
      </span>
    </a>
  {/capture}

  {if !$isAdmorrisProActive}
    <li class="reorder-menu nav-item d-block d-lg-none">
      {$reorderMobile}
    </li>
  {else}
    {$reorderMobile}
  {/if}

{/if}

<style>
.reorder__dropdown-button {
  border: none;
  color: inherit;
  background-color: transparent;
}
</style>