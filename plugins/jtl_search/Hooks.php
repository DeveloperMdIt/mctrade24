<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use Exception;
use JTL\Cron\JobQueue;
use JTL\DB\DbInterface;
use JTL\Filter\Items\Sort;
use JTL\Filter\Option;
use JTL\Filter\SearchResults;
use JTL\Helpers\Product;
use JTL\Helpers\Request;
use JTL\Plugin\PluginInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class Hooks
 * @package Plugin\jtl_search
 */
class Hooks
{
    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var PluginInterface
     */
    private PluginInterface $plugin;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Hooks constructor.
     * @param PluginInterface $plugin
     * @param DbInterface     $db
     * @param LoggerInterface $logger
     */
    public function __construct(PluginInterface $plugin, DbInterface $db, LoggerInterface $logger)
    {
        $this->db     = $db;
        $this->plugin = $plugin;
        $this->logger = $logger;
        require_once $plugin->getPaths()->getBasePath() . 'includes/defines_inc.php';
    }

    /**
     * @return bool
     */
    private function validateServerData(): bool
    {
        return \strlen($this->plugin->getConfig()->getValue('cProjectId')) > 0
            && \strlen($this->plugin->getConfig()->getValue('cAuthHash')) > 0
            && \strlen($this->plugin->getConfig()->getValue('cServerUrl')) > 0;
    }

    /**
     * HOOK_FILTER_PAGE
     */
    public function exec22(): void
    {
        $searchResults = Shop::Smarty()->getTemplateVars('Suchergebnisse');
        if ($searchResults === null) {
            return;
        }
        $productFilter = Shop::getProductFilter();
        if (!$productFilter->isExtendedJTLSearch()) {
            return;
        }
        /** @var SearchResults $searchResults */
        $options          = [];
        $names            = [
            'suche_sortierprio_name',
            'suche_sortierprio_name_ab',
            'suche_sortierprio_preis',
            'suche_sortierprio_preis_ab'
        ];
        $values           = [
            \SEARCH_SORT_NAME_ASC,
            \SEARCH_SORT_NAME_DESC,
            \SEARCH_SORT_PRICE_ASC,
            \SEARCH_SORT_PRICE_DESC
        ];
        $languages        = ['sortNameAsc', 'sortNameDesc', 'sortPriceAsc', 'sortPriceDesc'];
        $additionalFilter = new Sort($productFilter);
        $activeSortType   = (int)($_SESSION['Usersortierung'] ?? -1);
        foreach ($names as $i => $name) {
            $obj = new Option();
            $obj->setIsActive($activeSortType === $values[$i]);
            $obj->setValue($values[$i]);
            $obj->setName(Shop::Lang()->get($languages[$i], 'global'));
            $obj->setURL($productFilter->getFilterURL()->getURL($additionalFilter->init($values[$i])));

            $options[] = $obj;
        }
        $searchResults->setSortingOptions($options);
        $searchResults->setLimitOptions($productFilter->getLimits()->getOptions());
    }

    /**
     * HOOK_SEOCHECK_ANFANG
     *
     * @param array $args
     */
    public function exec142(array $args): void
    {
        $search     = Request::getVar('jtlsearch');
        $setQueue   = Request::getInt('jtlsearchsetqueue', null);
        $changeCron = Request::getVar('jtlsearch_change_cron');
        $delExport  = Request::verifyGPDataString('a') === 'delexport';
        if ($search === null && $setQueue === null && $changeCron === null && $delExport === false) {
            return;
        }
        $serverInfo             = new stdClass();
        $serverInfo->cProjectId = $this->plugin->getConfig()->getValue('cProjectId');
        $serverInfo->cAuthHash  = $this->plugin->getConfig()->getValue('cAuthHash');
        $serverInfo->cServerUrl = $this->plugin->getConfig()->getValue('cServerUrl');
        if ($delExport === true) {
            $sec = new Security($serverInfo->cProjectId, $serverInfo->cAuthHash);
            $sec->setParams(['delexport', Request::verifyGPDataString('url')]);
            if ($sec->createKey() === Request::verifyGPDataString('p')) {
                $urls = \parse_url(\urldecode(Request::verifyGPDataString('url')));
                if (
                    isset($urls['path'])
                    && \strlen(\substr($urls['path'], \strrpos($urls['path'], '/') + 1)) > 3
                    && \file_exists(
                        \JTLSEARCH_PFAD_EXPORTFILE_DIR . \substr($urls['path'], \strrpos($urls['path'], '/') + 1)
                    )
                ) {
                    \ob_start();
                    if (
                        \unlink(
                            \JTLSEARCH_PFAD_EXPORTFILE_DIR . \substr($urls['path'], \strrpos($urls['path'], '/') + 1)
                        )
                    ) {
                        \ob_clean();
                    } else {
                        \ob_get_flush();
                    }

                    die('1');
                }
            }
            die('0');
        }

        if (!Shop::isAdmin()) {
            die('Nicht als Admin angemeldet.');
        }
        if ($search === 'true') {
            $export = new ManageExport($this->logger, $this->db, $serverInfo);
            $export->doExport(Request::getInt('nExportMethod', 0));
            die();
        }
        if ($setQueue !== null) {
            $export = new ManageExport($this->logger, $this->db, $serverInfo);
            $export->newQueue($setQueue);
            die();
        }

        if ($changeCron !== null) {
            if (
                \preg_match(
                    '/^([0-3][0-9][.][0-1][0-9][.][0-9]{4}[ ][0-2][0-9][:][0-6][0-9])/',
                    $_POST['dStart'] ?? ''
                )
            ) {
                $starts = \explode(' ', $_POST['dStart']);
                $times  = \explode(':', $starts[1]);
                $dates  = \explode('.', $starts[0]);
                $dStart = \mktime((int)$times[0], (int)$times[1], 0, (int)$dates[1], (int)$dates[0], (int)$dates[2]);
                $this->db->queryPrepared(
                    "UPDATE tcron 
                        SET startDate = FROM_UNIXTIME(:strt),
                        lastStart = 0,
                        startTime = FROM_UNIXTIME(:strt) 
                        WHERE jobType = 'JTLSearchExport'",
                    ['strt' => $dStart]
                );
                $res         = new stdClass();
                $res->bError = 0;
                $res->cDatum = \date('d.m.Y', $dStart);
                $res->cZeit  = \date('H:i', $dStart);

                die(\json_encode($res));
            }

            $res           = new stdClass();
            $res->bError   = 1;
            $res->cMessage = \__('errorDateInvalid');
            die(\json_encode($res));
        }
    }

    /**
     * HOOK_LASTJOBS_HOLEJOBS
     *
     * @param array $args
     */
    public function exec134(array $args): void
    {
        $documentCount = (int)$this->db->getSingleObject(
            'SELECT COUNT(*) AS cnt FROM tjtlsearchdeltaexport'
        )->cnt;
        if ($documentCount === 0) {
            return;
        }
        // Alle Search-Einträge aus der Jobque entfernen
        $this->db->query('DELETE FROM tjobqueue WHERE jobType = "JTLSearchExport"');
        $this->db->query('UPDATE tjtlsearchexportqueue SET bFinished = 1 WHERE nExportMethod = 1');

        if ($documentCount <= \JTLSEARCH_DELTAEXPORT_ITEMS_MAX) {
            $this->db->query(
                "INSERT INTO `tjobqueue` (`cronID`, `jobType`, `tableName`,
                     `foreignKey`, `tasksExecuted`, `taskLimit`, `isRunning`, `startTime`)
                    VALUES ('0', 'JTLSearchExport', 'JTLSearchDeltaExportCron', 'kId', '0', "
                . \JTLSEARCH_LIMIT_N_METHOD_3 . ", '0', NOW());"
            );
        } else {
            // Neuen Eintrag in Jobqueue schreiben
            $tmpJobQ             = new JobQueue();
            $tmpJobQ->kKey       = $this->plugin->getID();
            $tmpJobQ->cKey       = 'kExportqueue';
            $tmpJobQ->cTabelle   = 'tjtlsearchexportqueue';
            $tmpJobQ->cJobArt    = 'JTLSearchExport';
            $tmpJobQ->dStartZeit = \date('Y-m-d H:i:s');
            $tmpJobQ->nLimitN    = 0;
            $tmpJobQ->nLimitM    = \JTLSEARCH_LIMIT_N_METHOD_1;
            $tmpJobQ->nInArbeit  = 0;
            $tmpJobQ->speicherJobInDB();
            $this->db->query('TRUNCATE TABLE tjtlsearchdeltaexport');
        }
        if ($this->plugin->getConfig()->getValue('jtlsearch_export_updates') === 'delta') {
            $tmpJobQ  = $this->db->getSingleObject(
                'SELECT * FROM tjobqueue WHERE jobType = "JTLSearchExport" ORDER BY tableName ASC LIMIT 0, 1'
            );
            $jobQueue = new JobQueue(
                (int)$tmpJobQ->jobQueueID,
                (int)$tmpJobQ->cronID,
                (int)$tmpJobQ->foreignKeyID,
                (int)$tmpJobQ->tasksExecuted,
                (int)$tmpJobQ->taskLimit,
                0,
                $tmpJobQ->jobType ?? 'JTLSearchExport',
                $tmpJobQ->tableName,
                $tmpJobQ->foreignKey,
                $tmpJobQ->startTime,
                $tmpJobQ->lastStart
            );
            $this->runCronManually($jobQueue);
        }
    }

    /**
     * @param JobQueue $jobQueue
     */
    public function runCronManually(JobQueue $jobQueue): void
    {
        if ($jobQueue->getCJobArt() !== 'JTLSearchExport') {
            return;
        }
        $serverInfo             = new stdClass();
        $serverInfo->cProjectId = $this->plugin->getConfig()->getValue('cProjectId');
        $serverInfo->cAuthHash  = $this->plugin->getConfig()->getValue('cAuthHash');
        $serverInfo->cServerUrl = $this->plugin->getConfig()->getValue('cServerUrl');
        $export                 = new ManageExport($this->logger, $this->db, $serverInfo);
        $this->logger->debug(\sprintf(\__('loggerCronStarted'), __CLASS__, $jobQueue->nLimitN));
        $jobQueue->nInArbeit = 1;
        $jobQueue->updateJobInDB();

        if ($jobQueue->cTabelle === 'JTLSearchDeltaExportCron') {
            $exportMethodType = 3;
            $this->logger->debug(\sprintf(\__('loggerExportMethodDelta'), __CLASS__, $exportMethodType));
        } else {
            $exportMethodType = 1;
            $this->logger->debug(\sprintf(\__('loggerExportMethodPlanner'), __CLASS__, $exportMethodType));
        }
        if ($jobQueue->nLimitN === 0) {
            $this->logger->debug(\sprintf(\__('loggerNewQueue'), __CLASS__));
            $export->newQueue($exportMethodType);
        }
        $res = $export->doExport($exportMethodType);
        if (isset($res) && \is_object($res)) {
            if ($res->nReturnCode === ManageBase::STATUS_DONE) {
                $this->logger->debug(\sprintf(\__('loggerExportDone'), __CLASS__));
                $jobQueue->deleteJobInDB();
            } else {
                $jobQueue->nLimitN   = $res->nExported;
                $jobQueue->nInArbeit = 0;
                $jobQueue->updateJobInDB();
            }
        } else {
            $this->logger->debug(\sprintf(\__('loggerErrorCronResult'), __CLASS__));
            $jobQueue->nInArbeit = 0;
            $jobQueue->updateJobInDB();
        }
    }

    /**
     * HOOK_ARTIKEL_XML_BEARBEITEDELETES
     *
     * @param array $args
     */
    public function exec152(array $args): void
    {
        try {
            if (isset($args['kArtikel']) && $args['kArtikel'] > 0) {
                $ins                = new stdClass();
                $ins->kId           = (int)$args['kArtikel'];
                $ins->eDocumentType = 'product';
                $ins->bDelete       = 1;
                $ins->dLastModified = 'now()';

                $this->db->query(
                    'REPLACE INTO tjtlsearchdeltaexport 
                        VALUES (' . $ins->kId . ', "' . $ins->eDocumentType
                    . '", ' . $ins->bDelete . ', ' . $ins->dLastModified . ')'
                );
            }
        } catch (Exception $e) {
            $this->logger->error('Exception@deltaexportProductDeletes: ' . $e->getMessage());
        }
    }

    /**
     * HOOK_KATEGORIE_XML_BEARBEITEDELETES
     *
     * @param array $args
     */
    public function exec172(array $args): void
    {
        try {
            if (isset($args['kKategorie']) && $args['kKategorie'] > 0) {
                $ins                = new stdClass();
                $ins->kId           = (int)$args['kKategorie'];
                $ins->eDocumentType = 'category';
                $ins->bDelete       = 1;
                $ins->dLastModified = 'now()';

                $this->db->query(
                    'REPLACE INTO tjtlsearchdeltaexport 
                        VALUES (' . $ins->kId . ', "' . $ins->eDocumentType
                    . '", ' . $ins->bDelete . ', ' . $ins->dLastModified . ')'
                );
            }
        } catch (Exception $e) {
            $this->logger->error('Exception@deltaexportCategoryDelete: ' . $e->getMessage());
        }
    }

    /**
     * HOOK_HERSTELLER_XML_BEARBEITEDELETES
     *
     * @param array $args
     */
    public function exec171(array $args): void
    {
        try {
            if (isset($args['kHersteller']) && $args['kHersteller'] > 0) {
                $ins                = new stdClass();
                $ins->kId           = (int)$args['kHersteller'];
                $ins->eDocumentType = 'manufacturer';
                $ins->bDelete       = 1;
                $ins->dLastModified = 'now()';

                $this->db->query(
                    'REPLACE INTO tjtlsearchdeltaexport 
                        VALUES (' . $ins->kId . ', "' . $ins->eDocumentType
                    . '", ' . $ins->bDelete . ', ' . $ins->dLastModified . ')'
                );
            }
        } catch (Exception $e) {
            $this->logger->error('Exception@deltaexportManufacturerDeletes: ' . $e->getMessage());
        }
    }

    /**
     * HOOK_KATEGORIE_XML_BEARBEITEINSERT
     *
     * @param array $args
     */
    public function exec174(array $args): void
    {
        try {
            if (isset($args['oKategorie']->kKategorie) && $args['oKategorie']->kKategorie > 0) {
                $ins                = new stdClass();
                $ins->kId           = (int)$args['oKategorie']->kKategorie;
                $ins->eDocumentType = 'category';
                $ins->bDelete       = 0;
                $ins->dLastModified = 'now()';

                $this->db->query(
                    'REPLACE INTO tjtlsearchdeltaexport 
                        VALUES (' . $ins->kId . ', "' . $ins->eDocumentType
                    . '", ' . $ins->bDelete . ', ' . $ins->dLastModified . ')'
                );
            }
        } catch (Exception $e) {
            $this->logger->error('Exception@deltaexportCategory: ' . $e->getMessage());
        }
    }

    /**
     * HOOK_HERSTELLER_XML_BEARBEITEINSERT
     *
     * @param array $args
     */
    public function exec173(array $args): void
    {
        try {
            if (isset($args['oHersteller']->kHersteller) && $args['oHersteller']->kHersteller > 0) {
                $ins                = new stdClass();
                $ins->kId           = (int)$args['oHersteller']->kHersteller;
                $ins->eDocumentType = 'manufacturer';
                $ins->bDelete       = 0;
                $ins->dLastModified = 'now()';

                $this->db->query(
                    'REPLACE INTO tjtlsearchdeltaexport 
                        VALUES (' . $ins->kId
                    . ', "' . $ins->eDocumentType
                    . '", ' . $ins->bDelete
                    . ', ' . $ins->dLastModified . ')'
                );
            }
        } catch (Exception $e) {
            $this->logger->error('Exception@deltaexportManufacturer: ' . $e->getMessage());
        }
    }

    /**
     * HOOK_QUICKSYNC_XML_BEARBEITEINSERT
     *
     * @param array $args
     */
    public function exec225(array $args): void
    {
        $this->exec151($args);
    }

    /**
     * HOOK_ARTIKEL_XML_BEARBEITEINSERT
     *
     * @param array $args
     */
    public function exec151(array $args): void
    {
        try {
            if (isset($args['oArtikel']->kArtikel) && $args['oArtikel']->kArtikel > 0) {
                $ins                = new stdClass();
                $ins->kId           = (int)$args['oArtikel']->kArtikel;
                $ins->eDocumentType = 'product';
                $ins->bDelete       = 0;
                $ins->dLastModified = 'now()';
                $this->db->query(
                    'REPLACE INTO tjtlsearchdeltaexport 
                        VALUES (' . $ins->kId
                    . ', "' . $ins->eDocumentType
                    . '", ' . $ins->bDelete
                    . ', ' . $ins->dLastModified . ')'
                );
            }
        } catch (Exception $e) {
            $this->logger->error('Exception@deltaexportProduct: ' . $e->getMessage());
        }
    }

    /**
     * HOOK_TOOLS_GLOBAL_CHECKEWARENKORBEINGANG_WUNSCHLISTE
     *
     * @param array $args
     */
    public function exec167(array $args): void
    {
        $this->exec1($args);
    }

    /**
     * @param array $args
     */
    public function exec1(array $args): void
    {
        $AktuellerArtikel = $args['AktuellerArtikel'] ?? $GLOBALS['AktuellerArtikel'];

        if (
            !isset($_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch)
            || !$_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch
            || !$this->validateServerData()
        ) {
            return;
        }
        $trackingCount = \count($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr ?? []);
        if (
            isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr)
            && \is_array($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr)
            && $trackingCount > 0
        ) {
            $trackings = QueryTracking::orderQueryTrackings($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr);
            foreach ($trackings ?? [] as $tracking) {
                if (!\in_array($AktuellerArtikel->kArtikel, $tracking->nProduct_arr, true)) {
                    continue;
                }
                $tmpID   = $AktuellerArtikel->kArtikel;
                $hitType = \JTLSEARCH_STAT_TYPE_VIEWED;
                if (Request::postInt('fragezumprodukt') === 1) {
                    $hitType = \JTLSEARCH_STAT_TYPE_NOTIFY;
                } elseif (Request::postInt('benachrichtigung_verfuegbarkeit') === 1) {
                    $hitType = \JTLSEARCH_STAT_TYPE_DEMAND;
                } elseif (Request::postInt('artikelweiterempfehlen') === 1) {
                    $hitType = \JTLSEARCH_STAT_TYPE_RECOMMEND;
                } elseif (isset($_POST['Wunschliste']) || isset($_GET['Wunschliste'])) {
                    $hitType = \JTLSEARCH_STAT_TYPE_WISHLIST;
                } elseif (isset($_POST['Vergleichsliste'])) {
                    $hitType = \JTLSEARCH_STAT_TYPE_COMPARE;
                } elseif (Request::postInt('wke') === 1) {
                    if (Product::isParent($AktuellerArtikel->kArtikel)) { // Varikombi
                        $tmpID = Product::getArticleForParent($AktuellerArtikel->kArtikel);
                    }
                    $hitType = \JTLSEARCH_STAT_TYPE_BASKET;
                }

                JtlSearch::doProductStats(
                    (int)$tracking->kQuery,
                    $tmpID,
                    $hitType,
                    $this->plugin->getConfig()->getValue('cProjectId'),
                    $this->plugin->getConfig()->getValue('cAuthHash'),
                    $this->plugin->getConfig()->getValue('cServerUrl')
                );

                break;
            }
        }
    }

    /**
     * HOOK_NAVI_PRESUCHE
     *
     * @param array $args
     */
    public function exec160(array &$args): void
    {
        if (!$this->validateServerData()) {
            return;
        }
        $currentLanguage = $_SESSION['jtl_search_currentiso'] ?? null;

        if (
            isset($_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch, $_SESSION['ExtendedJTLSearch']->nCreateTime)
            && ($_SESSION['ExtendedJTLSearch']->nCreateTime + 300) > \time()
            && $_SESSION['cISOSprache'] === $currentLanguage
        ) {
            $args['bExtendedJTLSearch'] = $_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch;
        } else {
            $return                     = JtlSearch::doCheck(
                Frontend::getCustomerGroup()->getID(),
                Shop::getLanguageCode(),
                Frontend::getCurrency()->getCode(),
                $this->plugin->getConfig()->getValue('cProjectId'),
                $this->plugin->getConfig()->getValue('cAuthHash'),
                \urldecode($this->plugin->getConfig()->getValue('cServerUrl'))
            );
            $args['bExtendedJTLSearch'] = false;
            if (\is_object($return)) {
                $_SESSION['jtl_search_currentiso'] = Shop::getLanguageCode();
                // Server change
                $this->db->update(
                    'tplugineinstellungen',
                    ['kPlugin', 'cName'],
                    [$this->plugin->getID(), 'cServerUrl'],
                    (object)['cWert' => $return->_serverurl]
                );
                $this->db->update(
                    'tjtlsearchserverdata',
                    'cKey',
                    'cServerUrl',
                    (object)['cValue' => $return->_serverurl]
                );

                if ($return->_code === 3) {
                    $args['bExtendedJTLSearch'] = true;
                }
            } else {
                $args['bExtendedJTLSearch'] = $return;
            }

            // Save state into session
            if (!isset($_SESSION['ExtendedJTLSearch'])) {
                $_SESSION['ExtendedJTLSearch']                     = new stdClass();
                $_SESSION['ExtendedJTLSearch']->cQueryTracking_arr = [];
            }

            $_SESSION['ExtendedJTLSearch']->bExtendedJTLSearch = $args['bExtendedJTLSearch'];
            $_SESSION['ExtendedJTLSearch']->nCreateTime        = \time();
        }
    }

    /**
     * HOOK_NAVI_SUCHE
     *
     * @param array $args
     */
    public function exec161(array $args): void
    {
        $gpcSort = Request::verifyGPCDataInt('Sortierung');
        if ($gpcSort > 0) {
            $args['nSortierung'] = $gpcSort;
        }
        $isBot = (isset($_SESSION['oBesucher']->kBesucherBot) && $_SESSION['oBesucher']->kBesucherBot > 0);

        if (
            !(isset($args['bExtendedJTLSearch'], $args['cValue'])
                && !$isBot
                && $args['bExtendedJTLSearch']
                && $args['nArtikelProSeite']
                && $args['nSeite']
                && $this->validateServerData())
        ) {
            return;
        }
        $filters   = JtlSearch::getFilter($_GET);
        $filterURL = JtlSearch::buildFilterURL($filters);
        $sorting   = JtlSearch::getSorting(
            $args['nSortierung'],
            Frontend::getCurrency()->getCode(),
            Frontend::getCustomerGroup()->getID()
        );
        $totalTime = \microtime(true);
        $validator = new QueryValidator($this->plugin);

        $args['oExtendedJTLSearchResponse'] = $validator->validate($args['cValue']) === true
            ? JtlSearch::doSearch(
                \md5(\session_id()),
                Frontend::getCustomerGroup()->getID(),
                Shop::getLanguageCode(),
                Frontend::getCurrency()->getCode(),
                $args['cValue'],
                $this->plugin->getConfig()->getValue('cProjectId'),
                $this->plugin->getConfig()->getValue('cAuthHash'),
                \urldecode($this->plugin->getConfig()->getValue('cServerUrl')),
                $args['nArtikelProSeite'],
                $args['nSeite'],
                $args['bLagerbeachten'],
                $filterURL,
                $sorting
            )
            : null;
        if (
            $args['oExtendedJTLSearchResponse'] === null
            || !isset($args['oExtendedJTLSearchResponse']->oSearch)
            || !\is_object($args['oExtendedJTLSearchResponse']->oSearch)
        ) {
            $args['bExtendedJTLSearch'] = false;
            return;
        }
        $args['oExtendedJTLSearchResponse']->oSearch->cFilterShopURL = '';
        if ((int)($args['oExtendedJTLSearchResponse']->oSearch->nStatus ?? 0) === 1) {
            $query = $args['oExtendedJTLSearchResponse']->oSearch->cQuery;
            // QueryTracking
            if (isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[\strtolower($query)])) {
                $qt = $_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[\strtolower($query)];
                QueryTracking::addProducts(
                    QueryTracking::filterProductKeys($args['oExtendedJTLSearchResponse']->oSearch->oItem_arr),
                    $qt->nProduct_arr
                );
            } else {
                $oldCount = 0;
                if (isset($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr)) {
                    $oldCount = \count($_SESSION['ExtendedJTLSearch']->oQueryTracking_arr);
                }
                $qt                 = new stdClass();
                $qt->cQuery         = $query;
                $qt->kQuery         = (int)$args['oExtendedJTLSearchResponse']->oSearch->kQuery;
                $qt->nProduct_arr   = QueryTracking::filterProductKeys(
                    $args['oExtendedJTLSearchResponse']->oSearch->oItem_arr
                );
                $qt->nQueryTracking = $oldCount++;

                $_SESSION['ExtendedJTLSearch']->oQueryTracking_arr[\strtolower($query)] = $qt;
            }

            $shopURL = Shop::getURL() . '/?suchausdruck=' . \urlencode($args['cValue']);
            JtlSearch::extendFilterItemURL(
                $args['oExtendedJTLSearchResponse']->oSearch->oFilterGroup_arr,
                \count($filters),
                $shopURL
            );
            $args['bExtendedJTLSearch']                                  = true;
            $args['oExtendedJTLSearchResponse']->oSearch->cFilterShopURL = JtlSearch::buildFilterShopURL($filters);

            $path = $this->plugin->getPaths()->getFrontendURL();
            Shop::Smarty()->assign(
                'cExtendedJTLSearchURL',
                JtlSearch::extendFilterStandaloneURL(
                    $args['oExtendedJTLSearchResponse']->oSearch->oFilterGroup_arr ?? [],
                    $shopURL
                )
            )
                ->assign('path_jq_ui', $path . 'js/jquery-ui.min.js')
                ->assign('path_jq_migrate', $path . 'js/jquery-migrate-1.4.1.min.js')
                ->assign('oExtendedJTLSearchResponse', $args['oExtendedJTLSearchResponse'])
                ->assign('nStatedFilterCount', \count($filters))
                ->assign('nOverallTime', (\microtime(true) - $totalTime));
        }
    }

    /**
     * HOOK_FILTER_INC_GIBARTIKELKEYS
     *
     * @param array $args
     */
    public function exec178(array &$args): void
    {
        if ($args['bExtendedJTLSearch'] !== true) {
            return;
        }
        $newKeys                 = $this->getSearchProductIDs($args['oExtendedJTLSearchResponse']);
        $args['oArtikelKey_arr'] = \collect($newKeys);
    }


    /**
     * @param stdClass $searchResponse
     * @return int[]
     * @former gibArtikelKeysExtendedJTLSearch()
     */
    private function getSearchProductIDs(stdClass $searchResponse): array
    {
        $productIDs = [];
        if (
            isset($searchResponse->oSearch->oItem_arr)
            && \is_array($searchResponse->oSearch->oItem_arr)
            && \count($searchResponse->oSearch->oItem_arr) > 0
        ) {
            // Artikelkeys in der Session halten, da andere Seite wie z.B.
            // artikel.php auf die voherige Artikelübersicht Daten aufbaut.
            $_SESSION['oArtikelUebersichtKey_arr']   = [];
            $_SESSION['nArtikelUebersichtVLKey_arr'] = [];
            $actual                                  = \count($searchResponse->oSearch->oItem_arr);
            $total                                   = $searchResponse->oSearch->nItemFound;
            if ($total > $actual) {
                $productIDs = \array_fill(0, $total, -1);
                $idx        = $searchResponse->oSearch->nStart;
                foreach ($searchResponse->oSearch->oItem_arr as $item) {
                    $productIDs[$idx] = (int)$item->nId;
                    ++$idx;
                }
            } else {
                foreach ($searchResponse->oSearch->oItem_arr as $item) {
                    $productIDs[] = (int)$item->nId;
                }
            }
        }

        return $productIDs;
    }
}
