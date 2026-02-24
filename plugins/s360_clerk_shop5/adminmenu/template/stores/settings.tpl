<form method="POST">
    {$jtl_token}

    <div class="subheading1">{__('Einstellungen')}</div>
    <hr class="mb-3">

    {* Public Key *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_api_key">{__('API Key')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_api_key" name="api_key" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getApiKey()|escape}{/if}" required />
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Der API-Key des Data-Feed bei Clerk')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Private Key*}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_private_key">{__('Private Key')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_private_key" name="private_key" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getPrivateKey()|escape}{/if}" />
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Der Legacy Private API-Key des Data-Feed bei Clerk. Wird für die Zugriffsbeschränkung auf den Daten-Feed benötigt.')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Language *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_language">{__('Sprache')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <select id="s360_language" name="language" class="custom-select">
                {foreach from=$s360_clerk_store.languages item=language}
                    <option value="{$language->id}" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getLanguageId() === $language->id}selected{/if}>{$language->localizedName}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Die Sprache der Daten im Feed')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Customer Groups*}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_customer_group">{__('Kundengruppe')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <select id="s360_customer_group" name="customer_group" class="custom-select">
                {foreach from=$s360_clerk_store.customer_groups item=group}
                    <option value="{$group->getID()}" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getCustomerGroupId() === $group->getID()}selected{/if}>{$group->getName()}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Die Einschränkung der Kundengruppe')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Currency*}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_currency">{__('Währung')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <select id="s360_currency" name="setting[currency]" class="custom-select">
                {foreach from=$s360_clerk_store.currencies item=currency}
                    <option value="{$currency->getID()}" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getCurrency() === $currency->getID()}selected{/if}>{$currency->getName()}</option>
                {/foreach}
            </select>
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Die verwendete Währung der Preise im Feed')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Currency*}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_facets_design">{__('Facetten Design')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_facets_design" name="setting[facets_design]" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getSettings()->getFacetsDesign()|escape}{/if}" />
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Facetten Design, zu finden bei Clerk -> Search -> Designs -> id vom Facetten Design')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    <div class="subheading1">{__('Data Feed')}</div>
    <hr class="mb-3">


    {* Data Feed *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right">{__('Zu übermittelnde Daten')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_enable_products" name="setting[enable_products]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableProducts()}checked{/if} />
                <label class="custom-control-label" for="s360_enable_products">{__('Produkte in Feed aufnehmen')}</label>
            </div>
            <div class="pl-4">
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input form-control" id="s360_enable_characteristics" name="setting[enable_characteristics]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableCharacteristics()}checked{/if} />
                    <label class="custom-control-label" for="s360_enable_characteristics">{__('Merkmale in Feed aufnehmen')}</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input form-control" id="s360_enable_attributes" name="setting[enable_attributes]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableAttributes()}checked{/if} />
                    <label class="custom-control-label" for="s360_enable_attributes">{__('Attribute in Feed aufnehmen')}</label>
                </div>
                <div class="custom-control custom-checkbox">
                    <input class="custom-control-input form-control" id="s360_enable_func_attributes" name="setting[enable_func_attributes]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableFuncAttributes()}checked{/if} />
                    <label class="custom-control-label" for="s360_enable_func_attributes">{__('Funktions-Attribute in Feed aufnehmen')}</label>
                </div>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_enable_categories" name="setting[enable_categories]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableCategories()}checked{/if} />
                <label class="custom-control-label" for="s360_enable_categories">{__('Kategorien in Feed aufnehmen')}</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_enable_customers" name="setting[enable_customers]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableCustomers()}checked{/if} />
                <label class="custom-control-label" for="s360_enable_customers">{__('Kunden in Feed aufnehmen')}</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_enable_last_orders" name="setting[enable_last_orders]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableLastOrders()}checked{/if} />
                <label class="custom-control-label" for="s360_enable_last_orders">{__('Letzte Bestellungen in Feed aufnehmen')}</label>
                <div class="form-help text-danger">
                    <i class="fa fa-warning"></i> {__('termsAndServiceWarning')}
                </div>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_enable_blog" name="setting[enable_blog]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableBlog()}checked{/if} />
                <label class="custom-control-label" for="s360_enable_blog">{__('Blog in Feed aufnehmen')}</label>
            </div>
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_enable_cms" name="setting[enable_cms]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getEnableCms()}checked{/if} />
                <label class="custom-control-label" for="s360_enable_cms">{__('Eigene Seiten in Feed aufnehmen')}</label>
            </div>
            
        </div>
        
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Welche Daten sollen im Feed enthalten sein')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Products without price *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_products_without_price">{__('Produkte ohne Preis')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_products_without_price" name="setting[products_without_price]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getProductsWithoutPrice()}checked{/if} />
                <label class="custom-control-label" for="s360_products_without_price">{__('Produkte ohne Preis in Feed aufnehmen')}</label>
            </div>
        </div>
    </div>

    {* Products without price *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_min_bulk_price_as_price">{__('Kleinster Staffelpreis als Preis')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_min_bulk_price_as_price" name="setting[min_bulk_price_as_price]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getMinBulkPriceAsPrice()}checked{/if} />
                <label class="custom-control-label" for="s360_min_bulk_price_as_price">{__('Den kleinsten Staffelpreis als Preis festlegen')}</label>
            </div>
        </div>
    </div>

    {* Category Separator *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_category_separator">{__('Kategorie Separator')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_category_separator" name="setting[category_separator]" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getSettings()->getCategorySeparator()|escape}{/if}"/>
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Separator für Kategorien, z.B. &gt;')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Blog ID Prefix *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_blog_id_prefix">{__('Blog-Seiten ID Prefix')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_blog_id_prefix" name="setting[blog_id_prefix]" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getSettings()->getBlogIdPrefix()|escape}{/if}" />
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('ID-Prefix für Blog-Seiten, z.B. 10000-')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* CMS ID Prefix *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_cms_id_prefix">{__('Eigene-Seiten ID Prefix')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_cms_id_prefix" name="setting[cms_id_prefix]" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getSettings()->getCmsIdPrefix()|escape}{/if}" />
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('ID-Prefix für CMS-Seiten, z.B. 20000-')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>
                
    <div class="form-group form-row align-items-center mb-0">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_feed_product_mode">
            {__('Feed Product Mode')}
        </label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2">
            <select class="form-control" id="s360_feed_product_mode" name="setting[feed_product_mode]">
                <option value="all" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getFeedProductMode() === 'all'}selected{/if}>{__('All products')}</option>
                <option value="parent_only" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getFeedProductMode() === 'parent_only'}selected{/if}>{__('Only parent products')}</option>
                <option value="children_only" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getFeedProductMode() === 'children_only'}selected{/if}>{__('Only child products')}</option>
            </select>
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Which products should be included in the product feed?')}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    <div class="subheading1">{__('Mapping')}</div>
    <hr class="mb-3">

    {* Mapping: colors *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_mapping_colors">{__('Wert für <code>color_codes</code> Attribut')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_mapping_colors" name="setting[mapping_colors]" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getSettings()->getMappingColors()|escape}{/if}" />
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Aus welchem Attribut soll der Wert für \'color_codes\' geladen werden (sofern vorhanden)')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Mapping: color_names *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_mapping_color_names">{__('Wert für <code>color_names</code> Attribut')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_mapping_color_names" name="setting[mapping_color_names]" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getSettings()->getMappingColorNames()|escape}{/if}" />
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Aus welchem Attribut soll der Wert für \'color_names\' geladen werden (sofern vorhanden)')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    {* Mapping: gender *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_mapping_gender">{__('Wert für <code>gender</code> Attribut')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <input type="text" id="s360_mapping_gender" name="setting[mapping_gender]" class="form-control" value="{if isset($s360_clerk_store.feed)}{$s360_clerk_store.feed->getSettings()->getMappingGender()|escape}{/if}" />
        </div>
        <div class="col-auto ml-sm-n4 order-2 order-sm-3">
            <span data-html="true" data-toggle="tooltip" data-placement="left" title="" data-original-title="{__('Aus welchem Attribut soll der Wert für \'gender\' geladen werden (sofern vorhanden)')|escape}">
                <span class="fas fa-info-circle fa-fw"></span>
            </span>
        </div>
    </div>

    <div class="subheading1">{__('Debugging')}</div>
    <hr class="mb-3">

    {* Disable Aiuth *}
    <div class="form-group form-row align-items-center">
        <label class="col col-sm-4 col-form-label text-sm-right" for="s360_disable_auth_check">{__('Zugriffsbeschränkung deaktivieren')}</label>
        <div class="col-sm pl-sm-3 pr-sm-5 order-last order-sm-2 ">
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input form-control" id="s360_disable_auth_check" name="setting[disable_auth_check]" type="checkbox" value="1" {if isset($s360_clerk_store.feed) && $s360_clerk_store.feed->getSettings()->getDisableAuthCheck()}checked{/if} />
                <label class="custom-control-label" for="s360_disable_auth_check"></label>
            </div>
        </div>
    </div>

    {* Save Button *}
    <div class="save-wrapper">
        <div class="row">
            <div class="mr-auto col-sm-6 col-xl-auto">
                <a href="{$s360_clerk_store.helpers->getFullAdminTabUrl($s360_clerk_store.tabname)}" class="btn btn-link ">{__('cancel')}</a>
            </div>
            <div class="ml-auto col-sm-6 col-xl-auto">
                <button name="saving" type="submit" value="1" class="btn btn-primary btn-block">
                    <i class="fal fa-save"></i> {__('save')}
                </button>
            </div>
        </div>
    </div>
</form>