{*custom*}
{block name='layout-breadcrumb'}
    {strip}
        {* changed for loading with pagination  *}
        {if !empty($Brotnavi) && !$bExclusive && $nSeitenTyp !== $smarty.const.PAGE_STARTSEITE && $nSeitenTyp !== $smarty.const.PAGE_BESTELLVORGANG && $nSeitenTyp !== $smarty.const.PAGE_BESTELLSTATUS}
            <nav aria-label="breadcrumb" class="breadcrumb-wrapper{if !empty($hideOnMobile)} d-none d-md-inline-block{/if}" itemscope>
                <ol id="breadcrumb" class="breadcrumb" itemprop="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
                    {foreach $Brotnavi as $oItem}
                        {if $oItem@first}
                            {block name='breadcrumb-first-item'}
                                <li class="breadcrumb-item first" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" href="{$oItem->getURL()}" title="{$oItem->getName()|escape:'quotes'}">
                                        {* <span class="fa fa-home"></span> *}
                                        <span itemprop="name" class="">{$oItem->getName()}</span>
                                    </a>
                                    {* <meta itemprop="url" content="{$oItem->url}" /> *}
                                    <meta itemprop="position" content="{$oItem@iteration}" />
                                </li>
                            {/block}
                        {elseif $oItem@last}
                            {block name='breadcrumb-last-item'}
                                {if $oItem->getName() !== null}
                                    <li class="breadcrumb-item last" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                        <a itemprop="item" href="{$oItem->getURL()}" title="{$oItem->getName()|escape:'quotes'}">
                                            <span itemprop="name">{$oItem->getName()}</span>
                                        </a>
                                        {* <meta itemprop="url" content="{$oItem->getURL()}" /> *}
                                        <meta itemprop="position" content="{$oItem@iteration}" />
                                    </li>
                                {elseif isset({$Suchergebnisse->getSearchTermWrite()})}
                                    <li class="breadcrumb-item last">
                                        {{$Suchergebnisse->getSearchTermWrite()}}
                                    </li>
                                {/if}
                            {/block}
                        {else}
                            {block name='breadcrumb-item'}
                                <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                                    <a itemprop="item" href="{$oItem->getURL()}" title="{$oItem->getName()|escape:'quotes'}">
                                        <span itemprop="name">{$oItem->getName()}</span>
                                    </a>
                                    {* <meta itemprop="url" content="{$oItem->getURL()}" /> *}
                                    <meta itemprop="position" content="{$oItem@iteration}" />
                                </li>
                            {/block}
                        {/if}
                    {/foreach}
                </ol>
            </nav>
        {/if}
    {/strip}
{/block}