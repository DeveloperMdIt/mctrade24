<?php

namespace Plugin\jtl_search\ExportModules;

use JTL\DB\DbInterface;

/**
 * Interface IItemData
 * @package Plugin\jtl_search\ExportModules
 */
interface IItemData
{
    /**
     * @param int  $id
     * @param int  $languageID
     * @param bool $noCache
     * @return mixed
     */
    public function loadFromDB(int $id, int $languageID = 0, bool $noCache = true);

    /**
     * @return int
     */
    public function getCount(): int;

    /**
     * @param DbInterface $db
     * @param int         $nLimitN
     * @param int         $nLimitM
     * @return array
     */
    public static function getItemKeys(DbInterface $db, int $nLimitN, int $nLimitM): array;

    /**
     * @return mixed
     */
    public function getFilledObject();
}
