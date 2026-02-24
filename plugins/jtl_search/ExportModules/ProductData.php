<?php

namespace Plugin\jtl_search\ExportModules;

use Exception;
use Illuminate\Support\Collection;
use JTL\Catalog\Currency;
use JTL\Catalog\Product\Preise;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Language\LanguageModel;
use JTL\Media\Image;
use JTL\Session\Frontend;
use JTL\Shop;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class ProductData
 * @package Plugin\jtl_search\ExportModules
 */
class ProductData implements IItemData
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var LanguageModel[]
     */
    private array $languages;

    /**
     * @var LanguageModel
     */
    private LanguageModel $defaultLanguage;

    /**
     * @var array
     */
    private array $customerGroups = [];

    /**
     * @var object
     */
    private $product;

    /**
     * @var null|float
     */
    private $fBestsellerMax;

    /**
     * @var Collection
     */
    private $nonVisibleIDs;

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @param LoggerInterface $logger
     * @param DbInterface     $db
     * @param array           $languages
     * @param LanguageModel   $defaultLanguage
     */
    public function __construct(
        LoggerInterface $logger,
        DbInterface $db,
        array $languages,
        LanguageModel $defaultLanguage
    ) {
        $this->logger          = $logger;
        $this->db              = $db;
        $this->languages       = $languages;
        $this->defaultLanguage = $defaultLanguage;
        try {
            $this->loadUsergroups();
        } catch (Exception $exception) {
            $logger->warning(__METHOD__ . ' - Exception: ' . \print_r($exception->getMessage(), true));
        }
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM tartikel
                LEFT JOIN tartikelattribut 
                    ON tartikelattribut.kArtikel = tartikel.kArtikel
                    AND tartikelattribut.cName = :atr
                WHERE tartikelattribut.kArtikel IS NULL '
            . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL(),
            'cnt',
            ['atr' => \JTLSEARCH_PRODUCT_EXCLUDE_ATTR]
        );
    }

    /**
     * @inheritdoc
     */
    public static function getItemKeys(DbInterface $db, int $nLimitN, int $nLimitM): array
    {
        return $db->getObjects(
            'SELECT tartikel.kArtikel AS kItem
                FROM tartikel
                LEFT JOIN tartikelattribut
                    ON tartikelattribut.kArtikel = tartikel.kArtikel
                    AND tartikelattribut.cName = :atr
                WHERE tartikelattribut.kArtikel IS NULL '
            . Shop::getProductFilter()->getFilterSQL()->getStockFilterSQL()
            . ' ORDER BY tartikel.kArtikel LIMIT :lmtf, :lmtu',
            [
                'atr'  => \JTLSEARCH_PRODUCT_EXCLUDE_ATTR,
                'lmtf' => $nLimitN,
                'lmtu' => $nLimitM,
            ]
        );
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function loadUsergroups(): self
    {
        $this->customerGroups = CustomerGroup::getGroups();
        if (\count($this->customerGroups) === 0) {
            throw new Exception(\__('errorLoadNoCustomerGroup'), 1);
        }


        return $this;
    }

    /**
     * @return $this
     */
    private function loadUsergroupVisibility(): self
    {
        $this->nonVisibleIDs = $this->db->getCollection(
            'SELECT kKundengruppe 
                FROM tartikelsichtbarkeit 
                WHERE kArtikel = :pid',
            ['pid' => $this->product->kArtikel]
        )->map(static function ($e) {
            return (int)$e->kKundengruppe;
        });

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function loadFromDB(int $id, int $languageID = 0, bool $noCache = true)
    {
        try {
            $res = $this->db->getSingleObject(
                "SELECT tartikel.kArtikel, tartikel.kHersteller, tartikel.kSteuerklasse, tartikel.kEigenschaftKombi,
                    tartikel.kVaterArtikel, tartikel.kStueckliste, tartikel.kWarengruppe, tartikel.cArtNr,
                    tartikel.cName, tartikel.cBeschreibung, tartikel.cKurzBeschreibung, tartikel.cAnmerkung,
                    tartikel.fLagerbestand, tartikel.fMwSt, tartikel.cBarcode, tartikel.cLagerBeachten,
                    tartikel.cLagerKleinerNull, tartikel.cLagerVariation, tartikel.cTeilbar, tartikel.fPackeinheit,
                    tartikel.cSuchbegriffe, tartikel.cSerie, tartikel.cISBN, tartikel.cASIN, tartikel.cHAN,
                    tartikel.cUPC, tartikel.nIstVater, tartikel.fVPEWert, tartikel.cVPE,
                    (SELECT cPfad FROM tartikelpict WHERE kArtikel = :pid AND nNr = 1 LIMIT 0, 1) AS cPfad,
                    (SELECT cSeo FROM tseo WHERE kKey = :pid 
                        AND kSprache = (
                            SELECT kSprache 
                                FROM tsprache 
                                WHERE cStandard = 'Y' LIMIT 0, 1
                            ) 
                        AND cKey = 'kArtikel' LIMIT 0, 1) AS cSeo,
                    teinheit.cName AS cEinheit
                FROM tartikel
                LEFT JOIN teinheit 
                    ON teinheit.kEinheit = tartikel.kEinheit 
                    AND teinheit.kSprache = (SELECT kSprache FROM tsprache WHERE cStandard = 'Y' LIMIT 0, 1)
                LEFT JOIN tartikelattribut 
                    ON tartikelattribut.kArtikel = tartikel.kArtikel
                    AND tartikelattribut.cName = '" . \JTLSEARCH_PRODUCT_EXCLUDE_ATTR . "'
                WHERE tartikelattribut.kArtikel IS NULL
                    AND tartikel.kArtikel = :pid",
                ['pid' => $id]
            );
            if ($res === null || $res === false) {
                throw new Exception(\sprintf(\__('errorLoadNoItem'), $id), 1);
            }
            $res->kArtikel          = (int)$res->kArtikel;
            $res->kHersteller       = (int)$res->kHersteller;
            $res->kSteuerklasse     = (int)$res->kSteuerklasse;
            $res->kEigenschaftKombi = (int)$res->kEigenschaftKombi;
            $res->kVaterArtikel     = (int)$res->kVaterArtikel;
            $res->kStueckliste      = (int)$res->kStueckliste;
            $res->kWarengruppe      = (int)$res->kWarengruppe;
            $res->nIstVater         = (int)$res->nIstVater;
            $this->product          = $res;
            unset($res);
            if ($this->product->kArtikel > 0) {
                $this->loadUsergroupVisibility()
                    ->loadProductCategoryFromDB()
                    ->loadProductPriceFromDB()
                    ->loadProductLanguagesFromDB()
                    ->loadProductAttributeFromDB()
                    ->loadProductVariationFromDB()
                    ->loadMetaKeywordsFromDB()
                    ->loadSalesRank();
            } else {
                $this->logger->error(
                    __CLASS__ . '->' . __METHOD__ . ': '
                    . \sprintf(\__('loggerErrorExportItem'), \var_export($this->product, true))
                );
            }
        } catch (Exception $e) {
            $return              = new stdClass();
            $return->nReturnCode = 0;
            $return->cMessage    = $e->getMessage();
            $this->logger->debug(__CLASS__ . '->' . __METHOD__ . '; ' . \json_encode($return));
        }

        return $this;
    }

    /**
     * @param int    $langID
     * @param string $locale
     * @param bool   $forceNoneSeo
     * @return string
     */
    private function buildProductURL(int $langID, string $locale, bool $forceNoneSeo = false): string
    {
        if ($forceNoneSeo === false && $langID > 0 && \method_exists(Shop::class, 'getRouter')) {
            $router = Shop::getRouter();

            return $router->getURLByType(
                $router::TYPE_PRODUCT,
                \array_merge(['lang' => $locale, 'name' => $this->product->cSeo, 'id' => $this->product->kArtikel])
            );
        }
        $shopURL = Shop::getURL();
        $res     = $shopURL;
        if ($forceNoneSeo === true) {
            $res .= '?a=' . $this->product->kArtikel;
            if ($langID > 0) {
                $res .= '&lang=' . $langID;
            }

            return $res;
        }
        if ($langID === 0) {
            $res .= '/' . $this->product->cSeo;
        } else {
            foreach ($this->product->oProductLanguage_arr as $loc) {
                if (isset($loc->kSprache) && (int)$loc->kSprache === $langID) {
                    $res .= '/' . $loc->cSeo;
                    break;
                }
            }
        }
        if ($res === $shopURL) {
            $res = $this->buildProductURL($langID, $locale, true);
        }

        return $res;
    }

    /**
     * @return $this
     */
    private function loadProductLanguagesFromDB(): self
    {
        $localizations                       = $this->db->getObjects(
            "SELECT tartikelsprache.kSprache, tartikelsprache.cName,
            tartikelsprache.cBeschreibung, tartikelsprache.cKurzBeschreibung,
                (SELECT cSeo 
                    FROM tseo 
                    WHERE kKey = :pid 
                    AND kSprache = tartikelsprache.kSprache 
                    AND cKey = 'kArtikel' LIMIT 0, 1) AS cSeo, tsprache.cStandard
                FROM tartikelsprache
                LEFT JOIN tsprache
                    ON tartikelsprache.kSprache = tsprache.kSprache
                WHERE tartikelsprache.kArtikel = :pid",
            ['pid' => $this->product->kArtikel]
        );
        $this->product->oProductLanguage_arr = [];
        foreach ($localizations as $localization) {
            $localization->kSprache                                       = (int)$localization->kSprache;
            $this->product->oProductLanguage_arr[$localization->kSprache] = $localization;
        }
        // tab% inhalt an beschreibungen anhaengen
        $productAttributes = $this->db->getObjects(
            "SELECT kAttribut, cStringWert, cTextWert
                FROM tattribut
                WHERE kArtikel = :pid 
                AND cName LIKE 'tab% inhalt'",
            ['pid' => $this->product->kArtikel]
        );
        foreach ($productAttributes as $attr) {
            if (\strlen($attr->cStringWert) > 0) {
                $text = $attr->cStringWert;
            } else {
                $text = $attr->cTextWert;
            }
            //std-Sprache
            $this->product->cBeschreibung .= ' ' . $text;
            //Andere Sprachen
            $attrLocalizations = $this->db->getObjects(
                "SELECT kSprache, cStringWert, cTextWert
                    FROM tattributsprache
                    WHERE kAttribut = :aid AND (cStringWert != '' OR cTextWert != '')",
                ['aid' => $attr->kAttribut]
            );
            foreach ($attrLocalizations as $attrLocalization) {
                $id = $attrLocalization->kSprache;
                if (!empty($attrLocalization->cStringWert)) {
                    $text = $attrLocalization->cStringWert;
                } else {
                    $text = $attrLocalization->cTextWert;
                }
                if (!isset($this->product->oProductLanguage_arr[$id])) {
                    $this->product->oProductLanguage_arr[$id]                = new stdClass();
                    $this->product->oProductLanguage_arr[$id]->cBeschreibung = '';
                }
                $this->product->oProductLanguage_arr[$id]->cBeschreibung .= ' ' . $text;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadProductAttributeFromDB(): self
    {
        $productAttributes             = $this->db->getObjects(
            "SELECT cName, cStringWert, cTextWert
                FROM tattribut
                WHERE kArtikel = :pid
                    AND (cStringWert <> '' OR cTextWert <> '')
                    AND cName NOT LIKE 'tab%_inhalt' AND cName NOT LIKE 'tab%_name'",
            ['pid' => $this->product->kArtikel]
        );
        $this->product->oAttribute_arr = [];
        foreach ($productAttributes as $productAttribute) {
            $attr               = new stdClass();
            $attr->cName        = $productAttribute->cName;
            $attr->cLanguageIso = 'ger';
            $attr->cWert        = empty($productAttribute->cStringWert)
                ? $productAttribute->cTextWert
                : $productAttribute->cStringWert;

            $this->product->oAttribute_arr[] = $attr;
            unset($attr);
        }

        $prodAttrs = $this->db->getObjects(
            'SELECT tmerkmal.cName AS cName, tmerkmalwertsprache.cWert AS cWert,
            tmerkmalwertsprache.kSprache AS kSprache
                FROM tartikelmerkmal
                JOIN tmerkmal 
                    ON tmerkmal.kMerkmal = tartikelmerkmal.kMerkmal
                JOIN tmerkmalwertsprache 
                    ON tmerkmalwertsprache.kMerkmalWert = tartikelmerkmal.kMerkmalWert
                WHERE tartikelmerkmal.kArtikel = :pid',
            ['pid' => $this->product->kArtikel]
        );
        foreach ($prodAttrs as $prodAttr) {
            $attr        = new stdClass();
            $attr->cName = $prodAttr->cName;
            foreach ($this->languages as $language) {
                if ($language->getId() === (int)$prodAttr->kSprache) {
                    $attr->cLanguageIso = $language->getCode();
                    break;
                }
            }
            $attr->cWert = $prodAttr->cWert;
            if (!isset($attr->cLanguageIso)) {
                continue;
            }

            $this->product->oAttribute_arr[] = $attr;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadProductVariationFromDB(): self
    {
        $variations = $this->db->getObjects(
            "SELECT teigenschaft.kEigenschaft AS kEigenschaft, teigenschaftwert.kEigenschaftWert AS kEigenschaftWert, 
                teigenschaft.cName AS cName, teigenschaftwert.cName AS cWert, 
                (SELECT cISO FROM tsprache WHERE cStandard = 'Y') AS cLanguageIso
                FROM teigenschaft
                JOIN teigenschaftwert
                    ON teigenschaftwert.kEigenschaft = teigenschaft.kEigenschaft
                    WHERE teigenschaft.kArtikel = :pid",
            ['pid' => $this->product->kArtikel]
        );
        $where      = '';
        foreach ($variations as $variation) {
            $where .= (\strlen($where) > 0) ? ' OR ' : '';
            $where .= '(teigenschaftsprache.kEigenschaft = ' . (int)$variation->kEigenschaft . ' AND
                teigenschaftwertsprache.kEigenschaftWert = ' . (int)$variation->kEigenschaftWert . ')';
        }
        if (\strlen($where) > 0) {
            $varLocalizations = $this->db->getObjects(
                'SELECT teigenschaftsprache.cName AS cName,
                    (SELECT cISO FROM tsprache WHERE kSprache = teigenschaftsprache.kSprache) AS cLanguageIso,
                    teigenschaftwertsprache.cName AS cWert
                    FROM teigenschaftsprache, teigenschaftwertsprache
                    WHERE (' . $where . ')
                        AND teigenschaftsprache.kSprache = teigenschaftwertsprache.kSprache
                    ORDER BY teigenschaftwertsprache.kEigenschaftWert, teigenschaftwertsprache.kSprache'
            );
            if (\is_array($varLocalizations)) {
                $this->product->oVariation_arr = $varLocalizations;
            }
        }
        $this->product->oVariation_arr = isset($this->product->oVariation_arr)
            ? \array_merge($this->product->oVariation_arr, $variations)
            : $variations;
        if (!isset($this->product->oVariation_arr) || !\is_array($this->product->oVariation_arr)) {
            $this->product->oVariation_arr = [];
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadMetaKeywordsFromDB(): self
    {
        $metaKeywords = $this->db->select(
            'tartikelattribut',
            'kArtikel',
            $this->product->kArtikel,
            'cName',
            'meta_title'
        );

        $this->product->cMetaKeywords = $metaKeywords->cWert ?? '';

        return $this;
    }

    /**
     * @param int $customerGroupID
     * @return bool
     */
    private function checkVisibility(int $customerGroupID): bool
    {
        $nonVisibleIDs = isset($this->nonVisibleIDs)
            ? $this->nonVisibleIDs->toArray()
            : [];

        if (\count($nonVisibleIDs) === 0) {
            return true;
        }

        if (\in_array($customerGroupID, $nonVisibleIDs, true)) {
            return false;
        }

        return true;
    }

    /**
     * @return $this
     */
    private function loadProductPriceFromDB(): self
    {
        $currencies  = $this->db->getObjects('SELECT * FROM twaehrung');
        $oldCurrency = Frontend::getCurrency();
        foreach ($this->customerGroups as $group) {
            foreach ($currencies as $currency) {
                $_SESSION['Waehrung']    = new Currency((int)$currency->kWaehrung);
                $price                   = new Preise($group->getID(), $this->product->kArtikel);
                $priceData               = new stdClass();
                $priceData->fPrice       = $group->isMerchant()
                    ? $price->fVK[1]
                    : $price->fVK[0];
                $priceData->cCurrencyIso = \strtoupper($currency->cISO);
                $priceData->kUserGroup   = $group->getID();
                if ($this->checkVisibility($group->getID())) {
                    $this->product->oPrice_arr[] = $priceData;
                }
            }
        }
        $_SESSION['Waehrung'] = $oldCurrency;

        return $this;
    }

    /**
     * @return $this
     */
    private function loadProductCategoryFromDB(): self
    {
        $categories = $this->db->getObjects(
            'SELECT kKategorie 
                FROM tkategorieartikel 
                WHERE kArtikel = :pid 
                    OR kArtikel = :ppid 
                GROUP BY kKategorie',
            ['pid' => $this->product->kArtikel, 'ppid' => $this->product->kVaterArtikel]
        );
        if (\count($categories) > 0) {
            foreach ($categories as $category) {
                $this->product->kKategorie_arr[] = $category->kKategorie;
            }
        } else {
            $this->product->kKategorie_arr = [];
            $this->logger->debug(
                __FILE__ . ':' . __CLASS__ . '->' . __METHOD__ . ': '
                . \sprintf(\__('loggerErrorNoCategoryForItem'), $this->product->kArtikel)
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadSalesRank(): self
    {
        if ($this->fBestsellerMax === null) {
            $res                  = $this->db->getSingleObject(
                'SELECT MAX(fAnzahl) AS fBestsellerMax FROM tbestseller'
            );
            $this->fBestsellerMax = $res->fBestsellerMax ?? 1;
        }
        $res = $this->db->getSingleObject(
            'SELECT fAnzahl FROM tbestseller WHERE kArtikel = :pid',
            ['pid' => $this->product->kArtikel]
        );

        $this->product->nSalesRank = (isset($res->fAnzahl))
            ? (int)(100 / $this->fBestsellerMax * (float)$res->fAnzahl)
            : 0;

        return $this;
    }

    /**
     * @return int|float
     */
    private function getProductAvailability()
    {
        return ($this->product->cLagerBeachten === 'N' || $this->product->cLagerKleinerNull === 'Y')
            ? -1
            : $this->product->fLagerbestand;
    }

    /**
     * @return null|Product
     */
    public function getFilledObject(): ?Product
    {
        $defLang = $this->defaultLanguage;
        if (
            !isset($this->product->oPrice_arr, $this->product->kArtikel)
            || !\is_array($this->product->oPrice_arr)
            || $this->product->kArtikel <= 0
        ) {
            return null;
        }
        $product = new Product();
        if ($this->product->cPfad !== null && \strlen($this->product->cPfad) > 0) {
            $product->setPictureURL(
                Shop::getURL() . '/' .
                \JTL\Media\Image\Product::getThumb(
                    Image::TYPE_PRODUCT,
                    $this->product->kArtikel,
                    $this->product,
                    Image::SIZE_SM,
                    0
                )
            );
        }
        $product->setId((int)$this->product->kArtikel)
            ->setArticleNumber($this->product->cArtNr)
            ->setAvailability($this->getProductAvailability())
            ->setMasterId((int)$this->product->kVaterArtikel)
            ->setManufacturer((int)$this->product->kHersteller)
            ->setEAN($this->product->cBarcode)
            ->setISBN($this->product->cISBN)
            ->setMPN($this->product->cHAN)
            ->setUPC($this->product->cUPC)
            ->setSalesRank($this->product->nSalesRank);

        $product->setName($this->product->cName, $defLang->getCode())
            ->setDescription($this->product->cBeschreibung, $defLang->getCode())
            ->setShortDescription($this->product->cKurzBeschreibung, $defLang->getCode());

        foreach ($this->product->kKategorie_arr as $catID) {
            $product->setCategory($catID);
        }

        $keywords = \trim($this->product->cMetaKeywords . ' ' . $this->product->cSuchbegriffe);
        foreach ($this->languages as $lang) {
            if (\strlen($keywords) > 0) {
                $product->setKeywords($keywords, $lang->getCode());
            }
            $idx = $lang->getId();
            $product->setURL($this->buildProductURL($idx, $lang->getIso639()), $lang->getCode());
            if ($idx === $defLang->getId()) {
                continue;
            }
            if (isset($this->product->oProductLanguage_arr[$idx])) {
                $product->setName(
                    (isset($this->product->oProductLanguage_arr[$idx]->cName)
                        && \strlen(\trim($this->product->oProductLanguage_arr[$idx]->cName)) > 0)
                        ? $this->product->oProductLanguage_arr[$idx]->cName
                        : $this->product->cName,
                    $lang->cISO
                )->setDescription(
                    (isset($this->product->oProductLanguage_arr[$idx]->cBeschreibung)
                        && \strlen(\trim($this->product->oProductLanguage_arr[$idx]->cBeschreibung)) > 0)
                        ? $this->product->oProductLanguage_arr[$idx]->cBeschreibung
                        : $this->product->cBeschreibung,
                    $lang->cISO
                )->setShortDescription(
                    (isset($this->product->oProductLanguage_arr[$idx]->cKurzBeschreibung)
                        && \strlen(\trim($this->product->oProductLanguage_arr[$idx]->cKurzBeschreibung)) > 0)
                        ? $this->product->oProductLanguage_arr[$idx]->cKurzBeschreibung
                        : $this->product->cKurzBeschreibung,
                    $lang->cISO
                );
            }
        }
        if (isset($this->product->oAttribute_arr) && \is_array($this->product->oAttribute_arr)) {
            foreach ($this->product->oAttribute_arr as $val) {
                $product->setAttribute($val->cName, $val->cWert, $val->cLanguageIso);
            }
        }
        if (isset($this->product->oVariation_arr) && \is_array($this->product->oVariation_arr)) {
            foreach ($this->product->oVariation_arr as $val) {
                $product->setVariation($val->cName, $val->cWert, $val->cLanguageIso);
            }
        }
        foreach ($this->product->oPrice_arr as $price) {
            if (isset($this->product->fVPEWert, $this->product->cEinheit)
                && $this->product->cVPE === 'Y'
                && $this->product->fVPEWert > 0
                && \strlen($this->product->cEinheit) > 0
            ) {
                $product->setPrice(
                    $price->cCurrencyIso,
                    $price->kUserGroup,
                    $price->fPrice,
                    ($price->fPrice / (float)$this->product->fVPEWert) . '/' . $this->product->cEinheit
                );
            } else {
                $product->setPrice($price->cCurrencyIso, $price->kUserGroup, $price->fPrice);
            }
        }

        return ($this->isVisible()) ? $product : null;
    }

    /**
     * @return bool
     */
    protected function isVisible(): bool
    {
        $visible = true;
        $conf    = (int)Shop::getSettingValue(\CONF_GLOBAL, 'artikel_artikelanzeigefilter');
        if ($conf === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGER) {
            if ($this->product->cLagerBeachten === 'Y' && $this->product->fLagerbestand <= 0) {
                $visible = false;
            }
        } elseif ($conf === \EINSTELLUNGEN_ARTIKELANZEIGEFILTER_LAGERNULL) {
            if (
                $this->product->cLagerBeachten === 'Y'
                && $this->product->fLagerbestand <= 0
                && $this->product->cLagerKleinerNull === 'N'
            ) {
                $visible = false;
            }
        }

        return $visible;
    }
}
