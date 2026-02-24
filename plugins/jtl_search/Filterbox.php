<?php

declare(strict_types=1);

namespace Plugin\jtl_search;

use JTL\DB\DbInterface;
use JTL\Shop;
use stdClass;

/**
 * Class Filterbox
 * @package Plugin\jtl_search
 */
class Filterbox
{
    /**
     * @param DbInterface $db
     * @return int|null
     * @throws \Exception
     */
    public static function create(DbInterface $db): ?int
    {
        $containers = Shop::Container()->getTemplateService()->getActiveTemplate()->getBoxLayout();
        if (!\is_array($containers) || !isset($containers['left'])) {
            return null;
        }
        $model = self::getModel($db);
        if ($model !== null) {
            $key              = null;
            $box              = new stdClass();
            $box->kBoxvorlage = $model->kBoxvorlage;
            $box->kCustomID   = $model->kCustomID;
            $box->kContainer  = 0;
            $box->cTitel      = $model->cName;
            // Linke Box vorhanden?
            if ($containers['left']) {
                $box->ePosition = 'left';
                $key            = $db->insert('tboxen', $box);
            } elseif ($containers['right']) { // Rechte Box vorhanden?
                $box->ePosition = 'right';
                $key            = $db->insert('tboxen', $box);
            }

            if ($key !== null) {
                $vis         = new stdClass();
                $vis->kBox   = $key;
                $vis->kSeite = $model->cVerfuegbar;
                $vis->nSort  = 1;
                $vis->bAktiv = 1;

                return $db->insert('tboxensichtbar', $vis);
            }
        }

        return null;
    }

    /**
     * @param DbInterface $db
     * @return stdClass|null
     */
    private static function getModel(DbInterface $db): ?stdClass
    {
        $data = $db->getSingleObject(
            "SELECT tboxvorlage.*
                FROM tplugin
                JOIN tboxvorlage ON tboxvorlage.kCustomID = tplugin.kPlugin
                WHERE tplugin.cPluginID = 'jtl_search'"
        );

        return $data !== null && $data->kBoxvorlage > 0 ? $data : null;
    }
}
