<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class Migration202505170000
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20250517094500 extends Migration implements IMigration
{
    public function getAuthor(): ?string
    {
        return 'fp';
    }

    public function getDescription(): string
    {
        return 'New fields for package tracking';
    }

    /**
     * @inheritDoc
     * @noinspection SqlResolve
     */
    public function up(): void
    {
        $this->db->executeQueryPrepared(
            'DELETE FROM xplugin_jtl_paypal_checkout_shipment_state
                WHERE status_sent > 0
                    OR shipment_date < DATE_SUB(CURDATE(), INTERVAL :maxDays DAY)',
            [
                'maxDays' => \BESTELLUNG_VERSANDBESTAETIGUNG_MAX_TAGE
            ]
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                ADD COLUMN capture_id VARCHAR(64) NULL DEFAULT NULL AFTER transaction_id,
                ADD COLUMN delivery_note_id INT NOT NULL DEFAULT 0 AFTER tracking_id,
                ADD COLUMN carrier_name VARCHAR(64) NULL DEFAULT NULL AFTER carrier'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                DROP KEY idx_transaction_uq'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                ADD UNIQUE KEY idx_transaction_uq(transaction_id, tracking_id)'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                DROP KEY idx_delivery_date'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                ADD KEY idx_delivery_state_date(status_sent, delivery_date)'
        );
    }

    /**
     * @inheritDoc
     * @noinspection SqlResolve
     */
    public function down(): void
    {
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                DROP KEY idx_delivery_state_date'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                ADD KEY idx_delivery_date(delivery_date)'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                DROP KEY idx_transaction_uq'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                ADD UNIQUE KEY idx_transaction_uq(transaction_id)'
        );
        $this->execute(
            'ALTER TABLE xplugin_jtl_paypal_checkout_shipment_state
                DROP COLUMN capture_id,
                DROP COLUMN delivery_note_id,
                DROP COLUMN carrier_name'
        );
    }
}
