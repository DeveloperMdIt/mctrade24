<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use Exception;
use JTL\Catalog\Product\Artikel;
use JTL\Helpers\Text;
use JTL\Language\LanguageHelper;
use JTL\Network\Communication;
use JTL\Session\Frontend;
use JTL\Shop;
use stdClass;

/**
 * Class JtlSearch
 * @package Plugin\jtl_search
 */
class JtlSearch
{
    /**
     * @var string
     */
    private static string $projectID = '';

    /**
     * @var string
     */
    private static string $authHash = '';

    /**
     * @var string
     */
    private static string $searchURL = '';

    /**
     * @var string
     */
    private static string $action = '';

    /**
     * @param int         $customerGroupID
     * @param string      $langISO
     * @param string      $currencyISO
     * @param string      $query
     * @param string      $projectID
     * @param string      $authHash
     * @param string      $serverURL
     * @param null|object $response
     * @return array|null
     */
    public static function doSuggest(
        int $customerGroupID,
        string $langISO,
        string $currencyISO,
        string $query,
        string $projectID,
        string $authHash,
        string $serverURL,
        ?object &$response
    ): ?array {
        if ($customerGroupID === 0
            || \strlen($currencyISO) === 0
            || \strlen($query) === 0
            || \strlen($projectID) === 0
            || \strlen($authHash) === 0
            || \strlen($serverURL) === 0
        ) {
            return [];
        }
        self::$projectID = $projectID;
        self::$authHash  = $authHash;
        self::$searchURL = $serverURL;
        self::$action    = 'dosuggest';

        $query    = \mb_convert_encoding($query, 'ISO-8859-1', 'UTF-8');
        $security = new Security(self::$projectID, self::$authHash);
        $security->setParams([self::$action, $query, $customerGroupID, $langISO, $currencyISO]);

        try {
            $responseString = Communication::postData(
                self::$searchURL . 'searchdaemon/index.php',
                [
                    'pid'   => self::$projectID,
                    'p'     => $security->createKey(),
                    'a'     => self::$action,
                    'q'     => $query,
                    'kdgrp' => $customerGroupID,
                    'lang'  => $langISO,
                    'curr'  => $currencyISO
                ],
                true
            );
            $response       = \json_decode($responseString);
            $files          = [];
            foreach ($response->oSuggest->oGroup_arr ?? [] as $group) {
                $files[$group->cType] = $group;
            }

            return $files;
        } catch (Exception $exc) {
            Shop::Container()->getLogService()->warning('Exception@doSuggest: ' . $exc->getMessage());

            return null;
        }
    }

    /**
     * @param string $sessionID
     * @param int    $customerGroupID
     * @param string $languageISO
     * @param string $currencyISO
     * @param string $value
     * @param string $projectID
     * @param string $authHash
     * @param string $serverURL
     * @param int    $productsPerPage
     * @param int    $page
     * @param bool   $checkStock
     * @param string $filter
     * @param string $sort
     * @return mixed|null
     */
    public static function doSearch(
        string $sessionID,
        int $customerGroupID,
        string $languageISO,
        string $currencyISO,
        string $value,
        string $projectID,
        string $authHash,
        string $serverURL,
        int $productsPerPage = 100,
        int $page = 1,
        bool $checkStock = false,
        string $filter = '',
        string $sort = ''
    ) {
        if (
            $customerGroupID === 0
            || \strlen($sessionID) === 0
            || \strlen($currencyISO) === 0
            || \strlen($value) === 0
            || \strlen($projectID) === 0
            || \strlen($authHash) === 0
            || \strlen($serverURL) === 0
        ) {
            return null;
        }
        self::$projectID = $projectID;
        self::$authHash  = $authHash;
        self::$searchURL = $serverURL;
        self::$action    = 'dosearch';

        $start    = ($page - 1) * $productsPerPage;
        $security = new Security(self::$projectID, self::$authHash);
        $security->setParams([
            self::$action,
            $value,
            $productsPerPage,
            $start,
            $filter,
            $customerGroupID,
            $languageISO,
            $currencyISO
        ]);

        try {
            $postData = Communication::postData(
                self::$searchURL . 'searchdaemon/index.php',
                [
                    'pid'     => self::$projectID,
                    'p'       => $security->createKey(),
                    'a'       => self::$action,
                    'q'       => $value,
                    'rows'    => $productsPerPage,
                    'start'   => $start,
                    'filter'  => $filter,
                    'kdgrp'   => $customerGroupID,
                    'lang'    => $languageISO,
                    'curr'    => $currencyISO,
                    'sessid'  => $sessionID,
                    'sort'    => $sort,
                    'instock' => $checkStock
                ],
                true
            );
            $response = \json_decode($postData);
            // LandingPage hit?
            if (isset($response->oLandingPage)) {
                \header('Location: ' . $response->oLandingPage->cPageURL);
                exit();
            }
            self::checkEncoding($response);
            self::addBanner($response);

            return $response;
        } catch (Exception $exc) {
            Shop::Container()->getLogService()->warning('Exception@doSearch: ' . $exc->getMessage());

            return null;
        }
    }

    /**
     * @param mixed $response
     */
    private static function addBanner($response): void
    {
        if (!isset($response->oSearch->oBanner_arr) || \count($response->oSearch->oBanner_arr) === 0) {
            return;
        }
        // Momentan wird nur eine ImageMap angezeigt
        $banner = $response->oSearch->oBanner_arr[0];

        // Nach Shop3 Standard
        $shop3Banner            = new stdClass();
        $shop3Banner->cBildPfad = $banner->cImgUrl;
        $shop3Banner->fWidth    = $banner->nImgWidth;
        $shop3Banner->fHeight   = $banner->nImgHeight;
        $shop3Banner->oArea_arr = [];
        $defaultOptions         = Artikel::getDefaultOptions();
        foreach ($banner->oBannercoord_arr as $area) {
            $shop3Area                = new stdClass();
            $shop3Area->cTitel        = $area->cTitle;
            $shop3Area->cUrl          = '';
            $shop3Area->cBeschreibung = $area->cDescription;
            $shop3Area->kImageMapArea = $area->kBannerCoord;
            $shop3Area->cStyle        = $area->cStyle;
            $shop3Area->oCoords       = new stdClass();
            $shop3Area->oCoords->x    = $area->oCoord->nX;
            $shop3Area->oCoords->y    = $area->oCoord->nY;
            $shop3Area->oCoords->w    = $area->oCoord->nW;
            $shop3Area->oCoords->h    = $area->oCoord->nH;

            if ($area->cKey === 'article' && (int)$area->cValue > 0) {
                $shop3Area->kArtikel = (int)$area->cValue;
                $langID              = LanguageHelper::getDefaultLanguage(true)->getId();
                $customerGroupID     = Frontend::getCustomerGroup()->getID();
                if (Frontend::getCustomer()->getGroupID() > 0) {
                    $customerGroupID = Frontend::getCustomer()->getGroupID();
                }
                $shop3Area->oArtikel = new Artikel();
                $shop3Area->oArtikel->fuelleArtikel(
                    $shop3Area->kArtikel,
                    $defaultOptions,
                    $customerGroupID,
                    $langID
                );

                if (\strlen($shop3Area->cTitel) === 0) {
                    $shop3Area->cTitel = $shop3Area->oArtikel->cName;
                }
                if (\strlen($shop3Area->cUrl) === 0) {
                    $shop3Area->cUrl = $shop3Area->oArtikel->cURL;
                }
                if (\strlen($shop3Area->cBeschreibung) === 0) {
                    $shop3Area->cBeschreibung = $shop3Area->oArtikel->cKurzBeschreibung;
                }
            }

            $shop3Banner->oArea_arr[] = $shop3Area;
        }

        Shop::Smarty()->assign('oImageMap', $shop3Banner);
    }

    /**
     * @param int    $queryID
     * @param int    $productID
     * @param int    $hitType
     * @param string $projectID
     * @param string $authHash
     * @param string $serverURL
     * @return mixed
     */
    public static function doProductStats(
        int $queryID,
        int $productID,
        int $hitType,
        string $projectID,
        string $authHash,
        string $serverURL
    ) {
        self::$projectID = $projectID;
        self::$authHash  = $authHash;
        self::$searchURL = $serverURL;
        self::$action    = 'doproductstats';

        // Security Class
        $security = new Security(self::$projectID, self::$authHash);
        $security->setParams([self::$action, $queryID, $productID, $hitType]);

        try {
            $postData = Communication::postData(
                self::$searchURL . 'searchdaemon/index.php',
                [
                    'pid'     => self::$projectID,
                    'p'       => $security->createKey(),
                    'a'       => self::$action,
                    'query'   => $queryID,
                    'product' => $productID,
                    'hittype' => $hitType
                ]
            );

            $response = \json_decode($postData);

            return $response ?? $postData;
        } catch (Exception $exc) {
            Shop::Container()->getLogService()->warning('Exception@doProductStats: ' . $exc->getMessage());

            return null;
        }
    }

    /**
     * @param string $query
     * @param string $languageISO
     * @param string $projectID
     * @param string $authHash
     * @param string $serverURL
     * @return mixed
     */
    public static function doSuggestForward(
        string $query,
        string $languageISO,
        string $projectID,
        string $authHash,
        string $serverURL
    ) {
        self::$projectID = $projectID;
        self::$authHash  = $authHash;
        self::$searchURL = $serverURL;
        self::$action    = 'dosuggestforward';

        $security = new Security(self::$projectID, self::$authHash);
        $security->setParams([self::$action, $query, $languageISO]);
        try {
            return Communication::postData(
                self::$searchURL . 'searchdaemon/index.php',
                [
                    'pid'     => self::$projectID,
                    'p'       => $security->createKey(),
                    'a'       => self::$action,
                    'query'   => $query,
                    'langiso' => $languageISO
                ]
            );
        } catch (Exception $e) {
            Shop::Container()->getLogService()->warning('Exception@doSuggestForward: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Convert UTF-8 into shop standard encoding
     * @param mixed $response
     */
    private static function checkEncoding($response): void
    {
        foreach ($response->oSearch->oFilterGroup_arr ?? [] as $filterGroup) {
            foreach ($filterGroup->oFilterItem_arr ?? [] as $filterItem) {
                if (
                    $filterGroup->cDataType === 'float'
                    && (int)$filterGroup->nType === 2
                    && \strpos($filterGroup->cName, 'price') !== false
                ) {
                    // Price Slider
                    $filterItem->cUnit = Frontend::getCurrency()->getHtmlEntity();
                }
            }
        }
    }

    /**
     * @param int    $customerGroupID
     * @param string $languageISO
     * @param string $currencyISO
     * @param string $projectID
     * @param string $authHash
     * @param string $serverURL
     * @return mixed
     * true               => ready for searching
     * false              => server not accessible or account closed
     * object oResponse   => server change -> write new data to db
     * oResponse->_code 2 => only change ... solr is not accessible
     * oResponse->_code 3 => change and ready for searching
     */
    public static function doCheck(
        int $customerGroupID,
        string $languageISO,
        string $currencyISO,
        string $projectID,
        string $authHash,
        string $serverURL
    ) {
        if ($customerGroupID === 0
            || \strlen($currencyISO) === 0
            || \strlen($projectID) === 0
            || \strlen($authHash) === 0
            || \strlen($serverURL) === 0
        ) {
            return false;
        }

        self::$projectID = $projectID;
        self::$authHash  = $authHash;
        self::$searchURL = $serverURL;
        self::$action    = 'docheck';

        $security = new Security(self::$projectID, self::$authHash);
        $security->setParams([self::$action, $customerGroupID, $languageISO, $currencyISO]);

        try {
            $postData = Communication::postData(
                self::$searchURL . 'searchdaemon/index.php',
                [
                    'pid'   => self::$projectID,
                    'p'     => $security->createKey(),
                    'a'     => self::$action,
                    'kdgrp' => $customerGroupID,
                    'lang'  => $languageISO,
                    'curr'  => $currencyISO
                ],
                true
            );
            $response = \json_decode($postData);
            if (\is_object($response) && $response->_code === 1) {
                return true;
            }

            if (\is_object($response)
                && ($response->_code === 2 || $response->_code === 3)
                && \strlen($response->_serverurl) > 0
            ) {
                // Server change
                return $response;
            }

            return false;
        } catch (Exception $exc) {
            Shop::Container()->getLogService()->warning('Exception@doCheck: ' . $exc->getMessage());

            return false;
        }
    }

    /**
     * @param array $params
     * @return array
     */
    public static function getFilter(array $params): array
    {
        $filters = [];
        for ($i = 0; $i < 20; $i++) {
            $idx = 'fq' . $i;
            if (isset($params[$idx])) {
                $filters[] = Text::filterXSS($params[$idx]);
            }
        }

        return $filters;
    }

    /**
     * Solr request
     * @param array $filter
     * @return string
     */
    public static function buildFilterURL(array $filter): string
    {
        if (\count($filter) > 0) {
            return \implode('__', $filter);
        }

        return '';
    }

    /**
     * @param array $filters
     * @return string
     */
    public static function buildFilterShopURL(array $filters): string
    {
        $url = '';
        foreach ($filters as $i => $filter) {
            $url .= \urlencode('&fq' . $i . '=' . $filter);
        }

        return $url;
    }

    /**
     * @param array $filterGroups
     * @return array
     */
    private static function buildStatedFilterList(array $filterGroups): array
    {
        $stated = [];
        foreach ($filterGroups as $filterGroup) {
            foreach ($filterGroup->oFilterItem_arr as $filterItem) {
                if ($filterItem->bSet) {
                    if (!isset($stated[$filterGroup->cName])) {
                        $stated[$filterGroup->cName] = [];
                    }

                    $stated[$filterGroup->cName][] = $filterItem->cValue;
                }
            }
        }

        return $stated;
    }

    /**
     * @param array  $stated
     * @param string $groupRel
     * @param string $keyRel
     * @return string
     */
    private static function buildStatedFilterURL(array $stated, string $groupRel = '', string $keyRel = ''): string
    {
        $url         = '';
        $statedCount = 0;
        foreach ($stated as $group => $statedFilters) {
            foreach ($statedFilters as $statedFilter) {
                // Release or set filter
                if (
                    ($group !== $groupRel && $statedFilter !== $keyRel)
                    || ($group === $groupRel && $statedFilter !== $keyRel)
                ) {
                    $url .= '&fq' . $statedCount . '=' . $group . ':'
                        . \urlencode(\mb_convert_encoding($statedFilter, 'ISO-8859-1', 'UTF-8'));
                    $statedCount++;
                }
            }
        }

        return $url;
    }

    /**
     * @param array  $filterGroups
     * @param string $shopURL
     * @return string
     */
    public static function extendFilterStandaloneURL(array $filterGroups, string $shopURL): string
    {
        if (\count($filterGroups) > 0) {
            return $shopURL . self::buildStatedFilterURL(self::buildStatedFilterList($filterGroups));
        }

        return $shopURL;
    }

    /**
     * Extended the filter entries for the cURL variable to release or set filters
     * @param array  $filterGroups
     * @param int    $statedFilterCount
     * @param string $shopURL
     */
    public static function extendFilterItemURL(array $filterGroups, int $statedFilterCount, string $shopURL): void
    {
        if (\count($filterGroups) === 0) {
            return;
        }
        $statedFilterList = self::buildStatedFilterList($filterGroups);
        foreach ($filterGroups as $filterGroup) {
            foreach ($filterGroup->oFilterItem_arr as $filterItem) {
                if ($filterItem->bSet) {
                    $filterItem->cURL =
                        $shopURL
                        . self::buildStatedFilterURL(
                            $statedFilterList,
                            $filterGroup->cName,
                            $filterItem->cValue
                        );
                } else {
                    $filterItem->cURL = $shopURL . self::buildStatedFilterURL($statedFilterList)
                        . '&fq' . $statedFilterCount . '=' . $filterGroup->cName . ':'
                        . \urlencode(\mb_convert_encoding($filterItem->cValue ?? '', 'ISO-8859-1', 'UTF-8'));
                }
            }
        }
    }

    /**
     * @param string $statedFilter
     */
    public static function extendSessionCurrencyURL(string $statedFilter): void
    {
        if (\strlen($statedFilter) === 0) {
            return;
        }
        foreach (Frontend::getCurrencies() as $currency) {
            $url = $currency->getURLFull();
            if (!empty($url)) {
                $currency->setURLFull($url . $statedFilter);
            }
        }
    }

    /**
     * @param string $statedFilter
     */
    public static function extendSessionLanguageURL(string $statedFilter): void
    {
        if (\strlen($statedFilter) === 0) {
            return;
        }
        foreach (Frontend::getLanguages() as $language) {
            $url = $language->getUrl();
            if (!empty($url)) {
                $language->setUrl($url . $statedFilter);
            }
        }
    }

    /**
     * @param int    $sorting
     * @param string $currencyISO
     * @param int    $customerGroupID
     * @return string
     */
    public static function getSorting(int $sorting, string $currencyISO, int $customerGroupID): string
    {
        switch ($sorting) {
            case 1:
                $sort = 'name asc';
                break;
            case 2:
                $sort = 'name desc';
                break;
            case 3:
                $sort = 'price_' . \strtolower($currencyISO) . '_' . $customerGroupID . ' asc';
                break;
            case 4:
                $sort = 'price_' . \strtolower($currencyISO) . '_' . $customerGroupID . ' desc';
                break;
            default:
                $sort = '';
                break;
        }

        return $sort;
    }
}
