<?php declare(strict_types=1);

namespace Plugin\lfs_shopvote\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20200111120000 extends Migration implements IMigration
{
    public function up() {
        $this->execute("CREATE TABLE IF NOT EXISTS `xplugin_lfs_shopvote_article_reviews`(
                                    `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                                    `article_id` INT NOT NULL,
                                    `reviewpage` VARCHAR(255) NOT NULL,
                                    `rating_count` INT,
                                    `rating_value` FLOAT,
                                    `created_at` DATETIME NOT NULL,
                                    `updated_at` DATETIME NOT NULL
                              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->execute("CREATE TABLE IF NOT EXISTS `xplugin_lfs_shopvote_article_review_items`(
                                    `id` VARCHAR(255),
                                    `review_id` INT NOT NULL,
                                    `author` VARCHAR(255) NOT NULL,
                                    `rating_value` VARCHAR(3) NOT NULL,
                                    `rating_text` LONGTEXT NOT NULL,
                                    `created_at` DATE NOT NULL
                              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->execute("CREATE TABLE IF NOT EXISTS `xplugin_lfs_shopvote_config`(
                                    `cName` VARCHAR(255) NOT NULL PRIMARY KEY,
                                    `cWert` LONGTEXT NOT NULL
                              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        $this->execute("INSERT IGNORE INTO xplugin_lfs_shopvote_config SET cName = 'sv_graphic_code'");
    }

    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS `xplugin_lfs_shopvote_article_reviews`");
        $this->execute('DROP TABLE IF EXISTS `xplugin_lfs_shopvote_article_review_items`');
        $this->execute('DROP TABLE IF EXISTS `xplugin_lfs_shopvote_config`');
    }
}