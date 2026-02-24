<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5\Migrations;

use JTL\DB\ReturnType;
use JTL\Plugin\Migration;
use JTL\Update\IMigration;
use Plugin\s360_amazonpay_shop5\lib\Utils\Constants;
use Plugin\s360_amazonpay_shop5\lib\Utils\Database;

class Migration20190522000000 extends Migration implements IMigration {

    /**
     * Initial Migration.
     * Create all plugin tables.
     */
    public function up() {
        $this->createConfigTable();
        $this->createAccountMappingTable();
        $this->createChargePermissionTable();
        $this->createChargeTable();
        $this->createRefundTable();

        // Try to prefill tables from an existing (new) shop 4 plugin.
        $this->prefillTablesFromShop4Plugin();
        // Try to migrate account mappings only from existing (old) shop 4 plugin.
        $this->migrateAccountMappingTableFromShop4Plugin();
    }

    public function down() {
        if ($this->doDeleteData()) {
            $this->getDB()->executeQuery('DROP TABLE IF EXISTS `xplugin_s360_amazonpay_shop5_config`', ReturnType::DEFAULT);
            $this->getDB()->executeQuery('DROP TABLE IF EXISTS `xplugin_s360_amazonpay_shop5_accountmapping`', ReturnType::DEFAULT);
            $this->getDB()->executeQuery('DROP TABLE IF EXISTS `xplugin_s360_amazonpay_shop5_chargepermission`', ReturnType::DEFAULT);
            $this->getDB()->executeQuery('DROP TABLE IF EXISTS `xplugin_s360_amazonpay_shop5_charge`', ReturnType::DEFAULT);
            $this->getDB()->executeQuery('DROP TABLE IF EXISTS `xplugin_s360_amazonpay_shop5_refund`', ReturnType::DEFAULT);
        }

        // Delete the cronjob which might be installed - regularly, this is enabled / disabled by Config
        $this->getDB()->delete('tcron', 'jobType', Constants::CRON_JOB_TYPE_SYNC);
        $this->getDB()->delete('tjobqueue', 'jobType', Constants::CRON_JOB_TYPE_SYNC);
    }

    private function createConfigTable() {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_amazonpay_shop5_config` (
                      `id` int(10) NOT NULL AUTO_INCREMENT,
                      `configKey` varchar(255) NOT NULL,
                      `configValue` text,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci');
    }

    private function createAccountMappingTable() {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_amazonpay_shop5_accountmapping` (
                      `id` int(10) NOT NULL AUTO_INCREMENT,
                      `amazonUserId` varchar(255) NOT NULL,
                      `jtlCustomerId` int(10) DEFAULT -1,
                      `isVerified` int(10) DEFAULT 0,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci');
    }

    private function createChargePermissionTable() {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_amazonpay_shop5_chargepermission` (
                        `chargePermissionId` varchar(50) NOT NULL,
                        `buyerId` varchar(255) NOT NULL,
                        `buyerEmail` varchar(255) NOT NULL,
                        `buyerName` varchar(255) NOT NULL,
                        `shopOrderId` int(10) NOT NULL,
                        `shopOrderNumber` varchar(50),
                        `status` varchar(50) NOT NULL,
                        `statusReason` text,
                        `chargeAmountLimitAmount` varchar(50) NOT NULL,
                        `chargeAmountLimitCurrencyCode` varchar(50) NOT NULL,
                        `creationTimestamp` varchar(50) NOT NULL,
                        `expirationTimestamp` varchar(50) NOT NULL,
                        `releaseEnvironment` varchar(50) NOT NULL,
                        PRIMARY KEY (`chargePermissionId`)
                      ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci');
    }

    private function createChargeTable() {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_amazonpay_shop5_charge` (
                        `chargeId` varchar(50) NOT NULL,
                        `chargePermissionId` varchar(50) NOT NULL,
                        `status` varchar(50) NOT NULL,
                        `statusReason` text,
                        `chargeAmountAmount` varchar(50) NOT NULL,
                        `chargeAmountCurrencyCode` varchar(50) NOT NULL,
                        `captureAmountAmount` varchar(50),
                        `captureAmountCurrencyCode` varchar(50),
                        `refundedAmountAmount` varchar(50),
                        `refundedAmountCurrencyCode` varchar(50),
                        `creationTimestamp` varchar(50) NOT NULL,
                        `expirationTimestamp` varchar(50) NOT NULL,
                        PRIMARY KEY (`chargeId`)
                      ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci');
    }

    private function createRefundTable() {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_amazonpay_shop5_refund` (
                        `refundId` varchar(50) NOT NULL,
                        `chargeId` varchar(50) NOT NULL,
                        `status` varchar(50) NOT NULL,
                        `statusReason` text,
                        `refundAmountAmount` varchar(50) NOT NULL,
                        `refundAmountCurrencyCode` varchar(50) NOT NULL,
                        `creationTimestamp` varchar(50) NOT NULL,
                        PRIMARY KEY (`refundId`)
                      ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci');
    }

    private function prefillTablesFromShop4Plugin() {
        $oldAccountMappingTable = 'xplugin_s360_amazonpay_shop4_accountmapping';
        $oldConfigTable = 'xplugin_s360_amazonpay_shop4_config';
        $oldChargePermissionTable = 'xplugin_s360_amazonpay_shop4_chargepermission';
        $oldChargeTable = 'xplugin_s360_amazonpay_shop4_charge';
        $oldRefundTable = 'xplugin_s360_amazonpay_shop4_refund';
        if ($this->tableExists($oldAccountMappingTable) && $this->isTableEmpty(Database::PLUGIN_TABLE_NAME_ACCOUNTMAPPING)) {
            // only do something if the new table is empty - this should usually be the case, though
            $this->getDB()->executeQuery('INSERT INTO ' . Database::PLUGIN_TABLE_NAME_ACCOUNTMAPPING . ' (amazonUserId, jtlCustomerId, isVerified) SELECT amazonUserId, jtlCustomerId, isVerified FROM ' . $oldAccountMappingTable, ReturnType::AFFECTED_ROWS);
        }
        if ($this->tableExists($oldConfigTable) && $this->isTableEmpty(Database::PLUGIN_TABLE_NAME_CONFIG)) {
            $this->getDB()->executeQuery('INSERT INTO ' . Database::PLUGIN_TABLE_NAME_CONFIG . ' (id, configKey, configValue) SELECT id, configKey, configValue FROM ' . $oldConfigTable, ReturnType::AFFECTED_ROWS);
        }
        if ($this->tableExists($oldChargePermissionTable) && $this->isTableEmpty(Database::PLUGIN_TABLE_NAME_CHARGEPERMISSION)) {
            $this->getDB()->executeQuery('INSERT INTO ' . Database::PLUGIN_TABLE_NAME_CHARGEPERMISSION . ' (chargePermissionId, buyerId, buyerEmail, buyerName, shopOrderId, shopOrderNumber, status, statusReason, chargeAmountLimitAmount, chargeAmountLimitCurrencyCode, creationTimestamp, expirationTimestamp, releaseEnvironment) SELECT chargePermissionId, buyerId, buyerEmail, buyerName, shopOrderId, shopOrderNumber, status, statusReason, chargeAmountLimitAmount, chargeAmountLimitCurrencyCode, creationTimestamp, expirationTimestamp, releaseEnvironment FROM ' . $oldChargePermissionTable, ReturnType::AFFECTED_ROWS);
        }
        if ($this->tableExists($oldChargeTable) && $this->isTableEmpty(Database::PLUGIN_TABLE_NAME_CHARGE)) {
            $this->getDB()->executeQuery('INSERT INTO ' . Database::PLUGIN_TABLE_NAME_CHARGE . ' (chargeId, chargePermissionId, status, statusReason, chargeAmountAmount, chargeAmountCurrencyCode, captureAmountAmount, captureAmountCurrencyCode, refundedAmountAmount, refundedAmountCurrencyCode, creationTimestamp, expirationTimestamp) SELECT chargeId, chargePermissionId, status, statusReason, chargeAmountAmount, chargeAmountCurrencyCode, captureAmountAmount, captureAmountCurrencyCode, refundedAmountAmount, refundedAmountCurrencyCode, creationTimestamp, expirationTimestamp FROM ' . $oldChargeTable, ReturnType::AFFECTED_ROWS);
        }
        if ($this->tableExists($oldRefundTable) && $this->isTableEmpty(Database::PLUGIN_TABLE_NAME_REFUND)) {
            $this->getDB()->executeQuery('INSERT INTO ' . Database::PLUGIN_TABLE_NAME_REFUND . ' (refundId, chargeId, status, statusReason, refundAmountAmount, refundAmountCurrencyCode, creationTimestamp) SELECT refundId, chargeId, status, statusReason, refundAmountAmount, refundAmountCurrencyCode, creationTimestamp FROM ' . $oldRefundTable, ReturnType::AFFECTED_ROWS);

        }
    }

    private function migrateAccountMappingTableFromShop4Plugin(): void {
        $oldAccountMappingTable = 'xplugin_s360_amazon_lpa_shop4_taccountmapping';
        if ($this->tableExists($oldAccountMappingTable) && $this->isTableEmpty(Database::PLUGIN_TABLE_NAME_ACCOUNTMAPPING)) {
            // only do something if the new table is empty - this should usually be the case, though
            $this->getDB()->executeQuery('INSERT INTO ' . Database::PLUGIN_TABLE_NAME_ACCOUNTMAPPING . ' (amazonUserId, jtlCustomerId, isVerified) SELECT cAmazonId, kKunde, nVerifiziert FROM ' . $oldAccountMappingTable, ReturnType::AFFECTED_ROWS);
        }
    }

    private function tableExists($table) {
        $test = $this->getDB()->executeQuery('SHOW TABLES LIKE "' . $table . '"', ReturnType::ARRAY_OF_ASSOC_ARRAYS);
        return !empty($test);
    }

    private function isTableEmpty($table) {
        $test = $this->getDB()->selectAll($table, [], []);
        return \is_array($test) && empty($test);
    }

}
