<?php

namespace Plugin\jtl_search\ExportModules;

use Exception;
use JTL\Catalog\Hersteller;
use JTL\DB\DbInterface;
use JTL\Language\LanguageModel;
use JTL\Shop;
use Psr\Log\LoggerInterface;

/**
 * Class ManufacturerData
 * @package Plugin\jtl_search\ExportModules
 */
class ManufacturerData extends Hersteller implements IItemData
{
    /**
     * @var DbInterface
     */
    protected DbInterface $db;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var string
     */
    public string $cURL = '';

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
     * @param LanguageModel[] $languages
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

        parent::__construct(0, $defaultLanguage->getId());
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt FROM thersteller',
            'cnt'
        );
    }

    /**
     * @inheritdoc
     */
    public static function getItemKeys(DbInterface $db, int $nLimitN, int $nLimitM): array
    {
        return $db->getObjects(
            'SELECT kHersteller AS kItem 
                FROM thersteller 
                ORDER BY kHersteller 
                LIMIT ' . $nLimitN . ', ' . $nLimitM
        );
    }

    /**
     * @param int  $id
     * @param int  $languageID
     * @param bool $noCache
     * @return Hersteller
     */
    public function loadFromDB(int $id, int $languageID = 0, bool $noCache = true): \JTL\Catalog\Hersteller
    {
        if ($languageID === 0) {
            $languageID = $this->defaultLanguage->getId();
        }

        return parent::loadFromDB($id, $languageID, $noCache);
    }

    /**
     * @param int $languageID
     * @return bool
     */
    private function loadManufacturerLanguage(int $languageID): bool
    {
        $id  = $this->getID();
        $res = $this->db->getSingleObject(
            "SELECT therstellersprache.cMetaTitle, therstellersprache.cMetaKeywords, 
                therstellersprache.cMetaDescription, therstellersprache.cBeschreibung, tseo.cSeo
                FROM thersteller
                LEFT JOIN therstellersprache 
                    ON therstellersprache.kHersteller = thersteller.kHersteller
                    AND therstellersprache.kSprache = :lid
                LEFT JOIN tseo 
                    ON tseo.kKey = thersteller.kHersteller
                    AND tseo.cKey = 'kHersteller'
                    AND tseo.kSprache = :lid
                WHERE thersteller.kHersteller = :mid",
            ['lid' => $languageID, 'mid' => $id]
        );
        if ($res === null) {
            return false;
        }
        foreach (\get_object_vars($res) as $key => $value) {
            $this->$key = $value;
        }
        $this->cURL = Shop::getURL() . '/' . (\strlen($res->cSeo) > 0
                ? $res->cSeo
                : '?h=' . $id);

        return true;
    }

    /**
     * @return Manufacturer
     * @throws Exception
     */
    public function getFilledObject()
    {
        $langCode     = $this->defaultLanguage->getCode();
        $id           = $this->getID();
        $manufacturer = new Manufacturer();
        $manufacturer->setId($id)
            ->setPriority(5);
        $manufacturer->setPictureURL($this->getImage());

        $manufacturer->setName($this->getName(), $langCode)
            ->setDescription($this->getDescription(), $langCode)
            ->setKeywords($this->getMetaKeywords(), $langCode)
            ->setURL($this->getURL(), $langCode);

        foreach ($this->languages as $lang) {
            $langID   = $lang->getId();
            $langCode = $lang->getCode();
            if (!$lang->isDefault() && $this->loadFromDB($id, $langID)) {
                $manufacturer->setName($this->getName($langID), $langCode)
                    ->setDescription($this->getDescription($langID), $langCode)
                    ->setKeywords($this->getMetaKeywords($langID), $langCode)
                    ->setURL($this->getURL($langID), $langCode);
            }
        }

        return $manufacturer;
    }
}
