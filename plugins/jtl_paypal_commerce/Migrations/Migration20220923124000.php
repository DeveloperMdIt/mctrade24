<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class Migration20220923124000
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20220923124000 extends Migration implements IMigration
{
    /**
     * @var string
     */
    protected $description = 'Remove shipment dependency from carrier mapping.';

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute(
            'DELETE FROM `xplugin_jtl_paypal_checkout_carrier_mapping`
                WHERE (carrier_wawi, kVersandart) IN (
                    SELECT * FROM (
                        SELECT innermap.carrier_wawi, MAX(innermap.kVersandart)
                        FROM xplugin_jtl_paypal_checkout_carrier_mapping innermap
                        GROUP BY innermap.carrier_wawi
                        HAVING COUNT(innermap.carrier_wawi) > 1
                    ) AS src
                )'
        );
        $this->execute(
            'ALTER TABLE `xplugin_jtl_paypal_checkout_carrier_mapping`
                DROP KEY `idx_shipping_uq`'
        );
        $this->execute(
            'ALTER TABLE `xplugin_jtl_paypal_checkout_carrier_mapping`
                DROP COLUMN `kVersandart`'
        );
        $this->execute(
            'ALTER TABLE `xplugin_jtl_paypal_checkout_carrier_mapping`
                ADD UNIQUE KEY `idx_carrier_uq` (`carrier_wawi`)'
        );
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute(
            'ALTER TABLE `xplugin_jtl_paypal_checkout_carrier_mapping`
                DROP KEY `idx_carrier_uq`'
        );
        $this->execute(
            'ALTER TABLE `xplugin_jtl_paypal_checkout_carrier_mapping`
                ADD COLUMN `kVersandart` INT NOT NULL DEFAULT 0 AFTER id'
        );
        $this->execute(
            'ALTER TABLE `xplugin_jtl_paypal_checkout_carrier_mapping`
                ADD UNIQUE KEY `idx_shipping_uq` (`kVersandart`, `carrier_wawi`)'
        );
    }
}
