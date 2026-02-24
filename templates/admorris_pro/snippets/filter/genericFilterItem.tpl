{* custom - filter-item order changed for flex-box use *}
{block 'generic-filter-item'}
{if !isset($itemClass)}
    {assign var=itemClass value=''}
{/if}
{if !isset($class)}
    {$class = ''}
{/if}
{$limit = $Einstellungen.template.productlist.filter_max_options}
{$collapseInit = false}

<ul class="{if !empty($class)}{$class}{else}nav nav-list flex-column{/if}">
    {foreach $filter->getOptions() as $filterOption}
        {assign var=filterIsActive value=$filterOption->isActive() || $NaviFilter->getFilterValue($filter->getClassName()) === $filterOption->getValue()}
        {assign var=filterIsAvailable value=$filterOption->getCount() > 0}
        {if $limit != -1 && $filterOption@iteration > $limit && !$collapseInit && $class!='dropdown-menu'}
            <div class="filter-item-collapse collapse {if $filter->isActive()} in{/if}" id="box-collps-filter{$filter->getNiceName()}" aria-expanded="false"><ul class="nav nav-list flex-column">
            {$collapseInit = true}
        {/if}
        {* handle filter of Merkmale, e.g. Characteristic *}

        {if $filterOption->getNiceName() === "Characteristic"}

            {$attributeImageURL = null}
            {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'B' || $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'BT'}
                {$attributeImageURL = $filterOption->getImage(\JTL\Media\Image::SIZE_XS)}
                {if strpos($attributeImageURL, $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN) !== false
                    || strpos($attributeImageURL, $smarty.const.BILD_KEIN_MERKMALWERTBILD_VORHANDEN) !== false}
                    {$attributeImageURL = null}
                {/if}
            {/if}

            <li class="filter-item{if $filterOption->isActive()} active{/if}{if !$filterIsAvailable} not-available{/if}">
                <a {if $class == "dropdown-menu"}class="dropdown-item"{/if} rel="nofollow" href="{$filterOption->getURL()}" title="{$filterOption->getValue()|escape:'html':'UTF-8':FALSE}">
                    {if $filterOption->isActive()}
                        {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default icon-content--center text-muted')}
                    {else}
                        {$admIcon->renderIcon('squareO', 'icon-content icon-content--default icon-content--center text-muted')}
                    {/if}
                    <span class="value">
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'B' || $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'BT'}
                        {if !empty($attributeImageURL)}
                            {image lazy=true webp=true
                                src=$attributeImageURL
                                alt=$filterOption->getValue()|escape:'html'
                                class="vmiddle filter-img"
                            }
                        {/if}
                    {/if}
                    {if $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'T' || $Einstellungen.navigationsfilter.merkmal_anzeigen_als === 'BT'}
                        <span class="word-break">{$filterOption->getValue()|escape:'html':'UTF-8':FALSE}</span>
                    {/if}
                    </span>
                    <span class="badge badge-pill float-right">{$filterOption->getCount()}<span class="sr-only"> {lang key='productsFound'}</span></span>
                </a>
            </li>
        {else}
            <li class="filter-item{if $filterIsActive === true} active{/if}{if !$filterIsAvailable} not-available{/if}">
                <a rel="nofollow"
                href="{if $filterOption->isActive()}{$filter->getUnsetFilterURL($filterOption->getValue())}{else}{$filterOption->getURL()}{/if}"
                class="{$itemClass}{if $class == "dropdown-menu"} dropdown-item{/if}{if $filterOption->isActive()} active{/if}">
                    {if $filter->getIcon() !== null}
                        {$mappedIcon = $admIcon->mapIcon($filter->getIcon())}
                        {$admIcon->renderIcon($mappedIcon,'icon-content icon-content--default icon-content--center')}
                    {else}
                        {if $filterIsActive === true}
                            {$admIcon->renderIcon('checkSquare','icon-content icon-content--default icon-content--center text-muted')}
                        {else}
                            {$admIcon->renderIcon('squareO','icon-content icon-content--default icon-content--center text-muted')}
                        {/if}
                    {/if}
                    <span class="value">

                        {if $filter->getNiceName() === 'Rating'}
                            {include file='productdetails/rating.tpl' stars=$filterOption->getValue()}
                        {/if}
                        <span class="word-break">
                            {if $filter->getNiceName() === 'Manufacturer'}
                                {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als !== 'T'}
                                    <img src="{$filterOption->getData('cBildpfadKlein')}" alt="" class="vmiddle filter-img" />
                                {/if}
                                {if $Einstellungen.navigationsfilter.hersteller_anzeigen_als !== 'B'}
                                    &nbsp;{$filterOption->getName()}
                                {/if}
                            {else}
                                {$filterOption->getName()}
                            {/if}
                        </span>
                    </span>
                    <span class="badge badge-pill float-right">{$filterOption->getCount()}<span class="sr-only"> {lang key='productsFound'}</span></span>

                </a>
            </li>
        {/if}
    {/foreach}
    {if $limit != -1 && $filter->getOptions()|count > $limit && $class!='dropdown-menu'}
    </ul></div>
        <button class="btn btn-link float-right"
                role="button"
                data-toggle="collapse"
                data-target="#box-collps-filter{$filter->getNiceName()}"
                aria-expanded="false"
        >
            {lang key='showAll'}
        </button>
    {/if}
</ul>
{/block}
