<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class Migration20220405150932
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20220405150932 extends Migration implements IMigration
{
    /**
     * @var string
     */
    protected $description = 'Add table for shipment state.';

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute(
            'CREATE TABLE IF NOT EXISTS `xplugin_jtl_paypal_checkout_shipment_state` (
                `id`                INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `transaction_id`    VARCHAR(64)     NOT NULL,
                `tracking_id`       VARCHAR(512)    NOT NULL,
                `carrier`           VARCHAR(64)     NOT NULL,
                `shipment_date`     DATE            NOT NULL,
                `delivery_date`     DATE            NOT NULL,
                `status_sent`       INT             NOT NUll DEFAULT 0,
                `status_info`       VARCHAR(512)    NOT NULL DEFAULT \'\',
                UNIQUE KEY idx_transaction_uq (`transaction_id`),
                KEY idx_delivery_date (`delivery_date`)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->execute(
            'CREATE TABLE IF NOT EXISTS `xplugin_jtl_paypal_checkout_carrier_mapping` (
                `id`                INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `kVersandart`       INT         NOT NULL,
                `carrier_wawi`      VARCHAR(64) NOT NULL,
                `carrier_paypal`    VARCHAR(64) NOT NULL,
                UNIQUE KEY idx_shipping_uq (`kVersandart`, `carrier_wawi`)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        if ($this->doDeleteData()) {
            $this->execute('DROP TABLE IF EXISTS `xplugin_jtl_paypal_checkout_shipment_state`');
            $this->execute('DROP TABLE IF EXISTS `xplugin_jtl_paypal_checkout_carrier_mapping`');
        }
    }
}
