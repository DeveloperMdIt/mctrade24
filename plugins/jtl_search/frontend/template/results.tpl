{if count($queries) > 0}
    {if $isNova}
        <div id="result_set" class="is-nova" role="dialog" aria-modal="true">
            <div class="row mr-0">
                <div class="col col-12 {if isset($queries.product)}col-md-6{/if}">
                    <div class="h5 mb-0">{$localization->getTranslation('search_for')}</div>
                    <div class="mb-3">
                        <a href="#" rel="{$cSearch|escape:'html'}" class="rel-link">{$cSearch}</a>
                    </div>
                    {if strlen($oSearchResponse->oSuggest->cSuggest) > 0 && $oSearchResponse->oSuggest->nForwarding == 1}
                        <div class="h5 mb-0">{$localization->getTranslation('did_you_mean')}</div>
                        <div class="mb-3">
                            <a href="#" class="rel-link" rel="{$oSearchResponse->oSuggest->cSuggest|escape:'html'}">
                                {$oSearchResponse->oSuggest->cSuggest}
                            </a>
                        </div>
                    {/if}
                    {if isset($queries.landingpage)}
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.landingpage cName=$localization->getTranslation('suggested_pages')}
                    {/if}
                    {if isset($queries.query)}
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.query cName=$localization->getTranslation('suggested_search_terms')}
                    {/if}
                    {if isset($queries.category)}
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.category cName=$localization->getTranslation('suggested_categories')}
                    {/if}
                    {if isset($queries.manufacturer)}
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.manufacturer cName=$localization->getTranslation('suggested_manufacturers')}
                    {/if}
                </div>
                {if isset($queries.product)}
                    <div class="col col-12 col-md-6 mt-2 mt-md-0">
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.product cName=$localization->getTranslation('suggested_products')}
                    </div>
                {/if}
            </div>
            {if $bBranding}
                <div class="result_copy"></div>
            {/if}
        </div>
    {else}
        <div id="result_set">
            <div class="result_row_wrapper clearall">
                <div class="result_row first">
                    <p class="jtl-search-for">{$localization->getTranslation('search_for')}</p>
                    <a href="#" class="rel-link" rel="{$cSearch|escape:'html'}">{$cSearch}</a>
                    {if strlen($oSearchResponse->oSuggest->cSuggest) > 0 && $oSearchResponse->oSuggest->nForwarding == 1}
                        <p>{$localization->getTranslation('did_you_mean')}</p>
                        <a href="#" class="rel-link" rel="{$oSearchResponse->oSuggest->cSuggest|escape:'html'}">{$oSearchResponse->oSuggest->cSuggest}</a>
                    {/if}
                    {if isset($queries.landingpage)}
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.landingpage cName=$localization->getTranslation('suggested_pages')}
                    {/if}
                    {if isset($queries.query)}
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.query cName=$localization->getTranslation('suggested_search_terms')}
                    {/if}
                    {if isset($queries.category)}
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.category cName=$localization->getTranslation('suggested_categories')}
                    {/if}
                    {if isset($queries.manufacturer)}
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.manufacturer cName=$localization->getTranslation('suggested_manufacturers')}
                    {/if}
                </div>
                {if isset($queries.product)}
                    <div class="result_row">
                        {include file=$cTemplatePath|cat:'result_item.tpl' cType=$queries.product cName=$localization->getTranslation('suggested_products')}
                    </div>
                {/if}
            </div>
            {if $bBranding}
                <div class="result_copy"></div>
            {/if}
        </div>
    {/if}
{/if}
