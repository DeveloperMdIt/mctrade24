<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JTL\Events\Dispatcher;

class CategoryFeedBuilder extends AbstractFeedBuilder
{
    public const EVENT_PROCESS_ROW = 'process_categories_row';
    public const EVENT_GET_QUERY = 'get_categories_query';

    public function processRow(array $row): array
    {
        if (!empty($row['subcategories'])) {
            $row['subcategories'] = explode(',', $row['subcategories']);
        } else {
            $row['subcategories'] = [];
        }

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_PROCESS_ROW,
            ['row' => &$row, 'builder' => $this]
        );

        return $row;
    }

    public function getSqlQuery(): string
    {
        $baseUrl = rtrim(URL_SHOP, '/') . '/';
        $categoryseperator = $this->connection->escape_string(
            $this->store->getSettings()?->getCategorySeparator() ?? ''
        );

        // Check if we should skip empty categories
        $query = $this->connection->query(
            'SELECT * FROM teinstellungen WHERE cName = "kategorien_anzeigefilter" AND cWert = ' . \EINSTELLUNGEN_KATEGORIEANZEIGEFILTER_NICHTLEERE . ' LIMIT 1'
        );
        $skipEmpty = !empty($query->fetch_assoc());
        $exclude = '';

        if ($skipEmpty) {
            // Get all empty categories so that we can exclude them
            $query = $this->connection->query(
                "SELECT tkategorie.kKategorie as id
                FROM tkategorie
                LEFT JOIN tkategorieartikel ON tkategorieartikel.kKategorie = tkategorie.kKategorie
                GROUP BY id
                HAVING COUNT(tkategorieartikel.kArtikel) = 0"
            );

            $ids = array_column($query->fetch_all(MYSQLI_ASSOC), 'id');

            if (!empty($ids)) {
                $exclude = " AND p.kKategorie NOT IN (" . implode(',', $ids) . ") ";
            }
        }

        $query = "SELECT p.kKategorie as id,
                CASE
                    WHEN p.kOberKategorie ='0' THEN IFNULL(
                        IF(tks.cName IS NOT NULL, tks.cName, p.cName),
                        IF(tks.cSeo IS NOT NULL, tks.cSeo, p.cSeo)
                    )
                    ELSE IFNULL(
                        CONCAT(
                            (
                                SELECT IF(tkategoriesprache.cName IS NOT NULL, tkategoriesprache.cName, tkategorie.cName)
                                FROM tkategorie
                                LEFT JOIN tkategoriesprache
                                    ON tkategoriesprache.kKategorie = tkategorie.kKategorie AND tkategoriesprache.kSprache = {$this->store->getLanguageId()}
                                WHERE tkategorie.kKategorie = p.kOberKategorie
                            ),
                            '{$categoryseperator}',
                            IF(tks.cName IS NOT NULL, tks.cName, p.cName)
                        ),
                        IF(tks.cSeo IS NOT NULL, tks.cSeo, p.cSeo)
                    )
                END AS name,
                GROUP_CONCAT(c1.kKategorie) as subcategories,
                CONCAT('{$baseUrl}', IF(tks.cSeo IS NOT NULL, tks.cSeo, p.cSeo)) as url
            FROM tkategorie as p
            LEFT JOIN tkategorie as c1 ON c1.kOberKategorie = p.kKategorie
            LEFT JOIN tkategoriesichtbarkeit ON p.kKategorie = tkategoriesichtbarkeit.kKategorie
                AND tkategoriesichtbarkeit.kKundengruppe = {$this->store->getCustomerGroupId()}
            LEFT JOIN tkategoriesprache as tks ON
                tks.kKategorie = p.kKategorie AND tks.kSprache = {$this->store->getLanguageId()}
            WHERE tkategoriesichtbarkeit.kKategorie IS NULL {$exclude}
            GROUP BY p.kKategorie";

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_GET_QUERY,
            ['query' => &$query, 'builder' => $this]
        );

        return $query;
    }
}
