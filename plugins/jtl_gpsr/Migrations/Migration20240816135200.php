<?php

declare(strict_types=1);

namespace Plugin\jtl_gpsr\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class Migration20240816135200
 * @package Plugin\jtl_gpsr\Migrations
 */
class Migration20240816135200 extends Migration implements IMigration
{
    protected $description = 'Create setting table';

    /**
     * @inheritDoc
     */
    public function up(): void
    {
        $this->execute(
            'CREATE TABLE `xplugin_jtlgpsr_setting` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `key` VARCHAR(255) NOT NULL,
                `value` MEDIUMTEXT,
                UNIQUE KEY idx_key_uq (`key`)
            ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * @inheritDoc
     */
    public function down(): void
    {
        if ($this->doDeleteData()) {
            $this->execute(
                'DROP TABLE IF EXISTS `xplugin_jtlgpsr_setting`'
            );
        }
    }
}
