<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Repositories;

use Illuminate\Support\Collection;
use JTL\DB\ReturnType;
use Plugin\jtl_paypal_commerce\PPC\Order\PackageTracking\Carrier;

/**
 * Class OrderRepository
 * @package Plugin\jtl_paypal_commerce\Repositories
 */
class TrackingRepository extends AbstractPluginRepository
{
    public function getTableName(): string
    {
        return 'xplugin_jtl_paypal_checkout_shipment_state';
    }

    public function delete(int $stateId): int
    {
        return $this->db->delete('xplugin_jtl_paypal_checkout_shipment_state', 'id', $stateId);
    }

    public function deleteByTrackingId(int $orderId, string $trackingId): bool
    {
        return $this->db->executeQueryPrepared(
            'DELETE shipment_state
                    FROM xplugin_jtl_paypal_checkout_shipment_state AS shipment_state
                    INNER JOIN tzahlungsid ON tzahlungsid.txn_id = shipment_state.transaction_id
                    WHERE shipment_state.tracking_id = :tracking_id
                    AND tzahlungsid.kBestellung = :orderId',
            [
                'orderId'       => $orderId,
                'tracking_id'   => $trackingId,
            ],
            ReturnType::AFFECTED_ROWS
        ) > -1;
    }

    public function deleteByDate(int $maxDays, int $maxSentCount): bool
    {
        return $this->db->executeQueryPrepared(
            'DELETE FROM xplugin_jtl_paypal_checkout_shipment_state
                WHERE status_sent >= :maxSent
                    OR shipment_date < DATE_SUB(CURDATE(), INTERVAL :maxDays DAY)',
            [
                'maxDays' => $maxDays,
                'maxSent' => $maxSentCount,
            ]
        );
    }

    public function updateShipmentDate(int $orderId, string $trackingId, string $newShipmentDate): int
    {
        return $this->db->executeQueryPrepared(
            'UPDATE xplugin_jtl_paypal_checkout_shipment_state AS shipment_state
                INNER JOIN tzahlungsid
                        ON tzahlungsid.txn_id = shipment_state.transaction_id
                INNER JOIN tbestellung ON tbestellung.kBestellung = tzahlungsid.kBestellung
                INNER JOIN tversandart ON tversandart.kVersandart = tbestellung.kVersandart
                SET shipment_state.shipment_date = :shipment_date,
                    shipment_state.delivery_date = ADDDATE(:shipment_date, INTERVAL tversandart.nMaxLiefertage DAY)
                WHERE shipment_state.tracking_id = :tracking_id
                    AND tzahlungsid.kBestellung = :orderId',
            [
                'orderId'       => $orderId,
                'tracking_id'   => $trackingId,
                'shipment_date' => $newShipmentDate,
            ],
            ReturnType::AFFECTED_ROWS
        );
    }

    /**
     * @param int    $deliveryNote
     * @param int[]  $paymentIds
     * @param object $shippingData
     * @return object|null
     */
    public function getTrackingByDeliveryNote(int $deliveryNote, array $paymentIds, object $shippingData): ?object
    {
        return $this->db->getSingleObject(
            'SELECT tzahlungsid.txn_id AS transaction_id, tzahlungseingang.cHinweis AS capture_id,
                    :identCode AS tracking_id,
                    tlieferschein.kLieferschein AS delivery_note_id,
                    COALESCE(carrier.carrier_paypal, :carrierOther) AS carrier, :logistik AS carrier_name,
                    CURDATE() AS shipment_date, \'\' AS delivery_date, 0 AS status_sent, \'\' AS status_info
                FROM tlieferschein
                INNER JOIN tzahlungseingang ON tzahlungseingang.kBestellung = tlieferschein.kInetBestellung
                INNER JOIN tzahlungsid ON tzahlungsid.kBestellung = tlieferschein.kInetBestellung
                LEFT JOIN xplugin_jtl_paypal_checkout_carrier_mapping AS carrier ON carrier.carrier_wawi = :logistik
                WHERE tlieferschein.kLieferschein = :deliveryNoteId
                    AND tzahlungsid.kZahlungsart IN (' . \implode(', ', $paymentIds) . ')
                    AND tzahlungseingang.cHinweis RLIKE :idRegEx',
            [
                'identCode'      => $shippingData->cIdentCode,
                'logistik'       => $shippingData->cLogistik,
                'carrierOther'   => Carrier::CARRIER_OTHER,
                'deliveryNoteId' => $deliveryNote,
                'idRegEx'        => '^[A-Z0-9]+$',
            ]
        );
    }

    public function getItemsToTrack(int $deliveryNoteId): Collection
    {
        return $this->db->getCollection(
            'SELECT tlieferscheinpos.fAnzahl, twarenkorbpos.cName, twarenkorbpos.cArtNr,
                    twarenkorbpos.cEinheit, twarenkorbpos.kArtikel
                    FROM tlieferscheinpos
                    INNER JOIN twarenkorbpos ON twarenkorbpos.kBestellpos = tlieferscheinpos.kBestellPos
                    WHERE tlieferscheinpos.kLieferschein = :deliveryNoteId',
            [
                'deliveryNoteId' => $deliveryNoteId,
            ]
        );
    }

    public function getItemsToSend(int $stateAvail, int $maxItems, int &$itemsAvailable): Collection
    {
        $itemsAvailable = $this->db->getSingleInt(
            'SELECT COUNT(*) AS cnt
                FROM xplugin_jtl_paypal_checkout_shipment_state
                WHERE status_sent < :stateAvail',
            'cnt',
            [
                'stateAvail' => $stateAvail,
            ]
        );

        return $this->db->getCollection(
            'SELECT id, transaction_id, capture_id, tracking_id, carrier, carrier_name,
                    shipment_date, delivery_note_id, status_sent
                FROM xplugin_jtl_paypal_checkout_shipment_state
                WHERE status_sent < :stateAvail
                ORDER BY delivery_date
                LIMIT :maxItems',
            [
                'stateAvail' => $stateAvail,
                'maxItems'   => $maxItems,
            ]
        );
    }

    public function updateOrInsert(object $trackingData): int
    {
        return $this->db->upsert('xplugin_jtl_paypal_checkout_shipment_state', $trackingData, [
            'transaction_id', 'tracking_id', 'shipment_date', 'delivery_date',
        ]);
    }

    public function updateSentState(int $stateId, int $status): void
    {
        $this->db->update('xplugin_jtl_paypal_checkout_shipment_state', 'id', $stateId, (object)[
            'status_sent' => $status,
        ]);
    }

    public function updateFailedState(int $stateId, string $stateInfo): void
    {
        $this->db->executeQueryPrepared(
            'UPDATE xplugin_jtl_paypal_checkout_shipment_state
                        SET status_sent = status_sent + 1,
                            status_info = :stateInfo
                        WHERE id = :stateId',
            [
                'stateId'   => $stateId,
                'stateInfo' => $stateInfo,
            ]
        );
    }
}
