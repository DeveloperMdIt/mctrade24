<?php declare(strict_types = 1);

namespace Plugin\s360_amazonpay_shop5\Migrations;

use JTL\DB\ReturnType;
use JTL\Plugin\Migration;
use JTL\Update\IMigration;

class Migration20210902000000 extends Migration implements IMigration {

    /**
     * Initial Migration.
     * Create all plugin tables.
     */
    public function up() {
        $this->createSubscriptionTable();
        $this->createSubscriptionOrderTable();
        $this->modifyChargeTable();
    }

    public function down() {
        if ($this->doDeleteData()) {
            $this->getDB()->executeQuery('DROP TABLE IF EXISTS `xplugin_s360_amazonpay_shop5_subscription`', ReturnType::DEFAULT);
            $this->getDB()->executeQuery('DROP TABLE IF EXISTS `xplugin_s360_amazonpay_shop5_subscription_order`', ReturnType::DEFAULT);
            $test = $this->getDB()->executeQuery('SHOW TABLES LIKE \'xplugin_s360_amazonpay_shop5_charge\'', ReturnType::AFFECTED_ROWS);
            if(!empty($test)) {
                $test = $this->getDB()->executeQuery('SHOW COLUMNS FROM `xplugin_s360_amazonpay_shop5_charge` LIKE \'shopOrderId\'', ReturnType::AFFECTED_ROWS);
                if(!empty($test)) {
                    $this->getDB()->executeQuery('ALTER TABLE `xplugin_s360_amazonpay_shop5_charge` DROP COLUMN `shopOrderId`', ReturnType::DEFAULT);
                }
            }
        }
    }

    private function createSubscriptionTable() {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_amazonpay_shop5_subscription` (
                      `id` int(10) NOT NULL AUTO_INCREMENT,
                      `shopOrderId` int(10) NOT NULL,
                      `shopOrderNumber` varchar(50) NOT NULL,
                      `jtlCustomerId`int(10) NOT NULL,
                      `interval` varchar(255) NOT NULL,
                      `lastOrderTimestamp` int(10),
                      `nextOrderTimestamp` int(10),
                      `chargePermissionId` varchar(50) NOT NULL,
                      `status` varchar(50) NOT NULL,
                      `statusReason` varchar(512) DEFAULT "",
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci');
    }

    private function createSubscriptionOrderTable() {
        $this->execute('CREATE TABLE IF NOT EXISTS `xplugin_s360_amazonpay_shop5_subscription_order` (
                      `subscriptionId` int(10) NOT NULL,
                      `shopOrderId` int(10) NOT NULL,
                      `initialShopOrderId` int(10) NOT NULL,
                      PRIMARY KEY (`subscriptionId`, `shopOrderId`)
                    ) ENGINE=InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci');
    }

    private function modifyChargeTable() {
        $test = $this->getDB()->executeQuery('SHOW COLUMNS FROM `xplugin_s360_amazonpay_shop5_charge` LIKE \'shopOrderId\'', ReturnType::AFFECTED_ROWS);
        if(empty($test)) {
            $this->execute('ALTER TABLE `xplugin_s360_amazonpay_shop5_charge` ADD COLUMN `shopOrderId` int(10) NOT NULL DEFAULT 0');
        }
    }


}
