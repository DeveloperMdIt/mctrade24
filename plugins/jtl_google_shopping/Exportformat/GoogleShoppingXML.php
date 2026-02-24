<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Exportformat;

use DateTime;
use Exception;
use Illuminate\Support\Collection;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Currency;
use JTL\Customer\Customer;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Extensions\Config\Configurator;
use JTL\Helpers\Tax;
use JTL\Helpers\Text;
use JTL\Plugin\Data\Localization;
use JTL\Session\Frontend;
use JTL\Shop;
use Psr\Log\LoggerInterface;

use function Functional\pluck;

/**
 * Class GoogleShoppingXML
 * @package Plugin\jtl_google_shopping\Exportformat
 */
class GoogleShoppingXML
{
    protected DbInterface $db;

    protected LoggerInterface $logger;

    /**
     * @var object
     */
    protected $exportformat;

    /**
     * @var resource
     */
    protected $tmpFile;

    protected ?Localization $localization = null;

    public int $cacheHits = 0;

    public int $cacheMisses = 0;

    protected string $header = /** @lang text */
        '<?xml version="1.0"?>' . "\r"
        . '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">' . "\r"
        . "\t" . '<channel>' . "\r"
        . "\t\t" . '<title><![CDATA[###cShop###]]></title>' . "\r"
        . "\t\t" . '<link><![CDATA[###cShopUrl###]]></link>' . "\r"
        . "\t\t" . '<description><![CDATA[###cShopBeschreibung###]]></description>' . "\r";

    protected string $footer = "\t</channel>\r</rss>";

    protected Collection $settings;

    protected bool $headInitialized = false;

    protected bool $headWritten = false;

    /**
     * @var array|null
     */
    protected ?array $exportProductIDs = null;

    /**
     * @var Product[]
     */
    protected array $exportProducts = [];

    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var string[]
     */
    protected array $unitCodeMapping = [
        '[lb_av]' => 'lb',
        'm2'      => 'sqm',
        'm3'      => 'cbm',
        '[in_i]'  => 'in',
    ];

    /**
     * @var string[]
     */
    protected array $unitCodes = [
        'oz',
        'lb',
        'mg',
        'g',
        'kg',
        'floz',
        'pt',
        'qt',
        'gal',
        'ml',
        'cl',
        'l',
        'cbm',
        'in',
        'ft',
        'yd',
        'cm',
        'm',
        'sqft',
        'sqm',
        'ct',
    ];

    protected Currency $currency;

    protected const MAX_PRODUCT_DESCRIPTION_LENGTH = 5000;

    protected const MAX_PRODUCT_NAME_LENGTH = 5000;

    /**
     * @param object      $exportformat
     * @param mixed       $f
     * @param Collection  $settings
     * @param DbInterface $db
     * @throws Exception
     */
    public function __construct(object $exportformat, $f, Collection $settings, DbInterface $db)
    {
        $this->db           = $db;
        $this->logger       = Shop::Container()->getLogService();
        $this->currency     = Frontend::getCurrency();
        $this->exportformat = $exportformat;
        if (isset($f) && \is_resource($f)) {
            $this->tmpFile = $f;
        } else {
            throw new Exception(\__('An error occurred while getting the file handle'));
        }
        $this->settings = $settings;
        $this->loadAttr()
            ->initHead();
    }

    private function mapUnitCode(?string $code): string
    {
        if ($code === '') {
            return 'ct';
        }
        if ($code === null) {
            return '';
        }
        $code = $this->unitCodeMapping[$code] ?? \mb_strtolower($code);

        return \in_array($code, $this->unitCodes, true) ? $code : '';
    }

    /**
     * Lädt optionale Attribute die der Benutzer in der Plugin-Einstellung selber definiert aus der DB
     */
    public function loadAttr(): self
    {
        $attributes = $this->db->query(
            'SELECT kAttribut, kVaterAttribut, cGoogleName, cWertName, eWertHerkunft
                FROM xplugin_jtl_google_shopping_attribut
                WHERE bAktiv = 1
                ORDER BY kVaterAttribut ASC',
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($attributes as $attribute) {
            $parent = $attribute->kVaterAttribut;
            if ($parent > 0) {
                if (\count($this->attributes[$parent]->oKindAttr_arr ?? []) > 0) {
                    $this->attributes[$parent]->oKindAttr_arr[$attribute->kAttribut] = $attribute;
                } else {
                    if (!isset($this->attributes[$parent])) {
                        $this->attributes[$parent] = (object)['oKindAttr_arr' => []];
                    }
                    $this->attributes[$parent]->oKindAttr_arr = [$attribute->kAttribut => $attribute];
                }
            } else {
                $this->attributes[$attribute->kAttribut] = $attribute;
            }
        }

        return $this;
    }

    /**
     * Initialisiert den Head (ersetzt Platzhalter für Shopname, -beschreibung und -URL)
     */
    public function initHead(): self
    {
        $this->header = \str_replace(
            '###cShop###',
            Text::htmlentities($this->settings->get('shopname')),
            $this->header
        );
        $this->header = \str_replace('###cShopUrl###', Shop::getURL(), $this->header);
        $this->header = \str_replace(
            '###cShopBeschreibung###',
            Text::htmlentities($this->settings->get('shopbeschreibung')),
            $this->header
        );

        $this->headInitialized = true;

        return $this;
    }

    public function writeHead(): self
    {
        if ($this->headInitialized && !$this->headWritten) {
            \fwrite($this->tmpFile, $this->header);
            $this->headWritten = true;
        }

        return $this;
    }

    /**
     * Setzt die zu exportierenden kArtikel
     *
     * @param array $exportProducts
     * @return self
     */
    public function setExportProductIds(array $exportProducts): self
    {
        if ($this->exportProductIDs !== null) {
            return $this;
        }
        $this->exportProductIDs = \array_map('\intval', pluck($exportProducts, 'kArtikel'));

        return $this;
    }

    public function setLocalization(Localization $localization): self
    {
        $this->localization = $localization;

        return $this;
    }

    /**
     * @param int        $productID
     * @param array|null $taxRates
     * @return self
     * @throws Exception
     */
    public function loadExportProduct(int $productID, ?array $taxRates = null): self
    {
        if ($productID <= 0) {
            return $this;
        }
        $opt                              = Product::getExportOptions();
        $product                          = new Product();
        $this->exportProducts[$productID] = $product;
        try {
            $product->fuelleArtikel(
                $productID,
                $opt,
                $this->exportformat->kKundengruppe,
                $this->exportformat->kSprache,
                $this->exportformat->nUseCache !== 1
            );
        } catch (Exception) {
            unset($product);

            return $this;
        }
        if ($product->kArtikel === null) {
            unset($this->exportProducts[$productID]);
            $this->logger->notice(
                \sprintf(
                    \__('Product %d could not be exported because no product exists for current settings'),
                    $productID
                )
            );

            return $this;
        }
        if ($product->cacheHit === true) {
            ++$this->cacheHits;
        } else {
            ++$this->cacheMisses;
        }
        $sep = '?';
        if (isset($product->cDeeplink) && \mb_strpos($product->cDeeplink, '.php')) {
            $sep = '&';
        }
        $product->cDeeplink = $product->cURLFull;

        if (!$this->currency->isDefault()) {
            $product->cDeeplink .= $sep . 'curr=' . $this->currency->getCode();
            $sep                = '&';
        }
        if (!empty($this->exportformat->tkampagne_cParameter) && !empty($this->exportformat->tkampagne_cWert)) {
            $product->cDeeplink .= $sep
                . $this->exportformat->tkampagne_cParameter
                . '=' . $this->exportformat->tkampagne_cWert;
        }
        if (isset($product->kStueckliste) && $product->kStueckliste > 0) {
            $product->bIsBundle = 'TRUE';
        }
        $product->fUst      = $taxRates[$product->kSteuerklasse] ?? Tax::getSalesTax($product->kSteuerklasse);
        $product->fVKBrutto = Tax::getGross(
            $product->Preise->fVKNetto * $this->currency->getConversionFactor(),
            $product->fUst
        ) . ' ' . $this->currency->getCode();

        if ((int)$product->nIstVater === 0 && $product->kVaterArtikel > 0) {
            $this->loadExportProduct($product->kVaterArtikel, $taxRates);
            if (isset($this->exportProducts[$product->kVaterArtikel]->kArtikel)) {
                if ((int)$this->settings->get('ext_artnr_child') === 1) {
                    $product->cArtNr .= '_' . $product->kArtikel;
                }
                $product->cVaterArtNr = $this->exportProducts[$product->kVaterArtikel]->cArtNr;
                unset($this->exportProducts[$product->kVaterArtikel]);
            } else {
                unset(
                    $this->exportProducts[$productID],
                    $this->exportProducts[$product->kVaterArtikel]
                );
                $this->logger->notice(
                    \sprintf(\__('Product %d could not be exported because no parent product exists'), $productID)
                );

                return $this;
            }
        }

        $this->loadProductAttributes($product)
            ->loadAvailibility($product)
            ->loadImages($product)
            ->loadGtin($product)
            ->loadState($product)
            ->loadShipping($product)
            ->loadCategory($product)
            ->loadGoogleCategory($product)
            ->loadSale($product)
            ->loadUnitPricing($product)
            ->loadConfigPrice($product)
            ->formatItems($product);

        return $this;
    }

    private function loadProductAttributes(Product $product): self
    {
        if (isset($product->cMerkmalAssoc_arr) && \is_array($product->cMerkmalAssoc_arr)) {
            $mappings              = $this->db->query(
                'SELECT cVon, cZu, cType
                    FROM xplugin_jtl_google_shopping_mapping',
                ReturnType::ARRAY_OF_OBJECTS
            );
            $mappedAttributes      = [];
            $mappedAttributeValues = [];
            foreach ($mappings as $mapping) {
                if (\mb_strtolower($mapping->cType) === 'merkmal') {
                    $mappedAttributes[$mapping->cVon] = $mapping->cZu;
                } elseif (\mb_strtolower($mapping->cType) === 'merkmalwert') {
                    $mappedAttributeValues[$mapping->cVon] = $mapping->cZu;
                }
            }

            foreach ($product->oMerkmale_arr as $attribute) {
                $name = \method_exists($attribute, 'getName')
                    ? $attribute->getName()
                    : $attribute->cName;
                if ($name === null) {
                    continue; // only happens when an attribute is not translated
                }
                $value      = '';
                $charValues = \method_exists($attribute, 'getCharacteristicValues')
                    ? $attribute->getCharacteristicValues()
                    : $attribute->oMerkmalWert_arr;
                foreach ($charValues as $i => $attribValue) {
                    if ($i > 0) {
                        $value .= '/';
                    }
                    $currentValue = \method_exists($attribValue, 'getValue')
                        ? $attribValue->getValue()
                        : $attribValue->cWert ?? null;
                    $value        .= $currentValue;
                }
                if (\array_key_exists(\mb_strtolower($name), $mappedAttributes)) {
                    $name = $mappedAttributes[\mb_strtolower($name)];
                }
                if (\array_key_exists(\mb_strtolower($value), $mappedAttributeValues)) {
                    $value = $mappedAttributeValues[\mb_strtolower($value)];
                }

                $lowerName = \mb_strtolower($name);
                if (\str_replace(['ö', 'ß'], ['oe', 'ss'], $lowerName) === 'groesse') {
                    $product->cGroesse = $value;
                } elseif ($lowerName === 'farbe') {
                    $product->cFarbe = $value;
                } elseif ($lowerName === 'geschlecht') {
                    $product->cGeschlecht = $value;
                } elseif ($lowerName === 'altersgruppe') {
                    $product->cAltersgruppe = $value;
                } elseif ($lowerName === 'muster') {
                    $product->cMuster = $value;
                } elseif ($lowerName === 'material') {
                    $product->cMaterial = $value;
                }
            }
        }

        if ((int)$product->nIstVater === 0 && (int)$product->kVaterArtikel > 0) {
            $this->addVariations($product);
        }

        return $this;
    }

    private function addVariations(Product $product): void
    {
        foreach ($product->Variationen as $variation) {
            if ($variation->cName === null) {
                continue;
            }
            if (\mb_strtolower($variation->cName) === 'farbe') {
                foreach ($product->oVariationenNurKind_arr as $variationProduct) {
                    if (\mb_strtolower($variationProduct->cName) === 'farbe') {
                        $product->cFarbe = $variationProduct->Werte[0]->cName;
                    }
                }
            } elseif (\mb_strtolower($variation->cName) === 'material') {
                foreach ($product->oVariationenNurKind_arr as $variationProduct) {
                    if (\mb_strtolower($variationProduct->cName) === 'material') {
                        $product->cMaterial = $variationProduct->Werte[0]->cName;
                    }
                }
            } elseif (\mb_strtolower($variation->cName) === 'muster') {
                foreach ($product->oVariationenNurKind_arr as $variationProduct) {
                    if (\mb_strtolower($variationProduct->cName) === 'muster') {
                        $product->cMuster = $variationProduct->Werte[0]->cName;
                    }
                }
            } elseif (\str_replace(['ö', 'ß'], ['oe', 'ss'], \mb_strtolower($variation->cName)) === 'groesse') {
                foreach ($product->oVariationenNurKind_arr as $variationProduct) {
                    if (
                        \str_replace(
                            ['ö', 'ß'],
                            ['oe', 'ss'],
                            \mb_strtolower($variationProduct->cName)
                        ) === 'groesse'
                    ) {
                        $product->cGroesse = $variationProduct->Werte[0]->cName;
                    }
                }
            }
        }
    }

    private function loadAvailibility(Product $product): self
    {
        $product->cVerfuegbarkeit = 'out of stock';
        if (
            $product->nErscheinendesProdukt === 1
            && !empty($product->dErscheinungsdatum)
            && Shop::getSettingValue(\CONF_GLOBAL, 'global_erscheinende_kaeuflich') === 'Y'
        ) {
            $product->cVerfuegbarkeit = 'preorder';
            try {
                $release          = new DateTime($product->dErscheinungsdatum);
                $product->release = $release->format('Y-m-d\TH:iO');
            } catch (Exception) {
            }

            return $this;
        }
        if ($product->fLagerbestand > 0 || $product->cLagerBeachten === 'N' || $product->cLagerKleinerNull === 'Y') {
            $product->cVerfuegbarkeit = 'in stock';
        }

        return $this;
    }

    private function loadImages(Product $product): self
    {
        $product->Artikelbild = $product->Bilder[0]->cURLGross;

        $imageCount = \count($product->Bilder);
        for ($i = 1; $i < $imageCount && $i <= 10; $i++) {
            $product->cArtikelbild_arr[] = $product->Bilder[$i]->cURLGross;
        }

        return $this;
    }

    private function loadGtin(Product $product): self
    {
        $product->cGtin = '';
        if (!empty($product->cBarcode)) {
            $product->cGtin = $product->cBarcode;
        } elseif (!empty($product->cISBN)) {
            $product->cGtin = $product->cISBN;
        }

        return $this;
    }

    /**
     * @throws Exception
     */
    private function loadSale(Product $product): self
    {
        if ($product->Preise->rabatt <= 0 && $product->Preise->Sonderpreis_aktiv !== 1) {
            return $this;
        }
        $currencyFactor = $this->currency->getConversionFactor();
        $currencyCode   = $this->currency->getCode();
        // Google: Das Attribut price [Preis] muss mit dem vollen (regulären) Preis für den Artikel
        // eingereicht werden
        $product->salePrice = $product->fVKBrutto;
        $product->fVKBrutto = Tax::getGross(
            $product->Preise->alterVKNetto * $currencyFactor,
            $product->fUst
        ) . ' ' . $currencyCode;
        // Google: Wenn Sie das Attribut sale_price_effective_date [Sonderangebotszeitraum] nicht einreichen,
        // gilt immer das Attribut sale_price [Sonderangebotspreis].
        if (!empty($product->Preise->SonderpreisBis_en)) {
            $product->salePriceEffectiveDate =
                (new DateTime())->sub(new \DateInterval('P1D'))->format('Y-m-d') . '/'
                . $product->Preise->SonderpreisBis_en;
        }

        return $this;
    }

    private function loadUnitPricing(Product $product): self
    {
        $unitCode     = $this->mapUnitCode($product->cMasseinheitCode);
        $baseUnitCode = $this->mapUnitCode($product->cGrundpreisEinheitCode);
        if (empty($unitCode) || empty($baseUnitCode)) {
            return $this;
        }
        if ($product->fMassMenge > 0 && $product->fGrundpreisMenge > 0) {
            $product->unitPricingMeasure     = $product->fMassMenge . ' ' . $unitCode;
            $product->unitPricingBaseMeasure = $product->fGrundpreisMenge . ' ' . $baseUnitCode;
        }

        return $this;
    }

    /**
     * Prüft ob für den Artikel ein Zustand verfügbar ist.
     * Wenn ja wird dieser geladen sonst Standart-Wert aus den Plugineinstellungen
     */
    private function loadState(Product $product): self
    {
        $conf = $this->settings->get('product_condition');
        if (isset($product->FunktionsAttribute[$conf]) && \is_string($product->FunktionsAttribute[$conf])) {
            $product->cZustand = $product->FunktionsAttribute[$conf];
        } else {
            $product->cZustand = $this->settings->get('default_product_condition');
        }

        return $this;
    }


    /**
     * calculate shipping costs for exports
     *
     * @param string  $iso
     * @param Product $product
     * @param int     $customerGroupID
     * @return int|float
     * @former gibGuenstigsteVersandkosten()
     */
    private function getLowestShippingFees(string $iso, Product $product, int $customerGroupID)
    {
        $customer                = new Customer();
        $customer->kKundengruppe = $customerGroupID;
        $productShipping         = Shop::Container()->getShippingService()->getLowestShippingFeesForProduct(
            $iso,
            $product,
            false,
            $customer,
            $this->currency,
        );
        if ($productShipping === -1.0) {
            return -1;
        }

        return Tax::getGross(
            $productShipping,
            $product->fUst ?? Tax::getSalesTax($product->kSteuerklasse ?? 0)
        );
    }

    /**
     * Lädt Versanddaten (Versandklasse, Lieferland und Versandkosten)
     *
     * @param Product $product
     * @return self
     */
    private function loadShipping(Product $product): self
    {
        /** @var string $deliveryCountry */
        $deliveryCountry      = $this->settings->get('exportformate_lieferland');
        $product->cLieferland = $deliveryCountry;
        $shippingCosts        = (float)\number_format((float)$this->getLowestShippingFees(
            $deliveryCountry,
            $product,
            (int)($this->exportformat->kKundengruppe ?? 0)
        ), 2);
        if ($shippingCosts < 0) {
            unset($this->exportProducts[$product->kArtikel]);

            return $this;
        }

        $product->Versandkosten = \number_format(
            $shippingCosts * (float)$this->currency->getConversionFactor(),
            2,
            '.',
            ''
        ) . ' ' . $this->currency->getName();

        return $this;
    }

    private function loadCategory(Product $product): self
    {
        $count = 0;
        if (\count($product->oKategorie_arr) === 0) {
            unset($this->exportProducts[$product->kArtikel]);

            return $this;
        }
        foreach ($product->oKategorie_arr as $categoryID) {
            $category      = new Kategorie(
                $categoryID,
                $this->exportformat->kSprache,
                $this->exportformat->kKundengruppe
            );
            $categoryPaths = $category->getCategoryPath();

            $product->cCategorie_arr[] = \implode(' &gt; ', $categoryPaths);
            if ($count++ >= 10) {
                return $this;
            }
        }

        return $this;
    }

    /**
     * Lädt Google-Kategorie.
     * Wenn für den Artikel eine GoogleKategorie angegeben ist wird diese verwendet,
     * sonst die Google-Kategorie der Artikelkategorie im Shop
     */
    private function loadGoogleCategory(Product $product): self
    {
        /** @var string $conf */
        $conf = $this->settings->get('product_googlecat');
        if (isset($product->FunktionsAttribute[$conf]) && \is_string($product->FunktionsAttribute[$conf])) {
            $product->cGoogleCategorie[] = \str_replace(
                ['"', '>'],
                ['', '&gt;'],
                $product->FunktionsAttribute[$conf]
            );
        } elseif (isset($product->oKategorie_arr) && \is_array($product->oKategorie_arr)) {
            foreach ($product->oKategorie_arr as $categoryID) {
                $category = new Kategorie(
                    $categoryID,
                    $this->exportformat->kSprache,
                    $this->exportformat->kKundengruppe
                );
                $func     = $category->getCategoryFunctionAttribute($conf);
                $parentID = $category->getParentID();
                if (!empty($func)) {
                    if (
                        empty($product->cGoogleCategorie)
                        || !\is_array($product->cGoogleCategorie)
                        || !\in_array(
                            \str_replace(
                                ['"', '>'],
                                ['', '&gt;'],
                                $func
                            ),
                            $product->cGoogleCategorie,
                            true
                        )
                    ) {
                        $product->cGoogleCategorie[] = \str_replace(
                            ['"', '>'],
                            ['', '&gt;'],
                            $func
                        );

                        return $this;
                    }
                } elseif ($parentID > 0) {
                    if ($this->loadGoogleCategoryVater($parentID, $product)) {
                        return $this;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Lädt Google-Kategorie wenn keine beim Artikel hinterlegt ist oder bei der Kategorie keine angegeben ist.
     * Verwendet die Google-Kategorie der Vater-Kategorie im Shop
     *
     * @param int     $categoryID
     * @param Product $product
     * @return bool
     */
    private function loadGoogleCategoryVater(int $categoryID, Product $product): bool
    {
        /** @var string $conf */
        $conf     = $this->settings->get('product_googlecat');
        $category = new Kategorie($categoryID, $this->exportformat->kSprache, $this->exportformat->kKundengruppe);
        $func     = $category->getCategoryFunctionAttribute($conf);
        $parentID = $category->getParentID();
        if (!empty($func)) {
            if (
                !isset($product->cGoogleCategorie)
                || !\is_array($product->cGoogleCategorie)
                || !\in_array(
                    \str_replace(
                        ['"', '>'],
                        ['', '&gt;'],
                        $func
                    ),
                    $product->cGoogleCategorie,
                    true
                )
            ) {
                $product->cGoogleCategorie[] = \str_replace(
                    ['"', '>'],
                    ['', '&gt;'],
                    $func
                );

                return true;
            }
        } elseif ($parentID > 0) {
            if ($this->loadGoogleCategoryVater($parentID, $product)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Product $product
     * @return self
     */
    private function loadConfigPrice(Product $product): self
    {
        if (!$product->bHasKonfig || !Configurator::validateKonfig($product->kArtikel)) {
            return $this;
        }
        $configGroups = Configurator::getKonfig($product->kArtikel, $this->exportformat->kSprache);
        $landingPrice = (float)$product->fVKBrutto;
        foreach ($configGroups as $configGroup) {
            foreach ($configGroup->oItem_arr as $configItem) {
                if ($configItem->getSelektiert()) {
                    $configItem->fAnzahl = $configItem->getInitial();
                    $landingPrice        += $configItem->getFullPrice();
                }
            }
        }

        $product->fVKBrutto = $landingPrice . ' ' . $this->currency->getCode();

        return $this;
    }

    private function formatItems(Product $product): self
    {
        $product->cBeschreibungHTML     = \str_replace('"', '&quot;', $product->cBeschreibung ?? '');
        $product->cKurzBeschreibungHTML = \str_replace('"', '&quot;', $product->cKurzBeschreibung ?? '');

        $find    = ['<br />', '<br>', '</'];
        $replace = [' ', ' ', ' </'];

        $defaultDesc = $this->localization !== null
            ? $this->localization->getTranslation('no_product_description_exists')
            : \__('Sorry, but there is no description for this product');
        if (empty($product->cBeschreibung)) {
            $product->cBeschreibung = $defaultDesc;
        }
        if (empty($product->cKurzBeschreibung)) {
            $product->cKurzBeschreibung = $defaultDesc;
        }
        if ($this->exportformat->cKodierung !== 'ASCII') {
            $data = $this->db->getSingleObject(
                'SELECT tartikel.cName, tartikelsprache.cName AS cName_spr
                    FROM tartikel
                    LEFT JOIN tartikelsprache
                       ON tartikelsprache.kArtikel = tartikel.kArtikel 
                       AND tartikelsprache.kSprache = :lid
                    WHERE tartikel.kArtikel = :pid',
                ['pid' => $product->kArtikel, 'lid' => $this->exportformat->kSprache]
            );
            if ($data !== null) {
                $product->cName = empty($data->cName_spr) ? $data->cName : $data->cName_spr;
            }
        }
        $product->cName             = \str_replace(
            $find,
            $replace,
            $product->cName
        );
        $product->cBeschreibung     = \str_replace(
            $find,
            $replace,
            $product->cBeschreibung
        );
        $product->cKurzBeschreibung = \str_replace(
            $find,
            $replace,
            $product->cKurzBeschreibung
        );
        if ($this->settings['strip_tags'] ?? 'N' === 'Y') {
            $product->cName             = \strip_tags($product->cName);
            $product->cBeschreibung     = \strip_tags($product->cBeschreibung);
            $product->cKurzBeschreibung = \strip_tags($product->cKurzBeschreibung);
        }
        $product->cName             = \mb_substr($product->cName, 0, self::MAX_PRODUCT_NAME_LENGTH);
        $product->cBeschreibung     = \mb_substr($product->cBeschreibung, 0, self::MAX_PRODUCT_DESCRIPTION_LENGTH);
        $product->cKurzBeschreibung = \mb_substr($product->cKurzBeschreibung, 0, self::MAX_PRODUCT_DESCRIPTION_LENGTH);

        $unit = ' ' . Shop::Lang()->get('weightUnit');
        if (isset($product->cGewicht, $product->fGewicht)) {
            $product->cGewicht = \number_format((float)$product->fGewicht, 4, '.', '') . $unit;
        }
        if (isset($product->cArtikelgewicht, $product->fArtikelgewicht)) {
            $product->cArtikelgewicht = \number_format((float)$product->fArtikelgewicht, 4, '.', '') . $unit;
        }

        return $this;
    }

    public function writeFooter(): self
    {
        \fwrite($this->tmpFile, $this->footer);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function writeContent(): self
    {
        $exportCountry = $this->settings->get('exportformate_lieferland');
        $taxClassRates = $this->db->getObjects(
            'SELECT ss.kSteuerklasse, ss.fSteuersatz
                FROM tsteuersatz AS ss
                JOIN tsteuerzoneland AS szl ON szl.kSteuerzone = ss.kSteuerzone AND szl.cISO = :iso
                JOIN tsteuerklasse AS sk ON sk.kSteuerklasse = ss.kSteuerklasse',
            ['iso' => $exportCountry]
        );
        $taxRates      = [];
        foreach ($taxClassRates as $taxRate) {
            $taxRates[(int)$taxRate->kSteuerklasse] = (float)$taxRate->fSteuersatz;
        }

        $defaultCountry = $_SESSION['Steuerland'] ?? null;
        if ($defaultCountry !== $exportCountry) {
            Tax::setTaxRates($exportCountry, true);
        }
        foreach ($this->exportProductIDs as $productID) {
            $this->loadExportProduct($productID, $taxRates)
                ->writeProduct($this->exportProducts[$productID] ?? new Product());

            unset($this->exportProducts[$productID]);
        }
        if ($defaultCountry !== $exportCountry) {
            Tax::setTaxRates($defaultCountry, true);
        }

        return $this;
    }

    private function writeProduct(Product $product): self
    {
        if ((int)$product->kArtikel <= 0 || \count($this->attributes) === 0) {
            return $this;
        }
        $prefixAttr  = "\t\t\t";
        $prefixChild = "\t\t\t\t";
        $xml         = "\t\t<item>\r";
        foreach ($this->attributes as $attribute) {
            if ($attribute->eWertHerkunft === 'VaterAttribut') {
                if (isset($attribute->oKindAttr_arr) && \count($attribute->oKindAttr_arr) > 0) {
                    $xml .= $prefixAttr . '<' . $attribute->cGoogleName . ">\r";
                    foreach ($attribute->oKindAttr_arr as $child) {
                        if ($child->eWertHerkunft === 'WertName') {
                            $xml .= $this->writeAttribute(
                                $prefixChild,
                                $child->cGoogleName,
                                $child->cWertName
                            );
                        } elseif ($child->eWertHerkunft === 'ArtikelEigenschaft') {
                            if (isset($product->{$child->cWertName}) && $product->{$child->cWertName} !== '') {
                                $xml .= $this->writeAttribute(
                                    $prefixChild,
                                    $child->cGoogleName,
                                    $product->{$child->cWertName}
                                );
                            }
                        } elseif ($child->eWertHerkunft === 'FunktionsAttribut') {
                            $idx = \mb_strtolower($child->cWertName);
                            if (isset($product->FunktionsAttribute[$idx])) {
                                $xml .= $this->writeAttribute(
                                    $prefixChild,
                                    $child->cGoogleName,
                                    $product->FunktionsAttribute[$idx]
                                );
                            }
                        } elseif ($child->eWertHerkunft === 'Attribut') {
                            if (isset($product->AttributeAssoc[$child->cWertName])) {
                                $xml .= $this->writeAttribute(
                                    $prefixChild,
                                    $child->cGoogleName,
                                    $product->AttributeAssoc[$child->cWertName]
                                );
                            }
                        } elseif ($child->eWertHerkunft === 'Merkmal') {
                            $valName = \preg_replace(
                                '/[^öäüÖÄÜßa-zA-Z0-9.\-_]/u',
                                '',
                                $child->cWertName
                            );
                            if (isset($product->cMerkmalAssoc_arr[$valName])) {
                                $xml .= $this->writeAttribute(
                                    $prefixChild,
                                    $child->cGoogleName,
                                    $product->cMerkmalAssoc_arr[$valName]
                                );
                            }
                        }
                    }
                    $xml .= $prefixAttr . '</' . $attribute->cGoogleName . ">\r";
                }
            } elseif ($attribute->eWertHerkunft === 'WertName') {
                $xml .= $this->writeAttribute($prefixAttr, $attribute->cGoogleName, $attribute->cWertName);
            } elseif ($attribute->eWertHerkunft === 'ArtikelEigenschaft') {
                if (isset($product->{$attribute->cWertName}) && ($product->{$attribute->cWertName} !== '')) {
                    $xml .= $this->writeAttribute(
                        $prefixAttr,
                        $attribute->cGoogleName,
                        $product->{$attribute->cWertName}
                    );
                }
                if (
                    $attribute->cGoogleName === 'g:availability'
                    && $product->cVerfuegbarkeit === 'preorder'
                    && $product->release !== null
                ) {
                    $xml .= $this->writeAttribute(
                        $prefixAttr,
                        'g:availability_date',
                        $product->release
                    );
                }
            } elseif ($attribute->eWertHerkunft === 'FunktionsAttribut') {
                $idx = \mb_strtolower($attribute->cWertName);
                if (isset($product->FunktionsAttribute[$idx])) {
                    $xml .= $this->writeAttribute(
                        $prefixAttr,
                        $attribute->cGoogleName,
                        $product->FunktionsAttribute[$idx]
                    );
                }
            } elseif ($attribute->eWertHerkunft === 'Attribut') {
                if (isset($product->AttributeAssoc[$attribute->cWertName])) {
                    $xml .= $this->writeAttribute(
                        $prefixAttr,
                        $attribute->cGoogleName,
                        $product->AttributeAssoc[$attribute->cWertName]
                    );
                }
            } elseif ($attribute->eWertHerkunft === 'Merkmal') {
                $valName = \preg_replace('/[^öäüÖÄÜßa-zA-Z0-9.\-_]/u', '', $attribute->cWertName);
                if (isset($product->cMerkmalAssoc_arr[$valName])) {
                    $xml .= $this->writeAttribute(
                        $prefixAttr,
                        $attribute->cGoogleName,
                        $product->cMerkmalAssoc_arr[$valName]
                    );
                }
            }
        }
        if (\count($product->Preise->nAnzahl_arr) > 0) {
            foreach ($product->Preise->nAnzahl_arr as $idx => $item) {
                $price = Tax::getGross(
                    $product->Preise->fStaffelpreis_arr[$idx][1] * $this->currency->getConversionFactor(),
                    $product->fUst
                ) . ' ' . $this->currency->getCode();

                $xml .= $prefixAttr . '<g:bulk_price>' . "\r";
                $xml .= $prefixChild . '<g:min_quantity>' . $item . '</g:min_quantity>' . "\r";
                $xml .= $prefixChild . '<g:price>' . $price . '</g:price>' . "\r";
                $xml .= $prefixAttr . '</g:bulk_price>' . "\r";
                if ($idx > 5) {
                    break;
                }
            }
        }
        $xml .= "\t\t</item>\r";
        \fwrite($this->tmpFile, $xml);

        return $this;
    }

    /**
     * Generiert einen XML-String für das Attribut: $cAttributeName mit dem Inhalt: $cContent
     * und dem Prefix: $cPreAttribute
     * Wenn $cContent ein Array ist dann ruft sich die Methode rekursiv auf
     *
     * @param string       $preAttribute
     * @param string       $attributeName
     * @param string|array $content
     * @return string mit XML für das Attribut
     */
    private function writeAttribute(string $preAttribute, string $attributeName, $content): string
    {
        $xml = '';
        if (isset($content) && \is_array($content)) {
            foreach ($content as $i => $value) {
                $xml .= $this->writeAttribute($preAttribute, $attributeName, $value);
                if ($attributeName === 'g:product_type' && $i === 9) {
                    break;
                }
            }
        } else {
            $xml .= $preAttribute . '<' . $attributeName . '><![CDATA['
                . \trim((string)$content)
                . ']]></' . $attributeName . ">\r";
        }

        return $xml;
    }
}
