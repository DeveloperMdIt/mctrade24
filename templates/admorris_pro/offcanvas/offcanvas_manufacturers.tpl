{*custom*}
{block name="offcanvas-manufacturers"}
    {* OPTION: Sichtbarkeit für Standardkundengruppe *}
    {if isset($Einstellungen.global.global_sichtbarkeit) && ($Einstellungen.global.global_sichtbarkeit != 3 || isset($smarty.session.Kunde->kKunde) && $smarty.session.Kunde->kKunde != 0)}


        {* {if isset($Einstellungen.template.megamenu.show_manufacturers) && $Einstellungen.template.megamenu.show_manufacturers !== 'N'} *}
            {get_manufacturers assign='manufacturers'}
            {if !empty($manufacturers)}

                <nav aria-label="{lang key='manufacturers'}" class="navbar-manufacturers">
                    <ul class="nav nav--offcanvas">
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-display="static" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" title="{lang key='manufacturers'}">
                                <span class="icon-content--center">{lang key='manufacturers'}</span>
                                {$admIcon->renderIcon('caretRight', 'icon-content icon-content--default icon-content--center icon-content--toggle float-right')}
                            </a>
                            <ul class="dropdown-menu keepopen">
                                {foreach name='hersteller' from=$manufacturers item='hst'}
                                    <li>
                                        <a class="nav-link" href="{$hst->getSeo()}" title="{$hst->getName()|escape:'html':'UTF-8':FALSE}">{$hst->getName()|escape:'html':'UTF-8':FALSE}</a>
                                    </li>
                                {/foreach}
                            </ul>
                        </li>
                    </ul>
                </nav>
            {/if}
        {* {/if} *}
    {/if}
{/block}{* megamenu-manufacturers *}