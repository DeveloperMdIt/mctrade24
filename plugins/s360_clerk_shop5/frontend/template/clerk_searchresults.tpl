{* @var Plugin\s360_clerk_shop5\src\Entities\StoreEntity $s360_clerk_store *}
{* @var JTL\Plugin\Data\Config $s360_clerk_settings *}
{* @var array|null $s360_clerk_facets *}
{block name='clerk_search_results'}
    {container fluid=$Link->getIsFluid() class="link-content {if $Einstellungen.template.theme.left_sidebar === 'Y' && $boxesLeftActive}container-plus-sidebar{/if}"}
        {* Build Snippets array *}
        {block name='clerk_search_results_snippets'}
            {lang key='shippingInformation' section='productDetails' assign="shippingTimeNote"}
            {if isset($Firma, $Firma->country, $oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}
                {$shippingTimeNote = sprintf(
                    $shippingTimeNote,
                    $Firma->country->getName(),
                    $oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL(),
                    $oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL())
                }
            {/if}

            {block name='clerk_search_results_snippets_icons'}
                {$clerkIcons = [
                    "ratingEmpty" => 'far fa-star',
                    "ratingHalf" => 'fas fa-star-half-alt',
                    "rating" => 'fas fa-star',
                    "basket" => 'fas fa-shopping-cart'
                ]}

                {* ET Icons *}
                {* {$clerkIcons = [
                    "ratingEmpty" => {et_getIcon key="ratingEmpty"},
                    "ratingHalf" => {et_getIcon key="ratingHalf"},
                    "rating" => {et_getIcon key="rating"},
                    "basket" => {et_getIcon key='basket'}
                ]} *}
            {/block}

            {* settings.moreArticleQuantity => Just for compatability with beta versions, not used in provided designs anymore *}
            {$clerkSnippets = [
                "showAllResults" => {lang key='search_show_all_results' section='s360_clerk_shop5'},
                "textNoResults" => {lang key='no_results' section='s360_clerk_shop5'},
                "loadMore" => {lang key='search_label_load_more' section='s360_clerk_shop5'},
                "loadMoreProgress" => {lang key='search_label_load_more_progress' section='s360_clerk_shop5'},
                "shippingTimeNote" => {$shippingTimeNote|escape:"htmlall"},
                "addToCart" => {lang key="addToCart"},
                "ribbons" => [
                    "1" => {lang key="ribbon-1" section="productOverview"},
                    "2" => {lang key="ribbon-2" section="productOverview"},
                    "3" => {lang key="ribbon-3" section="productOverview"},
                    "4" => {lang key="ribbon-4" section="productOverview"},
                    "5" => {lang key="ribbon-5" section="productOverview"},
                    "6" => {lang key="ribbon-6" section="productOverview"},
                    "7" => {lang key="ribbon-7" section="productOverview"},
                    "8" => {lang key="ribbon-8" section="productOverview"},
                    "9" => {lang key="ribbon-9" section="productOverview"}
                ],
                "priceStarting" => {lang key="priceStarting"},
                "vpePer" => {lang key="vpePer"},
                "shippingTime" => {lang key='shippingTime'},
                "productRating" => {lang key='productRating' section='product rating'},
                "requestNotification" => {lang key='requestNotification'},
                "details" => {lang key='details'},
                "filterAndSort" => {lang key='filterAndSort'},
                "removeFilters" => {lang key='removeFilters'},
                "sorting" => {lang key='sorting' section='productOverview'},
                "sortPriceAsc" => {lang key='sortPriceAsc'},
                "sortPriceDesc" => {lang key='sortPriceDesc'},
                "sortNewestFirst" => {lang key='sortNewestFirst'},
                "sortNameAsc" => {lang key='sortNameAsc'},
                "sortNameDesc" => {lang key='sortNameDesc'},
                "icons" => $clerkIcons,
                "settings" => [
                    "moreArticleQuantity" => 16,
                    "showBrand" => $Einstellungen.artikeluebersicht.artikeluebersicht_hersteller_anzeigen !== 'N',
                    "newProductmaxDays" => $Einstellungen.boxen.box_neuimsortiment_alter_tage,
                    "topProductMinStars" => $Einstellungen.boxen.boxen_topbewertet_minsterne,
                    "bestSellerMinSales" => $Einstellungen.global.global_bestseller_minanzahl,
                    "bestSellerDayRange" => $Einstellungen.global.global_bestseller_tage,
                    "inventoryManagement" => $Einstellungen.global.artikel_ampel_lagernull_gruen,
                    "storageLightsGreen" => $Einstellungen.global.artikel_lagerampel_gruen,
                    "storageLightsRed" => $Einstellungen.global.artikel_lagerampel_rot,
                    "storageLightIcon" => 'fas fa-dot-circle',
                    "storageLightTextGreen" => {lang key='ampelGruen' section='global'},
                    "storageLightTextYellow" => {lang key='ampelGelb' section='global'},
                    "storageLightTextRed" => {lang key='ampelRot' section='global'}
                ]
            ]}
        {/block}

        {* Facetted Search Result *}
        {if isset($s360_clerk_facets) && $s360_clerk_facets.position !== 'none'}
            {block name='clerk_search_results_facetted_search'}
                {if $s360_clerk_facets.position === "left"}
                    {block name='clerk_search_results_facets_left'}
                        <div class="row">
                            <div class="col col-12 col-md-3">
                                <div id="s360_clerk_facets_container" class="d-none d-sm-block"></div>
                                <div class="col col-12 d-sm-none text-center mb-5">
                                    <button class="btn btn-primary" id="clerk-show-all-filter">
                                        {lang key="facets_view_all" section="s360_clerk_shop5"}
                                    </button>
                                </div>
                            </div>
                            <div class="col col-12 col-md-9">
                                <div id="s360_clerk_search_container"></div>
                            </div>
                        </div>
                        <script>
                            $(function () {
                                $("#clerk-show-all-filter").on('click', function () {
                                    $('#s360_clerk_facets_container').removeClass('d-none');
                                    $(this).hide();
                                });
                            });
                        </script>
                    {/block}
                {else}
                    {block name='clerk_search_results_facets_top'}
                        <div id="s360_clerk_facets_container"></div>
                        <div id="s360_clerk_search_container"></div>
                    {/block}
                {/if}
            {/block}
        {else}
            {* Just the search result *}
            {block name='clerk_search_results_search_container'}
                <div id="s360_clerk_search_container"></div>
            {/block}
        {/if}

        {* Api Call / Config *}
        {block name='cleark_search_results_api'}
            <span class="clerk" id="clerk-search"
                data-template="{$s360_clerk_settings->getValue(constant("\Plugin\s360_clerk_shop5\src\Utils\Config::SETTING_SEARCHPAGE_TEMPLATE"))}"
                data-query="{$smarty.get.query|escape:'html'}"
                data-target="#s360_clerk_search_container"
                data-snippets='{json_encode($clerkSnippets)}'
                {if isset($s360_clerk_facets) && $s360_clerk_facets.position !== 'none'}
                    {block name='cleark_search_results_api_facets'}
                        {if $s360_clerk_facets.in_url}data-facets-in-url="true"{/if}
                        {if !empty($s360_clerk_facets.design)}data-facets-design="{$s360_clerk_facets.design|escape:"htmlall"}"{/if}
                        data-facets-target="#s360_clerk_facets_container"
                        data-facets-price-append=" {$smarty.session.Waehrung->getHtmlEntity()}"
                        data-facets-view-more-text="{lang|escape:"htmlall" key='facets_view_more' section='s360_clerk_shop5'}"
                        data-facets-searchbox-text="{lang|escape:"htmlall" key='facets_search_for' section='s360_clerk_shop5'}"
                        data-facets-titles='{json_encode($s360_clerk_facets.titles)}'
                        data-facets-attributes='{json_encode($s360_clerk_facets.attributes)}'
                        data-facets-multiselect-attributes='{json_encode($s360_clerk_facets.multiselect_attributes)}'
                    {/block}
                {/if}
                {block name='cleark_search_results_api_extra'}{/block}
            >
            </span>
        {/block}
    {/container}
{/block}