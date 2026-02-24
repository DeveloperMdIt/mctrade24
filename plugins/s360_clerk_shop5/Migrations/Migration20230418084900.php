<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20230418084900 extends Migration implements IMigration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_clerk_shop5_store_settings` (
            `store_id` INT(10) NOT NULL,
            `key` VARCHAR(255) NOT NULL,
            `value` TEXT,
            UNIQUE (`store_id`, `key`),
            FOREIGN KEY (`store_id`) REFERENCES `xplugin_s360_clerk_shop5_store`(id) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci;');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if ($this->doDeleteData()) {
            $this->execute('DROP TABLE IF EXISTS `xplugin_s360_clerk_shop5_store_settings`');
        }
    }
}
