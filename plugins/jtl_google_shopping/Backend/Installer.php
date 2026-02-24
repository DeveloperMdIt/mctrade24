<?php

declare(strict_types=1);

namespace Plugin\jtl_google_shopping\Backend;

use JTL\DB\DbInterface;
use JTL\Plugin\PluginInterface;
use JTL\Shop;

/**
 * Class Installer
 * @package Plugin\jtl_google_shopping\Backend
 */
class Installer
{
    private DbInterface $db;

    public function __construct(PluginInterface $plugin, DbInterface $db)
    {
        $this->db = $db;
        Shop::Container()->getGetText()->loadPluginLocale('install', $plugin);
    }

    public function installAttributeData(): bool
    {
        $result = $this->db->getAffectedRows(
            "INSERT INTO `xplugin_jtl_google_shopping_attribut` (
                `kAttribut`, `kVaterAttribut`, `kStandardVaterAttribut`,
                `cGoogleName`, `cStandardGoogleName`, `cWertName`, `cStandardWertName`,
                `eWertHerkunft`, `eStandardWertHerkunft`, `bStandard`, `bAktiv`
            ) VALUES
                (
                    1, 0, 0, 
                    'title', 'title', 'cName', 'cName', 
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    2, 0, 0,
                    'link', 'link', 'cDeeplink', 'cDeeplink',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    3, 0, 0,
                    'description', 'description', 'cBeschreibung', 'cBeschreibung',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    4, 0, 0,
                    'g:id', 'g:id', 'cArtNr', 'cArtNr',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    5, 0, 0,
                    'g:condition', 'g:condition', 'cZustand', 'cZustand',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    6, 0, 0,
                    'g:price', 'g:price', 'fVKBrutto', 'fVKBrutto',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    7, 0, 0,
                    'g:availability', 'g:availability', 'cVerfuegbarkeit',
                    'cVerfuegbarkeit', 'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    8, 0, 0,
                    'g:image_link', 'g:image_link', 'Artikelbild', 'Artikelbild',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    9, 0, 0,
                    'g:shipping', 'g:shipping', NULL, NULL,
                    'VaterAttribut', 'VaterAttribut', 1, 1
                ),
                (
                    10, 0, 0,
                    'g:product_type', 'g:product_type', 'cCategorie_arr', 'cCategorie_arr',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    11, 0, 0,
                    'g:google_product_category', 'g:google_product_category', 'cGoogleCategorie', 'cGoogleCategorie',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    12, 0, 0,
                    'g:item_group_id', 'g:item_group_id', 'cVaterArtNr', 'cVaterArtNr',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    13, 0, 0,
                    'g:mpn', 'g:mpn', 'cHAN', 'cHAN',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    14, 0, 0,
                    'g:brand', 'g:brand', 'cHersteller', 'cHersteller',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    15, 0, 0,
                    'g:shipping_weight', 'g:shipping_weight', 'cGewicht', 'cGewicht',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    16, 0, 0,
                    'g:gtin', 'g:gtin', 'cGtin', 'cGtin',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    17, 0, 0,
                    'g:additional_image_link', 'g:additional_image_link', 'cArtikelbild_arr',
                    'cArtikelbild_arr', 'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (   
                    18, 0, 0,
                    'g:color', 'g:color', 'cFarbe', 'cFarbe',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    19, 0, 0,
                    'g:material', 'g:material', 'cMaterial',
                    'cMaterial', 'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    20, 0, 0,
                    'g:pattern', 'g:pattern', 'cMuster', 'cMuster',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    21, 0, 0,
                    'g:size', 'g:size', 'cGroesse', 'cGroesse',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    22, 0, 0,
                    'g:gender', 'g:gender', 'cGeschlecht', 'cGeschlecht',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    23, 0, 0,
                    'g:age_group', 'g:age_group', 'cAltersgruppe', 'cAltersgruppe',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    24, 9, 9,
                    'g:country', 'g:country', 'cLieferland', 'cLieferland',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    25, 9, 9,
                    'g:service', 'g:service', 'cVersandklasse', 'cVersandklasse',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    26, 9, 9,
                    'g:price', 'g:price', 'Versandkosten', 'Versandkosten',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    27, 0, 0,
                    'g:identifier_exists', 'g:identifier_exists', 'bIdentifierExists', 'bIdentifierExists',
                    'FunktionsAttribut', 'FunktionsAttribut', 1, 1
                ),
                (
                    28, 0, 0,
                    'mobile_link', 'mobile_link', 'cMobilLink', 'cMobilLink',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    29, 0, 0,
                    'g:is_bundle', 'g:is_bundle', 'bIsBundle', 'bIsBundle',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 0
                ),
                (
                    30, 0, 0,
                    'g:sale_price', 'g:sale_price', 'salePrice', 'salePrice',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    31, 0, 0,
                    'g:sale_price_effective_date', 'g:sale_price_effective_date',
                    'salePriceEffectiveDate', 'salePriceEffectiveDate',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    32, 0, 0,
                    'g:unit_pricing_measure', 'g:unit_pricing_measure', 'unitPricingMeasure', 'unitPricingMeasure',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                ),
                (
                    33, 0, 0,
                    'g:unit_pricing_base_measure', 'g:unit_pricing_base_measure',
                    'unitPricingBaseMeasure', 'unitPricingBaseMeasure',
                    'ArtikelEigenschaft', 'ArtikelEigenschaft', 1, 1
                )"
        );

        return $result !== 0;
    }

    public function install(): void
    {
        $this->db->query(
            'CREATE TABLE IF NOT EXISTS `xplugin_jtl_google_shopping_mapping` (
                `kMapping`  int(11)         NOT NULL AUTO_INCREMENT,
                `cVon`      varchar(255)    NOT NULL,
                `cZu`       varchar(255)    NOT NULL,
                `cType`     varchar(255)    NOT NULL,
                PRIMARY KEY (`kMapping`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1'
        );
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `xplugin_jtl_google_shopping_attribut` (
                `kAttribut`                 int(11) unsigned    NOT NULL AUTO_INCREMENT,
                `kVaterAttribut`            int(11) unsigned    NOT NULL    DEFAULT '0',
                `kStandardVaterAttribut`    int(11) unsigned                DEFAULT NULL,
                `cGoogleName`               varchar(255)        NOT NULL,
                `cStandardGoogleName`       varchar(255)                    DEFAULT NULL,
                `cWertName`                 varchar(255)                    DEFAULT NULL,
                `cStandardWertName`         varchar(255)                    DEFAULT NULL,
                `eWertHerkunft`             enum(
                                                'ArtikelEigenschaft',
                                                'FunktionsAttribut',
                                                'Attribut',
                                                'Merkmal',
                                                'WertName',
                                                'VaterAttribut'
                                            )                   NOT NULL    DEFAULT 'ArtikelEigenschaft',
                `eStandardWertHerkunft`     enum(
                                                'ArtikelEigenschaft',
                                                'FunktionsAttribut',
                                                'Attribut',
                                                'Merkmal',
                                                'WertName',
                                                'VaterAttribut'
                                            )                               DEFAULT NULL,
                `bStandard`                 tinyint(1)          NOT NULL    DEFAULT '0',
                `bAktiv`                    int(11)             NOT NULL    DEFAULT '1',
                PRIMARY KEY (`kAttribut`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
        );

        $this->installAttributeData();
    }

    public function uninstall(): void
    {
        $this->db->query('DROP TABLE IF EXISTS `xplugin_jtl_google_shopping_mapping`');
        $this->db->query('DROP TABLE IF EXISTS `xplugin_jtl_google_shopping_attribut`');
    }
}
