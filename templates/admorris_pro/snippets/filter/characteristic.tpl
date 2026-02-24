{* custom - filter-item class added and element order inside adjusted for flexbox use and custom block was added*}
{block name="characteristic"}
{$is_dropdown = false}
{$limit = $Einstellungen.template.productlist.filter_max_options}
{$collapseInit = false}
{if ($Merkmal->cTyp === 'SELECTBOX') && $Merkmal->oMerkmalWerte_arr|@count > 1}
    {$is_dropdown = true}
{/if}

{block name="characteristic-sort-function"}
    {if !empty($admorris_pro_templateSettings->characteristic_filter_order) && $admorris_pro_templateSettings->characteristic_filter_order == '1'}
        {$Merkmal = $admPro->sortFilterOptionsByCount($Merkmal)}
    {/if}
{/block}

<div {if $is_dropdown}class="dropdown-menu" role="menu" {elseif isset($class)}class="{$class}" {else}class="nav nav-list flex-column"{/if}>
    {foreach $Merkmal->getOptions() as $attributeValue}
        {$attributeImageURL = null}
        {assign var=filterIsAvailable value=$attributeValue->getCount() > 0}
        {if ($Merkmal->getData('cTyp') === 'BILD' || $Merkmal->getData('cTyp') === 'BILD-TEXT')}
            {$attributeImageURL = $attributeValue->getImage(\JTL\Media\Image::SIZE_XS)}
            {if strpos($attributeImageURL, $smarty.const.BILD_KEIN_ARTIKELBILD_VORHANDEN) !== false
                || strpos($attributeImageURL, $smarty.const.BILD_KEIN_MERKMALWERTBILD_VORHANDEN) !== false}
                {$attributeImageURL = null}
            {/if}
        {/if}

        {if $limit != -1 && $attributeValue@iteration > $limit && !$collapseInit && !$is_dropdown}
            <div class="filter-item-collapse collapse {if $Merkmal->isActive()} in{/if}" id="box-collps-{$Merkmal->kMerkmal}" aria-expanded="false">
                <div class="nav nav-list flex-column">
                {$collapseInit = true}
        {/if}
        {if $Merkmal->getData('cTyp') === 'BILD'}
            <div class="filter-item{if $attributeValue->isActive()} active{/if}{if !$filterIsAvailable} not-available{/if}">
                <a {if $is_dropdown}class="dropdown-item"{/if} rel="nofollow" href="{$attributeValue->getURL()}" title="{$attributeValue->getValue()|escape:'html':'UTF-8':FALSE}">
                    {if $attributeValue->isActive()}
                        {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default text-muted')}
                    {else}
                        {$admIcon->renderIcon('squareO', 'icon-content icon-content--default text-muted')}
                    {/if} 

                    <span class="value">
                        {if !empty($attributeImageURL)}
                            {image lazy=true webp=true
                                src=$attributeImageURL
                                alt=$attributeValue->getValue()|escape:'html'
                                class="vmiddle filter-img"
                            }
                        {/if}
                    </span>
                    <span class="badge badge-pill float-right">{$attributeValue->getCount()}<span class="sr-only"> {lang key='productsFound'}</span></span>
                </a>
            </div>
        {elseif $Merkmal->getData('cTyp') === 'BILD-TEXT'}
            <div class="filter-item{if $attributeValue->isActive()} active{/if}{if !$filterIsAvailable} not-available{/if}">
                <a {if $is_dropdown}class="dropdown-item"{/if} rel="nofollow" href="{$attributeValue->getURL()}" title="{$attributeValue->getValue()|escape:'html':'UTF-8':FALSE}">
                    {if $attributeValue->isActive()}
                        {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default icon-content--center text-muted')}
                    {else}
                        {$admIcon->renderIcon('squareO', 'icon-content icon-content--default icon-content--center text-muted')}
                    {/if} 
                    {if !empty($attributeImageURL)}
                        {image lazy=true webp=true
                            src=$attributeImageURL
                            alt=$attributeValue->getValue()|escape:'html'
                            class="vmiddle filter-img"
                        }
                    {/if}
                    <span class="value">
                        <span class="word-break">{$attributeValue->getValue()|escape:'html':'UTF-8':FALSE}</span>
                    </span>
                    <span class="badge badge-pill float-right">{$attributeValue->getCount()}<span class="sr-only"> {lang key='productsFound'}</span></span>
                </a>
            </div>
        {else}
            <div class="filter-item{if $attributeValue->isActive()} active{/if}{if !$filterIsAvailable} not-available{/if}">
                <a {if $is_dropdown}class="dropdown-item"{/if} rel="nofollow" href="{$attributeValue->getURL()}" title="{$attributeValue->getValue()|escape:'html':'UTF-8':FALSE}">
                    {if $attributeValue->isActive()}
                        {$admIcon->renderIcon('checkSquare', 'icon-content icon-content--default icon-content--center text-muted')}
                    {else}
                        {$admIcon->renderIcon('squareO', 'icon-content icon-content--default icon-content--center text-muted')}
                    {/if} 
                    <span class="value">
                        <span class="word-break">{$attributeValue->getValue()|escape:'html':'UTF-8':FALSE}</span>
                    </span>
                    <span class="badge badge-pill float-right">{$attributeValue->getCount()}<span class="sr-only"> {lang key='productsFound'}</span></span>
                </a>
            </div>
        {/if}
    {/foreach}
    {if $limit != -1 && $Merkmal->getOptions()|count > $limit && !$is_dropdown}
            </div>
        </div>
        <button class="btn btn-link float-right"
                data-toggle="collapse"
                data-target="#box-collps-{$Merkmal->kMerkmal}"
                aria-expanded="false"
        >
            {lang key='showAll'} <span class="caret"></span>
        </button>
    {/if}
</div>
{/block}
