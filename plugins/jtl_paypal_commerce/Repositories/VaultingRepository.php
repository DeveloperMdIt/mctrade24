<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Repositories;

use stdClass;

/**
 * Class VaultingRepository
 * @package Plugin\jtl_paypal_commerce\Repositories
 */
class VaultingRepository extends AbstractPluginRepository
{
    public function getTableName(): string
    {
        return 'xplugin_jtl_paypal_checkout_vaulting';
    }

    public function get(int $customerId, int $paymentId, string $fundingSource): ?stdClass
    {
        return $this->db->getSingleObject(
            'SELECT vault_id, vault_customer, vault_status, shipping_hash
                FROM ' . $this->getTableName() . '
                WHERE customer_id = :cId
                    AND payment_id = :pId
                    AND funding_source = :fundingSource',
            [
                'cId' => $customerId,
                'pId' => $paymentId,
                'fundingSource' => $fundingSource,
            ]
        );
    }

    public function updateOrInsert(object $data, array $excludeUpdate): int
    {
        return $this->db->upsert(
            $this->getTableName(),
            $data,
            $excludeUpdate
        );
    }

    public function delete(string $idHash): bool
    {
        return $this->db->delete($this->getTableName(), 'vault_id_hash', $idHash) > -1;
    }

    public function getAddress(int $addressId, bool $returnShippingAddress = false): ?stdClass
    {
        $table     = $returnShippingAddress ? 'tlieferadresse' : 'trechnungsadresse';
        $keyColumn = $returnShippingAddress ? 'kLieferadresse' : 'kRechnungsadresse';

        return $this->db->getSingleObject(
            'SELECT cPLZ, cOrt, cStrasse, cHausnummer, cLand
                FROM ' . $table . ' WHERE ' . $keyColumn . ' = :shippingId',
            ['shippingId' => $addressId]
        );
    }
}
