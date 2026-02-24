<?php declare(strict_types=1);

namespace Plugin\lfs_shopvote\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20241108000100 extends Migration implements IMigration
{
    /**
     * @inheritDoc
     */
    public function up()
    {
        // remove queue
        $this->execute("
            DROP TABLE IF EXISTS `xplugin_lfs_shopvote_reviewupdate_queue`
        ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS `xplugin_lfs_shopvote_reviewupdate_queue` (
                `sku` VARCHAR(255) NOT NULL UNIQUE,
                `kArtikel` INT NOT NULL,
                `added` DATETIME NOT NULL,
                `updated` DATETIME NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
}