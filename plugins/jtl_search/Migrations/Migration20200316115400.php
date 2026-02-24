<?php

declare(strict_types=1);

namespace Plugin\jtl_search\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;

/**
 * Class Migration20200316115400
 * @package Plugin\jtl_search\migrations
 */
class Migration20200316115400 extends Migration implements IMigration
{
    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tjtlsearchexportqueue` (
                `kExportqueue` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `nLimitN` INT( 10 ) UNSIGNED NOT NULL ,
                `nLimitM` INT( 10 ) UNSIGNED NOT NULL ,
                `nExportMethod` INT( 10 ) UNSIGNED NOT NULL ,
                `bFinished` BOOLEAN NOT NULL ,
                `bLocked` BOOLEAN NOT NULL ,
                `dStartTime` TIMESTAMP NOT NULL ,
                `dLastRun` TIMESTAMP NOT NULL
            ) ENGINE = InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci';"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tjtlsearchserverdata` (
                `kId` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `cKey` VARCHAR( 255 ) NOT NULL ,
                `cValue` VARCHAR( 255 ) NOT NULL
          ) ENGINE = InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci'"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tjtlsearchdeltaexport` (
                `kId` int(10) NOT NULL, `eDocumentType` enum('product','manufacturer','category') NOT NULL,
                `bDelete` tinyint(4) DEFAULT '0',
                `dLastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`kId`,`eDocumentType`)
            ) ENGINE = InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci';"
        );
        $this->execute(
            "CREATE TABLE IF NOT EXISTS `tjtlsearchexportlanguage` (
                `kExportLanguage` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `cISO` varchar(3) NOT NULL,
                PRIMARY KEY (`kExportLanguage`)
            ) ENGINE = InnoDB CHARACTER SET='utf8' COLLATE='utf8_unicode_ci';"
        );

        $this->execute("DELETE FROM tcron WHERE jobType = 'JTLSearchExport'");

        $localizations = $this->getDB()->getSingleInt(
            'SELECT COUNT(*) AS cnt FROM tjtlsearchexportlanguage',
            'cnt'
        );
        if ($localizations === 0) {
            $languages = $this->getDB()->getObjects('SELECT cISO FROM tsprache ORDER BY cNameDeutsch');
            $this->execute('TRUNCATE TABLE tjtlsearchexportlanguage');
            foreach ($languages as $lang) {
                $this->getDB()->insert('tjtlsearchexportlanguage', (object)['cISO' => $lang->cISO]);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
        $this->execute("DELETE FROM tcron WHERE jobType = 'JTLSearchExport'");
        $this->execute('DROP TABLE IF EXISTS tjtlsearchexportqueue');
        $this->execute('DROP TABLE IF EXISTS tjtlsearchserverdata');
        $this->execute('DROP TABLE IF EXISTS tjtlsearchdeltaexport');
        $this->execute('DROP TABLE IF EXISTS tjtlsearchexportlanguage');
    }
}
