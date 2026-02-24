<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JTL\Events\Dispatcher;

class PageFeedBuilder extends AbstractFeedBuilder
{
    public const EVENT_PROCESS_ROW = 'process_pages_row';
    public const EVENT_GET_QUERY = 'get_pages_query';

    public function processRow(array $row): array
    {
        $row['title'] = strip_tags(html_entity_decode($row['title']));

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_PROCESS_ROW,
            ['row' => &$row, 'builder' => $this]
        );

        return $row;
    }

    public function getSqlQuery(): string
    {
        $baseUrl = rtrim(URL_SHOP, '/') . '/';
        $idPrefix = $this->store->getSettings()?->getCmsIdPrefix();
        $sqlId = "tlink.kLink as id";

        if ($idPrefix) {
            $sqlId = "CONCAT('{$this->connection->escape_string($idPrefix)}', tlink.kLink) as id";
        }

        $query = "SELECT
                {$sqlId},
                'cms-page' as type,
                tlinksprache.cName as name,
                tlink.nLinkart as linkType,
                IF(tlinksprache.cSeo LIKE '%https://%', tlinksprache.cSeo, CONCAT('{$baseUrl}', tlinksprache.cSeo)) as url,
                CONCAT('empty') as image,
                IF(tlinksprache.cTitle IS NULL or tlinksprache.cTitle = '',tlinksprache.cName,tlinksprache.cTitle) as title,
                IF(tlinksprache.cContent IS NULL or tlinksprache.cContent = '', 'empty', tlinksprache.cContent) as text
            FROM tlink
            JOIN tlinksprache ON tlinksprache.kLink = tlink.kLink
            JOIN tsprache ON tsprache.cISO = tlinksprache.cISOSprache AND tsprache.kSprache = {$this->store->getLanguageId()}
            JOIN tlinkgroupassociations ON tlinkgroupassociations.linkID = tlink.kLink
            JOIN tlinkgruppe ON tlinkgroupassociations.linkGroupID = tlinkgruppe.kLinkgruppe AND tlinkgruppe.cName != 'hidden'
            WHERE tlink.bIsActive = 1 AND (
                tlink.cKundengruppen IS NULL OR tlink.cKundengruppen = 'NULL'
                OR FIND_IN_SET('{$this->store->getCustomerGroupId()}', REPLACE(tlink.cKundengruppen, ';', ',')) > 0
            )";

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_GET_QUERY,
            ['query' => &$query, 'builder' => $this]
        );

        return $query;
    }
}
