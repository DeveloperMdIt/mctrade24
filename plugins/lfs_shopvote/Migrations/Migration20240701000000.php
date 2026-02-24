<?php declare(strict_types=1);

namespace Plugin\lfs_shopvote\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 *
 */
class Migration20240701000000 extends Migration implements IMigration
{

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `xplugin_lfs_shopvote_reviewupdate_queue` (
                                `sku` VARCHAR(255) NOT NULL UNIQUE,
                                `kArtikel` INT NOT NULL,    
                                `added` DATETIME NOT NULL,
                                `updated` DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DROP TABLE IF EXISTS xplugin_lfs_shopvote_reviewupdate_queue");
    }
}