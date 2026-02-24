<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JTL\Catalog\Currency;
use JTL\Catalog\Product\Preise;
use JTL\Events\Dispatcher;
use JTL\Media\Image;
use JTL\Media\Image\Product;
use JTL\Shop;
use Plugin\s360_clerk_shop5\src\Utils\AttributesHelper;

/**
 * Query and process data for the product feed.
 *
 * @package Plugin\s360_clerk_shop5\src\Export
 */
class ProductFeedBuilder extends AbstractFeedBuilder
{
    use AttributesHelper;

    public const EVENT_BOOT = 'boot_product_feed_builder';
    public const EVENT_PROCESS_ROW = 'process_product_row';
    public const EVENT_GET_QUERY = 'get_product_query';
    public const EVENT_GET_BULK_PRICE_QUERY = 'get_product_bulk_price_query';

    protected array $attributes = [];
    protected array $funcAttributes = [];
    protected array $characteristics = [];
    protected array $parentPrices = [];
    protected array $imageSettings = [];

    protected Currency $currency;

    public function boot(): void
    {
        parent::boot();

        $this->imageSettings = Shop::getSettings([\CONF_BILDER]);
        $this->currency = new Currency($this->store->getSettings()?->getCurrency() ?? 1);

        //! We preload the different attributes before loading the products
        //! The reason is that when loading them in the product query via GROUP_CONCAT the query time gets exponentially
        //! worse. Then preloading these we are much faster while also consuming roughly the same amount of memory
        if ($this->store->getSettings()?->getEnableAttributes()) {
            $query = "SELECT
                    tattribut.kArtikel,
                    COALESCE(tas.cName, tattribut.cName, '') as cName,
                    IF(
                        IF(tas.cStringWert IS NOT NULL, tas.cStringWert, tattribut.cStringWert) != '',
                        IF(tas.cStringWert IS NOT NULL, tas.cStringWert, tattribut.cStringWert),
                        IF(tas.cTextWert IS NOT NULL, tas.cTextWert, tattribut.cTextWert)
                    ) as cWert
                FROM tattribut
                LEFT JOIN tattributsprache as tas ON
                    tas.kAttribut = tattribut.kAttribut AND tas.kSprache = {$this->store->getLanguageId()}";
            $this->attributes = $this->loadAttributes($query);
        }

        if ($this->store->getSettings()?->getEnableFuncAttributes()) {
            $this->funcAttributes = $this->loadAttributes("SELECT kArtikel, cName, cWert FROM tartikelattribut");
        }

        if ($this->store->getSettings()?->getEnableCharacteristics()) {
            $query = "SELECT
                    tartikelmerkmal.kArtikel, tmerkmal.cName, tmerkmalwertsprache.cWert
                FROM tartikelmerkmal
                LEFT JOIN tmerkmal ON tartikelmerkmal.kMerkmal = tmerkmal.kMerkmal
                LEFT JOIN tmerkmalwertsprache ON tartikelmerkmal.kMerkmalWert = tmerkmalwertsprache.kMerkmalWert
                    AND tmerkmalwertsprache.kSprache = {$this->store->getLanguageId()}";

            $this->characteristics = $this->loadAttributes($query);
        }

        // When the option minBulkPriceAsPrice is active when have to preprocess the bulk prices
        // otherwise we could not set the price for the parents correctly (we do not now the lowest child price)
        if ($this->store->getSettings()?->getMinBulkPriceAsPrice()) {
            $result = $this->connection->query($this->getBulkPriceQuery());

            while ($row = $result->fetch_assoc()) {
                $this->preprocessMinBulkPrices($row);
            }

            unset($result);
        }

        Dispatcher::getInstance()->fire('s360_clerk_shop5.' . self::EVENT_BOOT, ['builder' => $this]);
    }

    public function processRow(array $row): array
    {
        
        $row['categories'] = explode(',', $row['categories'] ?? '');
        $row['is_parent'] = (bool) $row['is_parent'];
        $row['in_stock'] = (bool) $row['in_stock'];
        $row['on_sale'] = (bool) $row['on_sale'];
        $row['top_article'] = $row['top_article'] === 'Y';
        $row['reviews_avg'] = $row['reviews_avg'] ?? 0;
        $row['has_variations'] = (bool) $row['has_variations'];
        $row['is_config_article'] = (bool) $row['is_config_article'];
        $row = $this->processAttributes($row);

        // Process Image
        $baseURL = Shop::getImageBaseURL();
        $row['image'] = $baseURL . \BILD_KEIN_ARTIKELBILD_VORHANDEN;
        if ($row['imgPath']) {
            if ($row['bildname'] ?? false) { // set by FKT_ATTRIBUT_BILDNAME and used by Product::getCustomName as customImgName
                $row['customImgName'] = $row['bildname'];
            }

            $name = Product::getCustomName((object)$row);
            if((int)$row['imgNo'] > 1) {
                $name .= $name . "~" . (int)$row['imgNo'];
            }

            // Build image url and remove tmp image data from exported row
            $config = \mb_convert_case($this->imageSettings['bilder']['bilder_dateiformat'], \MB_CASE_LOWER);
            $ext = $config === 'auto' ? \pathinfo($row['imgPath'])['extension'] ?? 'jpg' : $config;

            $row['image'] = $baseURL . 'media/image/product/' . $row['id'] . '/md/' . $name . '.' . $ext;

            unset($row['kArtikel'], $row['cArtNr'], $row['originalSeo'], $row['cSeo'], $row['cName'], $row['cBarcode']);
        }

        // Process Bulk prices
        if ($row['bulk_prices']) {
            $bulkPrices = explode(';', $row['bulk_prices']);
            $row['bulk_prices'] = null;
            $row['bulk_prices_localized'] = null;

            if (!empty($bulkPrices)) {
                $prices = [];
                $pricesLocalized = [];

                foreach ($bulkPrices as $entry) {
                    list($quantity, $price) = explode('|', $entry);
                    $prices[$quantity] = (float) ($price ?? 0);
                    $pricesLocalized[$quantity] = Preise::getLocalizedPriceString($price, $this->currency);
                }

                $row['bulk_prices'] = json_encode($prices);
                $row['bulk_prices_localized'] = json_encode($pricesLocalized);

                // Set min bulk price as the price
                if ($this->store->getSettings()?->getMinBulkPriceAsPrice() && !$row['on_sale']) {
                    $minPrice = min($prices);
                    $row['price'] = $minPrice;
                    $row['original_price'] = $minPrice;

                    // If we are a parent and have preproccessed child bulk prices, use the min child bulk price
                    if (array_key_exists($row['id'], $this->parentPrices)) {
                        $minPrice = min($this->parentPrices[$row['id']]);
                        $row['price'] = $minPrice;
                        $row['original_price'] = $minPrice;
                    }
                }
            }

            unset($bulkPrices);
        }

        // Process Base Prices
        if ($row['base_price']) {
            $row['base_price_localized'] = Preise::getLocalizedPriceString($row['base_price'], $this->currency);
            $row['base_price_with_unit_localized'] =
                Preise::getLocalizedPriceString($row['base_price'], $this->currency)
                . ' ' . Shop::Lang()->get('vpePer') . ' '
                .  $row['base_price_unit'];
        }

        // Process Price
        $row['price_localized'] = Preise::getLocalizedPriceString($row['price'], $this->currency);
        $row['list_price_localized'] = Preise::getLocalizedPriceString($row['list_price'], $this->currency);
        $row['original_price_localized'] = Preise::getLocalizedPriceString($row['original_price'], $this->currency);

        if ($row['list_price'] == 0) {
            $row['list_price'] = null;
            $row['list_price_localized'] = null;
        }

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_PROCESS_ROW,
            ['row' => &$row, 'builder' => $this]
        );

        return $row;
    }

    /**
     * Process min bulk prices to be able to set the min bulk price on the parent
     * @param array $row
     * @return void
     */
    protected function preprocessMinBulkPrices(array $row): void
    {
        if (empty($row['bulk_prices'])) {
            return;
        }

        $bulkPrices = explode(';', $row['bulk_prices']);

        if (!empty($bulkPrices) && $row['on_sale'] == 0 && $row['is_parent'] == 0) {
            $prices = [];
            foreach ($bulkPrices as $entry) {
                list($quantity, $price) = explode('|', $entry);
                $prices[$quantity] = (float) ($price ?? 0);
            }

            $minPrice = min($prices);

            if (!array_key_exists($row['parent_id'], $this->parentPrices)) {
                $this->parentPrices[$row['parent_id']] = [];
            }

            $this->parentPrices[$row['parent_id']][] = $minPrice;
        }
    }

    /**
     * Get the bulk prices SQL query so that we can determine the min bulk price for parent products.
     *
     * @return string
     */
    protected function getBulkPriceQuery(): string
    {
        $productsWithoutPrice = '';
        if (!$this->store->getSettings()?->getProductsWithoutPrice()) {
            $productsWithoutPrice = ' AND tartikel.fStandardpreisNetto > 0.0001';
        }

        $categoryDiscount = '(1 - IF(takr.fRabatt IS NULL, 0, takr.fRabatt) / 100)';

        $query =
            "SELECT
                tartikel.kArtikel as id,
                IF(tartikel.kVaterArtikel = '0', true, false) AS is_parent,
                tartikel.kVaterArtikel AS parent_id,
                tartikel.nIstVater as has_variations,
                tartikel.fLagerbestand as stock,
                CASE
                    WHEN tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten ='Y' AND tartikel.cLagerKleinerNull = 'N' then false
                    ELSE true
                END AS in_stock,
                CASE
                    WHEN tasp.kArtikelSonderpreis IS NULL THEN false
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 0 THEN true
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 1 AND tartikel.fLagerbestand >= tasp.nAnzahl THEN true
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 0 THEN true
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 1  AND tartikel.fLagerbestand >= tasp.nAnzahl THEN true
                    ELSE false
                END AS on_sale,

                # TODO: Check performance (might be a performance kill when having an excessive amount of bulk prices)
                GROUP_CONCAT(DISTINCT tpreisdetail.nAnzahlAb,'|',tpreisdetail.fVKNetto * {$categoryDiscount} * twaehrung.fFaktor * (1 + tartikel.fMwSt/100) SEPARATOR ';') as bulk_prices

            FROM tartikel

            LEFT JOIN tartikelsichtbarkeit ON
                tartikel.kArtikel = tartikelsichtbarkeit.kArtikel AND
                tartikelsichtbarkeit.kKundengruppe = {$this->store->getCustomerGroupId()}

            LEFT JOIN tartikelkategorierabatt as takr ON takr.kArtikel = tartikel.kArtikel AND takr.kKundengruppe = {$this->store->getCustomerGroupId()}
            LEFT JOIN tartikelsonderpreis as tasp ON tasp.kArtikel = tartikel.kArtikel AND tasp.cAktiv = 'Y'
            LEFT JOIN tpreis ON tpreis.kArtikel = tartikel.kArtikel AND (tpreis.kKunde IS NULL OR tpreis.kKunde = 0)  AND tpreis.kKundengruppe = {$this->store->getCustomerGroupId()}
            LEFT JOIN tpreisdetail on tpreisdetail.kPreis = tpreis.kPreis
            LEFT JOIN twaehrung ON twaehrung.kWaehrung = {$this->currency->getID()}

            WHERE tartikelsichtbarkeit.kArtikel IS NULL {$productsWithoutPrice}
            GROUP BY tartikel.kArtikel";

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_GET_BULK_PRICE_QUERY,
            ['query' => &$query, 'builder' => $this]
        );

        return $query;
    }

    /**
     * Get Total Products count
     *
     * @return int
     */
    public function getTotalProducts(): int
    {
        $productsWithoutPrice = '';
        if (!$this->store->getSettings()?->getProductsWithoutPrice()) {
            $productsWithoutPrice = ' AND tartikel.fStandardpreisNetto > 0.0001';
        }

        $result = $this->connection->query("SELECT count(tartikel.kArtikel) as total
            FROM tartikel
            LEFT JOIN tartikelsichtbarkeit ON
                tartikel.kArtikel = tartikelsichtbarkeit.kArtikel AND
                tartikelsichtbarkeit.kKundengruppe = {$this->store->getCustomerGroupId()}
            WHERE tartikelsichtbarkeit.kArtikel IS NULL {$productsWithoutPrice}");

        if ($result->num_rows) {
            return (int) $result->fetch_array()['total'] ?? 0;
        }

        return 0;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return string
     */
    public function getSqlQuery(): string
    {
        $baseUrl = rtrim(URL_SHOP, '/') . '/';
        $productsWithoutPrice = '';
        $priceCalc = " * (1 + tartikel.fMwSt/100)";
        $priceCalcOnSale = " * (1 + tartikel.fMwSt/100)";
        $priceCalcNet = "";
        $priceCalcNetOnSale = "";
        $productModeFilter = '';


        // check gross/net prices and change pricecalc
        if ($this->store->getCustomerGroup()->isMerchant()) {
            $priceCalc = "";
            $priceCalcOnSale = "";
        }

        if ($this->store->getCustomerGroup()->getDiscount()) {
            $priceCalc .= " * (100 - {$this->store->getCustomerGroup()->getDiscount()} ) / 100";
            $priceCalcNet = " * (100 - {$this->store->getCustomerGroup()->getDiscount()} ) / 100";
        }

        if (!$this->store->getSettings()?->getProductsWithoutPrice()) {
            $productsWithoutPrice = ' AND tartikel.fStandardpreisNetto > 0.0001';
        }
        if ($this->store->getSettings()?->getFeedProductMode() === 'parent_only') {
            $productModeFilter = ' AND tartikel.kVaterArtikel = 0';
        } elseif ($this->store->getSettings()?->getFeedProductMode() === 'children_only') {
            $productModeFilter = ' AND tartikel.kVaterArtikel > 0';
        }

        $categoryDiscount = '(1 - IF(takr.fRabatt IS NULL, 0, takr.fRabatt) / 100)';

        // Additional cols needed for building image path
        $cols = '';
        switch (Image::getSettings()['naming'][Image::TYPE_PRODUCT]) {
            case 1:
                $cols = 'tartikel.cArtNr,';
                break;
            case 2:
                $cols = 'tartikel.cSeo, tartikel.cSeo AS originalSeo, tartikel.cName,';
                break;
            case 3:
                $cols = 'tartikel.cArtNr, tartikel.cSeo, tartikel.cSeo AS originalSeo, tartikel.cName,';
                break;
            case 4:
                $cols = 'tartikel.cBarcode,';
                break;
            case 0:
            default:
                break;
        }

        $query =
            "SELECT
                tartikel.kArtikel as id,
                IF(tartikel.kVaterArtikel = '0', true, false) AS is_parent,
                tartikel.kVaterArtikel AS parent_id,
                tartikel.nIstVater as has_variations,
                tartikel.kEigenschaftKombi as variationCombinationId,
                IF(tas.cName IS NOT NULL, tas.cName, tartikel.cName) as name,
                IF(tas.cBeschreibung IS NOT NULL, tas.cBeschreibung, tartikel.cBeschreibung) as description,
                IF(tas.cKurzBeschreibung IS NOT NULL, tas.cKurzBeschreibung, tartikel.cKurzBeschreibung) as short_description,
                tartikel.cSuchbegriffe as keywords,
                tartikel.cHAN as han,
                tartikel.cTopArtikel as top_article,
                tartikel.fUVP as list_price,
                IF(tartikelkonfiggruppe.kArtikel IS NOT NULL, true, false) AS is_config_article,
                tartikelpict.cPfad as imgPath,
                tartikelpict.nNr as imgNo,
                {$cols}
                CONCAT('{$baseUrl}', IF(tas.cSeo IS NOT NULL, tas.cSeo, tartikel.cSeo)) as url,

                # TODO: Check performance (might be a performance killer when having an excessive amount of categories)
                CASE
                    WHEN GROUP_CONCAT(DISTINCT tkategorieartikel.kKategorie) IS NOT NULL
                    THEN GROUP_CONCAT(DISTINCT tkategorieartikel.kKategorie)
                    ELSE 0
                END as categories,

                # JTL rounds down to .5, while clerk rounds up (JTL 4.8 -> 4.5, Clerk 4.8 -> 5.0)
                CASE
                    WHEN tartikelext.fDurchschnittsBewertung IS NULL AND tartikel.kVaterArtikel = '0' THEN false
                    WHEN tartikelext.fDurchschnittsBewertung IS NULL AND tartikel.kVaterArtikel != '0' THEN (
                        SELECT ROUND(tartikelext.fDurchschnittsBewertung * 2) / 2 FROM tartikelext WHERE tartikelext.kArtikel = tartikel.kVaterArtikel
                    )
                    ELSE ROUND(tartikelext.fDurchschnittsBewertung * 2) / 2
                END as reviews_avg,
                (
                    SELECT count(tbewertung.kArtikel) FROM tbewertung
                    WHERE tbewertung.kArtikel = tartikel.kArtikel AND tbewertung.kSprache = {$this->store->getLanguageId()} AND tbewertung.nAktiv = 1
                ) as reviews_amount,
                thersteller.cName as brand,
                tartikel.cArtNr as sku,
                CASE
                    WHEN tartikel.kVaterArtikel = '0'
                    THEN (SELECT GROUP_CONCAT(sq.cArtNr SEPARATOR ' ') FROM tartikel sq WHERE sq.kVaterArtikel = tartikel.kArtikel)
                    ELSE null
                END as child_articles_sku,
                tartikel.cBarcode as gtin,
                datediff(CURDATE(), tartikel.dErstellt) as `age`,
                UNIX_TIMESTAMP(tartikel.dErstellt) as created_at,
                datediff(CURDATE(), tartikel.dErscheinungsdatum) as `vorbestellbardate`,
                tartikel.dErscheinungsdatum as erscheinungsdatum,
                tartikel.fLagerbestand as stock,
                CASE
                    WHEN tartikel.fLagerbestand <= 0 AND tartikel.cLagerBeachten ='Y' AND tartikel.cLagerKleinerNull = 'N' then false
                    ELSE true
                END AS in_stock,
                CASE
                    WHEN tasp.kArtikelSonderpreis IS NULL THEN false
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 0 THEN true
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 1 AND tartikel.fLagerbestand >= tasp.nAnzahl THEN true
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 0 THEN true
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 1  AND tartikel.fLagerbestand >= tasp.nAnzahl THEN true
                    ELSE false
                END AS on_sale,
                CASE
                    WHEN tasp.kArtikelSonderpreis IS NULL THEN tpreisdetail.fVKNetto * {$categoryDiscount} * twaehrung.fFaktor {$priceCalc}
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 0 THEN tsp.fNettoPreis * twaehrung.fFaktor {$priceCalcOnSale}
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 1 AND tartikel.fLagerbestand >= tasp.nAnzahl THEN tsp.fNettoPreis  * twaehrung.fFaktor {$priceCalcOnSale}
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 0 THEN tsp.fNettoPreis * twaehrung.fFaktor {$priceCalcOnSale}
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 1  AND tartikel.fLagerbestand >= tasp.nAnzahl THEN tsp.fNettoPreis * twaehrung.fFaktor {$priceCalcOnSale}
                    ELSE tpreisdetail.fVKNetto * {$categoryDiscount} * twaehrung.fFaktor {$priceCalc}
                END AS price,
                tpreisdetail.fVKNetto  * twaehrung.fFaktor {$priceCalc} as original_price,
                CASE
                    WHEN tasp.kArtikelSonderpreis IS NULL THEN tpreisdetail.fVKNetto * {$categoryDiscount} * twaehrung.fFaktor {$priceCalcNet}
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 0 THEN tsp.fNettoPreis * twaehrung.fFaktor {$priceCalcNetOnSale}
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 1 AND tartikel.fLagerbestand >= tasp.nAnzahl THEN tsp.fNettoPreis  * twaehrung.fFaktor {$priceCalcNetOnSale}
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 0 THEN tsp.fNettoPreis * twaehrung.fFaktor {$priceCalcNetOnSale}
                    WHEN tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 1  AND tartikel.fLagerbestand >= tasp.nAnzahl THEN tsp.fNettoPreis * twaehrung.fFaktor {$priceCalcNetOnSale}
                    ELSE tpreisdetail.fVKNetto * {$categoryDiscount} * twaehrung.fFaktor {$priceCalcNet}
                END AS net_price,


                # TODO: Check performance (might be a performance kill when having an excessive amount of bulk prices)
                GROUP_CONCAT(DISTINCT tpreisdetail.nAnzahlAb,'|',tpreisdetail.fVKNetto * {$categoryDiscount} * twaehrung.fFaktor {$priceCalc} SEPARATOR ';') as bulk_prices,

                CASE
                    WHEN tartikel.cVPE = 'Y' AND tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 0 THEN tsp.fNettoPreis / tartikel.fVPEWert * twaehrung.fFaktor {$priceCalcOnSale}
                    WHEN tartikel.cVPE = 'Y' AND tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 0 AND tasp.nIstAnzahl = 1 AND tartikel.fLagerbestand >= tasp.nAnzahl THEN tsp.fNettoPreis / tartikel.fVPEWert * twaehrung.fFaktor {$priceCalcOnSale}
                    WHEN tartikel.cVPE = 'Y' AND tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 0 THEN tsp.fNettoPreis / tartikel.fVPEWert * twaehrung.fFaktor {$priceCalcOnSale}
                    WHEN tartikel.cVPE = 'Y' AND tasp.kArtikelSonderpreis IS NOT NULL AND tasp.nIstDatum = 1 AND DATE(tasp.dStart) <= DATE(NOW()) AND DATE(tasp.dEnde) >= DATE(NOW()) AND tasp.nIstAnzahl = 1  AND tartikel.fLagerbestand >= tasp.nAnzahl THEN tsp.fNettoPreis / tartikel.fVPEWert * twaehrung.fFaktor {$priceCalcOnSale}
                    WHEN tartikel.cVPE = 'Y' THEN tpreisdetail.fVKNetto / tartikel.fVPEWert * {$categoryDiscount} * twaehrung.fFaktor {$priceCalc}
                    ELSE 0
                END as base_price,
                CASE
                    WHEN tartikel.kGrundPreisEinheit > 0 AND tartikel.fGrundpreisMenge > 0 THEN CONCAT(tartikel.fGrundpreisMenge, ' ', LOWER(tmasseinheit.cCode))
                    ELSE LOWER(tartikel.cVPEEinheit)
                END as base_price_unit

            FROM tartikel

            LEFT JOIN tartikelsichtbarkeit ON
                tartikel.kArtikel = tartikelsichtbarkeit.kArtikel AND
                tartikelsichtbarkeit.kKundengruppe = {$this->store->getCustomerGroupId()}
            LEFT JOIN tartikelsprache as tas ON
                tas.kArtikel = tartikel.kArtikel AND tas.kSprache = {$this->store->getLanguageId()}
            LEFT JOIN tartikelext on tartikelext.kArtikel = tartikel.kArtikel
            LEFT JOIN thersteller ON tartikel.kHersteller = thersteller.kHersteller

            LEFT JOIN tartikelpict ON tartikel.kArtikel = tartikelpict.kArtikel AND tartikelpict.nNr = (SELECT MIN(nNr) FROM tartikelpict WHERE kArtikel = tartikel.kArtikel)

            # TODO: Check performance
            INNER JOIN tkategorieartikel ON tkategorieartikel.kArtikel = (CASE WHEN tartikel.kVaterArtikel = 0 THEN tartikel.kArtikel ELSE tartikel.kVaterArtikel END)

            LEFT JOIN tartikelkategorierabatt as takr ON takr.kArtikel = tartikel.kArtikel AND takr.kKundengruppe = {$this->store->getCustomerGroupId()}
            LEFT JOIN tartikelsonderpreis as tasp ON tasp.kArtikel = tartikel.kArtikel AND tasp.cAktiv = 'Y'
            LEFT JOIN tsonderpreise as tsp ON tasp.kArtikelSonderpreis = tsp.kArtikelSonderpreis AND tsp.kKundengruppe = {$this->store->getCustomerGroupId()}
            LEFT JOIN tpreis ON tpreis.kArtikel = tartikel.kArtikel AND (tpreis.kKunde IS NULL OR tpreis.kKunde = 0)  AND tpreis.kKundengruppe = {$this->store->getCustomerGroupId()}
            LEFT JOIN tpreisdetail on tpreisdetail.kPreis = tpreis.kPreis
            LEFT JOIN twaehrung ON twaehrung.kWaehrung = {$this->currency->getID()}

            LEFT JOIN tmasseinheit ON tmasseinheit.kMassEinheit = tartikel.kGrundpreisEinheit

            LEFT JOIN tartikelkonfiggruppe ON tartikelkonfiggruppe.kArtikel = tartikel.kArtikel

            WHERE tartikelsichtbarkeit.kArtikel IS NULL {$productsWithoutPrice} {$productModeFilter}
            GROUP BY tartikel.kArtikel";

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_GET_QUERY,
            ['query' => &$query, 'builder' => $this]
        );

        return $query;
    }

    /**
     * Process Product attributes
     *
     * @param array $row
     * @return array
     */
    protected function processAttributes(array $row): array
    {
        
        if (array_key_exists($row['id'], $this->characteristics)) {
            
            foreach ($this->characteristics[$row['id']] as $key => $value) {
                $name = $this->transformAttributeName((string) $key);

                // Mapping of specific attributes
                if ($key === $this->store->getSettings()?->getMappingColors()) {
                    $name = 'color_codes';
                } elseif ($key === $this->store->getSettings()?->getMappingColorNames()) {
                    $name = 'color_names';
                } elseif ($key === $this->store->getSettings()?->getMappingGender()) {
                    $name = 'gender';
                }

                // If we already have the attribute name in the row, make sure to turn it into a list and append new val
                if (array_key_exists($name, $row)) {
                    if (!is_array($row['name'])) {
                        $row[$name] = [$row['name']];
                    }

                    $row[$name][] = $value;
                    continue;
                }

                $row[$name] = $value;
            }
            

            unset($this->characteristics[$row['id']]);
        }
        
        if (array_key_exists($row['id'], $this->attributes)) {
            foreach ($this->attributes[$row['id']] as $key => $value) {
                if ($key && $value) {
                    $key = $this->transformAttributeName((string)$key);
                    $row[$key] =  is_array($value)
                        ? array_map('html_entity_decode', $value)
                        : html_entity_decode($value);
                }
            }

            unset($this->attributes[$row['id']]);
        }

        if (array_key_exists($row['id'], $this->funcAttributes)) {
            foreach ($this->funcAttributes[$row['id']] as $key => $value) {
                $key = $this->transformAttributeName((string) $key);
                $row[$key] = $value;
            }

            unset($this->funcAttributes[$row['id']]);
        }
        
        if (      
            $row['is_parent'] === false && 
            (int)$row['variationCombinationId'] > 0
        ) {
            $variantParts = [];
            

            
            $properties = $this->getVariantPropertiesByKombiId((int)$row['variationCombinationId']);
            
            
            foreach ($properties as $prop) {
                $variantParts[] = $prop->property_name . ': ' . $prop->property_value;
            }
            if ($variantParts) {
                $row['variant_name'] = implode(', ', $variantParts);
            }
        }
        
        return $row;
    }
    
    /**
     * Load variant properties by kEigenschaftKombi
     * @param int $kEigenschaftKombi
     * @return array
     */
   
    public function getVariantPropertiesByKombiId(int $kEigenschaftKombi): array
    {
        $languageId = $this->store->getLanguageId();
        $sql = "
            SELECT 
                COALESCE(ts.cName, t.cName) AS property_name,
                COALESCE(ws.cName, w.cName) AS property_value
            FROM teigenschaftkombiwert AS kw
            JOIN teigenschaft AS t ON t.kEigenschaft = kw.kEigenschaft
            JOIN teigenschaftwert AS w ON w.kEigenschaftWert = kw.kEigenschaftWert
            LEFT JOIN teigenschaftsprache AS ts ON ts.kEigenschaft = t.kEigenschaft AND ts.kSprache = :languageId
            LEFT JOIN teigenschaftwertsprache AS ws ON ws.kEigenschaftWert = w.kEigenschaftWert AND ws.kSprache = :languageId
            WHERE kw.kEigenschaftKombi = :kEigenschaftKombi
            ORDER BY t.nSort
        ";
        /** @var stdClass[] $result */
        $result = Shop::Container()->getDB()->queryPrepared(
            $sql,
            ['kEigenschaftKombi' => $kEigenschaftKombi, 'languageId' => $languageId],
            2
        );
        return $result;
    }
    
    
    
    /**
     * Load Attrbitues from query
     * @param string $query
     * @return array
     */
    protected function loadAttributes(string $query)
    {
        $attr = [];
        $result = $this->connection->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Multiple Attr Values -> List
                if (array_key_exists($row['kArtikel'], $attr) && array_key_exists($row['cName'], $attr[$row['kArtikel']])) {
                    if (!is_array($attr[$row['kArtikel']][$row['cName']])) {
                        $attr[$row['kArtikel']][$row['cName']] = [$attr[$row['kArtikel']][$row['cName']]];
                    }

                    $attr[$row['kArtikel']][$row['cName']][] = $row['cWert'];
                    continue;
                }

                // Single Attr Value
                $attr[$row['kArtikel']][$row['cName']] = $row['cWert'];
            }
        }

        return $attr;
    }
}
