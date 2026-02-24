{*custom*}
{* This template is used in the getCategoryMenu io function *}
{block name='snippets-categories-offcanvas'}
    {lang key='view' assign='view_'}
    {$seoUrlAttr = $result->current->getCategoryAttribute('category_seo_url')}

    <div class="nav nav--offcanvas navbar-categories__title flex-wrap">
        <button type="button" class="btn nav-link nav-sub navbar-categories__back-link text-left mr-0 pr-1" data-ref="{$result->current->getParentID()}">
            {$admIcon->renderIcon('backward', 'icon-content icon-content--default icon-content--center')} 
            <span class="sr-only">
                {lang key="back" section="global"}
            </span>
        </button>
        <a href="{if isset($seoUrlAttr)}{$seoUrlAttr->cWert}{else}{$result->current->getURL()}{/if}" class="d-inline-flex ml-0 pl-1 nav-link nav-active">
            <span class="navbar-categories__current">{$result->current->getName()}</span> <span class="navbar-categories__view">{$view_|lower}&nbsp;{$admIcon->renderIcon('caretRight', 'icon-content icon-content--default icon-content--center')}</span>
        </a>
    </div>
    <ul class="nav nav--offcanvas">
        {* <li class="clearfix">
            <a href="#" class="nav-sub float-left" data-ref="0"><i class="fa fa-bars"></i> {lang key="showAll" section="global"}</a>
            
        </li> *}
        {* <li>
            <a href="{$result->current->cURL}" class="nav-active">{$result->current->cName}  <span class="navbar-categories__view">{$admIcon->renderIcon('caretRight', 'icon-content icon-content--default icon-content--center')}&nbsp;{$view_|lower}</span></a>
        </li> *}
        {include file='snippets/categories_recursive_offcanvas.tpl' i=0 categoryId=$result->current->getID() limit=2 caret=$admIcon->renderIcon('caretRight', 'icon-content icon-content--default icon-content--center')}
    </ul>
{/block}