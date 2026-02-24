<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class Migration20241113112000
 * @package Plugin\jtl_paypal_commerce\Migrations
 */
class Migration20241113112000 extends Migration implements IMigration
{
    /**
     * @inheritDoc
     */
    public function getAuthor(): ?string
    {
        return 'fp';
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return /** @lang text */ 'Create table for vaulting';
    }

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute(
            'CREATE TABLE `xplugin_jtl_paypal_checkout_vaulting` (
                `id`                INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `customer_id`       INT             NOT NULL DEFAULT 0,
                `payment_id`        INT             NOT NULL DEFAULT 0,
                `funding_source`    VARCHAR(32)     NOT NULL,
                `vault_id`          VARCHAR(64)     NOT NULL,
                `vault_status`      VARCHAR(32)     NOT NULL,
                `vault_customer`    VARCHAR(64)     NOT NULL,
                `shipping_hash`     VARCHAR(64)     NOT NULL,
                CONSTRAINT UNIQUE KEY `idx_customer_payment_uq` (`customer_id`, `payment_id`, `funding_source`),
                CONSTRAINT UNIQUE KEY `idx_vault_id` (`vault_id`),
                CONSTRAINT UNIQUE KEY `idx_vault_customer` (`vault_customer`)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        $this->execute('DROP TABLE IF EXISTS `xplugin_jtl_paypal_checkout_vaulting`');
    }
}
