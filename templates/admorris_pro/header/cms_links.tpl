{*custom / split from header_top_bar.tpl*}
{if $linkgroups->getLinkGroupByTemplate('Kopf') !== null}
    <ul class="cms-pages list-inline mb-0 header__cms-pages inline-separator-list">
        {block name='top-bar-cms-pages'}
        {foreach $linkgroups->getLinkGroupByTemplate('Kopf')->getLinks() as $Link}
            <li class="{if $Link->getIsActive()}active{/if}">
                <a href="{$Link->getURL()}"{if $Link->getNoFollow()} rel="nofollow"{/if} title="{$Link->getTitle()}">{$Link->getName()}</a>
            </li>
        {/foreach}
        {/block}
    </ul>
{/if}