<?php

namespace Plugin\jtl_search\ExportModules;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;
use JTL\Helpers\URL;
use JTL\Language\LanguageModel;
use JTL\Media\Image;
use JTL\Media\Image\Product;
use JTL\Shop;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class CategoryData
 * @package Plugin\jtl_search\ExportModules
 */
class CategoryData implements IItemData
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Collection<int>
     */
    private Collection $customerGroups;

    /**
     * @var stdClass|null
     */
    private $categoryData;

    /**
     * @var DbInterface
     */
    private DbInterface $db;

    /**
     * @var LanguageModel[]
     */
    private array $languages;

    /**
     * @var LanguageModel
     */
    protected LanguageModel $defaultLanguage;

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
        $this->defaultLanguage = $defaultLanguage;
        $this->languages       = $languages;
        $this->customerGroups  = $this->db->getCollection(
            'SELECT kKundengruppe FROM tkundengruppe'
        )->map(static function ($e) {
            return (int)$e->kKundengruppe;
        });
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt FROM tkategorie',
            'cnt'
        );
    }

    /**
     * @inheritdoc
     */
    public static function getItemKeys(DbInterface $db, int $nLimitN, int $nLimitM): array
    {
        return $db->getObjects(
            'SELECT kKategorie AS kItem 
                FROM tkategorie 
                ORDER BY kKategorie 
                LIMIT :lmtf, :lmtu',
            ['lmtf' => $nLimitN, 'lmtu' => $nLimitM]
        );
    }

    /**
     * @inheritdoc
     */
    public function loadFromDB(int $id, int $languageID = 0, bool $noCache = true)
    {
        unset($this->categoryData);
        $category = $this->db->getSingleObject(
            "SELECT tkategorie.*, tkategoriepict.cPfad,
                (SELECT cWert 
                    FROM tkategorieattribut 
                    WHERE cName = 'meta_keywords' 
                        AND kKategorie = :cid 
                    LIMIT 0, 1) AS cKeywords
                FROM tkategorie 
                LEFT JOIN tkategoriepict 
                    ON tkategoriepict.kKategorie = tkategorie.kKategorie
                WHERE tkategorie.kKategorie = :cid",
            ['cid' => $id]
        );

        if ($category === null) {
            $this->logger->warning(__CLASS__ . '->' . __METHOD__ . '; ' . \__('loggerErrorLoadCategory'));
        } else {
            $this->categoryData = $category;
            $this->loadCategoryLanguageFromDB()
                ->loadCategoryVisibilityFromDB();
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadCategoryLanguageFromDB(): self
    {
        $localizations = [];
        if (isset($this->categoryData->kKategorie)) {
            $localizations = $this->db->selectAll(
                'tkategoriesprache',
                'kKategorie',
                $this->categoryData->kKategorie
            );
        }

        $this->categoryData->oCategoryLanguage_arr = [];
        foreach ($localizations as $localization) {
            $localization->kSprache                                             = (int)$localization->kSprache;
            $this->categoryData->oCategoryLanguage_arr[$localization->kSprache] = $localization;
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function loadCategoryVisibilityFromDB(): self
    {
        if (!isset($this->categoryData->kKategorie)) {
            return $this;
        }
        $data = $this->db->selectAll(
            'tkategoriesichtbarkeit',
            'kKategorie',
            $this->categoryData->kKategorie
        );
        foreach ($this->customerGroups as $groupID) {
            $this->categoryData->bUsergroupVisible[$groupID] = true;
        }
        foreach ($data as $item) {
            $this->categoryData->bUsergroupVisible[(int)$item->kKundengruppe] = false;
        }

        return $this;
    }

    /**
     * @return Category
     */
    public function getFilledObject(): Category
    {
        $langCode = $this->defaultLanguage->getCode();
        $shopURL  = Shop::getURL() . '/';
        $category = new Category();
        if (!empty($this->categoryData->cPfad)) {
            $category->setPictureURL(
                Shop::getImageBaseURL()
                . Product::getThumb(
                    Image::TYPE_CATEGORY,
                    (int)$this->categoryData->kKategorie,
                    $this->categoryData,
                    Image::SIZE_SM,
                    0
                )
            );
        } else {
            $category->setPictureURL(Shop::getImageBaseURL() . \BILD_KEIN_KATEGORIEBILD_VORHANDEN);
        }
        $category->setId((int)$this->categoryData->kKategorie)
            ->setMasterCategory((int)$this->categoryData->kOberKategorie)
            ->setPriority(5)
            ->setName($this->categoryData->cName, $langCode)
            ->setDescription($this->categoryData->cBeschreibung, $langCode)
            ->setKeywords($this->categoryData->cKeywords, $langCode)
            ->setURL(
                URL::buildURL($this->categoryData, \URLART_KATEGORIE, true, $shopURL),
                $langCode
            );

        $_SESSION['Sprachen'] = $this->languages;
        foreach ($this->languages as $language) {
            $langID   = $language->getId();
            $langCode = $language->getCode();
            if (!isset($this->categoryData->oCategoryLanguage_arr[$langID])) {
                continue;
            }
            $category->setName(
                $this->categoryData->oCategoryLanguage_arr[$langID]->cName,
                $langCode
            )->setDescription(
                $this->categoryData->oCategoryLanguage_arr[$langID]->cBeschreibung,
                $langCode
            );
            if (isset($this->categoryData->oCategoryLanguage_arr[$langID]->cKeywords)) {
                $category->setKeywords(
                    $this->categoryData->oCategoryLanguage_arr[$langID]->cKeywords,
                    $langCode
                );
            }
            $_SESSION['kSprache']    = $langID;
            $_SESSION['cISOSprache'] = $langCode;
            $url                     = URL::buildURL(
                $this->categoryData->oCategoryLanguage_arr[$langID],
                \URLART_KATEGORIE,
                true,
                $shopURL
            );
            $category->setURL($url, $langCode);
            unset($_SESSION['kSprache'], $_SESSION['cISOSprache']);
        }
        unset($_SESSION['Sprachen']);

        foreach ($this->customerGroups as $customerGroupID) {
            $category->setVisibility(
                $this->categoryData->bUsergroupVisible[$customerGroupID],
                $customerGroupID
            );
        }

        return $category;
    }
}
