{*custom*}
{block name='offcanvas-cms-links'}
{block name='navbar-top-cms'}
    {if $linkgroups->getLinkGroupByTemplate('Kopf') !== null}
        {if !empty($firstelement)}
            <hr>
        {/if}
        <ul class="nav nav--offcanvas">
            {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
                <li class="nav-item {if $Link->getIsActive()}active{/if}">
                    <a class="nav-link" href="{$Link->getURL()}"{if $Link->getNoFollow()} rel="nofollow"{/if} title="{$Link->getTitle()}">{$Link->getName()}</a>
                </li>
            {/foreach}
        </ul>
    {/if}
{/block}{* /navbar-top *}
{/block}