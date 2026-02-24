<?php declare(strict_types=1);

namespace Plugin\lfs_shopvote\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20241108000200 extends Migration implements IMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `xplugin_lfs_shopvote_reviews`(
            `remote_id` VARCHAR(255) PRIMARY KEY NOT NULL,
            `sku` VARCHAR(255) NOT NULL,
            `article_id` INT NOT NULL,
            `author_name` VARCHAR(255) NOT NULL,
            `rating_value` FLOAT nOT NULL,
            `text` LONGTEXT NOT NULL DEFAULT '',
            `created_at` DATE NOT NULL DEFAULT '1900-01-01 00:00:00',
            INDEX `article` (`article_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS `xplugin_lfs_shopvote_reviews`");
    }
}