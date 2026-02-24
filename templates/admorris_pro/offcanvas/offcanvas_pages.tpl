{*custom*}
{block name="offcanvas-pages"}
     <ul class="nav nav--offcanvas">
        {include file='snippets/linkgroup_list.tpl' linkgroupIdentifier='megamenu' dropdownSupport=true dropdownHover=false tplscope='offcanvas' caret=$admIcon->renderIcon('caretDown', 'icon-content icon-content--default float-right icon-content--toggle')}
    </ul>
{/block}{* megamenu-pages *}