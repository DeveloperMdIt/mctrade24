<?php

declare(strict_types=1);

namespace JTL\Router;

use JTL\Catalog\Wishlist\Wishlist;
use JTL\Filter\ProductFilter;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Helpers\Text;

/**
 * Class State
 * @package JTL\Router
 */
class State
{
    public bool $is404 = false;

    public int $pageType = \PAGE_UNBEKANNT;

    public int $itemID = 0;

    public string $type = '';

    public int $languageID = 0;

    public string $slug = '';

    public int $pageID = 0;

    /**
     * @var array<string, int>
     */
    public array $customFilters = [];

    /**
     * @var int[]
     */
    public array $characteristicFilterIDs = [];

    /**
     * @var int[]
     */
    public array $searchFilterIDs = [];

    public int $manufacturerFilterID = 0;

    /**
     * @var int[]
     */
    public array $manufacturerFilterIDs = [];

    public int $categoryFilterID = 0;

    /**
     * @var int[]
     */
    public array $categoryFilterIDs = [];

    /**
     * @var array<string, string>
     */
    public array $caseMismatches = [];

    /**
     * @var int[]
     */
    public array $manufacturers = [];

    public bool $categoryFilterNotFound = false;

    public bool $manufacturerFilterNotFound = false;

    public bool $characteristicNotFound = false;

    public int $configItemID = 0;

    public int $categoryID = 0;

    public int $productID = 0;

    public int $childProductID = 0;

    public int $linkID = 0;

    public int $manufacturerID = 0;

    public int $searchQueryID = 0;

    public int $characteristicID = 0;

    public int $searchSpecialID = 0;

    public int $newsItemID = 0;

    public int $newsOverviewID = 0;

    public int $newsCategoryID = 0;

    public int $ratingFilterID = 0;

    public int $searchFilterID = 0;

    public int $searchSpecialFilterID = 0;

    /**
     * @var int[]
     */
    public array $searchSpecialFilterIDs = [];

    public int $viewMode = 0;

    public int $sortID = 0;

    public int $show = 0;

    public int $compareListID = 0;

    public int $linkType = 0;

    public int $stars = 0;

    public int $wishlistID = 0;

    public int $count = 0;

    public int $productsPerPage = 0;

    public string $priceRangeFilter = '';

    public string $canonicalURL = '';

    public string $date = '';

    public string $optinCode = '';

    public string $searchQuery = '';

    public string $fileName = '';

    public ?string $currentRouteName = null;

    /**
     * @var array{id: int, slug:string}|null
     */
    public ?array $routeData = null;

    /**
     * @var array<string, string>
     */
    private static array $mapping = [
        'kKonfigPos'             => 'configItemID',
        'kKategorie'             => 'categoryID',
        'kArtikel'               => 'productID',
        'kVariKindArtikel'       => 'childProductID',
        'kSeite'                 => 'pageID',
        'kLink'                  => 'linkID',
        'kHersteller'            => 'manufacturerID',
        'kSuchanfrage'           => 'searchQueryID',
        'kMerkmalWert'           => 'characteristicID',
        'kSuchspecial'           => 'searchSpecialID',
        'suchspecial'            => 'searchSpecialID',
        'kNews'                  => 'newsItemID',
        'kNewsMonatsUebersicht'  => 'newsOverviewID',
        'kNewsKategorie'         => 'newsCategoryID',
        'nBewertungSterneFilter' => 'ratingFilterID',
        'cPreisspannenFilter'    => 'priceRangeFilter',
        'manufacturerFilters'    => 'manufacturerFilterIDs',
        'kHerstellerFilter'      => 'manufacturerFilterID',
        'categoryFilters'        => 'categoryFilterIDs',
        'MerkmalFilter_arr'      => 'characteristicFilterIDs',
        'kKategorieFilter'       => 'categoryFilterID',
        'searchSpecialFilters'   => 'searchSpecialFilterIDs',
        'kSuchFilter'            => 'searchFilterID',
        'kSuchspecialFilter'     => 'searchSpecialFilterID',
        'SuchFilter_arr'         => 'searchFilterIDs',
        'nDarstellung'           => 'viewMode',
        'nSort'                  => 'sortID',
        'nSortierung'            => 'sortID',
        'show'                   => 'show',
        'vergleichsliste'        => 'compareListID',
        'bFileNotFound'          => 'is404',
        'is404'                  => 'is404',
        'cCanonicalURL'          => 'canonicalURL',
        'nLinkart'               => 'linkType',
        'nSterne'                => 'stars',
        'kWunschliste'           => 'wishlistID',
        'nNewsKat'               => 'newsCategoryID',
        'cDatum'                 => 'date',
        'nAnzahl'                => 'count',
        'optinCode'              => 'optinCode',
        'cSuche'                 => 'searchQuery',
        'nArtikelProSeite'       => 'productsPerPage',
    ];

    /**
     * @return string[]
     */
    public function getMapping(): array
    {
        return self::$mapping;
    }

    public function initFromRequest(): void
    {
        $this->configItemID          = Request::verifyGPCDataInt(\QUERY_PARAM_CONFIG_ITEM);
        $this->categoryID            = Request::verifyGPCDataInt(\QUERY_PARAM_CATEGORY);
        $this->productID             = Request::verifyGPCDataInt(\QUERY_PARAM_PRODUCT);
        $this->childProductID        = Request::verifyGPCDataInt(\QUERY_PARAM_CHILD_PRODUCT);
        $this->pageID                = Request::verifyGPCDataInt(\QUERY_PARAM_PAGE);
        $this->linkID                = Request::verifyGPCDataInt(\QUERY_PARAM_LINK);
        $this->manufacturerID        = Request::verifyGPCDataInt(\QUERY_PARAM_MANUFACTURER);
        $this->searchQueryID         = Request::verifyGPCDataInt(\QUERY_PARAM_SEARCH_QUERY_ID);
        $this->characteristicID      = Request::verifyGPCDataInt(\QUERY_PARAM_CHARACTERISTIC_VALUE);
        $this->searchSpecialID       = Request::verifyGPCDataInt(\QUERY_PARAM_SEARCH_SPECIAL);
        $this->newsItemID            = Request::verifyGPCDataInt(\QUERY_PARAM_NEWS_ITEM);
        $this->newsOverviewID        = Request::verifyGPCDataInt(\QUERY_PARAM_NEWS_OVERVIEW);
        $this->newsCategoryID        = Request::verifyGPCDataInt(\QUERY_PARAM_NEWS_CATEGORY);
        $this->ratingFilterID        = Request::verifyGPCDataInt(\QUERY_PARAM_RATING_FILTER);
        $this->priceRangeFilter      = Request::verifyGPDataString(\QUERY_PARAM_PRICE_FILTER);
        $this->manufacturerFilterIDs = Request::verifyGPDataIntegerArray(\QUERY_PARAM_MANUFACTURER_FILTER);
        $this->manufacturerFilterID  = \count($this->manufacturerFilterIDs) > 0
            ? $this->manufacturerFilterIDs[0]
            : 0;

        $this->categoryFilterIDs      = Request::verifyGPDataIntegerArray(\QUERY_PARAM_CATEGORY_FILTER);
        $this->categoryFilterID       = \count($this->categoryFilterIDs) > 0
            ? $this->categoryFilterIDs[0]
            : 0;
        $this->searchSpecialFilterIDs = Request::verifyGPDataIntegerArray(\QUERY_PARAM_SEARCH_SPECIAL_FILTER);
        $this->searchFilterID         = Request::verifyGPCDataInt(\QUERY_PARAM_SEARCH_FILTER);
        $this->searchSpecialFilterID  = \count($this->searchSpecialFilterIDs) > 0
            ? $this->searchSpecialFilterIDs[0]
            : 0;
        $this->viewMode               = Request::verifyGPCDataInt(\QUERY_PARAM_VIEW_MODE);
        $this->sortID                 = Request::verifyGPCDataInt(\QUERY_PARAM_SORT);
        $this->show                   = Request::verifyGPCDataInt(\QUERY_PARAM_SHOW);
        $this->compareListID          = Request::verifyGPCDataInt(\QUERY_PARAM_COMPARELIST);
        $this->stars                  = Request::verifyGPCDataInt(\QUERY_PARAM_STARS);
        $this->wishlistID             = Wishlist::checkeParameters();
        if ($this->newsCategoryID === 0) {
            $this->newsCategoryID = Request::verifyGPCDataInt(\QUERY_PARAM_NEWS_CATEGORY);
        }
        $this->date      = Request::verifyGPDataString(\QUERY_PARAM_DATE);
        $this->count     = Request::verifyGPCDataInt(\QUERY_PARAM_QTY);
        $this->optinCode = Request::verifyGPDataString(\QUERY_PARAM_OPTIN_CODE);
        $this->linkID    = Request::verifyGPCDataInt(\QUERY_PARAM_LINK);
        if (($query = Request::verifyGPDataString(\QUERY_PARAM_SEARCH_QUERY)) !== '') {
            $this->searchQuery = Text::xssClean($query);
        } elseif (($term = Request::verifyGPDataString(\QUERY_PARAM_SEARCH_TERM)) !== '') {
            $this->searchQuery = Text::xssClean($term);
        } else {
            $this->searchQuery = Text::xssClean(Request::verifyGPDataString(\QUERY_PARAM_SEARCH));
        }
        $this->productsPerPage = Request::verifyGPCDataInt(\QUERY_PARAM_PRODUCTS_PER_PAGE);
        if ($this->productID > 0) {
            $this->type = 'kArtikel';
            if (Product::isVariChild($this->productID)) {
                $this->childProductID = $this->productID;
                $this->productID      = Product::getParent($this->productID);
            }
            $this->itemID = $this->productID;
        } elseif ($this->categoryID > 0) {
            $this->type   = 'kKategorie';
            $this->itemID = $this->categoryID;
        } elseif ($this->manufacturerID > 0) {
            $this->type   = 'kHersteller';
            $this->itemID = $this->manufacturerID;
        } elseif ($this->linkID > 0) {
            $this->type   = 'kLink';
            $this->itemID = $this->linkID;
        } elseif ($this->characteristicID > 0) {
            $this->type   = 'kMerkmalWert';
            $this->itemID = $this->characteristicID;
        } elseif ($this->newsItemID > 0) {
            $this->type   = 'kNews';
            $this->itemID = $this->newsItemID;
        } elseif ($this->newsCategoryID > 0) {
            $this->type   = 'kNewsKategorie';
            $this->itemID = $this->newsCategoryID;
        } elseif ($this->newsOverviewID > 0) {
            $this->type   = 'kNewsMonatsUebersicht';
            $this->itemID = $this->newsOverviewID;
        } elseif ($this->searchQueryID > 0) {
            $this->type   = 'kSuchanfrage';
            $this->itemID = $this->searchQueryID;
        } elseif ($this->searchSpecialID > 0) {
            $this->type   = 'suchspecial';
            $this->itemID = $this->searchSpecialID;
        }
        $this->characteristicFilterIDs = ProductFilter::initCharacteristicFilter();
        $this->searchFilterIDs         = ProductFilter::initSearchFilter();
        $this->categoryFilterIDs       = ProductFilter::initCategoryFilter();
    }

    /**
     * @return array{kKonfigPos: int, kKategorie: int, kArtikel: int, kVariKindArtikel: int, kSeite: int, kLink: int,
     *     kHersteller: int, kSuchanfrage: int, kMerkmalWert: int, kSuchspecial: int, suchspecial: int, kNews: int,
     *     kNewsMonatsUebersicht: int, kNewsKategorie: int, nBewertungSterneFilter: int, cPreisspannenFilter: string,
     *     manufacturerFilters: int[], kHerstellerFilter: int, categoryFilters: int[], MerkmalFilter_arr: int[],
     *     kKategorieFilter: int, searchSpecialFilters: int[], kSuchFilter: int, kSuchspecialFilter: int,
     *     SuchFilter_arr: int[], nDarstellung: int, nSort: int, nSortierung: int, show: int, vergleichsliste: int,
     *     bFileNotFound: bool, is404: bool, cCanonicalURL: string, nLinkart: int, nSterne: int, kWunschliste: int,
     *     nAnzahl: int, optinCode: string, cSuche: string, nArtikelProSeite: int}
     */
    public function getAsParams(): array
    {
        return \array_map(function (string $new) {
            return $this->{$new};
        }, $this->getMapping());
    }

    public function filtersValid(): bool
    {
        return $this->characteristicNotFound === false
            && $this->manufacturerFilterNotFound === false
            && $this->categoryFilterNotFound === false;
    }
}
