<?php

declare(strict_types=1);

namespace Plugin\s360_clerk_shop5\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20230418084859 extends Migration implements IMigration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_clerk_shop5_store` (
            `id` INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `api_key` VARCHAR(255) NULL,
            `lang_id` INT(10) NOT NULL,
            `customer_group` INT(10) NOT NULL,
            `state` VARCHAR(255),
            `state_message` TEXT NULL,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci;');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        if ($this->doDeleteData()) {
            $this->execute('DROP TABLE IF EXISTS `xplugin_s360_clerk_shop5_store`');
        }
    }
}
