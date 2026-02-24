<?php declare(strict_types=1);

namespace Plugin\lfs_it_recht_kanzlei\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 *
 */
class Migration20190407100000 extends Migration implements IMigration
{
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_lfs_it_recht_kanzlei_tLog` 
                            (
                                `kUpdate` INT NOT NULL AUTO_INCREMENT,
                                `dLetzterPush` datetime  NOT NULL,
                                `cDokuArt` varchar(20) NOT NULL,
                                `cStatus` varchar(10) NOT NULL,
                                `cSprache` varchar(10) NOT NULL,
                                `cPDFName` varchar(40) NOT NULL, 
                                PRIMARY KEY (`kUpdate`)
                            )  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');

        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_lfs_it_recht_kanzlei_kundengruppensyncblock` 
                                (
                                    kKundengruppe INT,
                                    created_at DATETIME NOT NULL
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;');
    }

    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `xplugin_lfs_it_recht_kanzlei_tLog`');
        $this->execute('DROP TABLE IF EXISTS `xplugin_lfs_it_recht_kanzlei_kundengruppensyncblock`');
    }
}
