<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\src\Export;

use JTL\Events\Dispatcher;

class BlogFeedBuilder extends AbstractFeedBuilder
{
    public const EVENT_PROCESS_ROW = 'process_blog_row';
    public const EVENT_GET_QUERY = 'get_blog_query';

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
        $idPrefix = $this->store->getSettings()?->getBlogIdPrefix();
        $sqlId = "tnews.kNews as id";

        if ($idPrefix) {
            $sqlId = "CONCAT('{$this->connection->escape_string($idPrefix)}', tnews.kNews) as id";
        }

        $query = "SELECT
                {$sqlId},
                'blog' as type,
                CONCAT('{$baseUrl}', tseo.cSeo) as url,
                CONCAT('{$baseUrl}', tnews.cPreviewImage) as image,
                tnewssprache.title as title,
                IF(tnewssprache.preview IS NULL or tnewssprache.preview = '', 'empty', tnewssprache.preview) as text
            FROM tnews
            JOIN tnewssprache ON tnewssprache.kNews = tnews.kNews AND tnewssprache.languageID = {$this->store->getLanguageId()}
            JOIN tseo ON tseo.cKey = 'kNews' AND tseo.kKey = tnews.kNews AND tseo.kSprache = {$this->store->getLanguageId()}
            WHERE
                tnews.nAktiv = 1
                AND tnews.dGueltigVon <= NOW()
                AND (tnews.cKundengruppe LIKE '%;-1;%'
                OR FIND_IN_SET('{$this->store->getCustomerGroupId()}', REPLACE(tnews.cKundengruppe, ';',',')) > 0)";

        Dispatcher::getInstance()->fire(
            's360_clerk_shop5.' . self::EVENT_GET_QUERY,
            ['query' => &$query, 'builder' => $this]
        );

        return $query;
    }
}
