{*custom*}
{block 'header-offcanvas-button'}
    <button id="burger-menu" class="header__offcanvas-toggle burger-menu-wrapper navbar-toggler" type="button" data-toggle="modal" data-target="#navbar-offcanvas" aria-controls="navbar-offcanvas" aria-label="{lang section='aria' key='toggleNavigation'}" >
        {$admIcon->renderIcon('bars', 'icon-content icon-content--default')}
    </button>
{/block}