<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\adminmenu;

use Illuminate\Support\Collection;
use JTL\DB\DbInterface;

/**
 * Class CarrierMapping
 * @package Plugin\jtl_paypal_commerce\adminmenu
 */
class CarrierMapping
{
    /** @var DbInterface */
    private DbInterface $db;

    /**
     * CarrierMapping constructor
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * @param int    $offset
     * @param int    $limit
     * @return Collection
     */
    public function getMappings(int $offset = 0, int $limit = 0): Collection
    {
        $params   = [];
        $limitSQL = '';
        $whereSQL = '';

        if ($offset >= 0 && $limit > 0) {
            $limitSQL         = 'LIMIT :offset, :limit';
            $params['offset'] = $offset;
            $params['limit']  = $limit;
        } elseif ($limit > 0) {
            $limitSQL        = 'LIMIT :limit';
            $params['limit'] = $limit;
        }

        return $this->db->getCollection(
            'SELECT map.id, map.carrier_wawi, map.carrier_paypal
                FROM xplugin_jtl_paypal_checkout_carrier_mapping AS map
               ' . $whereSQL . '
               ' . $limitSQL,
            $params
        )->map(static function (object $item) {
            $item->id = (int)$item->id;

            return $item;
        });
    }

    /**
     * @param int    $id
     * @param string $carrierWawi
     * @param string $carrierPaypal
     * @return int
     */
    public function addMapping(int $id, string $carrierWawi, string $carrierPaypal): int
    {
        $maping = (object)[
            'carrier_wawi'   => $carrierWawi,
            'carrier_paypal' => $carrierPaypal,
        ];
        if ($id > 0) {
            return $this->db->update('xplugin_jtl_paypal_checkout_carrier_mapping', 'id', $id, $maping);
        }

        return $this->db->upsert('xplugin_jtl_paypal_checkout_carrier_mapping', $maping, ['carrier_wawi']);
    }

    /**
     * @param int $id
     * @return int
     */
    public function deleteMapping(int $id): int
    {
        return $this->db->delete('xplugin_jtl_paypal_checkout_carrier_mapping', 'id', $id);
    }
}
