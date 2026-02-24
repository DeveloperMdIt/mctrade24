<?php

declare(strict_types=1);

namespace Plugin\jtl_paypal_commerce\Migrations;

use JTL\Plugin\Migration;
use JTL\Update\IMigration;
use JTL\Plugin\Helper;
use Plugin\jtl_paypal_commerce\PPC\Configuration;

/**
 * Class Migration20240703174519
 */
class Migration20240703174519 extends Migration implements IMigration
{
    public function getAuthor(): string
    {
        return 'sl';
    }

    public function getDescription(): string
    {
        return 'Dismiss Giropay';
    }

    /**
     * @inheritdoc
     */
    public function up(): void
    {
        $plugin = Helper::getPluginById('jtl_paypal_commerce');
        if ($plugin !== null) {
            $this->getDB()->executeQueryPrepared(
                'DELETE FROM tplugineinstellungen
                    WHERE kPlugin = :pluginID
                        AND cName LIKE :settingName',
                [
                    'pluginID'    => $plugin->getID(),
                    'settingName' => 'jtl_paypal_commerce_giropay_APM_%'
                ]
            );
            //giropay aus jtl_paypal_commerce_paymentMethods_enabled entfernen
            $config  = Configuration::getInstance($plugin, $this->db);
            $enabled = \explode(',', $config->getPrefixedConfigItem('paymentMethods_enabled', ''));
            $key     = (int)array_search('giropay', $enabled);
            if ($key > 0) {
                unset($enabled[$key]);
            }
            $config->saveConfigItems(['paymentMethods_enabled' => \implode(',', $enabled)]);
        }
    }

    /**
     * @inheritdoc
     */
    public function down(): void
    {
    }
}
