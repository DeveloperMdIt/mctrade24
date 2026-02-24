{*custom*}
{* OPTION: Sichtbarkeit für Standardkundengruppe *}
{block 'offcanvas-categories'}
{if isset($Einstellungen.global.global_sichtbarkeit) && ($Einstellungen.global.global_sichtbarkeit != 3 || isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde != 0)}

    <div class="navbar-categories">
        <ul class="nav flex-column nav--offcanvas">
            {include file='snippets/categories_recursive_offcanvas.tpl' i=0 categoryId=0 limit=2 caret=$admIcon->renderIcon('caretRight', 'icon-content icon-content--default icon-content--center')}
        </ul>
    </div>

{/if}
{/block}