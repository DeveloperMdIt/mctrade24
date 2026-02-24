<?php declare(strict_types=1);

namespace Plugin\lfs_it_recht_kanzlei\classes;

use Carbon\Carbon;
use JTL\Alert\Alert;
use JTL\Backend\DirManager;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Helpers\Seo;
use JTL\Minify\MinifyService;
use JTL\Shop;
use JTL\Plugin\PluginInterface;

class ItrkConnector
{
    private PluginInterface $plugin;
    private float $apiVersion = 1.0;
    private array $supportedLegalContent = ['agb', 'datenschutz', 'widerruf', 'impressum'];
    private array $supportedActions = ['push'];
    private string $currentLanguageIso = '';
    private $currentLanguage;
    private int $nError = 0;
    private string $localPdfTargetName = '';
    private string $localPdfStorageDir = '';
    private string $localPdfStorageDirAdmin = '';
    private $pluginsettings;
    private $xmldata;
    private string $cSyncGroups = '';

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * ItrkConnector constructor.
     * @param PluginInterface $oPlugin
     * @param DbInterface     $db
     */
    public function __construct(PluginInterface $oPlugin, DbInterface $db)
    {
        $this->db = $db;
        $this->setPlugin($oPlugin);
        $this->setLocalPdfStorageDir($this->getPlugin()->getPaths()->getBasePath() . 'pdf-dokumente/');
        $this->setLocalPdfStorageDirAdmin(\PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . \PFAD_EMAILPDFS);
        $this->setPluginsettings();
    }

    /**
     * @return string
     */
    public function getLocalPdfTargetName(): string
    {
        return $this->localPdfTargetName;
    }

    /**
     * @param string $localPdfTargetName
     * @return ItrkConnector
     */
    public function setLocalPdfTargetName($localPdfTargetName): ItrkConnector
    {
        $this->localPdfTargetName = $localPdfTargetName;
        return $this;
    }

    /**
     * @return object
     */
    public function getCurrentLanguage(): object
    {
        return $this->currentLanguage;
    }

    /**
     * @param object $currentLanguage
     * @return ItrkConnector
     */
    public function setCurrentLanguage(object $currentLanguage): ItrkConnector
    {
        $this->currentLanguage = $currentLanguage;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentLanguageIso(): string
    {
        return $this->currentLanguageIso;
    }

    /**
     * @param string $currentLanguageIso
     * @return ItrkConnector
     */
    public function setCurrentLanguageIso(string $currentLanguageIso): ItrkConnector
    {
        $this->currentLanguageIso = $currentLanguageIso;
        return $this;
    }

    /**
     * @return string
     */
    public function getLocalPdfStorageDirAdmin(): string
    {
        return $this->localPdfStorageDirAdmin;
    }

    /**
     * @param string $localPdfStorageDirAdmin
     * @return ItrkConnector
     */
    public function setLocalPdfStorageDirAdmin($localPdfStorageDirAdmin): ItrkConnector
    {
        $this->localPdfStorageDirAdmin = $localPdfStorageDirAdmin;
        return $this;
    }

    /**
     * @return array
     */
    public function getPluginsettings(): array
    {
        return $this->pluginsettings;
    }

    /**
     * @return ItrkConnector
     */
    public function setPluginsettings(): ItrkConnector
    {
        $einstellungen_arr = $this->db->executeQueryPrepared('SELECT * FROM tplugineinstellungen WHERE kPlugin = :kPlugin',
            [
                'kPlugin' => $this->getPlugin()->getID()
            ],
            ReturnType::ARRAY_OF_ASSOC_ARRAYS);

        foreach ($einstellungen_arr as $einstellung_arr) {
            if ($einstellung_arr['cName'] === 'activeAttachments')
            {
                $this->pluginsettings[$einstellung_arr['cName']] = explode(",", $einstellung_arr['cWert']);
            }
            else
            {
                $this->pluginsettings[$einstellung_arr['cName']] = $einstellung_arr['cWert'];
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLocalPdfStorageDir(): string
    {
        return $this->localPdfStorageDir;
    }

    /**
     * @param mixed $localPdfStorageDir
     * @return ItrkConnector
     */
    public function setLocalPdfStorageDir($localPdfStorageDir): ItrkConnector
    {
        $this->localPdfStorageDir = $localPdfStorageDir;
        return $this;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getXmldata(): \SimpleXMLElement
    {
        return $this->xmldata;
    }

    /**
     * @param \SimpleXMLElement $xmldata
     * @return ItrkConnector
     */
    public function setXmldata($xmldata): ItrkConnector
    {
        $this->xmldata = $xmldata;
        return $this;
    }

    /**
     * @return PluginInterface
     */
    public function getPlugin(): PluginInterface
    {
        return $this->plugin;
    }

    /**
     * @param PluginInterface $plugin
     * @return ItrkConnector
     */
    public function setPlugin($plugin): ItrkConnector
    {
        $this->plugin = $plugin;
        return $this;
    }

    /**
     * @return float
     */
    public function getApiVersion(): float
    {
        return $this->apiVersion;
    }

    /**
     * @return array
     */
    public function getSupportedLegalContent(): array
    {
        return $this->supportedLegalContent;
    }

    /**
     * @return array
     */
    public function getSupportedActions(): array
    {
        return $this->supportedActions;
    }

    /**
     * @return int
     */
    public function getNError(): int
    {
        return $this->nError;
    }

    /**
     * @param int $nError
     * @return $this
     */
    public function setNError(int $nError): ItrkConnector
    {
        $this->nError = $nError;
        return $this;
    }

    public function check_spezialseite($pagetype, $lfs_it_recht_kanzlei_sprache): int
    {
        $lfs_it_recht_kanzlei_Tabelle = 'tlinksprache';

        switch ($pagetype) {
            // AGB
            case 'agb':
                $lfs_it_recht_kanzlei_Link = $this->db->selectSingleRow('tlink', 'nLinkart', LINKTYP_AGB);
                break;

            // WRB
            case 'widerruf':
                $lfs_it_recht_kanzlei_Link = $this->db->selectSingleRow('tlink', 'nLinkart', LINKTYP_WRB);
                break;

            // Impressum
            case 'impressum':
                $lfs_it_recht_kanzlei_Link = $this->db->selectSingleRow('tlink', 'nLinkart', LINKTYP_IMPRESSUM);
                break;

            // Datenschutz
            case 'datenschutz':
                $lfs_it_recht_kanzlei_Link = $this->db->selectSingleRow('tlink', 'nLinkart', LINKTYP_DATENSCHUTZ);
                break;
        }

        $checkSpezial = $this->db->executeQueryPrepared('SELECT * FROM ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                                    WHERE cISOSprache = :cISOSprache AND kLink = :kLink',
            [
                'cISOSprache' => Shop::Lang()->getIsoFromLangID((int)$lfs_it_recht_kanzlei_sprache)->cISO,
                'kLink'       => $lfs_it_recht_kanzlei_Link->kLink
            ],
            ReturnType::SINGLE_OBJECT);

        if (isset($checkSpezial->kLink) && $checkSpezial->kLink > 0) {
            return 1;
        }

        return 0;
    }

    public function checkPost($post_arr): void
    {

        if (!is_array($post_arr)) {
            $this->setNError(12);
            return;
        }

        $this->setXmldata(@\simplexml_load_string((string)$post_arr['xml']));

        if ($this->getXmldata() === false) {
            $this->setNError(12);
            return;
        }

        if ($this->getXmldata()->action === '' || (!\in_array((string)$this->getXmldata()->action, $this->getSupportedActions()))) {
            $this->setNError(10);
            return;
        }

        if ((float)$this->getXmldata()->api_version !== $this->getApiVersion()) {
            $this->setNError(1);
            return;
        }

        if ((string)$this->getXmldata()->user_auth_token !== $this->getPluginsettings()['cAPIToken']) {
            $this->setNError(3);
            return;
        }

        if ((string)$this->getXmldata()->rechtstext_type === '' || (!\in_array((string)$this->getXmldata()->rechtstext_type, $this->getSupportedLegalContent()))) {
            $this->setNError(4);
            return;
        }

        if (\strlen((string)$this->getXmldata()->rechtstext_text) < 50) {
            $this->setNError(5);
            return;
        }

        if (\strlen((string)$this->getXmldata()->rechtstext_html) < 50) {
            $this->setNError(6);
            return;
        }

        $lfs_it_recht_kanzlei_sprache = $this->db->executeQueryPrepared('SELECT kSprache, cISO FROM tsprache WHERE cISO = :cISO',
            [
                'cISO' => (string)$this->getXmldata()->rechtstext_language_iso639_2b
            ],
            ReturnType::SINGLE_OBJECT);

        if (!isset($lfs_it_recht_kanzlei_sprache->kSprache) || $lfs_it_recht_kanzlei_sprache->kSprache <= 0) {
            $this->setNError(9);
            return;
        }

        $this->setCurrentLanguageIso((string)$this->getXmldata()->rechtstext_language_iso639_2b);
        $this->setCurrentLanguage($lfs_it_recht_kanzlei_sprache);
    }


    public function saveLegalContent(): void
    {
        $logger = Shop::Container()->getLogService();

        if ($this->getPluginsettings()['cPDFDown'] === '1' && (string)$this->getXmldata()->rechtstext_type !== 'impressum') {
            $this->downloadPdfFile();
            $this->checkForError();
        }

        $this->configureMailtemplates();
        $this->checkForError();

        $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - Neuer Rechtstext erhalten - Typ: ' . $this->getXmldata()->rechtstext_type);

        $lfs_it_recht_kanzlei_ContentHtml = (string)$this->getXmldata()->rechtstext_html;
        $lfs_it_recht_kanzlei_ContentText = (string)$this->getXmldata()->rechtstext_text;

        $lfs_it_recht_kanzlei_SpalteHtml = '';
        $lfs_it_recht_kanzlei_SpalteText = '';
        $lfs_it_recht_kanzlei_Tabelle    = '';

        if ($this->getSyncblockGroups(true) != '') {
            $oSyncGroups_arr = $this->db->executeQueryPrepared('SELECT * FROM tkundengruppe WHERE kKundengruppe NOT IN (:syncBlockGroups)',
                [
                    'syncBlockGroups' => $this->getSyncblockGroups(true)
                ],
                ReturnType::ARRAY_OF_OBJECTS);
        } else {
            $oSyncGroups_arr = $this->db->query('SELECT * FROM tkundengruppe', 2);
        }

        $bCleanupDseContent = false;

        switch ((string)$this->getXmldata()->rechtstext_type) {
            // AGB
            case 'agb':
                $lfs_it_recht_kanzlei_SpalteHtml    = 'cAGBContentHtml';
                $lfs_it_recht_kanzlei_SpalteText    = 'cAGBContentText';
                $lfs_it_recht_kanzlei_Tabelle       = 'ttext';

                if (\is_array($oSyncGroups_arr) && count($oSyncGroups_arr) > 0) {
                    foreach ($oSyncGroups_arr as $oGroup) {
                        $lfs_content_exists = $this->db->executeQueryPrepared('SELECT * FROM ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                                                            WHERE kSprache = :kSprache AND kKundengruppe= :kKundengruppe',
                            [
                                'kSprache'      => (int)$this->getCurrentLanguage()->kSprache,
                                'kKundengruppe' => $oGroup->kKundengruppe
                            ],
                            ReturnType::SINGLE_OBJECT);

                        if (!\is_object($lfs_content_exists)) {
                            $oInsert                  = new \stdClass();
                            $oInsert->kSprache        = (int)$this->getCurrentLanguage()->kSprache;
                            $oInsert->kKundengruppe   = (int)$oGroup->kKundengruppe;
                            $oInsert->cAGBContentText = '';
                            $oInsert->cAGBContentHtml = '';
                            $oInsert->cWRBContentText = '';
                            $oInsert->cWRBContentHtml = '';
                            $oInsert->cDSEContentText = '';
                            $oInsert->cDSEContentHtml = '';
                            $oInsert->nStandard       = 0;

                            $this->db->insertRow($lfs_it_recht_kanzlei_Tabelle, $oInsert);
                        }

                    }
                }

                break;

            // WRB
            case 'widerruf':
                $lfs_it_recht_kanzlei_SpalteHtml    = 'cWRBContentHtml';
                $lfs_it_recht_kanzlei_SpalteText    = 'cWRBContentText';
                $lfs_it_recht_kanzlei_Tabelle       = 'ttext';

                if (\is_array($oSyncGroups_arr) && count($oSyncGroups_arr) > 0) {
                    foreach ($oSyncGroups_arr as $oGroup) {
                        $lfs_content_exists = $this->db->executeQueryPrepared('SELECT * FROM ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                                                            WHERE kSprache = :kSprache AND kKundengruppe= :kKundengruppe',
                            [
                                'kSprache'      => (int)$this->getCurrentLanguage()->kSprache,
                                'kKundengruppe' => $oGroup->kKundengruppe
                            ],
                            ReturnType::SINGLE_OBJECT);

                        if (!\is_object($lfs_content_exists)) {
                            $oInsert                  = new \stdClass();
                            $oInsert->kSprache        = (int)$this->getCurrentLanguage()->kSprache;
                            $oInsert->kKundengruppe   = (int)$oGroup->kKundengruppe;
                            $oInsert->cAGBContentText = '';
                            $oInsert->cAGBContentHtml = '';
                            $oInsert->cWRBContentText = '';
                            $oInsert->cWRBContentHtml = '';
                            $oInsert->cDSEContentText = '';
                            $oInsert->cDSEContentHtml = '';
                            $oInsert->nStandard       = 0;

                            $this->db->insertRow($lfs_it_recht_kanzlei_Tabelle, $oInsert);
                        }

                    }
                }

                break;

            // Impressum
            case 'impressum':
                $lfs_it_recht_kanzlei_SpalteHtml    = 'cContent';
                $lfs_it_recht_kanzlei_SpalteText    = '';
                $lfs_it_recht_kanzlei_Tabelle       = 'tlinksprache';

                $lfs_it_recht_kanzlei_Link = $this->db->executeQueryPrepared('SELECT kLink FROM tlink WHERE nLinkart = :nLinkart',
                    [
                        'nLinkart' => LINKTYP_IMPRESSUM
                    ],
                    ReturnType::SINGLE_OBJECT);

                $lfs_content_exists = $this->db->executeQueryPrepared('SELECT * FROM ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                                                        WHERE cISOSprache = :cISOSprache 
                                                                         AND kLink = :kLink',
                    [
                        'cISOSprache' => Shop::Lang()->getIsoFromLangID((int)$this->getCurrentLanguage()->kSprache)->cISO,
                        'kLink'       => $lfs_it_recht_kanzlei_Link->kLink
                    ],
                    ReturnType::SINGLE_OBJECT);


                if (!\is_object($lfs_content_exists)) {
                    $oInsert                   = new \stdClass();
                    $oInsert->kLink            = (int)$lfs_it_recht_kanzlei_Link->kLink;
                    $oInsert->cSeo             = Seo::getSeo('Impressum');
                    $oInsert->cISOSprache      = Shop::Lang()->getIsoFromLangID((int)$this->getCurrentLanguage()->kSprache);
                    $oInsert->cName            = 'Impressum';
                    $oInsert->cTitle           = '';
                    $oInsert->cContent         = '';
                    $oInsert->cMetaTitle       = '';
                    $oInsert->cMetaKeywords    = '';
                    $oInsert->cMetaDescription = '';

                    $this->db->insertRow($lfs_it_recht_kanzlei_Tabelle, $oInsert);

                    $oSeo           = new \stdClass();
                    $oSeo->cSeo     = Seo::checkSeo($oInsert->cSeo);
                    $oSeo->kKey     = $oInsert->kLink;
                    $oSeo->cKey     = 'kLink';
                    $oSeo->kSprache = $this->getCurrentLanguage()->kSprache;

                    $this->db->insert('tseo', $oSeo);
                }

                break;

            // Datenschutz
            case 'datenschutz':
                $bCleanupDseContent = true;
                if ($this->getPluginsettings()['saveDseContentAs'] === 'content') {
                    $lfs_it_recht_kanzlei_SpalteHtml_clear    = 'cDSEContentHtml';
                    $lfs_it_recht_kanzlei_SpalteText_clear    = 'cDSEContentText';
                    $lfs_it_recht_kanzlei_Tabelle_clear       = 'ttext';

                    $lfs_it_recht_kanzlei_SpalteHtml    = 'cContent';
                    $lfs_it_recht_kanzlei_SpalteText    = '';
                    $lfs_it_recht_kanzlei_Tabelle       = 'tlinksprache';

                    $lfs_it_recht_kanzlei_Link = $this->db->executeQueryPrepared('SELECT kLink FROM tlink WHERE nLinkart = :nLinkart',
                        [
                            'nLinkart' => LINKTYP_DATENSCHUTZ
                        ],
                        ReturnType::SINGLE_OBJECT);

                    $lfs_content_exists = $this->db->executeQueryPrepared('SELECT * FROM ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                                                        WHERE cISOSprache = :cISOSprache 
                                                                         AND kLink = :kLink',
                        [
                            'cISOSprache' => Shop::Lang()->getIsoFromLangID((int)$this->getCurrentLanguage()->kSprache)->cISO,
                            'kLink'       => $lfs_it_recht_kanzlei_Link->kLink
                        ],
                        ReturnType::SINGLE_OBJECT);

                    if (!\is_object($lfs_content_exists)) {
                        $oInsert                   = new \stdClass();
                        $oInsert->kLink            = (int)$lfs_it_recht_kanzlei_Link->kLink;
                        $oInsert->cSeo             = Seo::getSeo('Datenschutz');
                        $oInsert->cISOSprache      = Shop::Lang()->getIsoFromLangID($this->currentLanguage->kSprache);
                        $oInsert->cName            = 'Datenschutz';
                        $oInsert->cTitle           = '';
                        $oInsert->cContent         = '';
                        $oInsert->cMetaTitle       = '';
                        $oInsert->cMetaKeywords    = '';
                        $oInsert->cMetaDescription = '';

                        $this->db->insertRow($lfs_it_recht_kanzlei_Tabelle, $oInsert);

                        $oSeo           = new \stdClass();
                        $oSeo->cSeo     = Seo::checkSeo($oInsert->cSeo);
                        $oSeo->kKey     = $oInsert->kLink;
                        $oSeo->cKey     = 'kLink';
                        $oSeo->kSprache = $this->getCurrentLanguage()->kSprache;

                        $this->db->insert('tseo', $oSeo);
                    }
                }
                else {
                    $lfs_it_recht_kanzlei_Link_free = $this->db->executeQueryPrepared('SELECT kLink FROM tlink WHERE nLinkart = :nLinkart',
                        [
                            'nLinkart' => LINKTYP_DATENSCHUTZ
                        ],
                        ReturnType::SINGLE_OBJECT);

                    $lfs_it_recht_kanzlei_SpalteHtml_clear    = 'cContent';
                    $lfs_it_recht_kanzlei_SpalteText_clear    = '';
                    $lfs_it_recht_kanzlei_Tabelle_clear       = 'tlinksprache';

                    $lfs_it_recht_kanzlei_SpalteHtml    = 'cDSEContentHtml';
                    $lfs_it_recht_kanzlei_SpalteText    = 'cDSEContentText';
                    $lfs_it_recht_kanzlei_Tabelle       = 'ttext';

                    if (\is_array($oSyncGroups_arr) && count($oSyncGroups_arr) > 0) {
                        foreach ($oSyncGroups_arr as $oGroup) {
                            $lfs_content_exists = $this->db->executeQueryPrepared('SELECT * FROM ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                                                            WHERE kSprache = :kSprache AND kKundengruppe= :kKundengruppe',
                                [
                                    'kSprache'      => (int)$this->getCurrentLanguage()->kSprache,
                                    'kKundengruppe' => $oGroup->kKundengruppe
                                ],
                                ReturnType::SINGLE_OBJECT);

                            if (!\is_object($lfs_content_exists)) {
                                $oInsert                  = new \stdClass();
                                $oInsert->kSprache        = (int)$this->getCurrentLanguage()->kSprache;
                                $oInsert->kKundengruppe   = (int)$oGroup->kKundengruppe;
                                $oInsert->cAGBContentText = '';
                                $oInsert->cAGBContentHtml = '';
                                $oInsert->cWRBContentText = '';
                                $oInsert->cWRBContentHtml = '';
                                $oInsert->cDSEContentText = '';
                                $oInsert->cDSEContentHtml = '';
                                $oInsert->nStandard       = 0;

                                $this->db->insertRow($lfs_it_recht_kanzlei_Tabelle, $oInsert);
                            }

                        }
                    }
                }

                break;
        }

        foreach ($oSyncGroups_arr as $oGroup) {
            if ($lfs_it_recht_kanzlei_Tabelle !== 'tlinksprache') {
                $this->db->executeQueryPrepared('UPDATE ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                         SET ' . $lfs_it_recht_kanzlei_SpalteHtml . ' = :newContent 
                                         WHERE 
                                            kSprache = :kSprache 
                                         AND 
                                            kKundengruppe = :kundengruppe',
                    [
                        'newContent'   => $lfs_it_recht_kanzlei_ContentHtml,
                        'kSprache'     => (int)$this->getCurrentLanguage()->kSprache,
                        'kundengruppe' => $oGroup->kKundengruppe
                    ],
                    ReturnType::AFFECTED_ROWS);

                if ($lfs_it_recht_kanzlei_SpalteText !== '') {
                    $this->db->executeQueryPrepared('UPDATE ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                            SET ' . $lfs_it_recht_kanzlei_SpalteText . ' = :newContent 
                                            WHERE 
                                                kSprache = :kSprache 
                                            AND 
                                                kKundengruppe = :kundengruppe',
                        [
                            'newContent'   => $lfs_it_recht_kanzlei_ContentText,
                            'kSprache'     => (int)$this->getCurrentLanguage()->kSprache,
                            'kundengruppe' => $oGroup->kKundengruppe
                        ],
                        ReturnType::AFFECTED_ROWS);
                }
            }
        }

        if ($lfs_it_recht_kanzlei_Tabelle === 'tlinksprache') {
            $this->db->executeQueryPrepared('UPDATE ' . $lfs_it_recht_kanzlei_Tabelle . ' 
                                         SET ' . $lfs_it_recht_kanzlei_SpalteHtml . " = :newContent,
                                            cTitle = ''
                                         WHERE 
                                            cISOSprache = :cISOSprache 
                                         AND
                                            kLink = :kLink",
                [
                    'newContent'  => $lfs_it_recht_kanzlei_ContentHtml,
                    'cISOSprache' => $this->getCurrentLanguageIso(),
                    'kLink'       => $lfs_it_recht_kanzlei_Link->kLink
                ],
                ReturnType::DEFAULT);
        }

        if ($bCleanupDseContent) {
            $kLink = $lfs_it_recht_kanzlei_Link_free->kLink ?? 0;
            $this->cleanupDseContent(
                $lfs_it_recht_kanzlei_SpalteHtml_clear,
                $lfs_it_recht_kanzlei_SpalteText_clear,
                $lfs_it_recht_kanzlei_Tabelle_clear,
                $oSyncGroups_arr,
                (int)$kLink
            );
        }

        $oInsert               = new \stdClass();
        $oInsert->dLetzterPush = Carbon::now()->toDateTimeString();
        $oInsert->cDokuArt     = (string)$this->getXmldata()->rechtstext_type;
        $oInsert->cSprache     = $this->getCurrentLanguageIso();
        $oInsert->cStatus      = 'OK';
        $oInsert->cPDFName     = $this->getLocalPdfTargetName();

        $this->db->insertRow('xplugin_lfs_it_recht_kanzlei_tLog', $oInsert);

        $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . ' - Neuer Rechtstext gespeichert - Typ: ' . $this->getXmldata()->rechtstext_type);

        $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . ' - Template-Cache leeren');
        $this->clearTemplateCache();

        $cache = Shop::Container()->getCache();
        if ($cache->isActive()) {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . ' - Object-Cache leeren');
            $cache->flushAll();
        }
    }


    private function cleanupDseContent(string $spalteHtml, string $spalteText, string $table, array $syncGroups, int $kLink = 0): void
    {
        if ($this->getPluginsettings()['saveDseContentAs'] === 'content') {
            foreach ($syncGroups as $oGroup) {
                if ($table !== 'tlinksprache') {
                    $this->db->executeQueryPrepared('UPDATE ' . $table . ' 
                                         SET ' . $spalteHtml . ' = :newContent 
                                         WHERE 
                                            kSprache = :kSprache 
                                         AND 
                                            kKundengruppe = :kundengruppe',
                        [
                            'newContent'   => '',
                            'kSprache'     => (int)$this->getCurrentLanguage()->kSprache,
                            'kundengruppe' => $oGroup->kKundengruppe
                        ],
                        ReturnType::AFFECTED_ROWS);

                    $this->db->executeQueryPrepared('UPDATE ' . $table . ' 
                                            SET ' . $spalteText . ' = :newContent 
                                            WHERE 
                                                kSprache = :kSprache 
                                            AND 
                                                kKundengruppe = :kundengruppe',
                        [
                            'newContent'   => '',
                            'kSprache'     => (int)$this->getCurrentLanguage()->kSprache,
                            'kundengruppe' => $oGroup->kKundengruppe
                        ],
                        ReturnType::AFFECTED_ROWS);
                }
            }
        }
        else if ($table === 'tlinksprache' && $kLink !== 0) {
            $this->db->executeQueryPrepared('UPDATE ' . $table . ' 
                                     SET ' . $spalteHtml . " = :newContent,
                                        cTitle = ''
                                     WHERE 
                                        cISOSprache = :cISOSprache 
                                     AND
                                        kLink = :kLink",
                [
                    'newContent'  => '',
                    'cISOSprache' => $this->getCurrentLanguageIso(),
                    'kLink'       => $kLink
                ],
                ReturnType::DEFAULT);
        }
    }


    public function configureMailtemplates(): void
    {
        $logger = Shop::Container()->getLogService();

        $lfs_it_recht_kanzlei_MailID = $this->db->executeQueryPrepared('SELECT kEmailvorlage FROM temailvorlage 
                                                                        WHERE cModulId = :cModulId',
            [
                'cModulId' => MAILTEMPLATE_BESTELLBESTAETIGUNG
            ],
            ReturnType::SINGLE_OBJECT);

        if ($this->getPluginsettings()['cPDFMail'] > 0) {
            if ($this->getXmldata()->rechtstext_type != 'impressum') {
                if ($this->getPluginsettings()['cPDFMail'] === '1' || $this->getPluginsettings()['cPDFMail'] === '3') {
                    $file_pdf_target_rename = $this->getLocalPdfStorageDir() . $this->getLocalPdfTargetName();
                    $file_pdf_targetfilename_copy_admin = $this->getXmldata()->rechtstext_type . '_' . $this->getCurrentLanguageIso() . '.pdf';
                    $file_pdf_target_copy_admin         = $this->getLocalPdfStorageDirAdmin() . $file_pdf_targetfilename_copy_admin;

                    if (!\copy($file_pdf_target_rename, $file_pdf_target_copy_admin)) {
                        $logger->error('Plugin ' . $this->getPlugin()->getPluginID() . ' - Error - PDF-Dokument Emailvorlage konnte nicht gespeichert werden');
                    }
                }

                $lfs_emailvorlage_exists = $this->db->executeQueryPrepared('SELECT * FROM temailvorlagesprache 
                                                                        WHERE 
                                                                              kEmailvorlage = :kEmailvorlage 
                                                                          AND 
                                                                              kSprache = :kSprache',
                    [
                        'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage,
                        'kSprache'      => $this->getCurrentLanguage()->kSprache
                    ],
                    ReturnType::SINGLE_OBJECT);

                $cPDFS_value = $lfs_emailvorlage_exists->cPDFS??'';
                $cDateiname_value = $lfs_emailvorlage_exists->cPDFNames??'';

                if (!\is_object($lfs_emailvorlage_exists)) {
                    $oInsert                = new \stdClass();
                    $oInsert->kEmailvorlage = (int)$lfs_it_recht_kanzlei_MailID->kEmailvorlage;
                    $oInsert->kSprache      = (int)$this->getCurrentLanguage()->kSprache;
                    $oInsert->cBetreff      = '';
                    $oInsert->cContentHtml  = '';
                    $oInsert->cContentText  = '';
                    $oInsert->cPDFS         = '';
                    $oInsert->cDateiname    = '';

                    $this->db->insertRow('temailvorlagesprache', $oInsert);
                    $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . "- Debug - Emailvorlage für Sprache '" . $this->getCurrentLanguageIso() . "' erstellt");
                }

                switch ($this->getPluginsettings()['cPDFMail']) {
                    case 1: // PDF-Datei

                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'datenschutz_' . $this->getCurrentLanguageIso() . '.pdf');
                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'widerruf_' . $this->getCurrentLanguageIso() . '.pdf');
                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'agb_' . $this->getCurrentLanguageIso() . '.pdf');

                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'dse_doc');
                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'widerruf_doc');
                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'agb_doc');

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('agb', $this->getPluginsettings()['activeAttachments'])) {
                            $cPDFS_value .= ';agb_' . $this->getCurrentLanguageIso() . '.pdf';
                            $cDateiname_value .= ';agb_doc';
                        }

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('wrb', $this->getPluginsettings()['activeAttachments'])) {
                            $cPDFS_value .= ';widerruf_' . $this->getCurrentLanguageIso() . '.pdf';
                            $cDateiname_value .= ';widerruf_doc';
                        }

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('dse', $this->getPluginsettings()['activeAttachments'])) {
                            $cPDFS_value .= ';datenschutz_' . $this->getCurrentLanguageIso() . '.pdf';
                            $cDateiname_value .= ';dse_doc';
                        }

                        $this->db->executeQueryPrepared("UPDATE temailvorlage 
                                                        SET 
                                                            nAGB = '0',
                                                            nWRB = '0',
                                                            nDSE = '0'
                                                        WHERE kEmailvorlage = :kEmailvorlage",
                            [
                                'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage
                            ],
                            ReturnType::DEFAULT);

                        $this->db->executeQueryPrepared('UPDATE temailvorlagesprache 
                                                        SET 
                                                            cPDFS = :cPDFS,
                                                            cPDFNames = :cDateiname
                                                        WHERE 
                                                              kEmailvorlage = :kEmailvorlage 
                                                          AND 
                                                              kSprache = :kSprache',
                            [
                                'cPDFS'         => $cPDFS_value,
                                'cDateiname'    => $cDateiname_value,
                                'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage,
                                'kSprache'      => $this->getCurrentLanguage()->kSprache
                            ],
                            ReturnType::DEFAULT);
                        break;

                    case 2: // Mail-Text
                        $nAGB = 0;
                        $nWRB = 0;
                        $nDSE = 0;

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('agb', $this->getPluginsettings()['activeAttachments'])) {
                            $nAGB = 1;
                        }

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('wrb', $this->getPluginsettings()['activeAttachments'])) {
                            $nWRB = 1;
                        }

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('dse', $this->getPluginsettings()['activeAttachments'])) {
                            $nDSE = 1;
                        }
                        $this->db->executeQueryPrepared("UPDATE temailvorlage 
                                                        SET 
                                                            nAGB = :nAGB,
                                                            nWRB = :nWRB,
                                                            nDSE = :nDSE
                                                        WHERE kEmailvorlage = :kEmailvorlage",
                            [
                                'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage,
                                'nAGB' => $nAGB,
                                'nWRB' => $nWRB,
                                'nDSE' => $nDSE
                            ],
                            ReturnType::QUERYSINGLE);

                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'datenschutz_' . $this->getCurrentLanguageIso() . '.pdf');
                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'widerruf_' . $this->getCurrentLanguageIso() . '.pdf');
                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'agb_' . $this->getCurrentLanguageIso() . '.pdf');

                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'dse_doc');
                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'widerruf_doc');
                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'agb_doc');

                        $this->db->executeQueryPrepared('UPDATE temailvorlagesprache 
                                                        SET 
                                                            cPDFS = :cPDFS,
                                                            cPDFNames = :cDateiname
                                                        WHERE 
                                                              kEmailvorlage = :kEmailvorlage 
                                                          AND 
                                                              kSprache = :kSprache',
                            [
                                'cPDFS'         => $cPDFS_value,
                                'cDateiname'    => $cDateiname_value,
                                'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage,
                                'kSprache'      => $this->getCurrentLanguage()->kSprache
                            ],
                            ReturnType::QUERYSINGLE);

                        break;

                    case 3: // PDF-Datei & Mail-Text
                        $nAGB = 0;
                        $nWRB = 0;
                        $nDSE = 0;

                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'datenschutz_' . $this->getCurrentLanguageIso() . '.pdf');
                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'widerruf_' . $this->getCurrentLanguageIso() . '.pdf');
                        $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'agb_' . $this->getCurrentLanguageIso() . '.pdf');

                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'dse_doc');
                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'widerruf_doc');
                        $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'agb_doc');

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('agb', $this->getPluginsettings()['activeAttachments'])) {
                            $cPDFS_value .= ';agb_' . $this->getCurrentLanguageIso() . '.pdf';
                            $cDateiname_value .= ';agb_doc';
                            $nAGB = 1;
                        }

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('wrb', $this->getPluginsettings()['activeAttachments'])) {
                            $cPDFS_value .= ';widerruf_' . $this->getCurrentLanguageIso() . '.pdf';
                            $cDateiname_value .= ';widerruf_doc';
                            $nWRB = 1;
                        }

                        if (is_array($this->getPluginsettings()['activeAttachments'])
                            && in_array('dse', $this->getPluginsettings()['activeAttachments'])) {
                            $cPDFS_value .= ';datenschutz_' . $this->getCurrentLanguageIso() . '.pdf';
                            $cDateiname_value .= ';dse_doc';
                            $nDSE = 1;
                        }

                        $this->db->executeQueryPrepared("UPDATE temailvorlage 
                                                        SET 
                                                            'nAGB' = :nAGB,
                                                            'nWRB' = :nWRB,
                                                            'nDSE' = :nDSE
                                                        WHERE kEmailvorlage = :kEmailvorlage",
                            [
                                'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage,
                                'nAGB' => $nAGB,
                                'nWRB' => $nWRB,
                                'nDSE' => $nDSE
                            ],
                            ReturnType::QUERYSINGLE);

                        $this->db->executeQueryPrepared('UPDATE temailvorlagesprache 
                                                        SET 
                                                            cPDFS = :cPDFS,
                                                            cPDFNames = :cDateiname
                                                        WHERE 
                                                              kEmailvorlage = :kEmailvorlage 
                                                          AND 
                                                              kSprache = :kSprache',
                            [
                                'cPDFS'         => $cPDFS_value,
                                'cDateiname'    => $cDateiname_value,
                                'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage,
                                'kSprache'      => $this->getCurrentLanguage()->kSprache
                            ],
                            ReturnType::QUERYSINGLE);

                        break;
                }
            }
        } else {
            $lfs_emailvorlage_exists = $this->db->executeQueryPrepared('SELECT * FROM temailvorlagesprache 
                                                                        WHERE 
                                                                              kEmailvorlage = :kEmailvorlage 
                                                                          AND 
                                                                              kSprache = :kSprache',
                [
                    'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage,
                    'kSprache'      => $this->getCurrentLanguage()->kSprache
                ],
                ReturnType::SINGLE_OBJECT);

            $cPDFS_value = $lfs_emailvorlage_exists->cPDFS??'';
            $cDateiname_value = $lfs_emailvorlage_exists->cPDFNames??'';

            $this->db->executeQueryPrepared("UPDATE temailvorlage 
                                                        SET 
                                                            nAGB = '0',
                                                            nWRB = '0',
                                                            nDSE = '0'
                                                        WHERE kEmailvorlage = :kEmailvorlage",
                [
                    'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage
                ],
                ReturnType::QUERYSINGLE);

            $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'datenschutz_' . $this->getCurrentLanguageIso() . '.pdf');
            $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'widerruf_' . $this->getCurrentLanguageIso() . '.pdf');
            $cPDFS_value = $this->cleanupAttachmentField($cPDFS_value, 'agb_' . $this->getCurrentLanguageIso() . '.pdf');

            $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'dse_doc');
            $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'widerruf_doc');
            $cDateiname_value = $this->cleanupAttachmentField($cDateiname_value, 'agb_doc');

            $this->db->executeQueryPrepared('UPDATE temailvorlagesprache 
                                                        SET 
                                                            cPDFS = :cPDFS,
                                                            cPDFNames = :cDateiname
                                                        WHERE 
                                                              kEmailvorlage = :kEmailvorlage 
                                                          AND 
                                                              kSprache = :kSprache',
                [
                    'cPDFS'         => $cPDFS_value,
                    'cDateiname'    => $cDateiname_value,
                    'kEmailvorlage' => $lfs_it_recht_kanzlei_MailID->kEmailvorlage,
                    'kSprache'      => $this->getCurrentLanguage()->kSprache
                ],
                ReturnType::QUERYSINGLE);
        }

    }

    private function cleanupAttachmentField(string $input, string $filename): string
    {
        $parts = explode(';', $input);

        $filtered = array_filter(
            $parts,
            function($item) use ($filename) {
                return $item !== '' && $item !== $filename;
            }
        );

        return implode(';', $filtered);
    }

    public function downloadPdfFile(): void
    {
        $logger = Shop::Container()->getLogService();

        if (\strlen((string)$this->getXmldata()->rechtstext_pdf_url) === 0) {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - PDF-Url der Rechtstexte fehlerhaft: ' . \print_r((string)$this->getXmldata()->rechtstext_pdf_url, true));
            $this->setNError(7);
            return;
        }

        $file_pdf_targetfilename        = \md5(\uniqid('', true)) . '.pdf'; // #### adapt the created filename to your needs, if required
        $file_pdf_target                = $this->getLocalPdfStorageDir() . $file_pdf_targetfilename;
        $file_pdf_targetfilename_rename = $this->getXmldata()->rechtstext_type . '-' . $this->getCurrentLanguageIso() . '-' . \date('Ymd_His') . '.pdf';
        $file_pdf_target_rename         = $this->getLocalPdfStorageDir() . $file_pdf_targetfilename_rename;
        $this->setLocalPdfTargetName($file_pdf_targetfilename_rename);

        $file_pdf = @\fopen($file_pdf_target, 'wb+');
        if ($file_pdf === false) {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - PDF-Datei konnte nicht zur Bearbeitung geöffnet werden');
            $this->setNError(7);
            return;
        }

        //new in V200
        $file_content = Request::make_http_request(\trim((string)$this->getXmldata()->rechtstext_pdf_url), 20, null, false, false);
        if ($file_content === '') {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - Inhalt der PDF-Datei ist leer');
            $this->setNError(7);
            return;
        }

        $retval = @\fwrite($file_pdf, $file_content);

        if ($retval === false) {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - PDF-Datei konnte nicht gespeichert werden - Write error');
            $this->setNError(7);
            return;
        }

        $retval = @\fclose($file_pdf);
        if ($retval === false) {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - PDF-Datei konnte nicht gespeichert werden - Error on close');
            $this->setNError(7);
            return;
        }

        // Catch errors - downloaded file was not properly saved
        if (\file_exists($file_pdf_target) !== true) {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - PDF-Datei konnte nicht gespeichert werden - Filex does not exist');
            $this->setNError(7);
            return;
        }

        // verify that file is a pdf
        if ($this->lfs_it_recht_kanzlei_check_if_pdf_file($file_pdf_target) !== true) {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - Datei ist keine PDF-Datei');
            \unlink($file_pdf_target);
            $this->setNError(7);
            return;
        }

        // verify md5-hash, delete file if hash is not equal
        if (\md5_file($file_pdf_target) !== (string)$this->getXmldata()->rechtstext_pdf_md5hash) {
            $logger->debug('Plugin ' . $this->getPlugin()->getPluginID() . '  - Datei-Hash stimmt nicht mit Hash aus Übertragung überein');
            \unlink($file_pdf_target);
            $this->setNError(7);
            return;
        }

        \rename($file_pdf_target, $file_pdf_target_rename);
    }


    private function lfs_it_recht_kanzlei_check_if_pdf_file($filename): bool
    {
        $handle   = @\fopen($filename, 'rb');
        $contents = @\fread($handle, 4);
        @\fclose($handle);

        return $contents === '%PDF';
    }

    private function checkForError(): void
    {
        if ($this->getNError() > 0) {
            return;
        }
    }

    private function clearTemplateCache(): void
    {
        // delete all template cachefiles
        $callback     = static function (array $pParameters) {
            if (str_starts_with($pParameters['filename'], '.')) {
                return;
            }
            if (!$pParameters['isdir']) {
                if (@unlink($pParameters['path'] . $pParameters['filename'])) {
                    $pParameters['count']++;
                } else {
                    $pParameters['error'] .= sprintf(
                            __('errorFileDelete'),
                            '<strong>' . $pParameters['path'] . $pParameters['filename'] . '</strong>'
                        ) . '<br/>';
                }
            } elseif (!@rmdir($pParameters['path'] . $pParameters['filename'])) {
                $pParameters['error'] .= sprintf(
                        __('errorDirDelete'),
                        '<strong>' . $pParameters['path'] . $pParameters['filename'] . '</strong>'
                    ) . '<br/>';
            }
        };
        $deleteCount  = 0;
        $cbParameters = [
            'count'  => &$deleteCount,
            'notice' => &$notice,
            'error'  => &$error
        ];
        $template     = Shop::Container()->getTemplateService()->getActiveTemplate();
        $dirMan       = new DirManager();
        $dirMan->getData(PFAD_ROOT . PFAD_COMPILEDIR . $template->getDir(), $callback, $cbParameters);
        $ms = new MinifyService();
        $ms->flushCache();
    }

    public function returnStatus(): string
    {
        $cReturn = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
        $cReturn .= "<response>\n";

        if ($this->getNError() === 0) {
            // success
            $cReturn .= "	<status>success</status>\n";
        } else {
            // output error
            $cReturn .= "	<status>error</status>\n";
            $cReturn .= '	<error>' . $this->getNError() . "</error>\n";
        }

        $cReturn .= '	<meta_shopversion>' . APPLICATION_VERSION . "</meta_shopversion>\n";
        $cReturn .= '	<meta_modulversion>' . $this->getPlugin()->getMeta()->getVersion() . "</meta_modulversion>\n";
        $cReturn .= '	<meta_phpversion>' . PHP_VERSION . "</meta_phpversion>\n";
        $cReturn .= '</response>';

        return $cReturn;
    }

    public function setSyncSettings(array $groups): void
    {
        if (count($groups) > 0) {
            $tmpKundengruppen_arr = $this->db->executeQuery('SELECT kKundengruppe from tkundengruppe', 2);

            if (\is_array($tmpKundengruppen_arr) && count($tmpKundengruppen_arr) > 0) {
                foreach ($tmpKundengruppen_arr as $kundengruppe) {
                    if (\in_array($kundengruppe->kKundengruppe, $groups, true)) {
                        // Gruppe ist aktiv - Syncblock-Einträge zu Gruppe entfernen
                        $this->db->deleteRow('xplugin_lfs_it_recht_kanzlei_kundengruppensyncblock',
                            'kKundengruppe', $kundengruppe->kKundengruppe);
                    } else {
                        // Gruppe ist inaktiv
                        $tmpSyncblockEntry = $this->db->executeQueryPrepared('SELECT * FROM xplugin_lfs_it_recht_kanzlei_kundengruppensyncblock 
                                                                        WHERE kKundengruppe=:kKundengruppe',
                            [
                                'kKundengruppe' => $kundengruppe->kKundengruppe
                            ],
                            ReturnType::SINGLE_OBJECT);

                        if (!\is_object($tmpSyncblockEntry)) {
                            $oInsert                = new \stdClass();
                            $oInsert->kKundengruppe = $kundengruppe->kKundengruppe;
                            $oInsert->created_at    = \date('Y-m-d H:i:s');

                            $this->db->insertRow('xplugin_lfs_it_recht_kanzlei_kundengruppensyncblock', $oInsert);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param bool $returnAsString
     * @return array|string
     */
    private function getSyncblockGroups(bool $returnAsString = false)
    {
        $oBlockGroups_arr = [];

        $oResult_arr = $this->db->executeQuery('SELECT kKundengruppe from xplugin_lfs_it_recht_kanzlei_kundengruppensyncblock', ReturnType::ARRAY_OF_OBJECTS);

        if (\is_array($oResult_arr) && count($oResult_arr) > 0) {
            foreach ($oResult_arr as $oResult) {
                $oBlockGroups_arr[] = $oResult->kKundengruppe;
            }
        }

        return $returnAsString ? \implode(',', $oBlockGroups_arr) : $oBlockGroups_arr;
    }

    /**
     * @param array $post
     * @return void
     */
    public function updateSettings(array $post)
    {
        $tmpSetting = Shop::Container()->getDB()->executeQueryPrepared("SELECT * FROM tplugineinstellungen
                                                            WHERE 
                                                                kPlugin = :kPlugin 
                                                                AND  
                                                                cName = :cName",
            [
                'kPlugin' => $this->getPlugin()->getID(),
                'cName' => 'activeAttachments'
            ],
            ReturnType::SINGLE_OBJECT);

        if (!is_object($tmpSetting))
        {
            Shop::Container()->getDB()->executeQueryPrepared('INSERT INTO tplugineinstellungen (kPlugin, cName, cWert) VALUES
				(:kPlugin, "activeAttachments", "agb,wrb,dse")',
                [
                    'kPlugin' => $this->getPlugin()->getID(),
                ],
                ReturnType::DEFAULT
            );
        }

        Shop::Container()->getDB()->executeQueryPrepared("UPDATE tplugineinstellungen SET cWert = :cApiToken 
                                        WHERE kPlugin = :kPlugin AND cName = 'cAPIToken'",
            [
                'cApiToken' => $post['cAPIToken'],
                'kPlugin' => $this->getPlugin()->getID()
            ],
            ReturnType::QUERYSINGLE);

        Shop::Container()->getDB()->executeQueryPrepared("UPDATE tplugineinstellungen SET cWert = :cPDFDown 
                                        WHERE kPlugin = :kPlugin AND cName = 'cPDFDown'",
            [
                'cPDFDown' => $post['cPDFDown'],
                'kPlugin' => $this->getPlugin()->getID()
            ],
            ReturnType::QUERYSINGLE);

        Shop::Container()->getDB()->executeQueryPrepared("UPDATE tplugineinstellungen SET cWert = :cPDFMail 
                                        WHERE kPlugin = :kPlugin AND cName = 'cPDFMail'",
            [
                'cPDFMail' => $post['cPDFMail'],
                'kPlugin' => $this->getPlugin()->getID()
            ],
            ReturnType::QUERYSINGLE);

        Shop::Container()->getDB()->executeQueryPrepared("UPDATE tplugineinstellungen SET cWert = :saveDseContentAs 
                                        WHERE kPlugin = :kPlugin AND cName = 'saveDseContentAs'",
            [
                'saveDseContentAs' => $post['saveDseContentAs'],
                'kPlugin' => $this->getPlugin()->getID()
            ],
            ReturnType::QUERYSINGLE);

        $activeAttachments = "";
        if (is_array($post['activeAttachments']))
        {
            $activeAttachments = implode(",", $post['activeAttachments']);
        }

        Shop::Container()->getDB()->executeQueryPrepared("UPDATE tplugineinstellungen SET cWert = :activeAttachments 
                                        WHERE kPlugin = :kPlugin AND cName = 'activeAttachments'",
            [
                'activeAttachments' => $activeAttachments,
                'kPlugin' => $this->getPlugin()->getID()
            ],
            ReturnType::QUERYSINGLE);

        if (Shop::Container()->getCache()->isActive()) {
            $result = Shop::Container()->getCache()->flushTags(['CACHING_GROUP_PLUGIN']);
            if (\is_numeric($result)) {
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS, __('Cache erfolgreich invalidiert'), 'lfsItrkSuccess');
            } else {
                Shop::Container()->getAlertService()->addAlert(Alert::TYPE_ERROR, __('Konnte Cache nicht löschen!'), 'lfsItrkError');
            }
        }

        Shop::Container()->getAlertService()->addAlert(Alert::TYPE_SUCCESS,
            __('Einstellungen geändert - Bitte führen Sie einen vollständigen Abgleich der Rechtstexte durch!'),
            'lfsItrkSuccess');
    }

}
